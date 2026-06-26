import { Link, useForm, usePage } from '@inertiajs/react';
import { type LucideIcon, ClipboardList, FileText, KeyRound, LogOut, Stethoscope, Type, Menu, X } from 'lucide-react';
import { type PropsWithChildren, useState } from 'react';

import { OdonticaPanelBrand } from '@/components/odontica/odontica-panel-brand';
import { IsiPlazaFlashMessages } from '@/components/isi-plaza/isi-plaza-flash-messages';
import { useIsiPlazaLightShell } from '@/hooks/use-isi-plaza-light-shell';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const nav: { title: string; path: string; routeName: string; icon: LucideIcon }[] = [
    { title: 'Gestión', path: '/isi-plaza/gestion', routeName: 'isi-plaza.gestion', icon: ClipboardList },
    { title: 'Tratamientos', path: '/isi-plaza/tratamientos', routeName: 'isi-plaza.tratamientos.index', icon: Stethoscope },
    { title: 'Textos paciente', path: '/isi-plaza/textos-paciente', routeName: 'isi-plaza.textos-paciente.index', icon: FileText },
    { title: 'Textos médico', path: '/isi-plaza/textos-numeros', routeName: 'isi-plaza.textos-numeros.index', icon: Type },
    { title: 'Tokens', path: '/isi-plaza/ajustes-acceso', routeName: 'isi-plaza.ajustes-acceso.index', icon: KeyRound },
];

export default function IsiPlazaLayout({ title, children }: PropsWithChildren<{ title: string }>) {
    useIsiPlazaLightShell();

    const { url } = usePage();
    const path = url.split('?')[0].replace(/\/$/, '') || '/';

    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const signOutForm = useForm({});

    const submitSignOut = (e: React.FormEvent) => {
        e.preventDefault();
        signOutForm.post(route('isi-plaza.sign-out'));
    };

    return (
        <div className="isi-plaza-theme min-h-screen bg-white text-neutral-950">
            <div className="flex min-h-screen flex-col md:flex-row">
                <div
                    className="flex items-center justify-between px-4 py-3 md:hidden"
                    style={{ backgroundColor: '#121660' }}>
                    <span className="text-sm font-bold tracking-widest text-white">ODONTICA</span>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="text-white hover:bg-white/10"
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}>
                        {mobileMenuOpen ? <X className="size-5" /> : <Menu className="size-5" />}
                    </Button>
                </div>

                <aside
                    className={cn(
                        'flex w-full shrink-0 flex-col border-r border-neutral-200 bg-white md:w-72',
                        mobileMenuOpen ? 'block' : 'hidden md:flex',
                    )}>
                    <div className="relative hidden md:block">
                        <div className="h-24 bg-[#121660]" />
                        <div className="absolute inset-x-0 top-10 flex justify-center px-4">
                            <div className="w-full max-w-[220px] border border-neutral-200 bg-white px-4 py-4 shadow-sm">
                                <OdonticaPanelBrand compact />
                            </div>
                        </div>
                    </div>

                    <div className="border-b border-neutral-200 px-4 py-4 md:hidden">
                        <OdonticaPanelBrand compact />
                    </div>

                    <nav className="flex flex-wrap gap-2 p-4 md:mt-14 md:flex-col md:gap-1">
                        {nav.map((item) => {
                            const active = path === item.path || path.startsWith(`${item.path}/`);
                            const Icon = item.icon;

                            return (
                                <Link
                                    key={item.routeName}
                                    href={route(item.routeName)}
                                    onClick={() => setMobileMenuOpen(false)}
                                    className={cn(
                                        'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition-colors md:w-full md:rounded-lg md:px-3 md:py-2 md:font-medium',
                                        active
                                            ? 'bg-[#121660] text-white md:border-l-4 md:border-[#121660] md:bg-[#eef0fb] md:text-[#121660]'
                                            : 'border border-[#121660] text-[#121660] hover:bg-[#eef0fb] md:border-transparent md:text-neutral-700 md:hover:bg-neutral-50',
                                    )}>
                                    <Icon className="size-4 shrink-0" />
                                    {item.title}
                                </Link>
                            );
                        })}
                    </nav>

                    <div className="mt-auto border-t border-neutral-200 p-4">
                        <form onSubmit={submitSignOut}>
                            <Button
                                type="submit"
                                variant="outline"
                                className="w-full justify-center gap-2 border-neutral-300 text-neutral-800 hover:border-[#121660] hover:bg-[#eef0fb] hover:text-[#121660]"
                                disabled={signOutForm.processing}>
                                <LogOut className="size-4" />
                                Cerrar sesión
                            </Button>
                        </form>
                    </div>
                </aside>

                <main className="flex min-w-0 flex-1 flex-col bg-[#FAFAFA]">
                    <header className="border-b border-neutral-200 bg-white px-4 py-4 md:px-6">
                        <h1 className="text-lg font-semibold tracking-tight text-neutral-900">{title}</h1>
                    </header>
                    <div className="flex-1 overflow-x-auto p-4 md:p-6">
                        <IsiPlazaFlashMessages />
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
