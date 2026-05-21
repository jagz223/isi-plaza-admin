<?php

namespace App\Http\Controllers\IsiPlaza;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreAdminTokenRequest;
use App\Http\Resources\Admin\AdminPanelTokenResource;
use App\Models\AdminToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TokensPanelController extends Controller
{
    public function index(): Response
    {
        $tokens = AdminToken::query()->orderByDesc('id')->get();

        return Inertia::render('isi-plaza/tokens', [
            'tokens' => AdminPanelTokenResource::collection($tokens),
            'flashPlainToken' => session()->pull('plain_token'),
        ]);
    }

    public function store(StoreAdminTokenRequest $request): RedirectResponse
    {
        $length = random_int(9, 15);
        $plain = Str::random($length);

        AdminToken::query()->create([
            'token_hash' => Hash::make($plain),
            'description' => $request->input('description'),
            'is_active' => true,
        ]);

        return redirect()->route('isi-plaza.ajustes-acceso.index')->with('plain_token', $plain);
    }

    public function destroy(AdminToken $adminToken): RedirectResponse
    {
        if ((int) session('isi_plaza_admin_token_id') === $adminToken->id) {
            return redirect()->route('isi-plaza.ajustes-acceso.index')->withErrors([
                'token' => 'No puedes eliminar el token con el que iniciaste sesión.',
            ]);
        }

        $adminToken->delete();

        return redirect()->route('isi-plaza.ajustes-acceso.index')->with('success', 'Token eliminado.');
    }
}
