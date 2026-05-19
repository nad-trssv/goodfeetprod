<?php

namespace App\Providers;

use App\Models\User;
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
    }
}
