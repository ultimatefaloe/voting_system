import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type PageHeroProps = {
    eyebrow: string;
    title: ReactNode;
    description?: ReactNode;
    actions?: ReactNode;
    chips?: ReactNode;
    startGlowClass?: string;
    endGlowClass?: string;
    className?: string;
};

export function PageHero({
    eyebrow,
    title,
    description,
    actions,
    chips,
    startGlowClass = 'bg-cyan-400/15',
    endGlowClass = 'bg-emerald-400/15',
    className,
}: PageHeroProps) {
    return (
        <section className={cn('relative overflow-hidden rounded-2xl border border-cyan-300/20 bg-linear-to-r from-slate-900/75 via-slate-950/65 to-sky-950/65 p-6 shadow-sm backdrop-blur-sm reveal-fade', className)}>
            <div
                className={cn(
                    'pointer-events-none absolute -top-16 right-0 h-44 w-44 rounded-full blur-3xl',
                    startGlowClass,
                )}
            />
            <div
                className={cn(
                    'pointer-events-none absolute -bottom-20 left-1/4 h-40 w-40 rounded-full blur-2xl',
                    endGlowClass,
                )}
            />

            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="space-y-2">
                    <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                        {eyebrow}
                    </p>
                    <h1 className="text-2xl font-semibold tracking-tight">{title}</h1>
                    {description ? (
                        <p className="max-w-2xl text-sm text-muted-foreground">{description}</p>
                    ) : null}
                </div>
                {actions ? <div className="flex items-center gap-2">{actions}</div> : null}
            </div>

            {chips ? <div className="mt-5 flex flex-wrap gap-2">{chips}</div> : null}
        </section>
    );
}
