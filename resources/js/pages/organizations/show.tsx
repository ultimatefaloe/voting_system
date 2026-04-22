import { Head, Link, router, usePage } from '@inertiajs/react';
import { Building2, ShieldCheck, Users } from 'lucide-react';
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
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type OrganizationData = {
    organization: {
        id: number;
        name: string;
        slug: string;
        owner_id: number;
    };
    summary: {
        active_members_count: number;
        pending_invites_count: number;
        elections_count: number;
    };
    permissions: {
        can_manage_members: boolean;
        current_user_role: 'owner' | 'admin' | 'member' | 'viewer' | null;
    };
    members: Array<{
        id: number;
        user_id: number;
        name: string | null;
        email: string | null;
        role: 'owner' | 'admin' | 'member' | 'viewer';
        status: 'active' | 'pending';
        created_at: string | null;
    }>;
    invites: Array<{
        id: number;
        email: string;
        role: 'admin' | 'member' | 'viewer';
        status: 'pending' | 'accepted';
        expires_at: string | null;
        created_at: string | null;
    }>;
};

type OrganizationShowPageProps = {
    organizationData: OrganizationData;
};

const roleTone: Record<string, 'default' | 'secondary' | 'outline'> = {
    owner: 'default',
    admin: 'secondary',
    member: 'outline',
    viewer: 'outline',
};

type AssignableRole = 'admin' | 'member' | 'viewer';
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

type ConfirmAction =
    | {
          kind: 'invite-cancel';
          inviteId: number;
          label: string;
      }
    | {
          kind: 'member-remove';
          userId: number;
          label: string;
      }
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
        return 'This organization resource was not found.';
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

