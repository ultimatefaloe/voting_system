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
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
}
