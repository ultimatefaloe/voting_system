import { Link } from '@inertiajs/react';
import {
    Activity,
    BarChart3,
    BookOpen,
    FolderGit2,
    LayoutGrid,
    ListChecks,
    PieChart,
    Settings,
    Vote,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Organizations',
        href: '/organizations',
        icon: Activity,
    },
    {
        title: 'Elections',
        href: '/elections',
        icon: Vote,
    },
    {
        title: 'Voting Sessions',
        href: '/voting-sessions',
        icon: ListChecks,
    },
    {
        title: 'Results',
        href: '/results',
        icon: PieChart,
    },
    {
        title: 'Analytics',
        href: '/analytics',
        icon: BarChart3,
    },
    {
        title: 'Settings',
        href: '/settings/profile',
        icon: Settings,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'API Docs',
        href: '/api/docs',
        icon: FolderGit2,
    },
    {
        title: 'Platform Guide',
        href: '/api/docs/api',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar
            collapsible="icon"
            variant="inset"
            className="border-r border-cyan-400/20 bg-linear-to-b from-slate-900/95 via-slate-900/90 to-sky-950/85 shadow-2xl shadow-slate-950/50 backdrop-blur-xl"
        >
            <SidebarHeader>
                <div className="rounded-xl border border-cyan-300/25 bg-linear-to-r from-cyan-500/20 to-emerald-500/18 px-2 py-2 text-xs font-medium text-cyan-100">
                    Command Center
                </div>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
