import { Head, Link, router, usePage } from '@inertiajs/react';
import { CalendarClock, Flag, Vote } from 'lucide-react';
import type { FormEvent} from 'react';
import { useState } from 'react';
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
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type ElectionShowProps = {
    electionData: {
        election: {
            id: number;
            organization_id: number;
            organization: string | null;
            organization_slug: string | null;
            title: string;
            description: string | null;
            type: 'public' | 'private';
            status: 'draft' | 'active' | 'stopped' | 'closed' | 'published';
            start_date: string | null;
            end_date: string | null;
            created_at: string | null;
        };
        summary: {
            positions_count: number;
            vote_sessions_count: number;
        };
        positions: Array<{
            id: number;
            title: string;
            description: string | null;
            max_votes: number;
            candidates_count: number;
            candidates: Array<{
                id: number;
                name: string;
                bio: string | null;
                avatar: string | null;
                order: number;
            }>;
        }>;
        permissions: {
            can_update: boolean;
            can_start: boolean;
            can_stop: boolean;
            can_publish: boolean;
        };
        lifecycle: {
            can_start_now: boolean;
            can_stop_now: boolean;
            can_publish_now: boolean;
        };
    };
};

type ToastType = 'success' | 'error';
type ToastItem = {
    id: number;
    type: ToastType;
    message: string;
};

type ApiPayload = {
    message?: string;
    errors?: Record<string, string[] | string>;
};

type LifecycleAction = 'start' | 'stop' | 'close' | 'publish';
type ConfirmAction =
    | { kind: 'lifecycle'; action: LifecycleAction }
    | { kind: 'position-delete'; positionId: number; label: string }
    | { kind: 'candidate-delete'; positionId: number; candidateId: number; label: string }
    | null;

function getXsrfTokenFromCookie(): string {
    const tokenPair = document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith('XSRF-TOKEN='));

    if (!tokenPair) {
        return '';
    }

    return decodeURIComponent(tokenPair.split('=')[1] ?? '');
}

async function apiRequest(url: string, init: RequestInit = {}) {
    const token = getXsrfTokenFromCookie();

    const headers: HeadersInit = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(token ? { 'X-XSRF-TOKEN': token } : {}),
        ...(init.headers ?? {}),
    };

    return fetch(url, {
        credentials: 'same-origin',
        ...init,
        headers,
    });
}

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

    if (response.status === 401) {
        return 'Your session has expired. Please sign in again.';
    }

    if (response.status === 403) {
        return 'You do not have permission to perform this action.';
    }

    if (response.status === 404) {
        return 'This election resource was not found.';
    }

    if (response.status === 422) {
        return 'Submitted data is invalid. Please review and try again.';
    }

    if (response.status === 429) {
        return 'Too many attempts. Please wait and try again.';
    }

    if (response.status >= 500) {
        return 'A server error occurred. Please try again shortly.';
    }

    return fallback;
}

const statusVariant: Record<ElectionShowProps['electionData']['election']['status'], 'default' | 'secondary' | 'outline'> = {
    draft: 'secondary',
    active: 'default',
    stopped: 'outline',
    closed: 'outline',
    published: 'default',
};

