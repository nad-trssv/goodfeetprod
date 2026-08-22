<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Appointments;
use App\Policies\AppointmentPolicy;
use App\Policies\AdminPolicy;
use App\Policies\SuperAdminPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        $this->configureRateLimiters();

        Gate::define('is-admin', [AdminPolicy::class, 'view']);
        Gate::define('is-superadmin', [SuperAdminPolicy::class, 'view']);
        Gate::policy(Appointments::class, AppointmentPolicy::class);

        foreach (array_keys(config('permissions')) as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('chat-state', fn (Request $request) =>
            Limit::perMinute(60)->by('chat-state|'.$request->ip()));
        RateLimiter::for('chat-csrf', fn (Request $request) =>
            Limit::perMinute(30)->by('chat-csrf|'.$request->ip()));
        RateLimiter::for('chat-start', fn (Request $request) =>
            Limit::perMinute(10)->by('chat-start|'.$request->ip()));
        RateLimiter::for('chat-poll', fn (Request $request) =>
            Limit::perMinute(120)->by('chat-poll|'.$this->chatConversationKey($request).'|'.$request->ip()));
        RateLimiter::for('chat-message', fn (Request $request) =>
            Limit::perMinute(30)->by('chat-message|'.$this->chatConversationKey($request).'|'.$request->ip())
                ->response(fn (Request $request, array $headers) =>
                    response()->json(['message'=>__('crm.too_many_messages')],429,$headers)));
        RateLimiter::for('chat-rating', fn (Request $request) =>
            Limit::perMinute(10)->by('chat-rating|'.$this->chatConversationKey($request).'|'.$request->ip()));
        RateLimiter::for('chat-restart', fn (Request $request) =>
            Limit::perMinute(5)->by('chat-restart|'.$this->chatConversationKey($request).'|'.$request->ip()));

        RateLimiter::for('admin-notifications-status', fn (Request $request) =>
            Limit::perMinute(30)->by('admin-notifications-status|'.$request->user()?->getAuthIdentifier()));
        RateLimiter::for('admin-crm-status', fn (Request $request) =>
            Limit::perMinute(60)->by('admin-crm-status|'.$request->user()?->getAuthIdentifier()));
        RateLimiter::for('admin-crm-messages', fn (Request $request) =>
            Limit::perMinute(120)->by('admin-crm-messages|'.$this->chatConversationKey($request).'|'.$request->user()?->getAuthIdentifier()));
        RateLimiter::for('admin-crm-reply', fn (Request $request) =>
            Limit::perMinute(60)->by('admin-crm-reply|'.$this->chatConversationKey($request).'|'.$request->user()?->getAuthIdentifier()));
    }

    private function chatConversationKey(Request $request): string
    {
        $conversation = $request->route('conversation');

        return (string) (is_object($conversation) ? $conversation->getRouteKey() : $conversation);
    }
}
