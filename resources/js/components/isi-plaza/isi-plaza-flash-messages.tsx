import { IsiPlazaAlert } from '@/components/isi-plaza/isi-plaza-alert';
import { usePage } from '@inertiajs/react';

interface IsiPlazaFlash {
    success?: string | null;
    warning?: string | null;
    info?: string | null;
}

export function IsiPlazaFlashMessages() {
    const { flash, errors } = usePage().props as {
        flash?: IsiPlazaFlash;
        errors?: Record<string, string | string[]>;
    };

    const errorMessages = Object.values(errors ?? {}).flatMap((value) => (Array.isArray(value) ? value : [value])).filter(Boolean);

    if (!flash?.success && !flash?.warning && !flash?.info && errorMessages.length === 0) {
        return null;
    }

    return (
        <div className="mb-6 space-y-3">
            {flash?.success && (
                <IsiPlazaAlert variant="success" title="Éxito">
                    {flash.success}
                </IsiPlazaAlert>
            )}
            {flash?.warning && (
                <IsiPlazaAlert variant="warning" title="Aviso">
                    {flash.warning}
                </IsiPlazaAlert>
            )}
            {flash?.info && (
                <IsiPlazaAlert variant="info" title="Información">
                    {flash.info}
                </IsiPlazaAlert>
            )}
            {errorMessages.map((message) => (
                <IsiPlazaAlert key={message} variant="error" title="Error">
                    {message}
                </IsiPlazaAlert>
            ))}
        </div>
    );
}
