<?php

use Illuminate\Support\Facades\Route;

// Import Controllers
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\RefreshController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Middleware\TokenAuthMiddleware;
use App\Http\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| API Base: /api
| Version: v1
| Rate Limiting: 60 requests per minute per user
|
*/

// ============================================================================
// Authentication Routes (Public)
// ============================================================================
Route::prefix('auth')->group(function () {
    // Registration
    Route::post('/register', [RegisterController::class, 'store'])
        ->name('auth.register');

    // Login
    Route::post('/login', [LoginController::class, 'store'])
        ->name('auth.login');

    // Password Reset
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
        ->name('auth.forgot-password');

    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->name('auth.reset-password');

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        // Get current user
        Route::get('/me', [MeController::class, 'show'])
            ->name('auth.me');

        // Refresh token
        Route::post('/refresh', [RefreshController::class, 'store'])
            ->name('auth.refresh');

        // Change password
        Route::post('/change-password', [ChangePasswordController::class, 'store'])
            ->name('auth.change-password');

        // Logout
        Route::post('/logout', [LogoutController::class, 'destroy'])
            ->name('auth.logout');
    });
});

// ============================================================================
// Authenticated Routes (Protected)
// ============================================================================
Route::middleware('auth:sanctum')->group(function () {
    // ========================================================================
    // Organization Routes
    // ========================================================================
    Route::prefix('organizations')->group(function () {
        // List all organizations for current user
        Route::get('/', [OrganizationController::class, 'index'])
            ->name('organizations.index');

        // Create new organization
        Route::post('/', [OrganizationController::class, 'store'])
            ->name('organizations.store');

        // Organization resource group
        Route::prefix('{organization}')->group(function () {
            // Get organization details
            Route::get('/', [OrganizationController::class, 'show'])
                ->name('organizations.show');

            // Update organization
            Route::patch('/', [OrganizationController::class, 'update'])
                ->name('organizations.update');

            // Delete organization
            Route::delete('/', [OrganizationController::class, 'destroy'])
                ->name('organizations.destroy');

            // ====================================================================
            // Member Management Routes
            // ====================================================================
            Route::prefix('members')->group(function () {
                // List all members in organization
                Route::get('/', [MemberController::class, 'index'])
                    ->name('members.index');

                // Add member to organization
                Route::post('/', [MemberController::class, 'store'])
                    ->name('members.store');

                // Update member role
                Route::patch('{member}', [MemberController::class, 'update'])
                    ->name('members.update');

                // Remove member from organization
                Route::delete('{member}', [MemberController::class, 'destroy'])
                    ->name('members.destroy');
            });

            // ====================================================================
            // Invite Management Routes
            // ====================================================================
            Route::prefix('invites')->group(function () {
                // List all invites
                Route::get('/', [InviteController::class, 'index'])
                    ->name('invites.index');

                // Create invite
                Route::post('/', [InviteController::class, 'store'])
                    ->name('invites.store');

                // Cancel invite
                Route::delete('{invite}', [InviteController::class, 'destroy'])
                    ->name('invites.destroy');

                // Resend invite
                Route::post('{invite}/resend', [InviteController::class, 'resend'])
                    ->name('invites.resend');
            });

            // ====================================================================
            // Election Management Routes
            // ====================================================================
            Route::prefix('elections')->group(function () {
                // List all elections in organization
                Route::get('/', [ElectionController::class, 'index'])
                    ->name('elections.index');

                // Create new election
                Route::post('/', [ElectionController::class, 'store'])
                    ->name('elections.store');

                // Compare two elections
                Route::post('compare', [ResultsController::class, 'compareElections'])
                    ->name('elections.compare');

                // Election resource group
                Route::prefix('{election}')->group(function () {
                    // Get election details
                    Route::get('/', [ElectionController::class, 'show'])
                        ->name('elections.show');

                    // Update election
                    Route::patch('/', [ElectionController::class, 'update'])
                        ->name('elections.update');

                    // Start election
                    Route::post('/start', [ElectionController::class, 'start'])
                        ->name('elections.start');

                    // Stop election
                    Route::post('/stop', [ElectionController::class, 'stop'])
                        ->name('elections.stop');

                    // Publish election
                    Route::post('/publish', [ElectionController::class, 'publish'])
                        ->name('elections.publish');

                    // Close election
                    Route::post('/close', [ElectionController::class, 'close'])
                        ->name('elections.close');

                    // ================================================================
                    // Position Management Routes
                    // ================================================================
                    Route::prefix('positions')->group(function () {
                        // List all positions in election
                        Route::get('/', [PositionController::class, 'index'])
                            ->name('positions.index');

                        // Create position
                        Route::post('/', [PositionController::class, 'store'])
                            ->name('positions.store');

                        // Position resource group
                        Route::prefix('{position}')->group(function () {
                            // Get position details
                            Route::get('/', [PositionController::class, 'show'])
                                ->name('positions.show');

                            // Update position
                            Route::patch('/', [PositionController::class, 'update'])
                                ->name('positions.update');

                            // Delete position
                            Route::delete('/', [PositionController::class, 'destroy'])
                                ->name('positions.destroy');

                            // ============================================================
                            // Candidate Management Routes
                            // ============================================================
                            Route::prefix('candidates')->group(function () {
                                // List all candidates in position
                                Route::get('/', [CandidateController::class, 'index'])
                                    ->name('candidates.index');

                                // Create candidate
                                Route::post('/', [CandidateController::class, 'store'])
                                    ->name('candidates.store');

                                // Candidate resource group
                                Route::prefix('{candidate}')->group(function () {
                                    // Get candidate details
                                    Route::get('/', [CandidateController::class, 'show'])
                                        ->name('candidates.show');

                                    // Update candidate
                                    Route::patch('/', [CandidateController::class, 'update'])
                                        ->name('candidates.update');

                                    // Delete candidate
                                    Route::delete('/', [CandidateController::class, 'destroy'])
                                        ->name('candidates.destroy');

                                    // Get candidate statistics
                                    Route::get('/statistics', [ResultsController::class, 'getCandidateStatistics'])
                                        ->name('candidates.statistics');
                                });
                            });

                            // Position statistics
                            Route::get('statistics', [ResultsController::class, 'getPositionStatistics'])
                                ->name('positions.statistics');

                            // Vote distribution curve
                            Route::get('distribution', [ResultsController::class, 'getVoteDistribution'])
                                ->name('positions.distribution');
                        });
                    });

                    // ================================================================
                    // Election Results Routes (Member Only)
                    // ================================================================
                    Route::prefix('results')->group(function () {
                        // Get live results (for active elections - members only)
                        Route::get('live', [ResultsController::class, 'getLiveResults'])
                            ->name('results.live');

                        // Get published results
                        Route::get('/', [ResultsController::class, 'getResults'])
                            ->name('results.index');

                        // Get results summary (winners only)
                        Route::get('summary', [ResultsController::class, 'getResultsSummary'])
                            ->name('results.summary');

                        // Export results
                        Route::get('export', [ResultsController::class, 'exportResults'])
                            ->name('results.export');
                    });

                    // ================================================================
                    // Election Analytics Routes (Member Only)
                    // ================================================================
                    Route::prefix('analytics')->group(function () {
                        // Get detailed analytics
                        Route::get('/', [ResultsController::class, 'getAnalytics'])
                            ->name('analytics.election');
                    });

                    // ================================================================
                    // Voting Routes
                    // ================================================================
                    Route::prefix('voting')->group(function () {
                        // Get voting statistics (members only)
                        Route::get('stats', [VoteController::class, 'stats'])
                            ->name('voting.stats');
                    });
                });
            });

            // ====================================================================
            // Organization Analytics Routes (Member Only)
            // ====================================================================
            Route::prefix('analytics')->group(function () {
                // Organization-wide analytics
                Route::get('/', [AnalyticsController::class, 'getOrganizationAnalytics'])
                    ->name('analytics.organization');

                // Election trends
                Route::get('trends', [AnalyticsController::class, 'getTrends'])
                    ->name('analytics.trends');

                // Member participation
                Route::get('participation', [AnalyticsController::class, 'getMemberParticipation'])
                    ->name('analytics.participation');

                // Most competitive elections
                Route::get('competitive', [AnalyticsController::class, 'getMostCompetitive'])
                    ->name('analytics.competitive');

                // High turnout elections
                Route::get('turnout', [AnalyticsController::class, 'getHighTurnout'])
                    ->name('analytics.turnout');

                // Candidate performance
                Route::get('candidates', [AnalyticsController::class, 'getCandidatePerformance'])
                    ->name('analytics.candidates');
            });
        });
    });

    // ========================================================================
    // Public Election Routes (Non-authenticated voters can access)
    // ========================================================================
});

// ============================================================================
// Public Election Voting Routes (Voter Token Required)
// ============================================================================
Route::prefix('elections')->group(function () {
    // Get voter ballot
    Route::get('{election}/ballot', [VoteController::class, 'getBallot'])
        ->name('voting.ballot')
        ->middleware(TokenAuthMiddleware::class);

    // Submit single vote
    Route::post('{election}/vote', [VoteController::class, 'submitVote'])
        ->name('voting.submit')
        ->middleware(TokenAuthMiddleware::class);

    // Submit batch votes
    Route::post('{election}/votes', [VoteController::class, 'submitBatchVotes'])
        ->name('voting.submit-batch')
        ->middleware(TokenAuthMiddleware::class);

    // Get election results (public)
    Route::get('{election}/results', [ResultsController::class, 'getResults'])
        ->name('voting.results');
});

// ============================================================================
// Health Check & Status Routes
// ============================================================================
Route::get('/health', [HealthController::class, 'show'])
    ->name('health');

// Fallback for undefined routes
Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found',
        'path' => request()->path(),
    ], 404);
});
