<?php

namespace App\Http\Controllers\IsiPlaza;

use App\Enums\AccessStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateSellerProfileRequest;
use App\Http\Resources\Admin\SellerDetailResource;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VendedoresPanelController extends Controller
{
    public function show(User $user): Response
    {
        abort_unless($user->role === UserRole::Mayorista, 404);

        $user->load([
            'sellerProfile.businessCategory',
            'sellerProfile.catalogImages',
            'sellerProfile.doctorServices.treatment.section',
        ]);

        return Inertia::render('isi-plaza/medico-detalle', [
            'seller' => SellerDetailResource::make($user)->resolve(),
        ]);
    }

    public function index(): RedirectResponse
    {
        return redirect()->route('isi-plaza.gestion');
    }

    public function update(UpdateSellerProfileRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Mayorista, 404);

        $existing = SellerProfile::query()->where('user_id', $user->id)->first();

        $data = array_merge($request->validated(), ['user_id' => $user->id]);

        if (isset($data['access_status'])) {
            $incoming = AccessStatus::from($data['access_status']);
            $wasActive = $existing?->access_status === AccessStatus::Active;

            if ($incoming === AccessStatus::Active && ! $wasActive) {
                $data['subscription_granted_at'] = now();
                $data['subscription_expires_at'] = now()->addDays(30);
            }

            if ($incoming === AccessStatus::Denied || $incoming === AccessStatus::Pending) {
                $data['subscription_granted_at'] = null;
                $data['subscription_expires_at'] = null;
            }
        }

        SellerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return redirect()->route('isi-plaza.gestion')->with('success', 'Perfil de médico actualizado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Mayorista, 404);
        $user->delete();

        return redirect()->route('isi-plaza.gestion')->with('success', 'Cuenta de médico eliminada.');
    }
}
