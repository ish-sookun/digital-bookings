<?php

namespace App\Providers;

use App\Models\User;
use App\UserRole;
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
        Password::defaults(fn () => Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
        );

        Gate::define('manage-users', fn (User $user) => $user->isSuperAdmin());

        Gate::define('manage-budgets', fn (User $user) => $user->isSuperAdmin());

        Gate::define('view-dashboard', fn (User $user) => $user->hasRole(
            UserRole::SuperAdmin, UserRole::Admin, UserRole::Salesperson, UserRole::Management,
        ));

        Gate::define('view-calendar', fn (User $user) => $user->hasRole(
            UserRole::SuperAdmin, UserRole::Admin, UserRole::Salesperson, UserRole::Management,
        ));

        Gate::define('view-reservations', fn (User $user) => $user->hasRole(
            UserRole::SuperAdmin, UserRole::Admin, UserRole::Salesperson, UserRole::Finance,
        ));

        Gate::define('manage-reservations', fn (User $user) => $user->hasRole(
            UserRole::SuperAdmin, UserRole::Admin, UserRole::Salesperson,
        ));

        Gate::define('manage-clients', fn (User $user) => $user->hasRole(
            UserRole::SuperAdmin, UserRole::Admin, UserRole::Salesperson,
        ));

        Gate::define('manage-agencies', fn (User $user) => $user->hasRole(
            UserRole::SuperAdmin, UserRole::Admin, UserRole::Salesperson,
        ));

        Gate::define('delete-clients', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));

        Gate::define('delete-agencies', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));

        Gate::define('manage-salespeople', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));

        Gate::define('manage-platforms', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));

        Gate::define('manage-placements', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));

        Gate::define('edit-financials', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Admin));

        Gate::define('view-targets', fn (User $user) => $user->hasRole(UserRole::SuperAdmin, UserRole::Management));
    }
}
