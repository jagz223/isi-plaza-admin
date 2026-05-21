<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\AccessStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\ForgotSellerPasswordRequest;
use App\Http\Requests\Api\Seller\LoginSellerRequest;
use App\Http\Requests\Api\Seller\RegisterSellerRequest;
use App\Http\Resources\Seller\SellerAccountResource;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterSellerRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => UserRole::Mayorista,
        ]);

        SellerProfile::query()->create([
            'user_id' => $user->id,
            'access_status' => AccessStatus::Pending,
        ]);

        $token = $user->createToken('seller-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => SellerAccountResource::make($user->load('sellerProfile')),
        ], 201);
    }

    public function login(LoginSellerRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->where('role', UserRole::Mayorista)
            ->first();

        if ($user === null || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('seller-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => SellerAccountResource::make($user->load('sellerProfile')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    public function me(Request $request): SellerAccountResource
    {
        return SellerAccountResource::make(
            $request->user()->load(['sellerProfile.businessCategory', 'sellerProfile.catalogImages'])
        );
    }

    public function forgotPassword(ForgotSellerPasswordRequest $request): JsonResponse
    {
        $userExists = User::query()
            ->where('email', $request->validated('email'))
            ->where('role', UserRole::Mayorista)
            ->exists();

        if ($userExists) {
            Password::sendResetLink(['email' => $request->validated('email')]);
        }

        return response()->json([
            'message' => 'Si el correo está registrado como mayorista, recibirás instrucciones para restablecer la contraseña.',
        ]);
    }
}
