<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Enums\SocialProvider;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Consumer\SocialLoginRequest;
use App\Http\Resources\Consumer\ConsumerAccountResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function socialLogin(SocialLoginRequest $request): JsonResponse
    {
        $provider = SocialProvider::from($request->validated('provider'));
        $providerId = $request->validated('provider_id');
        $email = $request->validated('email');
        $name = $request->validated('name');

        $user = User::query()
            ->where('provider', $provider->value)
            ->where('provider_id', $providerId)
            ->first();

        if ($user === null) {
            $user = User::query()
                ->where('email', $email)
                ->where('role', UserRole::Comprador)
                ->first();

            if ($user !== null) {
                $user->update([
                    'provider' => $provider->value,
                    'provider_id' => $providerId,
                    'name' => $name,
                ]);
            }
        }

        if ($user === null) {
            $existingOtherRole = User::query()->where('email', $email)->exists();

            if ($existingOtherRole) {
                throw ValidationException::withMessages([
                    'email' => ['Este correo ya está registrado con otro tipo de cuenta.'],
                ]);
            }

            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::password(32)),
                'role' => UserRole::Comprador,
                'provider' => $provider->value,
                'provider_id' => $providerId,
            ]);
        }

        if ($user->role !== UserRole::Comprador) {
            throw ValidationException::withMessages([
                'email' => ['Este correo pertenece a una cuenta que no es de comprador.'],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('consumer-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => ConsumerAccountResource::make($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    public function me(Request $request): ConsumerAccountResource
    {
        return ConsumerAccountResource::make($request->user());
    }
}
