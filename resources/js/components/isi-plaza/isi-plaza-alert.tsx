import { cn } from '@/lib/utils';
import { AlertCircle, AlertTriangle, CheckCircle2, Info } from 'lucide-react';
import type { ReactNode } from 'react';

export type IsiPlazaAlertVariant = 'success' | 'error' | 'warning' | 'info';

const variantStyles: Record<
    IsiPlazaAlertVariant,
    { container: string; icon: string; title: string; Icon: typeof CheckCircle2 }
> = {
    success: {
        container: 'border-emerald-200 bg-emerald-50 text-emerald-950',
        icon: 'text-emerald-600',
        title: 'text-emerald-800',
        Icon: CheckCircle2,
    },
    error: {
        container: 'border-red-200 bg-red-50 text-red-950',
        icon: 'text-red-600',
        title: 'text-red-800',
        Icon: AlertCircle,
    },
    warning: {
        container: 'border-amber-200 bg-amber-50 text-amber-950',
        icon: 'text-amber-600',
        title: 'text-amber-800',
        Icon: AlertTriangle,
    },
    info: {
        container: 'border-sky-200 bg-sky-50 text-sky-950',
        icon: 'text-sky-600',
        title: 'text-sky-800',
        Icon: Info,
    },
};

interface IsiPlazaAlertProps {
    variant: IsiPlazaAlertVariant;
    title?: string;
    children: ReactNode;
    className?: string;
}

export function IsiPlazaAlert({ variant, title, children, className }: IsiPlazaAlertProps) {
    const styles = variantStyles[variant];
    const Icon = styles.Icon;

    return (
        <div role="alert" className={cn('flex gap-3 rounded-xl border p-4 text-sm', styles.container, className)}>
            <Icon className={cn('mt-0.5 size-5 shrink-0', styles.icon)} aria-hidden />
            <div className="min-w-0 flex-1">
                {title && <p className={cn('mb-1 font-semibold', styles.title)}>{title}</p>}
                <div className="text-neutral-800">{children}</div>
            </div>
        </div>
    );
}
