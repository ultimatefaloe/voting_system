import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <SidebarGroup className="px-2 py-1">
            <SidebarGroupLabel className="px-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                Control Hub
            </SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton
                            asChild
                            isActive={isCurrentUrl(item.href)}
                            className="h-10 rounded-xl border border-transparent px-3 text-[13px] font-medium text-slate-200/90 hover:border-cyan-300/25 hover:bg-cyan-400/8 hover:text-white data-[active=true]:border-cyan-300/40 data-[active=true]:bg-linear-to-r data-[active=true]:from-cyan-500/22 data-[active=true]:to-emerald-500/16 data-[active=true]:text-white data-[active=true]:shadow-sm"
                            tooltip={{ children: item.title }}
                        >
                            <Link href={item.href} prefetch>
                                {item.icon && (
                                    <item.icon className="size-4 text-current" />
                                )}
                                <span>{item.title}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
