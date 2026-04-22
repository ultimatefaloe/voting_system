<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BallotPageController;
use App\Http\Controllers\AnalyticsPageController;
use App\Http\Controllers\ElectionDetailPageController;
use App\Http\Controllers\ElectionsPageController;
use App\Http\Controllers\OrganizationDetailPageController;
use App\Http\Controllers\OrganizationsPageController;
use App\Http\Controllers\ResultDetailPageController;
use App\Http\Controllers\ResultsPageController;
use App\Http\Controllers\VotingSessionsPageController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome/index', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::inertia('/welcome', 'welcome/index', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('welcome');

Route::inertia('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::inertia('/terms', 'terms')->name('terms');
Route::get('/vote/{election}', BallotPageController::class)->name('voting.ballot.page');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('organizations', OrganizationsPageController::class)->name('app.organizations.index');
    Route::get('organizations/{organization}', OrganizationDetailPageController::class)
        ->name('app.organizations.show');
    Route::get('elections', ElectionsPageController::class)->name('app.elections.index');
    Route::get('elections/{election}', ElectionDetailPageController::class)->name('app.elections.show');
    Route::get('voting-sessions', VotingSessionsPageController::class)->name('app.voting-sessions.index');
    Route::get('results', ResultsPageController::class)->name('app.results.index');
    Route::get('results/{election}', ResultDetailPageController::class)->name('app.results.show');
    Route::get('analytics', AnalyticsPageController::class)->name('app.analytics.index');
});

require __DIR__.'/settings.php';
