import { Head, usePage } from '@inertiajs/react';
import { CheckSquare, KeyRound, Vote } from 'lucide-react';
import type { FormEvent} from 'react';
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

type BallotPageProps = {
    ballotPageData: {
        election: {
            id: number;
            title: string;
            description: string | null;
            type: 'public' | 'private';
            status: 'draft' | 'active' | 'stopped' | 'closed' | 'published';
            start_date: string | null;
            end_date: string | null;
        };
    };
};

type BallotResponse = {
    election_id: number;
    positions: Array<{
        id: number;
        title: string;
        description: string | null;
        max_votes: number;
        candidates: Array<{
            id: number;
            name: string;
            bio: string | null;
            avatar: string | null;
        }>;
        selected_candidates: number[];
    }>;
};

type Toast = {
    id: number;
    type: 'success' | 'error';
    message: string;
};

type ApiPayload = {
    message?: string;
    errors?: Record<string, string[] | string>;
};

async function parseJsonSafe(response: Response): Promise<ApiPayload> {
    try {
        return (await response.json()) as ApiPayload;
    } catch {
        return {};
    }
}

function firstValidationMessage(payload: ApiPayload): string | null {
    if (!payload.errors) {
        return null;
    }

    const firstEntry = Object.values(payload.errors)[0];

    if (Array.isArray(firstEntry)) {
        return firstEntry[0] ?? null;
    }

    if (typeof firstEntry === 'string') {
        return firstEntry;
    }

    return null;
}

function getApiErrorMessage(response: Response, payload: ApiPayload, fallback: string): string {
    if (payload.message) {
        return payload.message;
    }

    const validationMessage = firstValidationMessage(payload);

    if (validationMessage) {
        return validationMessage;
    }

    if (response.status === 401 || response.status === 403) {
        return 'This voter token is invalid or expired. Request a new access token.';
    }

    if (response.status === 404) {
        return 'This ballot could not be found.';
    }

    if (response.status === 422) {
        return 'Submitted vote data is invalid. Review selections and try again.';
    }

    if (response.status === 429) {
        return 'Too many attempts. Please wait and try again.';
    }

    if (response.status >= 500) {
        return 'A server error occurred. Please try again shortly.';
    }

    return fallback;
}

