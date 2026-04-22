import { Head, Link, usePage } from '@inertiajs/react';
import { CalendarClock, PlayCircle, Trophy } from 'lucide-react';
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

type ElectionItem = {
    id: number;
    organization_id: number;
    organization: string | null;
    title: string;
    type: 'public' | 'private';
    status: 'draft' | 'active' | 'stopped' | 'closed' | 'published';
    start_date: string | null;
    end_date: string | null;
    positions_count: number;
    vote_sessions_count: number;
};

type ElectionsPageProps = {
    electionsData?: {
        items: ElectionItem[];
        summary: {
            total: number;
            draft: number;
            active: number;
            published: number;
        };
    };
};

const statusVariant: Record<ElectionItem['status'], 'default' | 'secondary' | 'outline'> = {
    draft: 'secondary',
    active: 'default',
    stopped: 'outline',
    closed: 'outline',
    published: 'default',
};

export default function ElectionsIndex() {
    const { electionsData } = usePage<ElectionsPageProps>().props;
    const elections = electionsData?.items ?? [];
    const summary = electionsData?.summary ?? {
        total: 0,
        draft: 0,
        active: 0,
        published: 0,
    };

    return (
        <>
            <Head title="Elections" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl bg-linear-to-b from-cyan-500/6 via-transparent to-transparent p-4">
                <PageHero
                    eyebrow="Lifecycle control"
                    title="Elections"
                    description="Coordinate election timelines from draft to publication with real-time status visibility."
                    startGlowClass="bg-cyan-400/15"
                    endGlowClass="bg-sky-400/15"
                    actions={(
                        <>
                            <Badge variant="secondary" className="bg-cyan-500/15 text-cyan-700">
                                Lifecycle-driven
                            </Badge>
                            <Button asChild size="sm" variant="outline">
                                <Link href="/organizations">Organizations first</Link>
                            </Button>
                        </>
                    )}
                    chips={(
                        <>
                            <Badge variant="outline" className="gap-1">
                                <CalendarClock className="h-3.5 w-3.5" />
                                Planned windows
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <PlayCircle className="h-3.5 w-3.5" />
                                Active monitoring
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <Trophy className="h-3.5 w-3.5" />
                                Outcome publication
                            </Badge>
                        </>
                    )}
                />

                <section className="grid gap-4 md:grid-cols-4 reveal-up">
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Total elections</CardDescription>
                            <CardTitle>{summary.total}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Draft</CardDescription>
                            <CardTitle>{summary.draft}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Active</CardDescription>
                            <CardTitle>{summary.active}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Published</CardDescription>
                            <CardTitle>{summary.published}</CardTitle>
                        </CardHeader>
                    </Card>
                </section>

                <section className="grid gap-4 lg:grid-cols-2 reveal-up reveal-delay-1">
                    <Card className="rounded-2xl lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Election workspace</CardTitle>
                            <CardDescription>
                                Organization-scoped elections with lifecycle and participation visibility.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            {elections.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-3 text-muted-foreground">
                                    No elections yet. Start by creating one from your organization context.
                                </div>
                            ) : (
                                elections.map((election) => (
                                    <div key={election.id} className="rounded-xl border bg-card/70 p-3">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <p className="text-sm font-medium">{election.title}</p>
                                            <div className="flex items-center gap-2">
                                                <Badge variant="outline" className="capitalize">
                                                    {election.type}
                                                </Badge>
                                                <Badge variant={statusVariant[election.status]} className="capitalize">
                                                    {election.status}
                                                </Badge>
                                            </div>
                                        </div>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {election.organization ?? 'Unassigned organization'}
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {election.positions_count} positions • {election.vote_sessions_count} vote sessions
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {election.start_date ?? 'No start date'} - {election.end_date ?? 'No end date'}
                                        </p>
                                        <div className="pt-2">
                                            <Button asChild size="sm" variant="outline">
                                                <Link href={`/elections/${election.id}`}>View details</Link>
                                            </Button>
                                        </div>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>
                </section>
            </div>
        </>
    );
}

ElectionsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Elections',
            href: '/elections',
        },
    ],
};
