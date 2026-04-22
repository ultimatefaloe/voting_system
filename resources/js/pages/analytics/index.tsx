import { Head, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type AnalyticsPageProps = {
    analyticsData?: {
        summary: {
            published_elections: number;
            total_votes: number;
            total_vote_sessions: number;
            average_votes_per_election: number;
        };
        top_elections: Array<{
            id: number;
            title: string;
            organization: string | null;
            votes_count: number;
            vote_sessions_count: number;
            positions_count: number;
        }>;
        top_candidates: Array<{
            id: number;
            name: string;
            votes_count: number;
            position: string | null;
            election: string | null;
        }>;
        recent_published: Array<{
            id: number;
            title: string;
            organization: string | null;
            start_date: string | null;
            end_date: string | null;
            votes_count: number;
            vote_sessions_count: number;
        }>;
    };
};

export default function AnalyticsIndex() {
    const { analyticsData } = usePage<AnalyticsPageProps>().props;
    const summary = analyticsData?.summary ?? {
        published_elections: 0,
        total_votes: 0,
        total_vote_sessions: 0,
        average_votes_per_election: 0,
    };
    const topElections = analyticsData?.top_elections ?? [];
    const topCandidates = analyticsData?.top_candidates ?? [];
    const recentPublished = analyticsData?.recent_published ?? [];

    return (
        <>
            <Head title="Analytics" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <section className="rounded-xl border bg-card p-6 shadow-sm">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <p className="text-sm text-muted-foreground">Module</p>
                            <h1 className="text-2xl font-semibold tracking-tight">Analytics</h1>
                            <p className="text-sm text-muted-foreground">
                                Ranked insights across published election data in your current organization scope.
                            </p>
                        </div>
                        <Badge variant="secondary">Live data</Badge>
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardDescription>Published elections</CardDescription>
                            <CardTitle>{summary.published_elections}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardDescription>Total votes</CardDescription>
                            <CardTitle>{summary.total_votes}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardDescription>Total sessions</CardDescription>
                            <CardTitle>{summary.total_vote_sessions}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardDescription>Avg votes / election</CardDescription>
                            <CardTitle>{summary.average_votes_per_election}</CardTitle>
                        </CardHeader>
                    </Card>
                </section>

                <section className="grid gap-4 xl:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Top elections by vote volume</CardTitle>
                            <CardDescription>Most active published elections by total votes.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            {topElections.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-3 text-muted-foreground">
                                    No published election activity yet.
                                </div>
                            ) : (
                                topElections.map((item) => (
                                    <div key={item.id} className="rounded-lg border p-3">
                                        <p className="font-medium">{item.title}</p>
                                        <p className="text-xs text-muted-foreground">{item.organization ?? 'Unknown organization'}</p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {item.votes_count} votes · {item.vote_sessions_count} sessions · {item.positions_count} positions
                                        </p>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Top candidates by votes</CardTitle>
                            <CardDescription>Leading candidates across published elections.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            {topCandidates.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-3 text-muted-foreground">
                                    No candidate vote data yet.
                                </div>
                            ) : (
                                topCandidates.map((item) => (
                                    <div key={item.id} className="rounded-lg border p-3">
                                        <p className="font-medium">{item.name}</p>
                                        <p className="text-xs text-muted-foreground">
                                            {item.position ?? 'Unknown position'} · {item.election ?? 'Unknown election'}
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">{item.votes_count} votes</p>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>
                </section>

                <section>
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent published elections</CardTitle>
                            <CardDescription>Latest published outcomes available for analysis.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            {recentPublished.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-3 text-muted-foreground">
                                    No recently published elections.
                                </div>
                            ) : (
                                recentPublished.map((item) => (
                                    <div key={item.id} className="rounded-lg border p-3">
                                        <p className="font-medium">{item.title}</p>
                                        <p className="text-xs text-muted-foreground">{item.organization ?? 'Unknown organization'}</p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {item.start_date ?? 'No start date'} - {item.end_date ?? 'No end date'}
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {item.votes_count} votes · {item.vote_sessions_count} sessions
                                        </p>
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

AnalyticsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Analytics',
            href: '/analytics',
        },
    ],
};
