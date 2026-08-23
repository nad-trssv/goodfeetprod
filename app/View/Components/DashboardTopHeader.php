<?php

namespace App\View\Components;

use App\Services\Crm\CrmChatAccess;
use App\Services\WorkTimeService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DashboardTopHeader extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $user = auth()->user();

        return view('components.dashboard-top-header', [
            'unreadNotificationCount' => $user?->unreadNotifications()->count() ?? 0,
            'headerNotifications' => $user?->unreadNotifications()->latest()->limit(6)->get() ?? collect(),
            'unreadCrmChatCount' => $user?->hasPermission('crm.chat.view') ? app(CrmChatAccess::class)->unreadCount($user) : 0,
            'workShiftState' => $user ? app(WorkTimeService::class)->state($user) : ['status' => 'inactive'],
        ]);
    }
}
