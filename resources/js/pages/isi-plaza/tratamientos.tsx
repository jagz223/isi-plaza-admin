import IsiPlazaLayout from '@/layouts/isi-plaza/isi-plaza-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

interface TreatmentItem {
    id: number;
    name: string;
    slug: string;
    sort_order: number;
    is_active: boolean;
}

interface SectionItem {
    id: number;
    name: string;
    slug: string;
    sort_order: number;
    is_active: boolean;
    treatments: TreatmentItem[];
}

interface TratamientosProps {
    sections: SectionItem[];
}

export default function IsiPlazaTratamientos({ sections }: TratamientosProps) {
    const sectionForm = useForm({ name: '', sort_order: sections.length + 1, is_active: true });
    const [newTreatment, setNewTreatment] = useState<Record<number, string>>({});

    return (
        <IsiPlazaLayout title="Tratamientos">
            <Head title="Odontica — Tratamientos" />

            <div className="mx-auto flex max-w-4xl flex-col gap-8">
                <section className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-[#121660]">
                        Nueva sección
                    </h2>
                    <form
                        className="flex flex-col gap-3 sm:flex-row sm:items-end"
                        onSubmit={(e) => {
                            e.preventDefault();
                            sectionForm.post(route('isi-plaza.tratamientos.sections.store'), {
                                preserveScroll: true,
                                onSuccess: () => sectionForm.reset('name'),
                            });
                        }}
                    >
                        <div className="flex-1">
                            <Label htmlFor="section-name">Nombre de la sección</Label>
                            <Input
                                id="section-name"
                                value={sectionForm.data.name}
                                onChange={(e) => sectionForm.setData('name', e.target.value)}
                                placeholder="Ej. Ortodoncia"
                            />
                        </div>
                        <Button type="submit" disabled={sectionForm.processing}>
                            Añadir sección
                        </Button>
                    </form>
                </section>

                {sections.map((section) => (
                    <SectionCard
                        key={section.id}
                        section={section}
                        newTreatmentName={newTreatment[section.id] ?? ''}
                        onNewTreatmentChange={(value) =>
                            setNewTreatment((prev) => ({ ...prev, [section.id]: value }))
                        }
                    />
                ))}
            </div>
        </IsiPlazaLayout>
    );
}

function SectionCard({
    section,
    newTreatmentName,
    onNewTreatmentChange,
}: {
    section: SectionItem;
    newTreatmentName: string;
    onNewTreatmentChange: (value: string) => void;
}) {
    const editForm = useForm({
        name: section.name,
        sort_order: section.sort_order,
        is_active: section.is_active,
    });

    return (
        <section className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div className="mb-4 flex flex-col gap-3 border-b border-neutral-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
                <form
                    className="flex flex-1 flex-col gap-2 sm:flex-row sm:items-end"
                    onSubmit={(e) => {
                        e.preventDefault();
                        editForm.transform((data) => ({ ...data, update_slug: true })).patch(
                            route('isi-plaza.tratamientos.sections.update', section.id),
                            { preserveScroll: true },
                        );
                    }}
                >
                    <div className="flex-1">
                        <Label>Sección</Label>
                        <Input
                            value={editForm.data.name}
                            onChange={(e) => editForm.setData('name', e.target.value)}
                        />
                    </div>
                    <div className="w-24">
                        <Label>Orden</Label>
                        <Input
                            type="number"
                            value={editForm.data.sort_order}
                            onChange={(e) => editForm.setData('sort_order', Number(e.target.value))}
                        />
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={editForm.data.is_active}
                            onChange={(e) => editForm.setData('is_active', e.target.checked)}
                        />
                        Activa
                    </label>
                    <Button type="submit" variant="outline" size="sm" disabled={editForm.processing}>
                        Guardar
                    </Button>
                </form>
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    onClick={() => {
                        if (confirm(`¿Eliminar la sección "${section.name}" y sus tratamientos?`)) {
                            router.delete(route('isi-plaza.tratamientos.sections.destroy', section.id), {
                                preserveScroll: true,
                            });
                        }
                    }}
                >
                    Eliminar sección
                </Button>
            </div>

            <ul className="mb-4 space-y-2">
                {section.treatments.map((treatment) => (
                    <TreatmentRow key={treatment.id} treatment={treatment} sectionId={section.id} />
                ))}
                {section.treatments.length === 0 ? (
                    <li className="text-sm text-neutral-500">Sin tratamientos en esta sección.</li>
                ) : null}
            </ul>

            <form
                className="flex flex-col gap-2 sm:flex-row sm:items-end"
                onSubmit={(e) => {
                    e.preventDefault();
                    if (!newTreatmentName.trim()) {
                        return;
                    }
                    router.post(
                        route('isi-plaza.tratamientos.treatments.store'),
                        {
                            treatment_section_id: section.id,
                            name: newTreatmentName.trim(),
                        },
                        {
                            preserveScroll: true,
                            onSuccess: () => onNewTreatmentChange(''),
                        },
                    );
                }}
            >
                <div className="flex-1">
                    <Label>Nuevo tratamiento</Label>
                    <Input
                        value={newTreatmentName}
                        onChange={(e) => onNewTreatmentChange(e.target.value)}
                        placeholder="Ej. Brackets metálicos"
                    />
                </div>
                <Button type="submit">Añadir tratamiento</Button>
            </form>
        </section>
    );
}

function TreatmentRow({ treatment, sectionId }: { treatment: TreatmentItem; sectionId: number }) {
    const form = useForm({
        name: treatment.name,
        sort_order: treatment.sort_order,
        is_active: treatment.is_active,
        treatment_section_id: sectionId,
    });

    return (
        <li className="flex flex-col gap-2 rounded-md border border-neutral-100 p-3 sm:flex-row sm:items-center">
            <Input
                className="flex-1"
                value={form.data.name}
                onChange={(e) => form.setData('name', e.target.value)}
            />
            <Input
                className="w-20"
                type="number"
                value={form.data.sort_order}
                onChange={(e) => form.setData('sort_order', Number(e.target.value))}
            />
            <label className="flex items-center gap-2 text-sm whitespace-nowrap">
                <input
                    type="checkbox"
                    checked={form.data.is_active}
                    onChange={(e) => form.setData('is_active', e.target.checked)}
                />
                Activo
            </label>
            <Button
                type="button"
                size="sm"
                variant="outline"
                disabled={form.processing}
                onClick={() =>
                    form.transform((data) => ({ ...data, update_slug: true })).patch(
                        route('isi-plaza.tratamientos.treatments.update', treatment.id),
                        { preserveScroll: true },
                    )
                }
            >
                Guardar
            </Button>
            <Button
                type="button"
                size="sm"
                variant="ghost"
                className="text-red-600"
                onClick={() => {
                    if (confirm(`¿Eliminar "${treatment.name}"?`)) {
                        router.delete(route('isi-plaza.tratamientos.treatments.destroy', treatment.id), {
                            preserveScroll: true,
                        });
                    }
                }}
            >
                Eliminar
            </Button>
        </li>
    );
}
