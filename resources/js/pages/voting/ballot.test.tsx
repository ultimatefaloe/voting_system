import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import VotingBallotPage from './ballot';

const mockedUsePage = vi.fn();

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    usePage: () => mockedUsePage(),
}));

describe('VotingBallotPage', () => {
    beforeEach(() => {
        mockedUsePage.mockReturnValue({
            props: {
                ballotPageData: {
                    election: {
                        id: 8,
                        title: 'General Vote',
                        description: null,
                        type: 'public',
                        status: 'active',
                        start_date: null,
                        end_date: null,
                    },
                },
            },
        });
    });

    it('loads ballot with voter token and renders candidates', async () => {
        let resolveFetch!: (value: Response) => void;
        const fetchPromise = new Promise<Response>((resolve) => {
            resolveFetch = resolve;
        });
        const fetchMock = vi.fn(() => fetchPromise);

        vi.stubGlobal('fetch', fetchMock);

        render(<VotingBallotPage />);

        const tokenInput = screen.getByPlaceholderText('voter_xxxxxxxxx');
        fireEvent.change(tokenInput, { target: { value: 'voter_token_1' } });
        fireEvent.click(screen.getByRole('button', { name: 'Load ballot' }));

        expect(screen.getByRole('button', { name: 'Loading...' })).toBeTruthy();

        resolveFetch(
            new Response(
                JSON.stringify({
                    election_id: 8,
                    positions: [
                        {
                            id: 101,
                            title: 'Chairperson',
                            description: null,
                            max_votes: 1,
                            selected_candidates: [],
                            candidates: [
                                {
                                    id: 900,
                                    name: 'Alex Doe',
                                    bio: 'Leadership candidate',
                                    avatar: null,
                                },
                            ],
                        },
                    ],
                }),
                {
                    status: 200,
                    headers: { 'Content-Type': 'application/json' },
                },
            ),
        );

        await waitFor(() => {
            expect(fetchMock).toHaveBeenCalledTimes(1);
        });

        await waitFor(() => {
            expect(screen.getByText('Alex Doe')).toBeTruthy();
        });

        vi.unstubAllGlobals();
    });
});
