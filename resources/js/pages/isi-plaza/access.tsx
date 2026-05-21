import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import { useIsiPlazaLightShell } from '@/hooks/use-isi-plaza-light-shell';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface AccessPageProps {
    logoUrl: string;
}

export default function IsiPlazaAccess({ logoUrl }: AccessPageProps) {
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
            <Head title="Sign in — ISI PLAZA Admin" />
            <div className="relative overflow-hidden pb-10">
                <div className="bg-gradient-to-b from-[#E00000] via-[#FF0000] to-[#FF4040] px-6 pb-20 pt-12 shadow-md">
                    <div className="mx-auto flex max-w-lg flex-col items-center text-center">
                        <img src={logoUrl} alt="ISI PLAZA" className="mb-2 h-28 w-auto drop-shadow-lg" />
                        <p className="text-sm font-medium uppercase tracking-[0.2em] text-white/90">Admin panel</p>
                    </div>
                </div>
                <div className="relative z-10 mx-auto -mt-14 max-w-md px-4">
                    <div className="rounded-2xl border border-neutral-200 bg-white p-8 shadow-xl">
                        <h2 className="mb-6 text-center text-xl font-semibold text-neutral-900">Sign in</h2>
                        <form onSubmit={submit} className="space-y-5">
                            <div className="space-y-2">
                                <Label htmlFor="token">Token</Label>
                                <Input
                                    id="token"
                                    name="token"
                                    type="password"
                                    value={data.token}
                                    onChange={(e) => setData('token', e.target.value)}
                                    autoComplete="off"
                                    autoFocus
                                    spellCheck={false}
                                    placeholder="Enter your token"
                                    className="border-neutral-300 bg-white text-neutral-900 caret-[#E00000] placeholder:text-neutral-400 focus-visible:ring-[#FF4040] dark:bg-white dark:text-neutral-900"
                                />
                                <InputError message={errors.token} />
                            </div>
                            <Button
                                type="submit"
                                className="w-full bg-[#E00000] text-white hover:bg-[#FF0000]"
                                disabled={processing}
                            >
                                Enter panel
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
