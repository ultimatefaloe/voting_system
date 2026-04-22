import { Head, Link, usePage } from '@inertiajs/react';
import { BarChart3, Medal, PieChart } from 'lucide-react';
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

type ResultItem = {
    id: number;
    organization: string | null;
    title: string;
    status: 'closed' | 'published';
    type: 'public' | 'private';
    positions_count: number;
    vote_sessions_count: number;
    votes_count: number;
    start_date: string | null;
    end_date: string | null;
};

type ResultsPageProps = {
    resultsData?: {
        items: ResultItem[];
        summary: {
            closed_or_published: number;
            published: number;
            closed_pending_publish: number;
        };
    };
};

const statusVariant: Record<ResultItem['status'], 'default' | 'outline'> = {
    closed: 'outline',
    published: 'default',
};

export default function ResultsIndex() {
    const { resultsData } = usePage<ResultsPageProps>().props;
    const items = resultsData?.items ?? [];
    const summary = resultsData?.summary ?? {
        closed_or_published: 0,
        published: 0,
        closed_pending_publish: 0,
    };

    return (
        <>
            <Head title="Results" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl bg-linear-to-b from-amber-500/7 via-transparent to-transparent p-4">
                <PageHero
                    eyebrow="Outcome intelligence"
                    title="Results"
                    description="Published and closed election outcomes from your organization scope."
                    startGlowClass="bg-amber-400/15"
                    endGlowClass="bg-orange-400/15"
                    actions={(
                        <>
                            <Badge variant="secondary" className="bg-amber-500/15 text-amber-700">
                                Live data
                            </Badge>
                            <Button asChild size="sm" variant="outline">
                                <Link href="/analytics">Open analytics</Link>
                            </Button>
                        </>
                    )}
                    chips={(
                        <>
                            <Badge variant="outline" className="gap-1">
                                <PieChart className="h-3.5 w-3.5" />
                                Turnout breakdowns
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <BarChart3 className="h-3.5 w-3.5" />
                                Comparative metrics
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <Medal className="h-3.5 w-3.5" />
                                Ranked winners
                            </Badge>
                        </>
                    )}
                />

                <section className="grid gap-4 md:grid-cols-3 reveal-up">
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Closed or published</CardDescription>
                            <CardTitle>{summary.closed_or_published}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Published</CardDescription>
                            <CardTitle>{summary.published}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Closed pending publish</CardDescription>
                            <CardTitle>{summary.closed_pending_publish}</CardTitle>
                        </CardHeader>
                    </Card>
                </section>

                <Card className="rounded-2xl reveal-up reveal-delay-1">
                    <CardHeader>
                        <CardTitle>Results-ready elections</CardTitle>
                        <CardDescription>Lifecycle-complete elections with turnout and vote volume context.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        {items.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-3 text-muted-foreground">
                                No closed or published elections yet.
                            </div>
                        ) : (
                            items.map((item) => (
                                <div key={item.id} className="rounded-xl border bg-card/70 p-3">
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <p className="font-medium">{item.title}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {item.organization ?? 'Unknown organization'}
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Badge variant="outline" className="capitalize">
                                                {item.type}
                                            </Badge>
                                            <Badge variant={statusVariant[item.status]} className="capitalize">
                                                {item.status}
                                            </Badge>
                                        </div>
                                    </div>

                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {item.positions_count} positions · {item.vote_sessions_count} sessions · {item.votes_count} votes
                                    </p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {item.start_date ?? 'No start date'} - {item.end_date ?? 'No end date'}
                                    </p>

                                    <div className="mt-3">
                                        <Button asChild size="sm" variant="outline">
                                            <Link href={`/results/${item.id}`}>View details</Link>
                                        </Button>
                                    </div>
                                </div>
                            ))
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

ResultsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Results',
            href: '/results',
        },
    ],
};
