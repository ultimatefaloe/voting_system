import * as React from 'react';
import { SidebarInset } from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';
import type { AppVariant } from '@/types';

type Props = React.ComponentProps<'main'> & {
    variant?: AppVariant;
};

export function AppContent({ variant = 'sidebar', children, ...props }: Props) {
    if (variant === 'sidebar') {
        const { className, ...rest } = props;

        return (
            <SidebarInset
                className={cn(
                    'overflow-hidden bg-linear-to-b from-slate-900/95 via-slate-950/95 to-sky-950/90 text-slate-100 before:pointer-events-none before:absolute before:inset-0 before:bg-[radial-gradient(circle_at_18%_16%,rgba(34,211,238,0.18),transparent_34%),radial-gradient(circle_at_85%_78%,rgba(16,185,129,0.16),transparent_40%)]',
                    className,
                )}
                {...rest}
            >
                {children}
            </SidebarInset>
        );
    }

    return (
        <main
            className="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl"
            {...props}
        >
            {children}
        </main>
    );
}
