import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import IsiPlazaLayout from '@/layouts/isi-plaza/isi-plaza-layout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { BadgeCheck } from 'lucide-react';
import { useEffect, useState } from 'react';

interface Stats {
    buyers_count: number;
    sellers_count: number;
}

interface BuyerRow {
    id: number;
    name: string;
    email: string;
    created_at: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

/** Laravel paginated API resources (JsonResource::collection + paginator) */
interface LaravelApiPaginationLinks {
    first?: string | null;
    last?: string | null;
    prev: string | null;
    next: string | null;
}

interface Paginated<T> {
    data: T[];
    links: PaginationLink[] | LaravelApiPaginationLinks;
    meta: {
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
    };
}

interface SellerProfile {
    access_status: string | null;
    is_verified: boolean | null;
    has_paid_promotion: boolean | null;
    subscription_expires_at: string | null;
    description: string | null;
}

interface SellerRow {
    id: number;
    name: string;
    email: string;
    has_password: boolean;
    seller_profile: SellerProfile | null;
    created_at: string | null;
}

interface BannerRow {
    id: number;
    image_url: string | null;
    sort_order: number;
    is_active: boolean;
    clicks_count: number;
    link_url: string | null;
}

interface GestionProps {
    stats: Stats;
    buyers: Paginated<BuyerRow>;
    sellers: Paginated<SellerRow>;
    banners: { data: BannerRow[] };
}

function SubscriptionCountdown({ expiresAt }: { expiresAt: string | null }) {
    const [now, setNow] = useState(() => Date.now());

    useEffect(() => {
        const id = window.setInterval(() => setNow(Date.now()), 1000);

        return () => window.clearInterval(id);
    }, []);

    if (!expiresAt) {
        return <span className="text-neutral-400">—</span>;
    }

    const end = new Date(expiresAt).getTime();

    if (Number.isNaN(end)) {
        return <span className="text-neutral-400">—</span>;
    }

    const ms = end - now;

    if (ms <= 0) {
        return <span className="font-medium text-red-600">Periodo finalizado</span>;
    }

    const totalSec = Math.floor(ms / 1000);
    const days = Math.floor(totalSec / 86400);
    const hours = Math.floor((totalSec % 86400) / 3600);
    const mins = Math.floor((totalSec % 3600) / 60);
    const secs = totalSec % 60;

    return (
        <span className="font-mono text-sm tabular-nums text-neutral-800">
            {String(days).padStart(2, '0')}d {String(hours).padStart(2, '0')}h {String(mins).padStart(2, '0')}m {String(secs).padStart(2, '0')}s
        </span>
    );
}

function DeleteBuyerButton({ buyerId }: { buyerId: number }) {
    const form = useForm({});

    return (
        <Button
            type="button"
            variant="outline"
            size="sm"
            className="border-red-200 text-red-700 hover:bg-red-50"
            disabled={form.processing}
            onClick={() => {
                if (confirm('¿Eliminar esta cuenta de comprador del sistema?')) {
                    form.delete(route('isi-plaza.buyers.destroy', buyerId));
                }
            }}
        >
            Eliminar cuenta
        </Button>
    );
}

type AccessStatusValue = 'active' | 'pending' | 'denied';

function accessStatusLabel(status: AccessStatusValue | null | undefined): string {
    switch (status) {
        case 'active':
            return 'Acceso activo';
        case 'denied':
            return 'Acceso denegado';
        case 'pending':
            return 'Pendiente (suscripción app)';
        default:
            return 'Sin estado';
    }
}

function accessStatusBadgeClass(status: AccessStatusValue | null | undefined): string {
    switch (status) {
        case 'active':
            return 'border-emerald-300 bg-emerald-50 text-emerald-800';
        case 'denied':
            return 'border-red-300 bg-red-50 text-red-800';
        case 'pending':
            return 'border-amber-300 bg-amber-50 text-amber-900';
        default:
            return 'border-neutral-200 bg-neutral-50 text-neutral-600';
    }
}

function SellerTableRow({ row }: { row: SellerRow }) {
    const p = row.seller_profile;
    const accessStatus = (p?.access_status ?? 'pending') as AccessStatusValue;
    const hasPaid = Boolean(p?.has_paid_promotion);
    const verified = Boolean(p?.is_verified);

    const patch = (payload: Record<string, string | boolean>) => {
        router.patch(route('isi-plaza.vendedores.update', row.id), payload, {
            preserveScroll: true,
            only: ['sellers', 'stats'],
        });
    };

    return (
        <tr className="border-b border-neutral-100 align-top hover:bg-neutral-50/80">
            <td className="px-3 py-3 font-medium text-neutral-900">
                <span className="block">{row.name}</span>
                <span className="font-mono text-xs font-normal text-neutral-500">#{row.id}</span>
            </td>
            <td className="max-w-[min(12rem,22vw)] truncate px-3 py-3 text-neutral-700" title={row.email}>
                {row.email}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-neutral-700">
                <span className="font-mono tracking-widest">{row.has_password ? '••••••••' : '—'}</span>
                <span className="ml-1 text-xs text-neutral-500">(hasheada)</span>
            </td>
            <td className="px-3 py-3">
                <div className="flex max-w-[11rem] flex-col gap-1.5">
                    <span
                        className={`inline-flex items-center justify-center rounded-md border px-2 py-1 text-center text-xs font-semibold ${accessStatusBadgeClass(accessStatus)}`}
                    >
                        {accessStatusLabel(accessStatus)}
                    </span>
                    <div className="flex flex-col gap-1">
                        {accessStatus !== 'active' && (
                            <Button
                                type="button"
                                size="sm"
                                className="h-8 bg-emerald-600 text-xs text-white hover:bg-emerald-700"
                                onClick={() => patch({ access_status: 'active' })}
                            >
                                Dar acceso
                            </Button>
                        )}
                        {accessStatus !== 'denied' && (
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                className="h-8 border-red-300 text-xs text-red-700 hover:bg-red-50"
                                onClick={() => patch({ access_status: 'denied' })}
                            >
                                Denegar acceso
                            </Button>
                        )}
                        {accessStatus !== 'pending' && (
                            <Button
                                type="button"
                                size="sm"
                                variant="secondary"
                                className="h-8 text-xs"
                                onClick={() => patch({ access_status: 'pending' })}
                            >
                                Marcar pendiente
                            </Button>
                        )}
                    </div>
                </div>
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-neutral-800">
                <span className="mb-1 block text-[10px] font-medium uppercase tracking-wide text-neutral-500">30 días desde acceso</span>
                <SubscriptionCountdown expiresAt={p?.subscription_expires_at ?? null} />
            </td>
            <td className="px-3 py-3">
                <button
                    type="button"
                    className={`flex w-full min-w-[7.5rem] items-center justify-center gap-1 rounded-md border px-2 py-1.5 text-xs font-medium transition-colors ${
                        hasPaid ? 'border-emerald-300 bg-emerald-50 text-emerald-800' : 'border-neutral-200 bg-white text-neutral-700'
                    }`}
                    onClick={() => patch({ has_paid_promotion: !hasPaid })}
                >
                    Promoción: {hasPaid ? 'Activo' : 'Inactivo'}
                </button>
            </td>
            <td className="px-3 py-3">
                <button
                    type="button"
                    className={`flex w-full min-w-[7.5rem] items-center justify-center gap-1 rounded-md border px-2 py-1.5 text-xs font-medium transition-colors ${
                        verified ? 'border-sky-500 bg-sky-50 text-sky-900' : 'border-neutral-200 bg-white text-neutral-700'
                    }`}
                    onClick={() => patch({ is_verified: !verified })}
                >
                    <BadgeCheck className={`size-3.5 shrink-0 ${verified ? 'text-sky-600' : 'text-neutral-400'}`} aria-hidden />
                    {verified ? 'Palomita azul' : 'Sin verificar'}
                </button>
            </td>
            <td className="px-3 py-3 text-right">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="border-red-200 text-xs text-red-700 hover:bg-red-50"
                    onClick={() => {
                        if (confirm('¿Eliminar cuenta de mayorista del sistema? Ya no podrá acceder.')) {
                            router.delete(route('isi-plaza.vendedores.destroy', row.id), { preserveScroll: true });
                        }
                    }}
                >
                    Eliminar cuenta
                </Button>
            </td>
        </tr>
    );
}

function BannerCard({ banner }: { banner: BannerRow }) {
    const del = useForm({});

    return (
        <div className="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm">
            <div className="aspect-[70/15] w-full bg-neutral-100">
                {banner.image_url ? (
                    <img src={banner.image_url} alt="" className="size-full object-cover" />
                ) : (
                    <div className="flex size-full items-center justify-center text-xs text-neutral-400">Sin vista previa</div>
                )}
            </div>
            <div className="space-y-1 p-3 text-xs text-neutral-600">
                <p>
                    Orden: <span className="font-mono text-neutral-900">{banner.sort_order}</span> · Clics:{' '}
                    <span className="font-mono text-neutral-900">{banner.clicks_count}</span>
                </p>
                <p>{banner.is_active ? <span className="font-medium text-emerald-700">Activo</span> : <span>Inactivo</span>}</p>
            </div>
            <div className="border-t border-neutral-100 p-3">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="w-full border-red-200 text-red-700 hover:bg-red-50"
                    disabled={del.processing}
                    onClick={() => {
                        if (confirm('¿Eliminar este banner del carrusel?')) {
                            del.delete(route('isi-plaza.banners.destroy', banner.id));
                        }
                    }}
                >
                    Eliminar
                </Button>
            </div>
        </div>
    );
}

function PaginationLinks({ paginator }: { paginator: Paginated<unknown> }) {
    const meta = paginator.meta;
    if (!meta || meta.last_page <= 1) {
        return null;
    }

    const { links } = paginator;

    if (Array.isArray(links)) {
        if (links.length <= 3) {
            return null;
        }

        return (
            <div className="mt-4 flex flex-wrap justify-center gap-1">
                {links.map((link, i) => {
                    if (link.url === null) {
                        return (
                            <span
                                key={i}
                                className="inline-flex min-w-9 items-center justify-center rounded-md border border-neutral-200 px-3 py-1 text-sm text-neutral-400"
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        );
                    }
                    return (
                        <Link
                            key={i}
                            href={link.url}
                            preserveScroll
                            className={
                                link.active
                                    ? 'inline-flex min-w-9 items-center justify-center rounded-md bg-[#E00000] px-3 py-1 text-sm font-medium text-white'
                                    : 'inline-flex min-w-9 items-center justify-center rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm text-neutral-700 hover:border-[#E00000] hover:text-[#E00000]'
                            }
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    );
                })}
            </div>
        );
    }

    if (links && typeof links === 'object' && 'prev' in links && 'next' in links) {
        const cursorLinks = links as LaravelApiPaginationLinks;

        return (
            <div className="mt-4 flex flex-wrap items-center justify-center gap-3 text-sm text-neutral-700">
                {cursorLinks.prev ? (
                    <Link
                        href={cursorLinks.prev}
                        preserveScroll
                        className="inline-flex rounded-md border border-neutral-200 bg-white px-3 py-1.5 font-medium hover:border-[#E00000] hover:text-[#E00000]"
                    >
                        Anterior
                    </Link>
                ) : (
                    <span className="inline-flex rounded-md border border-transparent px-3 py-1.5 text-neutral-400">Anterior</span>
                )}
                <span className="tabular-nums text-neutral-600">
                    Página {meta.current_page} de {meta.last_page}
                </span>
                {cursorLinks.next ? (
                    <Link
                        href={cursorLinks.next}
                        preserveScroll
                        className="inline-flex rounded-md border border-neutral-200 bg-white px-3 py-1.5 font-medium hover:border-[#E00000] hover:text-[#E00000]"
                    >
                        Siguiente
                    </Link>
                ) : (
                    <span className="inline-flex rounded-md border border-transparent px-3 py-1.5 text-neutral-400">Siguiente</span>
                )}
            </div>
        );
    }

    return null;
}

export default function IsiPlazaGestion({ stats, buyers, sellers, banners }: GestionProps) {
    const buyerRows = buyers?.data ?? [];
    const sellerRows = sellers?.data ?? [];
    const bannerRows = banners && typeof banners === 'object' && 'data' in banners ? (banners.data ?? []) : [];
    const createBannerForm = useForm({
        image: null as File | null,
        sort_order: 0,
        is_active: true,
        link_url: '',
    });

    return (
        <IsiPlazaLayout title="Gestión de datos">
            <Head title="ISI PLAZA — Gestión de datos" />

            <section className="mb-10">
                <div className="grid gap-8 lg:grid-cols-2 lg:items-start">
                    <div className="flex min-w-0 flex-col gap-3">
                        <h2 className="text-sm font-semibold uppercase tracking-wide text-[#E00000]">Compradores (app 1)</h2>
                        <div className="overflow-x-auto rounded-xl border border-neutral-200 bg-white shadow-sm">
                            <table className="w-full min-w-[28rem] text-left text-sm">
                                <thead>
                                    <tr className="border-b border-neutral-200 bg-[#E00000] text-white">
                                        <th className="px-4 py-3 font-semibold">ID</th>
                                        <th className="px-4 py-3 font-semibold">Usuario</th>
                                        <th className="px-4 py-3 font-semibold">Mail</th>
                                        <th className="px-4 py-3 font-semibold">Alta</th>
                                        <th className="px-4 py-3 text-right font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {buyerRows.length === 0 ? (
                                        <tr>
                                            <td colSpan={5} className="px-4 py-8 text-center text-neutral-500">
                                                No hay compradores.
                                            </td>
                                        </tr>
                                    ) : (
                                        buyerRows.map((row) => (
                                            <tr key={row.id} className="border-b border-neutral-100 hover:bg-neutral-50/80">
                                                <td className="px-4 py-3 font-mono text-xs text-neutral-600">{row.id}</td>
                                                <td className="px-4 py-3 font-medium text-neutral-900">{row.name}</td>
                                                <td className="px-4 py-3 text-neutral-700">{row.email}</td>
                                                <td className="px-4 py-3 text-neutral-600">{row.created_at ? new Date(row.created_at).toLocaleString() : '—'}</td>
                                                <td className="px-4 py-3 text-right">
                                                    <DeleteBuyerButton buyerId={row.id} />
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                        <p className="text-sm text-neutral-500">Total en listado: {buyers?.meta?.total ?? 0}</p>
                        {buyers && <PaginationLinks paginator={buyers} />}
                        <div className="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm">
                            <p className="text-sm font-medium text-neutral-600">Número de compradores (registrados)</p>
                            <p className="mt-1 text-3xl font-bold tabular-nums text-[#E00000]">{stats?.buyers_count ?? 0}</p>
                        </div>
                    </div>

                    <div className="flex min-w-0 flex-col gap-3">
                        <h2 className="text-sm font-semibold uppercase tracking-wide text-[#E00000]">Mayoristas (app 2)</h2>
                        <div className="overflow-x-auto rounded-xl border border-neutral-200 bg-white shadow-sm">
                            <table className="w-full min-w-[52rem] text-left text-sm">
                                <thead>
                                    <tr className="border-b border-neutral-200 bg-[#E00000] text-white">
                                        <th className="px-3 py-3 font-semibold">Usuario</th>
                                        <th className="px-3 py-3 font-semibold">Mail</th>
                                        <th className="px-3 py-3 font-semibold">Contraseña</th>
                                        <th className="px-3 py-3 font-semibold">Acceso app</th>
                                        <th className="px-3 py-3 font-semibold">Cuenta regresiva</th>
                                        <th className="px-3 py-3 font-semibold">Promoción</th>
                                        <th className="px-3 py-3 font-semibold">Verificación</th>
                                        <th className="px-3 py-3 text-right font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {sellerRows.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="px-4 py-8 text-center text-neutral-500">
                                                No hay mayoristas registrados.
                                            </td>
                                        </tr>
                                    ) : (
                                        sellerRows.map((row) => <SellerTableRow key={row.id} row={row} />)
                                    )}
                                </tbody>
                            </table>
                        </div>
                        <p className="text-sm text-neutral-500">Total en listado: {sellers?.meta?.total ?? 0}</p>
                        {sellers && <PaginationLinks paginator={sellers} />}
                        <div className="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm">
                            <p className="text-sm font-medium text-neutral-600">Número de mayoristas (registrados)</p>
                            <p className="mt-1 text-3xl font-bold tabular-nums text-[#E00000]">{stats?.sellers_count ?? 0}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-[#E00000]">Banners — carrusel app 1</h2>
                <div className="mb-8 rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-sm font-semibold text-neutral-800">Subir banner</h3>
                    <form
                        className="flex flex-col gap-4 md:flex-row md:flex-wrap md:items-end"
                        onSubmit={(e) => {
                            e.preventDefault();
                            createBannerForm.post(route('isi-plaza.banners.store'), { forceFormData: true });
                        }}
                    >
                        <div className="grid gap-1">
                            <Label>Imagen</Label>
                            <Input
                                type="file"
                                accept="image/*"
                                className="border-neutral-300"
                                onChange={(e) => createBannerForm.setData('image', e.target.files?.[0] ?? null)}
                            />
                        </div>
                        <div className="grid w-28 gap-1">
                            <Label>Orden</Label>
                            <Input
                                type="number"
                                min={0}
                                value={createBannerForm.data.sort_order}
                                onChange={(e) => createBannerForm.setData('sort_order', Number(e.target.value))}
                                className="border-neutral-300"
                            />
                        </div>
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox checked={createBannerForm.data.is_active} onCheckedChange={(v) => createBannerForm.setData('is_active', v === true)} />
                            Activo
                        </label>
                        <div className="grid min-w-[200px] flex-1 gap-1">
                            <Label>URL (opcional)</Label>
                            <Input
                                value={createBannerForm.data.link_url}
                                onChange={(e) => createBannerForm.setData('link_url', e.target.value)}
                                placeholder="https://..."
                                className="border-neutral-300"
                            />
                        </div>
                        <Button
                            type="submit"
                            className="bg-[#E00000] text-white hover:bg-[#FF0000]"
                            disabled={createBannerForm.processing || !createBannerForm.data.image}
                        >
                            Subir al carrusel
                        </Button>
                    </form>
                </div>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {bannerRows.map((b) => (
                        <BannerCard key={b.id} banner={b} />
                    ))}
                    {bannerRows.length === 0 && <p className="col-span-full text-center text-neutral-500">No hay banners. Sube uno arriba.</p>}
                </div>
            </section>
        </IsiPlazaLayout>
    );
}
