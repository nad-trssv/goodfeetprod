<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Appointments;
use App\Policies\AppointmentPolicy;
use App\Policies\AdminPolicy;
use App\Policies\SuperAdminPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::define('is-admin', [AdminPolicy::class, 'view']);
        Gate::define('is-superadmin', [SuperAdminPolicy::class, 'view']);
        Gate::policy(Appointments::class, AppointmentPolicy::class);

        foreach (array_keys(config('permissions')) as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }
    }
}
