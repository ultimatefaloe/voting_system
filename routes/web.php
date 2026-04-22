<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Dedoc\Scramble\Scramble;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::inertia('/welcome', 'welcome/index')->name('welcome');

Route::inertia('/privacy-policy', 'privacy-policy')->name('privacy-policy');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

// ============================================================================
// Scramble API Documentation Routes
// ============================================================================
// Access documentation at: /docs
Scramble::registerUiRoute('/docs');

// Access OpenAPI specification at: /api.json
Scramble::registerJsonSpecificationRoute('/api.json');

require __DIR__.'/settings.php';
