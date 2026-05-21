import { Link, useForm, usePage } from '@inertiajs/react';
import { type LucideIcon, ClipboardList, KeyRound, LogOut } from 'lucide-react';
import { type PropsWithChildren } from 'react';

import { IsiPlazaFlashMessages } from '@/components/isi-plaza/isi-plaza-flash-messages';
import { useIsiPlazaLightShell } from '@/hooks/use-isi-plaza-light-shell';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const nav: { title: string; path: string; routeName: string; icon: LucideIcon }[] = [
    { title: 'Gestión de datos', path: '/isi-plaza/gestion', routeName: 'isi-plaza.gestion', icon: ClipboardList },
    { title: 'Ajustes de acceso', path: '/isi-plaza/ajustes-acceso', routeName: 'isi-plaza.ajustes-acceso.index', icon: KeyRound },
];

export default function IsiPlazaLayout({ title, children }: PropsWithChildren<{ title: string }>) {
    useIsiPlazaLightShell();

    const { url } = usePage();
    const path = url.split('?')[0].replace(/\/$/, '') || '/';

    const signOutForm = useForm({});

    const submitSignOut = (e: React.FormEvent) => {
        e.preventDefault();
        signOutForm.post(route('isi-plaza.sign-out'));
    };

    return (
        <div className="isi-plaza-theme min-h-screen bg-white text-neutral-950">
            <div className="flex min-h-screen">
                <aside className="flex w-64 shrink-0 flex-col border-r border-neutral-200 bg-white">
                    <div className="border-b border-neutral-200 px-4 py-6">
                        <Link href={route('isi-plaza.gestion')} className="flex flex-col items-center gap-2">
                            <img src="/images/isi-plaza/logo.jpg" alt="ISI PLAZA" className="h-14 w-auto object-contain" />
                            <span className="text-xs font-semibold uppercase tracking-widest text-[#E00000]">Panel administrativo</span>
                        </Link>
                    </div>
                    <nav className="flex flex-1 flex-col gap-0.5 p-3">
                        {nav.map((item) => {
                            const active = path === item.path || path.startsWith(`${item.path}/`);
                            const Icon = item.icon;
                            return (
                                <Link
                                    key={item.routeName}
                                    href={route(item.routeName)}
                                    className={cn(
                                        'flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                        active
                                            ? 'border-l-4 border-[#E00000] bg-[#FFF5F5] text-[#E00000]'
                                            : 'border-l-4 border-transparent text-neutral-700 hover:bg-neutral-50 hover:text-neutral-900',
                                    )}
                                >
                                    <Icon className="size-4 shrink-0" />
                                    {item.title}
                                </Link>
                            );
                        })}
                    </nav>
                    <div className="border-t border-neutral-200 p-3">
                        <form onSubmit={submitSignOut}>
                            <Button
                                type="submit"
                                variant="outline"
                                className="w-full justify-center gap-2 border-neutral-300 text-neutral-800 hover:border-[#E00000] hover:bg-[#FFF5F5] hover:text-[#E00000]"
                                disabled={signOutForm.processing}
                            >
                                <LogOut className="size-4" />
                                Cerrar sesión
                            </Button>
                        </form>
                    </div>
                </aside>
                <main className="flex min-w-0 flex-1 flex-col bg-[#FAFAFA]">
                    <header className="border-b border-neutral-200 bg-white px-6 py-4">
                        <h1 className="text-lg font-semibold tracking-tight text-neutral-900">{title}</h1>
                    </header>
                    <div className="flex-1 p-6">
                        <IsiPlazaFlashMessages />
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
