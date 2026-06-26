import IsiPlazaLayout from '@/layouts/isi-plaza/isi-plaza-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/react';

interface TextosPacienteSettings {
    external_contact_disclaimer: string;
    app_store_url: string;
    play_store_url: string;
    privacy_notice: string;
}

interface TextosPacienteProps {
    settings: TextosPacienteSettings;
}

export default function IsiPlazaTextosPaciente({ settings }: TextosPacienteProps) {
    const form = useForm<TextosPacienteSettings>(settings);

    return (
        <IsiPlazaLayout title="Textos app paciente">
            <Head title="Odontica — Textos app paciente" />

            <form
                className="mx-auto flex max-w-3xl flex-col gap-8"
                onSubmit={(e) => {
                    e.preventDefault();
                    form.patch(route('isi-plaza.textos-paciente.update'));
                }}
            >
                <section className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-[#121660]">
                        Compliance y avisos
                    </h2>
                    <div className="grid gap-4">
                        <Field
                            label="Aviso de contacto externo"
                            hint="Se muestra al paciente al usar WhatsApp o contactar médicos"
                            value={form.data.external_contact_disclaimer}
                            onChange={(value) => form.setData('external_contact_disclaimer', value)}
                            multiline
                        />
                        <Field
                            label="Aviso de privacidad / directorio"
                            value={form.data.privacy_notice}
                            onChange={(value) => form.setData('privacy_notice', value)}
                            multiline
                        />
                    </div>
                </section>

                <section className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-[#121660]">
                        Enlaces a tiendas
                    </h2>
                    <div className="grid gap-4">
                        <Field
                            label="App Store (iOS)"
                            value={form.data.app_store_url}
                            onChange={(value) => form.setData('app_store_url', value)}
                        />
                        <Field
                            label="Google Play (Android)"
                            value={form.data.play_store_url}
                            onChange={(value) => form.setData('play_store_url', value)}
                        />
                    </div>
                </section>

                <Button type="submit" disabled={form.processing} className="self-start bg-[#121660]">
                    Guardar cambios
                </Button>
            </form>
        </IsiPlazaLayout>
    );
}

function Field({
    label,
    hint,
    value,
    onChange,
    multiline,
}: {
    label: string;
    hint?: string;
    value: string;
    onChange: (value: string) => void;
    multiline?: boolean;
}) {
    return (
        <div className="grid gap-1.5">
            <Label>{label}</Label>
            {hint ? <p className="text-xs text-neutral-500">{hint}</p> : null}
            {multiline ? (
                <textarea
                    className="min-h-[100px] w-full rounded-md border border-neutral-200 px-3 py-2 text-sm"
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                />
            ) : (
                <Input value={value} onChange={(e) => onChange(e.target.value)} />
            )}
        </div>
    );
}
