<?php

namespace App\Http\Controllers\IsiPlaza;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class BuyersPanelController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('isi-plaza.gestion');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Comprador, 404);
        $user->delete();

        return redirect()->route('isi-plaza.gestion')->with('success', 'Cuenta de comprador eliminada.');
    }
}
