import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="dark relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden bg-slate-950 p-6 text-slate-100 md:p-10">
            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(56,189,248,0.22),transparent_40%),radial-gradient(circle_at_85%_80%,rgba(16,185,129,0.18),transparent_45%)]" />
            <div className="w-full max-w-sm">
                <div className="relative flex flex-col gap-8 rounded-3xl border border-white/10 bg-slate-900/70 p-7 shadow-2xl shadow-slate-950/40 backdrop-blur-md">
                    <div className="flex flex-col items-center gap-4">
                        <Link
                            href={home()}
                            className="flex flex-col items-center gap-2 font-medium"
                        >
                            <div className="brand-mark mb-1 flex h-10 w-10 items-center justify-center rounded-md shadow-sm">
                                <AppLogoIcon className="size-6 fill-current text-white" />
                            </div>
                            <span className="sr-only">{title}</span>
                        </Link>

                        <div className="space-y-2 text-center">
                            <h1 className="text-xl font-medium text-white">{title}</h1>
                            <p className="text-center text-sm text-slate-300">
                                {description}
                            </p>
                        </div>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
