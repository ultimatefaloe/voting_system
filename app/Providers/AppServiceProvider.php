<?php

namespace App\Providers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use App\Policies\CandidatePolicy;
use App\Policies\ElectionPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\PositionPolicy;
use Carbon\CarbonImmutable;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePolicies();
        $this->configureDefaults();
        $this->configureScramble();
    }

    /**
     * Configure authorization policies
     */
    protected function configurePolicies(): void
    {
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(Election::class, ElectionPolicy::class);
        Gate::policy(Position::class, PositionPolicy::class);
        Gate::policy(Candidate::class, CandidatePolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure Scramble to document API and web routes.
     */
    protected function configureScramble(): void
    {
        if (! class_exists(Scramble::class)) {
            return;
        }

        Scramble::routes(function () {
            return collect(Route::getRoutes())
                ->filter(function ($route) {
                    $uri = $route->uri();
                    $action = $route->getAction('uses');

                    if (! is_string($action) || $action === 'Closure') {
                        return false;
                    }

                    return ! str_starts_with($uri, 'docs')
                        && ! str_starts_with($uri, 'api/docs')
                        && ! str_starts_with($uri, '_ignition')
                        && ! str_starts_with($uri, 'sanctum');
                })
                ->values();
        });
    }
}
