import { Head, Link, usePage } from '@inertiajs/react';
import { Activity, Clock4, ListChecks } from 'lucide-react';
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

type VotingSessionItem = {
    id: number;
    election_id: number;
    election_title: string | null;
    election_status: 'draft' | 'active' | 'stopped' | 'closed' | 'published' | null;
    election_type: 'public' | 'private' | null;
    organization: string | null;
    votes_count: number;
    submitted_at: string | null;
    created_at: string | null;
};

type VotingSessionsPageProps = {
    votingSessionsData?: {
        items: VotingSessionItem[];
        summary: {
            total_sessions: number;
            total_votes_cast: number;
            elections_with_activity: number;
        };
    };
};

const statusVariant: Record<Exclude<VotingSessionItem['election_status'], null>, 'default' | 'secondary' | 'outline'> = {
    draft: 'secondary',
    active: 'default',
    stopped: 'outline',
    closed: 'outline',
    published: 'default',
};

export default function VotingSessionsIndex() {
    const { votingSessionsData } = usePage<VotingSessionsPageProps>().props;
    const sessions = votingSessionsData?.items ?? [];
    const summary = votingSessionsData?.summary ?? {
        total_sessions: 0,
        total_votes_cast: 0,
        elections_with_activity: 0,
    };

    return (
        <>
            <Head title="Voting Sessions" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl bg-linear-to-b from-violet-500/6 via-transparent to-transparent p-4">
                <PageHero
                    eyebrow="Participation stream"
                    title="Voting Sessions"
                    description="Session-level activity feed sourced from real vote session records."
                    startGlowClass="bg-violet-400/15"
                    endGlowClass="bg-indigo-400/15"
                    actions={(
                        <>
                            <Badge variant="secondary" className="bg-violet-500/15 text-violet-700">
                                Live data
                            </Badge>
                            <Button asChild size="sm" variant="outline">
                                <Link href="/results">View results</Link>
                            </Button>
                        </>
                    )}
                    chips={(
                        <>
                            <Badge variant="outline" className="gap-1">
                                <Activity className="h-3.5 w-3.5" />
                                Real-time activity pulse
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <Clock4 className="h-3.5 w-3.5" />
                                Submission timelines
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <ListChecks className="h-3.5 w-3.5" />
                                Session-level auditability
                            </Badge>
                        </>
                    )}
                />

                <section className="grid gap-4 md:grid-cols-3 reveal-up">
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Total sessions</CardDescription>
                            <CardTitle>{summary.total_sessions}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Total votes cast</CardDescription>
                            <CardTitle>{summary.total_votes_cast}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Elections with activity</CardDescription>
                            <CardTitle>{summary.elections_with_activity}</CardTitle>
                        </CardHeader>
                    </Card>
                </section>

                <section className="grid gap-4 reveal-up reveal-delay-1">
                    <Card className="rounded-2xl">
                        <CardHeader>
                            <CardTitle>Recent session activity</CardTitle>
                            <CardDescription>Latest 50 voting sessions within your organization scope.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            {sessions.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-3 text-muted-foreground">
                                    No vote sessions yet.
                                </div>
                            ) : (
                                sessions.map((session) => (
                                    <div key={session.id} className="rounded-xl border bg-card/70 p-3">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <div>
                                                <p className="font-medium">{session.election_title ?? 'Unknown election'}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    {session.organization ?? 'Unknown organization'} · Session #{session.id}
                                                </p>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                {session.election_type ? (
                                                    <Badge variant="outline" className="capitalize">
                                                        {session.election_type}
                                                    </Badge>
                                                ) : null}
                                                {session.election_status ? (
                                                    <Badge variant={statusVariant[session.election_status]} className="capitalize">
                                                        {session.election_status}
                                                    </Badge>
                                                ) : null}
                                            </div>
                                        </div>

                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {session.votes_count} votes in this session
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            Submitted: {session.submitted_at ?? 'N/A'} · Created: {session.created_at ?? 'N/A'}
                                        </p>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card className="rounded-2xl">
                        <CardHeader>
                            <CardTitle>Operational notes</CardTitle>
                            <CardDescription>Current implementation boundaries for this module.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm text-muted-foreground">
                            <div className="rounded-lg border bg-card/70 p-3">Session list is now sourced from the database.</div>
                            <div className="rounded-lg border bg-card/70 p-3">Ballot casting experience is still a dedicated next step.</div>
                            <div className="rounded-lg border bg-card/70 p-3">Stats endpoint is available for election-level analytics expansion.</div>
                        </CardContent>
                    </Card>
                </section>
            </div>
        </>
    );
}

VotingSessionsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Voting Sessions',
            href: '/voting-sessions',
        },
    ],
};
