import IsiPlazaLayout from '@/layouts/isi-plaza/isi-plaza-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/react';

interface TextosNumerosSettings {
    subscription_plan_label: string;
    subscription_price_label: string;
    subscription_message_pending: string;
    subscription_message_active: string;
    subscription_whatsapp_url: string;
    promotion_whatsapp_url: string;
    subscribe_button_label: string;
    promotion_button_label: string;
}

interface TextosNumerosProps {
    settings: TextosNumerosSettings;
}

export default function IsiPlazaTextosNumeros({ settings }: TextosNumerosProps) {
    const form = useForm<TextosNumerosSettings>(settings);

    return (
        <IsiPlazaLayout title="Textos app médico">
            <Head title="Odontica — Textos app médico" />

            <form
                className="mx-auto flex max-w-3xl flex-col gap-8"
                onSubmit={(e) => {
                    e.preventDefault();
                    form.patch(route('isi-plaza.textos-numeros.update'));
                }}
            >
                <section className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-[#121660]">
                        Pantalla de suscripción
                    </h2>
                    <div className="grid gap-4">
                        <Field
                            label="Etiqueta del plan"
                            hint='Ej. "Plan dentista"'
                            value={form.data.subscription_plan_label}
                            onChange={(value) => form.setData('subscription_plan_label', value)}
                        />
                        <Field
                            label="Precio / descripción del plan"
                            hint='Ej. "Suscripción mensual de 69 MXN"'
                            value={form.data.subscription_price_label}
                            onChange={(value) => form.setData('subscription_price_label', value)}
                        />
                        <Field
                            label="Texto del botón Suscribirme"
                            value={form.data.subscribe_button_label}
                            onChange={(value) => form.setData('subscribe_button_label', value)}
                        />
                        <Field
                            label="URL de WhatsApp (suscripción)"
                            hint="Enlace wa.me completo que abre al pulsar Suscribirme"
                            value={form.data.subscription_whatsapp_url}
                            onChange={(value) => form.setData('subscription_whatsapp_url', value)}
                        />
                        <TextAreaField
                            label="Mensaje con acceso pendiente"
                            value={form.data.subscription_message_pending}
                            onChange={(value) => form.setData('subscription_message_pending', value)}
                        />
                        <TextAreaField
                            label="Mensaje con suscripción activa"
                            value={form.data.subscription_message_active}
                            onChange={(value) => form.setData('subscription_message_active', value)}
                        />
                    </div>
                </section>

                <section className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-[#121660]">
                        Pantalla de ajustes (promoción)
                    </h2>
                    <div className="grid gap-4">
                        <Field
                            label="Texto del botón Comprar promoción"
                            value={form.data.promotion_button_label}
                            onChange={(value) => form.setData('promotion_button_label', value)}
                        />
                        <Field
                            label="URL de WhatsApp (promoción banners)"
                            hint="Enlace wa.me completo que abre al pulsar Comprar promoción"
                            value={form.data.promotion_whatsapp_url}
                            onChange={(value) => form.setData('promotion_whatsapp_url', value)}
                        />
                    </div>
                </section>

                <div className="flex justify-end">
                    <Button
                        type="submit"
                        className="bg-[#121660] text-white hover:bg-[#0e1250]"
                        disabled={form.processing}
                    >
                        Guardar cambios
                    </Button>
                </div>
            </form>
        </IsiPlazaLayout>
    );
}

function Field({
    label,
    hint,
    value,
    onChange,
}: {
    label: string;
    hint?: string;
    value: string;
    onChange: (value: string) => void;
}) {
    return (
        <div className="grid gap-1">
            <Label>{label}</Label>
            {hint ? <p className="text-xs text-neutral-500">{hint}</p> : null}
            <Input
                value={value}
                onChange={(e) => onChange(e.target.value)}
                className="border-neutral-300"
            />
        </div>
    );
}

function TextAreaField({
    label,
    value,
    onChange,
}: {
    label: string;
    value: string;
    onChange: (value: string) => void;
}) {
    return (
        <div className="grid gap-1">
            <Label>{label}</Label>
            <textarea
                value={value}
                onChange={(e) => onChange(e.target.value)}
                rows={3}
                className="min-h-[80px] w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 shadow-xs outline-none focus-visible:border-neutral-400 focus-visible:ring-2 focus-visible:ring-neutral-200"
            />
        </div>
    );
}
