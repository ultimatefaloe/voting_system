import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import ElectionShow from './show';

const mockedUsePage = vi.fn();

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => <a href={href}>{children}</a>,
    router: {
        reload: vi.fn(),
    },
    usePage: () => mockedUsePage(),
}));

describe('ElectionShow', () => {
    beforeEach(() => {
        mockedUsePage.mockReturnValue({
            props: {
                electionData: {
                    election: {
                        id: 10,
                        organization_id: 1,
                        organization: 'Engineering Org',
                        organization_slug: 'engineering-org',
                        title: 'Team Election',
                        description: null,
                        type: 'private',
                        status: 'active',
                        start_date: null,
                        end_date: null,
                        created_at: null,
                    },
                    summary: {
                        positions_count: 0,
                        vote_sessions_count: 0,
                    },
                    positions: [],
                    permissions: {
                        can_update: false,
                        can_start: false,
                        can_stop: false,
                        can_publish: false,
                    },
                    lifecycle: {
                        can_start_now: false,
                        can_stop_now: false,
                        can_publish_now: false,
                    },
                },
            },
        });
    });

    it('shows clear read-only ballot management guidance when editing is disabled', () => {
        render(<ElectionShow />);

        expect(screen.getByText(/Ballot editing is disabled outside draft status/i)).toBeTruthy();
    });
});
