<?php

namespace App\Support;

use App\Models\AppSetting;

class ConsumerAppSettings
{
    public const EXTERNAL_CONTACT_DISCLAIMER = 'consumer.external_contact_disclaimer';

    public const APP_STORE_URL = 'consumer.app_store_url';

    public const PLAY_STORE_URL = 'consumer.play_store_url';

    public const PRIVACY_NOTICE = 'consumer.privacy_notice';

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return [
            self::EXTERNAL_CONTACT_DISCLAIMER,
            self::APP_STORE_URL,
            self::PLAY_STORE_URL,
            self::PRIVACY_NOTICE,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            self::EXTERNAL_CONTACT_DISCLAIMER => 'Al contactar a un médico por WhatsApp u otros medios externos, la comunicación y el pago quedan fuera de Odontica.',
            self::APP_STORE_URL => '',
            self::PLAY_STORE_URL => '',
            self::PRIVACY_NOTICE => 'Odontica es un directorio informativo. No sustituye una consulta médica presencial.',
        ];
    }

    public static function get(string $key): string
    {
        $stored = AppSetting::query()->where('key', $key)->value('value');

        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        return self::defaults()[$key] ?? '';
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        $stored = AppSetting::query()
            ->whereIn('key', self::keys())
            ->pluck('value', 'key')
            ->all();

        return array_merge(self::defaults(), $stored);
    }

    /**
     * @param  array<string, string>  $values
     */
    public static function updateMany(array $values): void
    {
        foreach ($values as $key => $value) {
            AppSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }
    }

    /**
     * @return array<string, string>
     */
    public static function formFields(): array
    {
        $all = self::all();

        return [
            'external_contact_disclaimer' => $all[self::EXTERNAL_CONTACT_DISCLAIMER],
            'app_store_url' => $all[self::APP_STORE_URL],
            'play_store_url' => $all[self::PLAY_STORE_URL],
            'privacy_notice' => $all[self::PRIVACY_NOTICE],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function publicPayload(): array
    {
        return self::formFields();
    }
}
