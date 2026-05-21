<?php

namespace App\Support;

use App\Models\AdminToken;
use Illuminate\Support\Facades\Hash;

class AdminPanelTokenValidator
{
    /**
     * Busca un token de panel activo que coincida con el texto plano (bcrypt).
     */
    public static function firstMatchingActivePlain(string $plain, bool $touchLastUsed = true): ?AdminToken
    {
        if (strlen($plain) < 9 || strlen($plain) > 15) {
            return null;
        }

        $tokens = AdminToken::query()->where('is_active', true)->get();

        foreach ($tokens as $adminToken) {
            if (Hash::check($plain, $adminToken->token_hash)) {
                if ($touchLastUsed) {
                    $adminToken->forceFill(['last_used_at' => now()])->saveQuietly();
                }

                return $adminToken;
            }
        }

        return null;
    }
}
