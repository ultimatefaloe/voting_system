import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-9 items-center justify-center rounded-xl bg-linear-to-br from-cyan-500 to-emerald-500 text-sidebar-primary-foreground shadow-sm">
                <AppLogoIcon className="size-5 fill-current text-white" />
            </div>
            <div className="ml-2 grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-semibold tracking-tight">
                    VoteFlow Control
                </span>
                <span className="truncate text-[11px] text-slate-300/80">
                    Election Admin Console
                </span>
            </div>
        </>
    );
}
