<?php

namespace App\Http\Controllers\IsiPlaza;

use App\Http\Controllers\Controller;
use App\Support\AdminPanelTokenValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccessController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('isi-plaza/access');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'min:9', 'max:15'],
        ]);

        $token = AdminPanelTokenValidator::firstMatchingActivePlain($request->input('token'), true);

        if ($token === null) {
            return back()->withErrors(['token' => 'El token no es válido o está inactivo.'])->withInput($request->only('token'));
        }

        $request->session()->put('isi_plaza_admin_token_id', $token->id);

        return redirect()->route('isi-plaza.gestion');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('isi_plaza_admin_token_id');

        return redirect()->route('isi-plaza.access');
    }
}
