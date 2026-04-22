import { Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    AlertTriangle,
    ArrowRight,
    ArrowUpRight,
    Building2,
    CalendarClock,
    CheckCircle2,
    Clock3,
    ShieldCheck,
    Vote,
    Users,
} from 'lucide-react';
import { PageHero } from '@/components/page-hero';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { ui as swaggerUi } from '@/routes/swagger';

type DashboardProps = {
    auth: {
        user: {
            name: string;
            email: string;
        };
    };
    name: string;
    dashboardData?: {
        overview?: {
            active_elections: number;
            registered_voters: number;
            votes_today: number;
            turnout_rate: number;
            total_elections: number;
            organizations_count: number;
        };
        pipeline?: Array<{
            name: string;
            stage: string;
            progress: number;
            organization?: string | null;
            votes_recorded?: number;
        }>;
        activity_feed?: Array<{
            title: string;
            time: string;
            status: 'success' | 'info' | 'warning' | 'neutral' | string;
        }>;
    };
};

type PipelineItem = {
    name: string;
    stage: string;
    progress: number;
    organization?: string | null;
    votes_recorded?: number;
};

type ActivityItem = {
    title: string;
    time: string;
    status: 'success' | 'info' | 'warning' | 'neutral' | string;
};

const defaultOverview = {
    active_elections: 0,
    registered_voters: 0,
    votes_today: 0,
    turnout_rate: 0,
    total_elections: 0,
    organizations_count: 0,
};

const statusIcon = {
    success: CheckCircle2,
    info: Activity,
    warning: AlertTriangle,
    neutral: Clock3,
};

function progressWidthClass(progress: number): string {
    if (progress >= 90) {
        return 'w-[95%]';
    }

    if (progress >= 75) {
        return 'w-[78%]';
    }

    if (progress >= 40) {
        return 'w-[42%]';
    }

    return 'w-[25%]';
}

