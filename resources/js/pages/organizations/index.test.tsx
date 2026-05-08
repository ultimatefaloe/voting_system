import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import OrganizationsIndex from './index';

const mockedUsePage = vi.fn();

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => <a href={href}>{children}</a>,
    router: {
        reload: vi.fn(),
        get: vi.fn(),
    },
    usePage: () => mockedUsePage(),
}));

describe('OrganizationsIndex optimistic invites', () => {
    beforeEach(() => {
        mockedUsePage.mockReturnValue({
            props: {
                organizationsData: {
                    items: [
                        {
                            id: 1,
                            name: 'Engineering Org',
                            slug: 'engineering-org',
                            role: 'owner',
                            can_manage_members: true,
                            can_update_org: true,
                            can_delete_org: true,
                            members_count: 3,
                            pending_invites_count: 0,
                            elections_count: 0,
                            created_at: null,
                        },
                    ],
                    summary: {
                        organizations_count: 1,
                        active_memberships: 1,
                        total_elections: 0,
                    },
                    permissions: {
                        can_create_organization: true,
                    },
                    filters: {
                        search: '',
                        role: 'all',
                        per_page: 6,
                    },
                    pagination: {
                        current_page: 1,
                        last_page: 1,
                        per_page: 6,
                        total: 1,
                        from: 1,
                        to: 1,
                    },
                },
            },
        });
    });

    it('optimistically increments pending_invites_count on submit', async () => {
        let resolvePost!: (value: Response) => void;
        let resolveGet!: (value: Response) => void;
        const postPromise = new Promise<Response>((resolve) => {
            resolvePost = resolve;
        });
        const getPromise = new Promise<Response>((resolve) => {
            resolveGet = resolve;
        });

        const fetchMock = vi.fn((url, init) => {
            // POST to /invites -> return postPromise, otherwise return getPromise for pending invites
            const method = (init && (init as RequestInit).method) || 'GET';
            if (String(url).endsWith('/invites') && method.toUpperCase() === 'POST') {
                return postPromise;
            }

            return getPromise;
        });

        vi.stubGlobal('fetch', fetchMock);

        render(<OrganizationsIndex />);

        // initial shows 0 pending
        expect(screen.getByText(/0 pending invites/)).toBeTruthy();

        // open invite dialog
        const inviteButton = screen.getByRole('button', { name: /Invite member/i });
        fireEvent.click(inviteButton);

        const emailInput = screen.getAllByPlaceholderText('member@organization.com').pop() as HTMLInputElement;
        fireEvent.change(emailInput, { target: { value: 'alice@example.com' } });
        fireEvent.submit(emailInput.closest('form') as HTMLFormElement);

        // optimistic increment should immediately show 1 pending (at least one element)
        expect(screen.getAllByText(/1 pending invites/).length).toBeGreaterThan(0);

        // resolve POST
        resolvePost(
            new Response(JSON.stringify({ message: 'Invitation queued for delivery successfully.', data: { token: 'abc' } }), {
                status: 200,
                headers: { 'Content-Type': 'application/json' },
            }),
        );

        // resolve GET pending invites with empty list
        resolveGet(
            new Response(JSON.stringify({ message: 'OK', data: [] }), {
                status: 200,
                headers: { 'Content-Type': 'application/json' },
            }),
        );

        await waitFor(() => {
            expect(fetchMock).toHaveBeenCalled();
        });

        vi.unstubAllGlobals();
    });

    it('reverts optimistic increment on server validation error', async () => {
        let resolvePost!: (value: Response) => void;
        let resolveGet!: (value: Response) => void;
        const postPromise = new Promise<Response>((resolve) => {
            resolvePost = resolve;
        });
        const getPromise = new Promise<Response>((resolve) => {
            resolveGet = resolve;
        });

        const fetchMock = vi.fn((url, init) => {
            const method = (init && (init as RequestInit).method) || 'GET';
            if (String(url).endsWith('/invites') && method.toUpperCase() === 'POST') {
                return postPromise;
            }

            return getPromise;
        });

        vi.stubGlobal('fetch', fetchMock);

        render(<OrganizationsIndex />);

        // initial shows 0 pending
        expect(screen.getByText(/0 pending invites/)).toBeTruthy();

        // open invite dialog
        const inviteButton = screen.getByRole('button', { name: /Invite member/i });
        fireEvent.click(inviteButton);

        const emailInput = screen.getAllByPlaceholderText('member@organization.com').pop() as HTMLInputElement;
        fireEvent.change(emailInput, { target: { value: 'invalid' } });
        fireEvent.submit(emailInput.closest('form') as HTMLFormElement);

        // optimistic increment should immediately show 1 pending (at least one element)
        expect(screen.getAllByText(/1 pending invites/).length).toBeGreaterThan(0);

        // resolve POST with validation error
        resolvePost(
            new Response(JSON.stringify({ message: 'Invalid input', errors: { email: ['Invalid email'] } }), {
                status: 422,
                headers: { 'Content-Type': 'application/json' },
            }),
        );

        // resolve GET pending invites with empty list
        resolveGet(
            new Response(JSON.stringify({ message: 'OK', data: [] }), {
                status: 200,
                headers: { 'Content-Type': 'application/json' },
            }),
        );

        await waitFor(() => {
            expect(fetchMock).toHaveBeenCalled();
        });

        // after error the optimistic change should be reverted back to 0
        await waitFor(() => {
            expect(screen.getByText(/0 pending invites/)).toBeTruthy();
        });

        vi.unstubAllGlobals();
    });
});
