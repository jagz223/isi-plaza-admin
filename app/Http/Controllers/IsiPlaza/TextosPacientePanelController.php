<?php

namespace App\Http\Controllers\IsiPlaza;

use App\Http\Controllers\Controller;
use App\Support\ConsumerAppSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TextosPacientePanelController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('isi-plaza/textos-paciente', [
            'settings' => ConsumerAppSettings::formFields(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'external_contact_disclaimer' => ['required', 'string', 'max:2000'],
            'app_store_url' => ['nullable', 'string', 'max:500'],
            'play_store_url' => ['nullable', 'string', 'max:500'],
            'privacy_notice' => ['required', 'string', 'max:2000'],
        ]);

        ConsumerAppSettings::updateMany([
            ConsumerAppSettings::EXTERNAL_CONTACT_DISCLAIMER => $validated['external_contact_disclaimer'],
            ConsumerAppSettings::APP_STORE_URL => $validated['app_store_url'] ?? '',
            ConsumerAppSettings::PLAY_STORE_URL => $validated['play_store_url'] ?? '',
            ConsumerAppSettings::PRIVACY_NOTICE => $validated['privacy_notice'],
        ]);

        return redirect()
            ->route('isi-plaza.textos-paciente.index')
            ->with('success', 'Textos de la app paciente actualizados.');
    }
}