export default function Dashboard() {
    const { auth, name, dashboardData } = usePage<DashboardProps>().props;
    const overview = dashboardData?.overview ?? defaultOverview;

    const kpiCards = [
        {
            title: 'Active elections',
            value: String(overview.active_elections),
            detail: `${overview.total_elections} total elections in scope`,
            icon: Vote,
            tone: 'text-emerald-600',
        },
        {
            title: 'Registered voters',
            value: overview.registered_voters.toLocaleString(),
            detail: `${overview.organizations_count} organizations connected`,
            icon: Users,
            tone: 'text-blue-600',
        },
        {
            title: 'Ballots cast today',
            value: overview.votes_today.toLocaleString(),
            detail: `Overall turnout ${overview.turnout_rate}%`,
            icon: Activity,
            tone: 'text-orange-600',
        },
        {
            title: 'Integrity checks',
            value: '100%',
            detail: 'No integrity anomalies flagged',
            icon: ShieldCheck,
            tone: 'text-violet-600',
        },
    ];

    const electionPipeline: PipelineItem[] = dashboardData?.pipeline ?? [];
    const activityFeed: ActivityItem[] = dashboardData?.activity_feed ?? [];
    const hasData = overview.total_elections > 0 || overview.organizations_count > 0;

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl bg-linear-to-b from-cyan-500/6 via-transparent to-transparent p-4">
                <PageHero
                    eyebrow="Operations center"
                    title={
                        <>
                            {auth.user.name},
                            {' '}
                            {hasData
                                ? 'your election operations are on track.'
                                : 'your workspace is ready for setup.'}
                        </>
                    }
                    description={
                        hasData
                            ? `${name} overview for today, with live status snapshots.`
                            : 'Create your first organization and election to start tracking live metrics.'
                    }
                    startGlowClass="bg-cyan-400/15"
                    endGlowClass="bg-emerald-400/20"
                    actions={(
                        <>
                            <Badge variant="secondary" className="bg-emerald-500/15 text-emerald-700">
                                Verified account
                            </Badge>
                            <Badge variant="outline">{auth.user.email}</Badge>
                            <Button asChild size="sm">
                                <Link href={swaggerUi()}>
                                    API Docs
                                    <ArrowUpRight className="ml-1 h-4 w-4" />
                                </Link>
                            </Button>
                        </>
                    )}
                    chips={(
                        <>
                            <Button asChild size="sm" variant="outline" className="bg-background/80">
                                <Link href="/organizations">
                                    <Building2 className="mr-2 h-4 w-4" />
                                    Manage Organizations
                                </Link>
                            </Button>
                            <Button asChild size="sm" variant="outline" className="bg-background/80">
                                <Link href="/elections">
                                    <CalendarClock className="mr-2 h-4 w-4" />
                                    Plan Elections
                                </Link>
                            </Button>
                            <Button asChild size="sm" variant="outline" className="bg-background/80">
                                <Link href="/results">
                                    View Results
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                        </>
                    )}
                />

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4 reveal-up">
                    {kpiCards.map((item) => {
                        const Icon = item.icon;

                        return (
                            <Card key={item.title} className="gap-4 rounded-2xl border-border/70 bg-card/95 shadow-sm">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-0">
                                    <CardDescription>{item.title}</CardDescription>
                                    <Icon className={`h-5 w-5 ${item.tone}`} />
                                </CardHeader>
                                <CardContent className="space-y-1">
                                    <p className="text-3xl font-semibold tracking-tight">{item.value}</p>
                                    <p className="text-sm text-muted-foreground">{item.detail}</p>
                                </CardContent>
                            </Card>
                        );
                    })}
                </section>

                <section className="grid gap-4 xl:grid-cols-3 reveal-up reveal-delay-1">
                    <Card className="rounded-2xl xl:col-span-2">
                        <CardHeader>
                            <CardTitle>Election pipeline</CardTitle>
                            <CardDescription>
                                Monitor readiness and progress across your active election programs.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {electionPipeline.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                                    No election pipelines yet. Create an election to monitor progress here.
                                </div>
                            ) : (
                                electionPipeline.map((election) => (
                                    <div key={election.name} className="space-y-2 rounded-xl border bg-card/70 p-3">
                                        <div className="flex items-center justify-between gap-2">
                                            <p className="text-sm font-medium">{election.name}</p>
                                            <Badge variant="outline">{election.stage}</Badge>
                                        </div>
                                        {election.organization ? (
                                            <p className="text-xs text-muted-foreground">
                                                {election.organization}
                                                {typeof election.votes_recorded === 'number'
                                                    ? ` • ${election.votes_recorded} vote sessions`
                                                    : ''}
                                            </p>
                                        ) : null}
                                        <div className="h-2 rounded-full bg-muted/80">
                                            <div
                                                className={`h-2 rounded-full bg-primary ${progressWidthClass(election.progress)}`}
                                            />
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            {election.progress}% completion
                                        </p>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card className="rounded-2xl">
                        <CardHeader>
                            <CardTitle>Live operations feed</CardTitle>
                            <CardDescription>
                                Real-time governance and voting workflow events.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {activityFeed.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                                    No recent operations yet. Activity events will appear once voting actions begin.
                                </div>
                            ) : (
                                activityFeed.map((event) => {
                                    const Icon =
                                        statusIcon[event.status as keyof typeof statusIcon] ?? statusIcon.neutral;

                                    return (
                                        <div
                                            key={event.title}
                                            className="flex items-start gap-3 rounded-xl border bg-card/70 p-3"
                                        >
                                            <Icon className="mt-0.5 h-4 w-4 text-muted-foreground" />
                                            <div className="space-y-1">
                                                <p className="text-sm font-medium leading-tight">
                                                    {event.title}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {event.time}
                                                </p>
                                            </div>
                                        </div>
                                    );
                                })
                            )}
                        </CardContent>
                    </Card>
                </section>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