export default function OrganizationShow() {
    const { organizationData } = usePage<OrganizationShowPageProps>().props;
    const { organization, summary, members, invites, permissions } = organizationData;
    const canManageMembers = permissions.can_manage_members;

    const [inviteEmail, setInviteEmail] = useState('');
    const [inviteRole, setInviteRole] = useState<AssignableRole>('member');
    const [isSubmittingInvite, setIsSubmittingInvite] = useState(false);
    const [busyInviteId, setBusyInviteId] = useState<number | null>(null);
    const [busyMemberId, setBusyMemberId] = useState<number | null>(null);
    const [toasts, setToasts] = useState<ToastItem[]>([]);
    const [confirmAction, setConfirmAction] = useState<ConfirmAction>(null);

    const pushToast = (type: ToastType, message: string) => {
        const id = Date.now() + Math.floor(Math.random() * 1000);

        setToasts((previous) => [...previous, { id, type, message }]);

        window.setTimeout(() => {
            setToasts((previous) => previous.filter((toast) => toast.id !== id));
        }, 3500);
    };

    const memberRoleDrafts = useMemo(() => {
        const roleMap: Record<number, AssignableRole> = {};

        for (const member of members) {
            if (member.role !== 'owner') {
                roleMap[member.user_id] = member.role;
            }
        }

        return roleMap;
    }, [members]);

    const [editedRoles, setEditedRoles] = useState<Record<number, AssignableRole>>(memberRoleDrafts);

    const refreshDetailData = () => {
        router.reload({
            only: ['organizationData'],
            onFinish: () => {
                setEditedRoles(memberRoleDrafts);
            },
        });
    };

    const handleInviteCreate = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (isSubmittingInvite) {
            return;
        }

        if (!inviteEmail.trim()) {
            pushToast('error', 'Email is required.');

            return;
        }

        setIsSubmittingInvite(true);

        try {
            const response = await apiRequest(`/api/organizations/${organization.id}/invites`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: inviteEmail.trim(),
                    role: inviteRole,
                }),
            });

            const payload = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, 'Failed to send invitation.'));
            }

            pushToast('success', payload.message ?? 'Invitation sent successfully.');
            setInviteEmail('');
            setInviteRole('member');
            refreshDetailData();
        } catch (error) {
            pushToast('error', error instanceof Error ? error.message : 'Failed to send invitation.');
        } finally {
            setIsSubmittingInvite(false);
        }
    };

    const handleInviteResend = async (inviteId: number) => {
        if (busyInviteId !== null) {
            return;
        }

        setBusyInviteId(inviteId);

        try {
            const response = await apiRequest(`/api/organizations/${organization.id}/invites/${inviteId}/resend`, {
                method: 'POST',
            });
            const payload = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, 'Failed to resend invitation.'));
            }

            pushToast('success', payload.message ?? 'Invitation resent successfully.');
            refreshDetailData();
        } catch (error) {
            pushToast('error', error instanceof Error ? error.message : 'Failed to resend invitation.');
        } finally {
            setBusyInviteId(null);
        }
    };

    const handleInviteCancel = async (inviteId: number): Promise<boolean> => {

        if (busyInviteId !== null) {
            return false;
        }

        setBusyInviteId(inviteId);

        try {
            const response = await apiRequest(`/api/organizations/${organization.id}/invites/${inviteId}`, {
                method: 'DELETE',
            });
            const payload = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, 'Failed to cancel invitation.'));
            }

            pushToast('success', payload.message ?? 'Invitation cancelled successfully.');
            refreshDetailData();

            return true;
        } catch (error) {
            pushToast('error', error instanceof Error ? error.message : 'Failed to cancel invitation.');

            return false;
        } finally {
            setBusyInviteId(null);
        }
    };

    const handleMemberRoleSave = async (userId: number) => {
        if (busyMemberId !== null) {
            return;
        }

        const nextRole = editedRoles[userId];

        if (!nextRole) {
            pushToast('error', 'Select a role before saving.');

            return;
        }

        setBusyMemberId(userId);

        try {
            const response = await apiRequest(`/api/organizations/${organization.id}/members/${userId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ role: nextRole }),
            });

            const payload = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, 'Failed to update member role.'));
            }

            pushToast('success', payload.message ?? 'Member role updated successfully.');
            refreshDetailData();
        } catch (error) {
            pushToast('error', error instanceof Error ? error.message : 'Failed to update member role.');
        } finally {
            setBusyMemberId(null);
        }
    };

    const handleMemberRemove = async (userId: number): Promise<boolean> => {

        if (busyMemberId !== null) {
            return false;
        }

        setBusyMemberId(userId);

        try {
            const response = await apiRequest(`/api/organizations/${organization.id}/members/${userId}`, {
                method: 'DELETE',
            });
            const payload = await parseJsonSafe(response);

            if (!response.ok) {
                throw new Error(getApiErrorMessage(response, payload, 'Failed to remove member.'));
            }

            pushToast('success', payload.message ?? 'Member removed successfully.');
            refreshDetailData();

            return true;
        } catch (error) {
            pushToast('error', error instanceof Error ? error.message : 'Failed to remove member.');

            return false;
        } finally {
            setBusyMemberId(null);
        }
    };

    const requestInviteCancel = (inviteId: number, email: string) => {
        setConfirmAction({
            kind: 'invite-cancel',
            inviteId,
            label: email,
        });
    };

    const requestMemberRemove = (userId: number, label: string) => {
        setConfirmAction({
            kind: 'member-remove',
            userId,
            label,
        });
    };

    const handleConfirmAction = async () => {
        if (!confirmAction) {
            return;
        }

        if (confirmAction.kind === 'invite-cancel') {
            const done = await handleInviteCancel(confirmAction.inviteId);

            if (done) {
                setConfirmAction(null);
            }

            return;
        }

        const done = await handleMemberRemove(confirmAction.userId);

        if (done) {
            setConfirmAction(null);
        }
    };

    const isConfirmBusy =
        (confirmAction?.kind === 'invite-cancel' && busyInviteId === confirmAction.inviteId) ||
        (confirmAction?.kind === 'member-remove' && busyMemberId === confirmAction.userId);

    return (
        <>
            <Head title={`${organization.name} · Organization`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl bg-linear-to-b from-emerald-500/6 via-transparent to-transparent p-4">
                <PageHero
                    eyebrow="Organization governance"
                    title={organization.name}
                    description={`/${organization.slug}`}
                    startGlowClass="bg-emerald-400/15"
                    endGlowClass="bg-cyan-400/15"
                    actions={(
                        <>
                            <Badge variant="secondary" className="bg-emerald-500/15 text-emerald-700">
                                {permissions.current_user_role ?? 'member'}
                            </Badge>
                            <Button asChild size="sm" variant="outline">
                                <Link href="/organizations">Back to organizations</Link>
                            </Button>
                        </>
                    )}
                    chips={(
                        <>
                            <Badge variant="outline" className="gap-1">
                                <Building2 className="h-3.5 w-3.5" />
                                Shared workspace
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <Users className="h-3.5 w-3.5" />
                                Membership controls
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <ShieldCheck className="h-3.5 w-3.5" />
                                Role-based access
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
                                {confirmAction?.kind === 'invite-cancel' ? 'Cancel invitation' : 'Remove member'}
                            </DialogTitle>
                            <DialogDescription>
                                {confirmAction?.kind === 'invite-cancel'
                                    ? `This will cancel the pending invite for ${confirmAction.label}.`
                                    : `This will remove ${confirmAction?.label ?? 'this member'} from the organization.`}
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <DialogClose asChild>
                                <Button variant="outline" disabled={isConfirmBusy}>
                                    Keep
                                </Button>
                            </DialogClose>
                            <Button variant="destructive" onClick={handleConfirmAction} disabled={isConfirmBusy}>
                                {isConfirmBusy ? 'Working...' : 'Confirm'}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <section className="grid gap-4 md:grid-cols-3 reveal-up">
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Active members</CardDescription>
                            <CardTitle>{summary.active_members_count}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Pending invites</CardDescription>
                            <CardTitle>{summary.pending_invites_count}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Elections</CardDescription>
                            <CardTitle>{summary.elections_count}</CardTitle>
                        </CardHeader>
                    </Card>
                </section>

                <section className="grid gap-4 xl:grid-cols-2 reveal-up reveal-delay-1">
                    <Card className="rounded-2xl">
                        <CardHeader>
                            <CardTitle>Members</CardTitle>
                            <CardDescription>
                                Role and status overview for organization membership.
                                {canManageMembers
                                    ? ' You can update roles and remove members.'
                                    : ' You currently have read-only access in this organization.'}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            {members.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-3 text-muted-foreground">
                                    No members found.
                                </div>
                            ) : (
                                members.map((member) => (
                                    <div key={member.id} className="rounded-xl border bg-card/70 p-3">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <div>
                                                <p className="font-medium">{member.name ?? 'Unknown user'}</p>
                                                <p className="text-xs text-muted-foreground">{member.email ?? 'No email'}</p>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Badge variant={roleTone[member.role] ?? 'outline'} className="capitalize">
                                                    {member.role}
                                                </Badge>
                                                <Badge variant="outline" className="capitalize">
                                                    {member.status}
                                                </Badge>
                                            </div>
                                        </div>
                                        <p className="mt-1 text-xs text-muted-foreground">Joined {member.created_at ?? 'N/A'}</p>

                                        {canManageMembers && member.role !== 'owner' ? (
                                            <div className="mt-3 flex flex-wrap items-center gap-2">
                                                <select
                                                    aria-label="Member role"
                                                    value={editedRoles[member.user_id] ?? 'member'}
                                                    onChange={(event) => {
                                                        setEditedRoles((previous) => ({
                                                            ...previous,
                                                            [member.user_id]: event.target.value as AssignableRole,
                                                        }));
                                                    }}
                                                    className="h-8 rounded-md border bg-background px-2 text-xs"
                                                    disabled={busyMemberId === member.user_id}
                                                >
                                                    <option value="admin">Admin</option>
                                                    <option value="member">Member</option>
                                                    <option value="viewer">Viewer</option>
                                                </select>

                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => handleMemberRoleSave(member.user_id)}
                                                    disabled={busyMemberId === member.user_id}
                                                >
                                                    {busyMemberId === member.user_id ? 'Saving...' : 'Save role'}
                                                </Button>

                                                <Button
                                                    size="sm"
                                                    variant="destructive"
                                                    onClick={() =>
                                                        requestMemberRemove(
                                                            member.user_id,
                                                            member.name ?? member.email ?? 'this member',
                                                        )
                                                    }
                                                    disabled={busyMemberId === member.user_id}
                                                >
                                                    {busyMemberId === member.user_id ? 'Removing...' : 'Remove'}
                                                </Button>
                                            </div>
                                        ) : null}
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card className="rounded-2xl">
                        <CardHeader>
                            <CardTitle>Invitations</CardTitle>
                            <CardDescription>
                                Pending invitation queue and expiry visibility.
                                {canManageMembers
                                    ? ' You can send, resend, and cancel invites.'
                                    : ' You currently have read-only access in this organization.'}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            {canManageMembers ? (
                                <form className="rounded-lg border bg-card/70 p-3" onSubmit={handleInviteCreate}>
                                    <p className="mb-2 text-xs font-medium text-muted-foreground">Send new invitation</p>
                                    <div className="grid gap-2 sm:grid-cols-[1fr_auto_auto]">
                                        <label htmlFor="invite-email" className="sr-only">Invite email</label>
                                        <input
                                            id="invite-email"
                                            type="email"
                                            value={inviteEmail}
                                            onChange={(event) => setInviteEmail(event.target.value)}
                                            placeholder="member@example.com"
                                            className="h-9 rounded-md border bg-background px-3 text-sm"
                                            disabled={isSubmittingInvite}
                                        />
                                        <label htmlFor="invite-role" className="sr-only">Invite role</label>
                                        <select
                                            id="invite-role"
                                            aria-label="Invite role"
                                            value={inviteRole}
                                            onChange={(event) => setInviteRole(event.target.value as AssignableRole)}
                                            className="h-9 rounded-md border bg-background px-2 text-sm"
                                            disabled={isSubmittingInvite}
                                        >
                                            <option value="admin">Admin</option>
                                            <option value="member">Member</option>
                                            <option value="viewer">Viewer</option>
                                        </select>
                                        <Button type="submit" size="sm" disabled={isSubmittingInvite}>
                                            {isSubmittingInvite ? 'Sending...' : 'Send invite'}
                                        </Button>
                                    </div>
                                </form>
                            ) : null}

                            {invites.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-3 text-muted-foreground">
                                    No pending invites.
                                </div>
                            ) : (
                                invites.map((invite) => (
                                    <div key={invite.id} className="rounded-xl border bg-card/70 p-3">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <div>
                                                <p className="font-medium">{invite.email}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    Sent {invite.created_at ?? 'N/A'} · Expires {invite.expires_at ?? 'N/A'}
                                                </p>
                                            </div>
                                            <Badge variant={roleTone[invite.role] ?? 'outline'} className="capitalize">
                                                {invite.role}
                                            </Badge>
                                        </div>

                                        {canManageMembers ? (
                                            <div className="mt-3 flex items-center gap-2">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => handleInviteResend(invite.id)}
                                                    disabled={busyInviteId === invite.id}
                                                >
                                                    {busyInviteId === invite.id ? 'Resending...' : 'Resend'}
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="destructive"
                                                    onClick={() => requestInviteCancel(invite.id, invite.email)}
                                                    disabled={busyInviteId === invite.id}
                                                >
                                                    {busyInviteId === invite.id ? 'Cancelling...' : 'Cancel'}
                                                </Button>
                                            </div>
                                        ) : null}
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

OrganizationShow.layout = {
    breadcrumbs: [
        {
            title: 'Organizations',
            href: '/organizations',
        },
        {
            title: 'Organization detail',
            href: '/organizations',
        },
    ],
};
