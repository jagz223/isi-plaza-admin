<?php

namespace App\Http\Controllers\IsiPlaza;

use App\Http\Controllers\Controller;
use App\Http\Requests\IsiPlaza\UpdateTextosNumerosRequest;
use App\Support\SellerAppSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TextosNumerosPanelController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('isi-plaza/textos-numeros', [
            'settings' => SellerAppSettings::formFields(),
        ]);
    }

    public function update(UpdateTextosNumerosRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        SellerAppSettings::updateMany([
            SellerAppSettings::SUBSCRIPTION_PLAN_LABEL => $validated['subscription_plan_label'],
            SellerAppSettings::SUBSCRIPTION_PRICE_LABEL => $validated['subscription_price_label'],
            SellerAppSettings::SUBSCRIPTION_MESSAGE_PENDING => $validated['subscription_message_pending'],
            SellerAppSettings::SUBSCRIPTION_MESSAGE_ACTIVE => $validated['subscription_message_active'],
            SellerAppSettings::SUBSCRIPTION_WHATSAPP_URL => $validated['subscription_whatsapp_url'],
            SellerAppSettings::PROMOTION_WHATSAPP_URL => $validated['promotion_whatsapp_url'],
            SellerAppSettings::SUBSCRIBE_BUTTON_LABEL => $validated['subscribe_button_label'],
            SellerAppSettings::PROMOTION_BUTTON_LABEL => $validated['promotion_button_label'],
        ]);

        return redirect()
            ->route('isi-plaza.textos-numeros.index')
            ->with('success', 'Textos y números actualizados correctamente.');
    }
}
