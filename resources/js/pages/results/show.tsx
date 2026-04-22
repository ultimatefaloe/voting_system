import { Head, Link, usePage } from '@inertiajs/react';
import { BarChart3, FileBarChart, Medal } from 'lucide-react';
import { useMemo, useState } from 'react';
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

type CandidateResult = {
    id: number;
    name: string;
    bio: string | null;
    votes_count: number;
    percentage: number;
};

type PositionResult = {
    id: number;
    title: string;
    description: string | null;
    max_votes: number;
    total_votes: number;
    candidates: CandidateResult[];
};

type ResultShowProps = {
    resultData: {
        election: {
            id: number;
            organization: string | null;
            organization_slug: string | null;
            title: string;
            status: 'draft' | 'active' | 'stopped' | 'closed' | 'published';
            type: 'public' | 'private';
            start_date: string | null;
            end_date: string | null;
        };
        summary: {
            positions_count: number;
            vote_sessions_count: number;
            votes_count: number;
        };
        positions: PositionResult[];
    };
};

const statusVariant: Record<ResultShowProps['resultData']['election']['status'], 'default' | 'secondary' | 'outline'> = {
    draft: 'secondary',
    active: 'default',
    stopped: 'outline',
    closed: 'outline',
    published: 'default',
};

export default function ResultsShow() {
    const { resultData } = usePage<ResultShowProps>().props;
    const { election, summary, positions } = resultData;
    const [candidateQuery, setCandidateQuery] = useState('');
    const [sortMode, setSortMode] = useState<'votes_desc' | 'votes_asc' | 'name_asc'>('votes_desc');
    const generatedAt = useMemo(() => new Date().toLocaleString(), []);

    const normalizedQuery = candidateQuery.trim().toLowerCase();

    const visiblePositions = useMemo(() => {
        return positions
            .map((position) => {
                const filteredCandidates = position.candidates
                    .filter((candidate) => {
                        if (!normalizedQuery) {
                            return true;
                        }

                        return candidate.name.toLowerCase().includes(normalizedQuery);
                    })
                    .sort((left, right) => {
                        if (sortMode === 'votes_desc') {
                            return right.votes_count - left.votes_count;
                        }

                        if (sortMode === 'votes_asc') {
                            return left.votes_count - right.votes_count;
                        }

                        return left.name.localeCompare(right.name);
                    });

                return {
                    ...position,
                    candidates: filteredCandidates,
                };
            })
            .filter((position) => position.candidates.length > 0 || !normalizedQuery);
    }, [positions, normalizedQuery, sortMode]);

    const exportCsv = () => {
        const lines = [
            'position,candidate,votes,percentage',
            ...positions.flatMap((position) =>
                position.candidates.map((candidate) => {
                    const safePosition = `"${position.title.replaceAll('"', '""')}"`;
                    const safeCandidate = `"${candidate.name.replaceAll('"', '""')}"`;

                    return `${safePosition},${safeCandidate},${candidate.votes_count},${candidate.percentage}`;
                }),
            ),
        ];

        const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = `results-${election.id}.csv`;
        anchor.click();
        URL.revokeObjectURL(url);
    };

    const exportJson = () => {
        const payload = {
            election,
            summary,
            positions,
            generated_at: new Date().toISOString(),
        };

        const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = `results-${election.id}.json`;
        anchor.click();
        URL.revokeObjectURL(url);
    };

    return (
        <>
            <Head title={`Results: ${election.title}`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl bg-linear-to-b from-amber-500/7 via-transparent to-transparent p-4">
                <section className="hidden print:block">
                    <h1 className="text-2xl font-semibold tracking-tight">Election Results Report</h1>
                    <p className="text-sm text-muted-foreground">{election.title}</p>
                    <p className="text-xs text-muted-foreground">Generated: {generatedAt}</p>
                </section>

                <PageHero
                    eyebrow="Results command view"
                    title={election.title}
                    description={(
                        <>
                            <span>{election.organization ?? 'Unknown organization'}</span>
                            <span className="mt-1 block text-xs">
                                {election.start_date ?? 'No start date'} - {election.end_date ?? 'No end date'}
                            </span>
                        </>
                    )}
                    startGlowClass="bg-amber-400/15"
                    endGlowClass="bg-orange-400/15"
                    actions={(
                        <>
                            <Badge variant="outline" className="capitalize">
                                {election.type}
                            </Badge>
                            <Badge variant={statusVariant[election.status]} className="capitalize">
                                {election.status}
                            </Badge>
                        </>
                    )}
                    chips={(
                        <>
                            <Badge variant="outline" className="gap-1 print:hidden">
                                <FileBarChart className="h-3.5 w-3.5" />
                                Exportable reports
                            </Badge>
                            <Badge variant="outline" className="gap-1 print:hidden">
                                <BarChart3 className="h-3.5 w-3.5" />
                                Position analytics
                            </Badge>
                            <Badge variant="outline" className="gap-1 print:hidden">
                                <Medal className="h-3.5 w-3.5" />
                                Candidate ranking
                            </Badge>
                        </>
                    )}
                />

                <section className="grid gap-4 md:grid-cols-3 reveal-up">
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Positions</CardDescription>
                            <CardTitle>{summary.positions_count}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Vote sessions</CardDescription>
                            <CardTitle>{summary.vote_sessions_count}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Total votes cast</CardDescription>
                            <CardTitle>{summary.votes_count}</CardTitle>
                        </CardHeader>
                    </Card>
                </section>

                <Card className="rounded-2xl reveal-up reveal-delay-1">
                    <CardHeader>
                        <CardTitle>Per-position breakdown</CardTitle>
                        <CardDescription>
                            Candidate totals and share of votes for each position.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid gap-2 md:grid-cols-[1fr_auto_auto_auto_auto] print:hidden">
                            <input
                                type="text"
                                value={candidateQuery}
                                onChange={(event) => setCandidateQuery(event.target.value)}
                                placeholder="Filter candidates by name"
                                className="h-9 rounded-md border bg-background px-3 text-sm"
                            />
                            <select
                                aria-label="Sort candidates"
                                value={sortMode}
                                onChange={(event) => setSortMode(event.target.value as 'votes_desc' | 'votes_asc' | 'name_asc')}
                                className="h-9 rounded-md border bg-background px-2 text-sm"
                            >
                                <option value="votes_desc">Votes: high to low</option>
                                <option value="votes_asc">Votes: low to high</option>
                                <option value="name_asc">Name: A to Z</option>
                            </select>
                            <Button type="button" variant="outline" onClick={exportCsv}>
                                Export CSV
                            </Button>
                            <Button type="button" variant="outline" onClick={exportJson}>
                                Export JSON
                            </Button>
                            <Button type="button" variant="outline" onClick={() => window.print()}>
                                Print report
                            </Button>
                        </div>

                        {visiblePositions.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                                No matching candidates for the current filter.
                            </div>
                        ) : (
                            visiblePositions.map((position) => (
                                <div key={position.id} className="rounded-xl border bg-card/70 p-4">
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <p className="font-medium">{position.title}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {position.description ?? 'No description'}
                                            </p>
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            Max picks: {position.max_votes} · Votes: {position.total_votes}
                                        </div>
                                    </div>

                                    <div className="mt-3 space-y-2">
                                        {position.candidates.length === 0 ? (
                                            <p className="text-xs text-muted-foreground">No candidates configured.</p>
                                        ) : (
                                            position.candidates.map((candidate) => (
                                                <div key={candidate.id} className="rounded-md border bg-card/80 p-3">
                                                    <div className="flex items-center justify-between gap-2">
                                                        <p className="text-sm font-medium">{candidate.name}</p>
                                                        <p className="text-sm text-muted-foreground">
                                                            {candidate.votes_count} votes ({candidate.percentage}%)
                                                        </p>
                                                    </div>
                                                    <meter
                                                        className="mt-2 h-2 w-full"
                                                        min={0}
                                                        max={100}
                                                        value={Math.max(0, Math.min(100, candidate.percentage))}
                                                    />
                                                    {candidate.bio ? (
                                                        <p className="mt-1 text-xs text-muted-foreground">
                                                            {candidate.bio}
                                                        </p>
                                                    ) : null}
                                                </div>
                                            ))
                                        )}
                                    </div>
                                </div>
                            ))
                        )}
                    </CardContent>
                </Card>

                <div className="flex flex-wrap gap-2 print:hidden">
                    <Button asChild variant="outline">
                        <Link href="/results">Back to results list</Link>
                    </Button>
                    <Button asChild variant="outline">
                        <Link href="/analytics">Open analytics</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}

ResultsShow.layout = {
    breadcrumbs: [
        {
            title: 'Results',
            href: '/results',
        },
        {
            title: 'Details',
            href: '#',
        },
    ],
};
