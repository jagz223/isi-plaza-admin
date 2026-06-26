import { cn } from '@/lib/utils';

type OdonticaPanelBrandProps = {
    compact?: boolean;
    className?: string;
};

export function OdonticaPanelBrand({ compact = false, className }: OdonticaPanelBrandProps) {
    return (
        <div className={cn('flex flex-col items-center text-center', className)}>
            <span
                className={cn(
                    'font-black tracking-[0.12em] text-neutral-950',
                    compact ? 'text-xl' : 'text-3xl sm:text-4xl',
                )}>
                ODONTICA
            </span>
            <span
                className={cn(
                    'mt-1 font-medium uppercase tracking-[0.35em] text-neutral-600',
                    compact ? 'text-[9px]' : 'text-[11px] sm:text-xs',
                )}>
                Panel web
            </span>
        </div>
    );
}
