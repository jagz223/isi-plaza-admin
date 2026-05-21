import { TokenCreatedDialog } from '@/components/isi-plaza/token-created-dialog';
import IsiPlazaLayout from '@/layouts/isi-plaza/isi-plaza-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

interface TokenRow {
    id: number;
    description: string | null;
    is_active: boolean;
    last_used_at: string | null;
    created_at: string | null;
}

interface TokensProps {
    tokens: { data: TokenRow[] };
    flashPlainToken?: string | null;
}

export default function IsiPlazaTokens({ tokens, flashPlainToken }: TokensProps) {
    const rows = tokens.data ?? [];
    /** After POST, Inertia updates props but useState initialiser does not re-run — track dismiss locally and reopen when a new flash arrives. */
    const [tokenModalDismissed, setTokenModalDismissed] = useState(false);

    useEffect(() => {
        if (flashPlainToken) {
            setTokenModalDismissed(false);
        }
    }, [flashPlainToken]);

    const tokenModalOpen = Boolean(flashPlainToken) && !tokenModalDismissed;
    const createForm = useForm({ description: '' });

    return (
        <IsiPlazaLayout title="Ajustes de acceso">
            <Head title="ISI PLAZA — Ajustes de acceso" />

            <TokenCreatedDialog
                open={tokenModalOpen}
                plainToken={flashPlainToken ?? ''}
                onOpenChange={(open) => {
                    if (!open) {
                        setTokenModalDismissed(true);
                    }
                }}
            />

            <div className="mb-8 rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-[#E00000]">Añadir token (9–15 caracteres)</h2>
                <form
                    className="flex flex-col gap-4 sm:flex-row sm:items-end"
                    onSubmit={(e) => {
                        e.preventDefault();
                        createForm.post(route('isi-plaza.ajustes-acceso.store'));
                    }}
                >
                    <div className="grid min-w-[240px] flex-1 gap-1">
                        <Label>Descripción (opcional)</Label>
                        <Input
                            value={createForm.data.description}
                            onChange={(e) => createForm.setData('description', e.target.value)}
                            placeholder="Ej. Soporte — Jesús"
                            className="border-neutral-300"
                        />
                    </div>
                    <Button type="submit" className="bg-[#E00000] text-white hover:bg-[#FF0000]" disabled={createForm.processing}>
                        Generar token
                    </Button>
                </form>
            </div>
            <div className="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm">
                <table className="w-full text-left text-sm">
                    <thead>
                        <tr className="border-b border-neutral-200 bg-[#E00000] text-white">
                            <th className="px-4 py-3 font-semibold">ID</th>
                            <th className="px-4 py-3 font-semibold">Descripción</th>
                            <th className="px-4 py-3 font-semibold">Activo</th>
                            <th className="px-4 py-3 font-semibold">Último uso</th>
                            <th className="px-4 py-3 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.map((t) => (
                            <TokenRow key={t.id} token={t} />
                        ))}
                        {rows.length === 0 && (
                            <tr>
                                <td colSpan={5} className="px-4 py-8 text-center text-neutral-500">
                                    No hay tokens. Genera uno para acceder al panel.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </IsiPlazaLayout>
    );
}

function TokenRow({ token }: { token: TokenRow }) {
    const del = useForm({});

    return (
        <tr className="border-b border-neutral-100 hover:bg-neutral-50/80">
            <td className="px-4 py-3 font-mono text-xs text-neutral-600">{token.id}</td>
            <td className="px-4 py-3 text-neutral-800">{token.description ?? '—'}</td>
            <td className="px-4 py-3">
                {token.is_active ? <span className="font-medium text-emerald-700">Sí</span> : <span className="text-neutral-500">No</span>}
            </td>
            <td className="px-4 py-3 text-neutral-600">{token.last_used_at ? new Date(token.last_used_at).toLocaleString() : '—'}</td>
            <td className="px-4 py-3 text-right">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="border-red-200 text-red-700 hover:bg-red-50"
                    disabled={del.processing}
                    onClick={() => {
                        if (confirm('¿Eliminar este token?')) {
                            del.delete(route('isi-plaza.ajustes-acceso.destroy', token.id));
                        }
                    }}
                >
                    Eliminar
                </Button>
            </td>
        </tr>
    );
}