export default function VotingBallotPage() {
    const { ballotPageData } = usePage<BallotPageProps>().props;
    const election = ballotPageData.election;

    const initialToken = useMemo(() => {
        if (typeof window === 'undefined') {
            return '';
        }

        return new URLSearchParams(window.location.search).get('token') ?? '';
    }, []);

    const [voterToken, setVoterToken] = useState(initialToken);
    const [ballot, setBallot] = useState<BallotResponse | null>(null);
    const [busy, setBusy] = useState<'load' | 'submit' | null>(null);
    const [toasts, setToasts] = useState<Toast[]>([]);

    const pushToast = (type: Toast['type'], message: string) => {
        const id = Date.now() + Math.floor(Math.random() * 1000);
        setToasts((previous) => [...previous, { id, type, message }]);

        window.setTimeout(() => {
            setToasts((previous) => previous.filter((toast) => toast.id !== id));
        }, 3500);
    };

    const fetchBallot = async () => {
        if (busy !== null) {
            return;
        }

        if (!voterToken.trim()) {
            pushToast('error', 'Voter token is required.');

            return;
        }

        setBusy('load');

        try {
            const response = await fetch(`/api/elections/${election.id}/ballot`, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-Voter-Token': voterToken.trim(),
                },
            });

            const payload = (await response.json().catch(() => ({}))) as BallotResponse & ApiPayload;

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, 'Failed to load ballot.'));
            }

            setBallot(payload);
            pushToast('success', 'Ballot loaded successfully.');
        } catch (error) {
            setBallot(null);
            pushToast('error', error instanceof Error ? error.message : 'Failed to load ballot.');
        } finally {
            setBusy(null);
        }
    };

    const toggleCandidate = (positionId: number, candidateId: number) => {
        if (!ballot) {
            return;
        }

        setBallot((previous) => {
            if (!previous) {
                return previous;
            }

            const nextPositions = previous.positions.map((position) => {
                if (position.id !== positionId) {
                    return position;
                }

                const alreadySelected = position.selected_candidates.includes(candidateId);

                if (alreadySelected) {
                    return {
                        ...position,
                        selected_candidates: position.selected_candidates.filter((id) => id !== candidateId),
                    };
                }

                if (position.selected_candidates.length >= position.max_votes) {
                    pushToast('error', `You can only select ${position.max_votes} candidate(s) for ${position.title}.`);

                    return position;
                }

                return {
                    ...position,
                    selected_candidates: [...position.selected_candidates, candidateId],
                };
            });

            return {
                ...previous,
                positions: nextPositions,
            };
        });
    };

    const handleSubmitVotes = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (busy !== null) {
            return;
        }

        if (!ballot) {
            pushToast('error', 'Load your ballot before submitting votes.');

            return;
        }

        const votes = ballot.positions.flatMap((position) =>
            position.selected_candidates.map((candidateId) => ({
                position_id: position.id,
                candidate_id: candidateId,
            })),
        );

        if (votes.length === 0) {
            pushToast('error', 'Select at least one candidate before submitting.');

            return;
        }

        setBusy('submit');

        try {
            const response = await fetch(`/api/elections/${election.id}/votes`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Voter-Token': voterToken.trim(),
                },
                body: JSON.stringify({ votes }),
            });

            const payload = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, 'Failed to submit votes.'));
            }

            pushToast('success', payload.message ?? 'Votes submitted successfully.');
            await fetchBallot();
        } catch (error) {
            pushToast('error', error instanceof Error ? error.message : 'Failed to submit votes.');
        } finally {
            setBusy(null);
        }
    };

    return (
        <>
            <Head title={`Ballot · ${election.title}`} />

            <div className="mx-auto flex w-full max-w-5xl flex-col gap-6 bg-linear-to-b from-sky-500/6 via-transparent to-transparent px-4 py-6">
                <PageHero
                    eyebrow="Voter experience"
                    title={election.title}
                    description={election.description ?? 'Select your preferred candidates and submit your votes.'}
                    startGlowClass="bg-sky-400/15"
                    endGlowClass="bg-cyan-400/15"
                    actions={(
                        <>
                            <Badge variant="outline" className="capitalize">{election.type}</Badge>
                            <Badge variant="secondary" className="capitalize">{election.status}</Badge>
                        </>
                    )}
                    chips={(
                        <>
                            <Badge variant="outline" className="gap-1">
                                <KeyRound className="h-3.5 w-3.5" />
                                Token-protected access
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <Vote className="h-3.5 w-3.5" />
                                Position-based selections
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <CheckSquare className="h-3.5 w-3.5" />
                                One-step submission
                            </Badge>
                        </>
                    )}
                />

                <div
                    className="fixed right-4 top-4 z-50 flex w-full max-w-sm flex-col gap-2"
                    aria-live="polite"
                    aria-atomic="true"
                >
                    {toasts.map((toast) => (
                        <div
                            key={toast.id}
                            role="status"
                            className={`rounded-lg border px-3 py-2 text-sm shadow-md ${
                                toast.type === 'error'
                                    ? 'border-red-300 bg-red-50 text-red-700'
                                    : 'border-emerald-300 bg-emerald-50 text-emerald-700'
                            }`}
                        >
                            {toast.message}
                        </div>
                    ))}
                </div>

                <Card className="rounded-2xl reveal-up">
                    <CardHeader>
                        <CardTitle>Voter access</CardTitle>
                        <CardDescription>Enter your voter token to load and resume this ballot.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-col gap-2 sm:flex-row">
                            <label htmlFor="voter-token" className="sr-only">Voter token</label>
                            <input
                                id="voter-token"
                                type="text"
                                aria-label="Voter token"
                                placeholder="voter_xxxxxxxxx"
                                value={voterToken}
                                onChange={(event) => setVoterToken(event.target.value)}
                                className="h-10 flex-1 rounded-md border bg-background px-3 text-sm"
                                disabled={busy !== null}
                            />
                            <Button onClick={fetchBallot} disabled={busy !== null}>
                                {busy === 'load' ? 'Loading...' : 'Load ballot'}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <form className="space-y-4" onSubmit={handleSubmitVotes}>
                    {ballot?.positions?.length ? (
                        ballot.positions.map((position) => (
                            <Card key={position.id} className="rounded-2xl reveal-up reveal-delay-1">
                                <CardHeader>
                                    <CardTitle>{position.title}</CardTitle>
                                    <CardDescription>
                                        {position.description ?? 'No description provided.'} · Select up to {position.max_votes}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <fieldset className="space-y-2">
                                        <legend className="sr-only">Candidate selection for {position.title}</legend>
                                        {position.candidates.map((candidate) => {
                                            const isSelected = position.selected_candidates.includes(candidate.id);

                                            return (
                                                <label
                                                    key={candidate.id}
                                                    className="flex cursor-pointer items-start gap-3 rounded-lg border bg-card/70 p-3"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={isSelected}
                                                        onChange={() => toggleCandidate(position.id, candidate.id)}
                                                        className="mt-1"
                                                        disabled={busy !== null}
                                                    />
                                                    <div>
                                                        <p className="font-medium">{candidate.name}</p>
                                                        <p className="text-xs text-muted-foreground">
                                                            {candidate.bio ?? 'No biography provided.'}
                                                        </p>
                                                    </div>
                                                </label>
                                            );
                                        })}
                                    </fieldset>
                                </CardContent>
                            </Card>
                        ))
                    ) : (
                        <Card className="rounded-2xl reveal-up reveal-delay-1">
                            <CardHeader>
                                <CardTitle>{busy === 'load' ? 'Loading ballot...' : 'Ballot not loaded'}</CardTitle>
                                <CardDescription>
                                    {busy === 'load'
                                        ? 'Please wait while we fetch your ballot.'
                                        : 'Provide a valid voter token and click Load ballot.'}
                                </CardDescription>
                            </CardHeader>
                        </Card>
                    )}

                    <div className="flex justify-end">
                        <Button type="submit" disabled={!ballot || busy !== null}>
                            {busy === 'submit' ? 'Submitting...' : 'Submit votes'}
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}
