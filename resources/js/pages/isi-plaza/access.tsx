import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import { OdonticaPanelBrand } from '@/components/odontica/odontica-panel-brand';
import { ODONTICA_PRIMARY } from '@/constants/odontica';
import { useIsiPlazaLightShell } from '@/hooks/use-isi-plaza-light-shell';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export default function OdonticaAccess() {
    useIsiPlazaLightShell();

    const { data, setData, post, processing, errors } = useForm({
        token: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('isi-plaza.access.store'));
    };

    return (
        <div className="isi-plaza-theme min-h-screen bg-white text-neutral-950">
            <Head title="Acceso — Odontica Panel" />

            <div className="h-28 w-full bg-[#121660] sm:h-32" />

            <div className="relative z-10 -mt-14 flex justify-center px-4 sm:-mt-16">
                <div className="w-full max-w-md border border-neutral-200 bg-white px-8 py-6 shadow-sm sm:px-10 sm:py-8">
                    <OdonticaPanelBrand />
                </div>
            </div>

            <div className="mx-auto mt-10 max-w-md px-6 pb-12 sm:mt-12">
                <form onSubmit={submit} className="space-y-6">
                    <div className="space-y-2">
                        <Label htmlFor="token" className="text-base font-normal text-neutral-900">
                            Token
                        </Label>
                        <Input
                            id="token"
                            name="token"
                            type="password"
                            value={data.token}
                            onChange={(e) => setData('token', e.target.value)}
                            autoComplete="off"
                            autoFocus
                            spellCheck={false}
                            className="h-11 border-neutral-900 bg-white text-neutral-900 focus-visible:ring-[#1a2280]"
                            style={{ caretColor: ODONTICA_PRIMARY }}
                        />
                        <InputError message={errors.token} />
                    </div>
                    <Button
                        type="submit"
                        className="h-11 w-full rounded-md bg-[#121660] text-base font-semibold text-white hover:bg-[#0e1250]"
                        disabled={processing}>
                        {processing ? 'Entrando…' : 'Acceder'}
                    </Button>
                </form>
            </div>
        </div>
    );
}
