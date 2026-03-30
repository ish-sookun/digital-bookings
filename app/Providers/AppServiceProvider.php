<?php

namespace App\Providers;

use App\Models\User;
use App\UserRole;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('manage-users', fn (User $user) => $user->isSuperAdmin());

        Gate::define('manage-clients', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));

        Gate::define('manage-agencies', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));

        Gate::define('manage-salespeople', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));

        Gate::define('manage-platforms', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));

        Gate::define('manage-placements', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));

        Gate::define('manage-reservations', fn (User $user) => true);

        Gate::define('edit-financials', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));
    }
}
