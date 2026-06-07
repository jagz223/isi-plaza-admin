<?php

namespace App\Support;

use App\Models\AppSetting;

class SellerAppSettings
{
    public const SUBSCRIPTION_PLAN_LABEL = 'seller.subscription_plan_label';

    public const SUBSCRIPTION_PRICE_LABEL = 'seller.subscription_price_label';

    public const SUBSCRIPTION_MESSAGE_PENDING = 'seller.subscription_message_pending';

    public const SUBSCRIPTION_MESSAGE_ACTIVE = 'seller.subscription_message_active';

    public const SUBSCRIPTION_WHATSAPP_URL = 'seller.subscription_whatsapp_url';

    public const PROMOTION_WHATSAPP_URL = 'seller.promotion_whatsapp_url';

    public const SUBSCRIBE_BUTTON_LABEL = 'seller.subscribe_button_label';

    public const PROMOTION_BUTTON_LABEL = 'seller.promotion_button_label';

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return [
            self::SUBSCRIPTION_PLAN_LABEL,
            self::SUBSCRIPTION_PRICE_LABEL,
            self::SUBSCRIPTION_MESSAGE_PENDING,
            self::SUBSCRIPTION_MESSAGE_ACTIVE,
            self::SUBSCRIPTION_WHATSAPP_URL,
            self::PROMOTION_WHATSAPP_URL,
            self::SUBSCRIBE_BUTTON_LABEL,
            self::PROMOTION_BUTTON_LABEL,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            self::SUBSCRIPTION_PLAN_LABEL => 'Plan mayorista',
            self::SUBSCRIPTION_PRICE_LABEL => (string) config('isi-plaza.seller.subscription_price_label'),
            self::SUBSCRIPTION_MESSAGE_PENDING => 'Contacta por WhatsApp para completar el pago. El administrador activará tu cuenta desde el panel.',
            self::SUBSCRIPTION_MESSAGE_ACTIVE => 'Tu suscripción está activa. Puedes usar el resto de la aplicación.',
            self::SUBSCRIPTION_WHATSAPP_URL => (string) config('isi-plaza.seller.subscription_whatsapp_url'),
            self::PROMOTION_WHATSAPP_URL => (string) config('isi-plaza.seller.promotion_whatsapp_url'),
            self::SUBSCRIBE_BUTTON_LABEL => 'Suscribirme',
            self::PROMOTION_BUTTON_LABEL => 'Comprar promoción (banners)',
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
            'subscription_plan_label' => $all[self::SUBSCRIPTION_PLAN_LABEL],
            'subscription_price_label' => $all[self::SUBSCRIPTION_PRICE_LABEL],
            'subscription_message_pending' => $all[self::SUBSCRIPTION_MESSAGE_PENDING],
            'subscription_message_active' => $all[self::SUBSCRIPTION_MESSAGE_ACTIVE],
            'subscription_whatsapp_url' => $all[self::SUBSCRIPTION_WHATSAPP_URL],
            'promotion_whatsapp_url' => $all[self::PROMOTION_WHATSAPP_URL],
            'subscribe_button_label' => $all[self::SUBSCRIBE_BUTTON_LABEL],
            'promotion_button_label' => $all[self::PROMOTION_BUTTON_LABEL],
        ];
    }
}
