import IsiPlazaLayout from '@/layouts/isi-plaza/isi-plaza-layout';
import { Head, Link } from '@inertiajs/react';

interface SellerDetailProps {
    seller: {
        id: number;
        name: string;
        email: string;
        seller_profile: {
            description: string | null;
            professional_license: string | null;
            phone: string | null;
            whatsapp: string | null;
            address: string | null;
            municipality: string | null;
            latitude: number | null;
            longitude: number | null;
            country: string | null;
            state: string | null;
            avatar_url: string | null;
            is_verified: boolean;
            access_status: string;
            business_category: { id: number; name: string } | null;
            catalog_images: { id: number; image_url: string | null; display_order: number }[];
            doctor_services: {
                id: number;
                treatment_id: number;
                price: number;
                treatment_name: string | null;
                section_name: string | null;
            }[];
        } | null;
    };
}

export default function MedicoDetalle({ seller }: SellerDetailProps) {
    const profile = seller.seller_profile;

    return (
        <IsiPlazaLayout title={`Médico — ${seller.name}`}>
            <Head title={`Médico — ${seller.name}`} />

            <div className="mx-auto max-w-4xl space-y-6">
                <Link
                    href={route('isi-plaza.gestion')}
                    className="text-sm font-medium text-[#121660] hover:underline"
                >
                    ← Volver a gestión
                </Link>

                <header className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start">
                        {profile?.avatar_url ? (
                            <img
                                src={profile.avatar_url}
                                alt=""
                                className="size-24 rounded-lg object-cover"
                            />
                        ) : (
                            <div className="size-24 rounded-lg bg-neutral-100" />
                        )}
                        <div className="flex-1 space-y-1">
                            <h1 className="text-2xl font-bold text-neutral-900">{seller.name}</h1>
                            <p className="text-sm text-neutral-600">{seller.email}</p>
                            {profile?.professional_license ? (
                                <p className="text-sm font-semibold text-[#121660]">
                                    Cédula: {profile.professional_license}
                                </p>
                            ) : null}
                            <p className="text-sm text-neutral-500">
                                Acceso: <span className="font-medium">{profile?.access_status}</span>
                                {profile?.is_verified ? ' · Verificado' : ''}
                            </p>
                        </div>
                    </div>
                    {profile?.description ? (
                        <p className="mt-4 text-sm leading-relaxed text-neutral-700">{profile.description}</p>
                    ) : null}
                </header>

                <section className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-[#121660]">
                        Contacto y ubicación
                    </h2>
                    <dl className="grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt className="text-neutral-500">WhatsApp</dt>
                            <dd className="font-medium">{profile?.whatsapp ?? '—'}</dd>
                        </div>
                        <div>
                            <dt className="text-neutral-500">Celular</dt>
                            <dd className="font-medium">{profile?.phone ?? '—'}</dd>
                        </div>
                        <div>
                            <dt className="text-neutral-500">Municipio</dt>
                            <dd className="font-medium">{profile?.municipality ?? '—'}</dd>
                        </div>
                        <div>
                            <dt className="text-neutral-500">Dirección</dt>
                            <dd className="font-medium">{profile?.address ?? '—'}</dd>
                        </div>
                        <div>
                            <dt className="text-neutral-500">Coordenadas</dt>
                            <dd className="font-mono text-xs">
                                {profile?.latitude != null && profile?.longitude != null
                                    ? `${profile.latitude}, ${profile.longitude}`
                                    : '—'}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-neutral-500">Rubro</dt>
                            <dd className="font-medium">{profile?.business_category?.name ?? '—'}</dd>
                        </div>
                    </dl>
                </section>

                <section className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-[#121660]">
                        Servicios y precios
                    </h2>
                    {profile?.doctor_services?.length ? (
                        <ul className="divide-y divide-neutral-100">
                            {profile.doctor_services.map((service) => (
                                <li
                                    key={service.id}
                                    className="flex items-center justify-between py-3 text-sm"
                                >
                                    <div>
                                        {service.section_name ? (
                                            <p className="text-xs font-semibold uppercase text-neutral-500">
                                                {service.section_name}
                                            </p>
                                        ) : null}
                                        <p className="font-medium text-neutral-900">
                                            {service.treatment_name ?? `Tratamiento #${service.treatment_id}`}
                                        </p>
                                    </div>
                                    <p className="font-bold text-[#121660]">
                                        ${service.price.toLocaleString('es-MX')} MXN
                                    </p>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="text-sm text-neutral-500">Sin servicios configurados.</p>
                    )}
                </section>

                {profile?.catalog_images?.length ? (
                    <section className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                        <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-[#121660]">
                            Fotos del consultorio
                        </h2>
                        <div className="flex flex-wrap gap-3">
                            {profile.catalog_images.map((img) => (
                                <img
                                    key={img.id}
                                    src={img.image_url ?? ''}
                                    alt=""
                                    className="size-24 rounded-lg object-cover"
                                />
                            ))}
                        </div>
                    </section>
                ) : null}
            </div>
        </IsiPlazaLayout>
    );
}