export default function ElectionShow() {
    const { electionData } = usePage<ElectionShowProps>().props;
    const { election, summary, positions, permissions, lifecycle } = electionData;
    const canManageBallot = permissions.can_update && election.status === 'draft';

    const [toasts, setToasts] = useState<ToastItem[]>([]);
    const [confirmAction, setConfirmAction] = useState<ConfirmAction>(null);
    const [busyAction, setBusyAction] = useState<string | null>(null);

    const [positionTitle, setPositionTitle] = useState('');
    const [positionDescription, setPositionDescription] = useState('');
    const [positionMaxVotes, setPositionMaxVotes] = useState(1);
    const [candidateNames, setCandidateNames] = useState<Record<number, string>>({});
    const [candidateBios, setCandidateBios] = useState<Record<number, string>>({});

    const pushToast = (type: ToastType, message: string) => {
        const id = Date.now() + Math.floor(Math.random() * 1000);

        setToasts((previous) => [...previous, { id, type, message }]);

        window.setTimeout(() => {
            setToasts((previous) => previous.filter((toast) => toast.id !== id));
        }, 3500);
    };

    const refreshData = () => {
        router.reload({
            only: ['electionData'],
        });
    };

    const runLifecycleAction = async (action: LifecycleAction): Promise<boolean> => {
        if (busyAction !== null) {
            return false;
        }

        setBusyAction(`lifecycle:${action}`);

        try {
            const response = await apiRequest(
                `/api/organizations/${election.organization_id}/elections/${election.id}/${action}`,
                {
                    method: 'POST',
                },
            );

            const payload = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, `Failed to ${action} election.`));
            }

            pushToast('success', payload.message ?? `Election ${action} completed successfully.`);
            refreshData();

            return true;
        } catch (error) {
            pushToast('error', error instanceof Error ? error.message : `Failed to ${action} election.`);

            return false;
        } finally {
            setBusyAction(null);
        }
    };

    const createPosition = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (busyAction !== null) {
            return;
        }

        if (!positionTitle.trim()) {
            pushToast('error', 'Position title is required.');

            return;
        }

        setBusyAction('position:create');

        try {
            const response = await apiRequest(
                `/api/organizations/${election.organization_id}/elections/${election.id}/positions`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        title: positionTitle.trim(),
                        description: positionDescription.trim() || null,
                        max_votes: positionMaxVotes,
                    }),
                },
            );

            const payload = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, 'Failed to create position.'));
            }

            pushToast('success', payload.message ?? 'Position created successfully.');
            setPositionTitle('');
            setPositionDescription('');
            setPositionMaxVotes(1);
            refreshData();
        } catch (error) {
            pushToast('error', error instanceof Error ? error.message : 'Failed to create position.');
        } finally {
            setBusyAction(null);
        }
    };

    const deletePosition = async (positionId: number): Promise<boolean> => {
        if (busyAction !== null) {
            return false;
        }

        setBusyAction(`position:delete:${positionId}`);

        try {
            const response = await apiRequest(
                `/api/organizations/${election.organization_id}/elections/${election.id}/positions/${positionId}`,
                {
                    method: 'DELETE',
                },
            );
            const payload = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, 'Failed to delete position.'));
            }

            pushToast('success', payload.message ?? 'Position deleted successfully.');
            refreshData();

            return true;
        } catch (error) {
            pushToast('error', error instanceof Error ? error.message : 'Failed to delete position.');

            return false;
        } finally {
            setBusyAction(null);
        }
    };

    const createCandidate = async (positionId: number) => {
        if (busyAction !== null) {
            return;
        }

        const name = (candidateNames[positionId] ?? '').trim();

        if (!name) {
            pushToast('error', 'Candidate name is required.');

            return;
        }

        setBusyAction(`candidate:create:${positionId}`);

        try {
            const response = await apiRequest(
                `/api/organizations/${election.organization_id}/elections/${election.id}/positions/${positionId}/candidates`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name,
                        bio: (candidateBios[positionId] ?? '').trim() || null,
                    }),
                },
            );

            const payload = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, 'Failed to create candidate.'));
            }

            pushToast('success', payload.message ?? 'Candidate created successfully.');
            setCandidateNames((previous) => ({ ...previous, [positionId]: '' }));
            setCandidateBios((previous) => ({ ...previous, [positionId]: '' }));
            refreshData();
        } catch (error) {
            pushToast('error', error instanceof Error ? error.message : 'Failed to create candidate.');
        } finally {
            setBusyAction(null);
        }
    };

    const deleteCandidate = async (positionId: number, candidateId: number): Promise<boolean> => {
        if (busyAction !== null) {
            return false;
        }

        setBusyAction(`candidate:delete:${candidateId}`);

        try {
            const response = await apiRequest(
                `/api/organizations/${election.organization_id}/elections/${election.id}/positions/${positionId}/candidates/${candidateId}`,
                {
                    method: 'DELETE',
                },
            );
            const payload = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, 'Failed to delete candidate.'));
            }

            pushToast('success', payload.message ?? 'Candidate deleted successfully.');
            refreshData();

            return true;
        } catch (error) {
            pushToast('error', error instanceof Error ? error.message : 'Failed to delete candidate.');

            return false;
        } finally {
            setBusyAction(null);
        }
    };

    const actionAvailability: Record<LifecycleAction, boolean> = {
        start: permissions.can_start && lifecycle.can_start_now,
        stop: permissions.can_stop && lifecycle.can_stop_now,
        close: permissions.can_stop && (election.status === 'active' || election.status === 'stopped'),
        publish: permissions.can_publish && lifecycle.can_publish_now,
    };

    const actionLabels: Record<LifecycleAction, string> = {
        start: 'Start',
        stop: 'Stop',
        close: 'Close',
        publish: 'Publish',
    };

    const runConfirmedAction = async () => {
        if (!confirmAction) {
            return;
        }

        if (confirmAction.kind === 'lifecycle') {
            const ok = await runLifecycleAction(confirmAction.action);

            if (ok) {
                setConfirmAction(null);
            }

            return;
        }

        if (confirmAction.kind === 'position-delete') {
            const ok = await deletePosition(confirmAction.positionId);

            if (ok) {
                setConfirmAction(null);
            }

            return;
        }

        const ok = await deleteCandidate(confirmAction.positionId, confirmAction.candidateId);

        if (ok) {
            setConfirmAction(null);
        }
    };

    const isConfirmBusy = busyAction !== null;

    return (
        <>
            <Head title={`${election.title} · Election`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl bg-linear-to-b from-cyan-500/6 via-transparent to-transparent p-4">
                <PageHero
                    eyebrow="Election lifecycle"
                    title={election.title}
                    description={`${election.organization ?? 'Unknown organization'}${election.organization_slug ? ` · /${election.organization_slug}` : ''}`}
                    startGlowClass="bg-cyan-400/15"
                    endGlowClass="bg-sky-400/15"
                    actions={(
                        <>
                            <Badge variant="outline" className="capitalize">{election.type}</Badge>
                            <Badge variant={statusVariant[election.status]} className="capitalize">{election.status}</Badge>
                            <Button asChild size="sm" variant="outline">
                                <Link href="/elections">Back to elections</Link>
                            </Button>
                        </>
                    )}
                    chips={(
                        <>
                            <Badge variant="outline" className="gap-1">
                                <CalendarClock className="h-3.5 w-3.5" />
                                Timeboxed stages
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <Flag className="h-3.5 w-3.5" />
                                Controlled transitions
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <Vote className="h-3.5 w-3.5" />
                                Ballot governance
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

                <Dialog open={confirmAction !== null} onOpenChange={(open) => !open && setConfirmAction(null)}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>
                                {confirmAction?.kind === 'lifecycle' && confirmAction.action
                                    ? `${actionLabels[confirmAction.action]} election`
                                    : confirmAction?.kind === 'position-delete'
                                      ? 'Delete position'
                                      : confirmAction?.kind === 'candidate-delete'
                                        ? 'Delete candidate'
                                        : 'Confirm action'}
                            </DialogTitle>
                            <DialogDescription>
                                {confirmAction?.kind === 'lifecycle' && confirmAction.action
                                    ? `Are you sure you want to ${confirmAction.action} this election?`
                                    : confirmAction?.kind === 'position-delete'
                                      ? `Are you sure you want to delete position "${confirmAction.label}"?`
                                      : confirmAction?.kind === 'candidate-delete'
                                        ? `Are you sure you want to delete candidate "${confirmAction.label}"?`
                                        : 'Confirm this action.'}
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <DialogClose asChild>
                                <Button variant="outline" disabled={isConfirmBusy}>Cancel</Button>
                            </DialogClose>
                            <Button
                                variant="destructive"
                                disabled={confirmAction === null || isConfirmBusy}
                                onClick={runConfirmedAction}
                            >
                                {isConfirmBusy ? 'Working...' : 'Confirm'}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

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
                            <CardDescription>Schedule</CardDescription>
                            <CardTitle className="text-sm">
                                {election.start_date ?? 'No start'} - {election.end_date ?? 'No end'}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                </section>

                <section className="grid gap-4 xl:grid-cols-2 reveal-up reveal-delay-1">
                    <Card className="rounded-2xl">
                        <CardHeader>
                            <CardTitle>Lifecycle actions</CardTitle>
                            <CardDescription>Use available transitions according to status and permissions.</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-wrap gap-2">
                            {(['start', 'stop', 'close', 'publish'] as LifecycleAction[]).map((action) => (
                                <Button
                                    key={action}
                                    size="sm"
                                    variant={action === 'publish' ? 'default' : 'outline'}
                                    onClick={() => setConfirmAction({ kind: 'lifecycle', action })}
                                    disabled={!actionAvailability[action]}
                                >
                                    {busyAction === `lifecycle:${action}` ? `${actionLabels[action]}...` : actionLabels[action]}
                                </Button>
                            ))}
                        </CardContent>
                    </Card>

                    <Card className="rounded-2xl">
                        <CardHeader>
                            <CardTitle>Election details</CardTitle>
                            <CardDescription>Context and metadata for this election.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            <p className="rounded-lg border p-3 text-muted-foreground">
                                {election.description ?? 'No description provided.'}
                            </p>
                            <p className="text-xs text-muted-foreground">Created {election.created_at ?? 'N/A'}</p>
                        </CardContent>
                    </Card>
                </section>

                <section>
                    <Card className="rounded-2xl reveal-up reveal-delay-2">
                        <CardHeader>
                            <CardTitle>Positions</CardTitle>
                            <CardDescription>
                                Current ballot structure for this election.
                                {canManageBallot
                                    ? ' You can add or remove positions and candidates while in draft.'
                                    : ' Ballot editing is disabled outside draft status or without update permission.'}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            {canManageBallot ? (
                                <form className="rounded-lg border bg-card/70 p-3" onSubmit={createPosition}>
                                    <p className="mb-2 text-xs font-medium text-muted-foreground">Add position</p>
                                    <div className="grid gap-2 md:grid-cols-3">
                                        <label htmlFor="position-title" className="sr-only">Position title</label>
                                        <input
                                            id="position-title"
                                            type="text"
                                            value={positionTitle}
                                            onChange={(event) => setPositionTitle(event.target.value)}
                                            placeholder="Position title"
                                            className="h-9 rounded-md border bg-background px-3 text-sm"
                                            disabled={busyAction === 'position:create'}
                                        />
                                        <label htmlFor="position-description" className="sr-only">Position description</label>
                                        <input
                                            id="position-description"
                                            type="text"
                                            value={positionDescription}
                                            onChange={(event) => setPositionDescription(event.target.value)}
                                            placeholder="Description (optional)"
                                            className="h-9 rounded-md border bg-background px-3 text-sm"
                                            disabled={busyAction === 'position:create'}
                                        />
                                        <div className="flex items-center gap-2">
                                            <label htmlFor="position-max-votes" className="sr-only">Maximum votes</label>
                                            <input
                                                id="position-max-votes"
                                                type="number"
                                                aria-label="Maximum votes"
                                                min={1}
                                                max={10}
                                                value={positionMaxVotes}
                                                onChange={(event) => setPositionMaxVotes(Number(event.target.value || 1))}
                                                className="h-9 w-20 rounded-md border bg-background px-2 text-sm"
                                                disabled={busyAction === 'position:create'}
                                            />
                                            <Button type="submit" size="sm" disabled={busyAction === 'position:create'}>
                                                {busyAction === 'position:create' ? 'Adding...' : 'Add'}
                                            </Button>
                                        </div>
                                    </div>
                                </form>
                            ) : null}

                            {positions.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-3 text-muted-foreground">
                                    No positions configured yet.
                                </div>
                            ) : (
                                positions.map((position) => (
                                    <div key={position.id} className="rounded-xl border bg-card/70 p-3">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <p className="font-medium">{position.title}</p>
                                            {canManageBallot ? (
                                                <Button
                                                    size="sm"
                                                    variant="destructive"
                                                    onClick={() =>
                                                        setConfirmAction({
                                                            kind: 'position-delete',
                                                            positionId: position.id,
                                                            label: position.title,
                                                        })
                                                    }
                                                    disabled={busyAction === `position:delete:${position.id}`}
                                                >
                                                    {busyAction === `position:delete:${position.id}` ? 'Deleting...' : 'Delete position'}
                                                </Button>
                                            ) : null}
                                        </div>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            Max votes: {position.max_votes} · Candidates: {position.candidates_count}
                                        </p>
                                        {position.description ? (
                                            <p className="mt-1 text-xs text-muted-foreground">{position.description}</p>
                                        ) : null}

                                        <div className="mt-3 space-y-2 rounded-md border bg-muted/30 p-3">
                                            <p className="text-xs font-medium text-muted-foreground">Candidates</p>

                                            {canManageBallot ? (
                                                <div className="grid gap-2 md:grid-cols-[1fr_1fr_auto]">
                                                    <label htmlFor={`candidate-name-${position.id}`} className="sr-only">
                                                        Candidate name for {position.title}
                                                    </label>
                                                    <input
                                                        id={`candidate-name-${position.id}`}
                                                        type="text"
                                                        value={candidateNames[position.id] ?? ''}
                                                        onChange={(event) =>
                                                            setCandidateNames((previous) => ({
                                                                ...previous,
                                                                [position.id]: event.target.value,
                                                            }))
                                                        }
                                                        placeholder="Candidate name"
                                                        className="h-9 rounded-md border bg-background px-3 text-sm"
                                                        disabled={busyAction === `candidate:create:${position.id}`}
                                                    />
                                                    <label htmlFor={`candidate-bio-${position.id}`} className="sr-only">
                                                        Candidate bio for {position.title}
                                                    </label>
                                                    <input
                                                        id={`candidate-bio-${position.id}`}
                                                        type="text"
                                                        value={candidateBios[position.id] ?? ''}
                                                        onChange={(event) =>
                                                            setCandidateBios((previous) => ({
                                                                ...previous,
                                                                [position.id]: event.target.value,
                                                            }))
                                                        }
                                                        placeholder="Bio (optional)"
                                                        className="h-9 rounded-md border bg-background px-3 text-sm"
                                                        disabled={busyAction === `candidate:create:${position.id}`}
                                                    />
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => createCandidate(position.id)}
                                                        disabled={busyAction === `candidate:create:${position.id}`}
                                                    >
                                                        {busyAction === `candidate:create:${position.id}` ? 'Adding...' : 'Add candidate'}
                                                    </Button>
                                                </div>
                                            ) : null}

                                            {position.candidates.length === 0 ? (
                                                <p className="text-xs text-muted-foreground">No candidates yet.</p>
                                            ) : (
                                                position.candidates.map((candidate) => (
                                                    <div
                                                        key={candidate.id}
                                                        className="flex flex-wrap items-center justify-between gap-2 rounded-md border bg-background p-2"
                                                    >
                                                        <div>
                                                            <p className="text-sm font-medium">{candidate.name}</p>
                                                            {candidate.bio ? (
                                                                <p className="text-xs text-muted-foreground">{candidate.bio}</p>
                                                            ) : null}
                                                        </div>

                                                        {canManageBallot ? (
                                                            <Button
                                                                size="sm"
                                                                variant="destructive"
                                                                onClick={() =>
                                                                    setConfirmAction({
                                                                        kind: 'candidate-delete',
                                                                        positionId: position.id,
                                                                        candidateId: candidate.id,
                                                                        label: candidate.name,
                                                                    })
                                                                }
                                                                disabled={busyAction === `candidate:delete:${candidate.id}`}
                                                            >
                                                                {busyAction === `candidate:delete:${candidate.id}` ? 'Deleting...' : 'Delete'}
                                                            </Button>
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
                </section>
            </div>
        </>
    );
}

ElectionShow.layout = {
    breadcrumbs: [
        {
            title: 'Elections',
            href: '/elections',
        },
        {
            title: 'Election detail',
            href: '/elections',
        },
    ],
};
