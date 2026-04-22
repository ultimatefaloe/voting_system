import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import OrganizationShow from './show';

const mockedUsePage = vi.fn();

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => <a href={href}>{children}</a>,
    router: {
        reload: vi.fn(),
    },
    usePage: () => mockedUsePage(),
}));

describe('OrganizationShow', () => {
    beforeEach(() => {
        mockedUsePage.mockReturnValue({
            props: {
                organizationData: {
                    organization: {
                        id: 1,
                        name: 'Engineering Org',
                        slug: 'engineering-org',
                        owner_id: 10,
                    },
                    summary: {
                        active_members_count: 1,
                        pending_invites_count: 0,
                        elections_count: 0,
                    },
                    permissions: {
                        can_manage_members: true,
                        current_user_role: 'owner',
                    },
                    members: [],
                    invites: [],
                },
            },
        });
    });

    it('shows sending state while invite request is in progress', async () => {
        let resolveFetch!: (value: Response) => void;
        const fetchPromise = new Promise<Response>((resolve) => {
            resolveFetch = resolve;
        });
        const fetchMock = vi.fn(() => fetchPromise);

        vi.stubGlobal('fetch', fetchMock);

        render(<OrganizationShow />);

        const emailInput = screen.getByPlaceholderText('member@example.com');
        fireEvent.change(emailInput, { target: { value: 'new.member@example.com' } });
        fireEvent.submit(emailInput.closest('form') as HTMLFormElement);

        expect(screen.getByRole('button', { name: 'Sending...' })).toBeTruthy();

        resolveFetch(
            new Response(JSON.stringify({ message: 'Invitation sent.' }), {
                status: 200,
                headers: { 'Content-Type': 'application/json' },
            }),
        );

        await waitFor(() => {
            expect(fetchMock).toHaveBeenCalledTimes(1);
        });

        await waitFor(() => {
            expect(screen.getByRole('button', { name: 'Send invite' })).toBeTruthy();
        });

        vi.unstubAllGlobals();
    });
});
