<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DashboardReportRequest;
use App\Services\DashboardService;
use App\Services\Dashboard\DashboardAnalyticsService;
use App\Services\Dashboard\DashboardFollowUpService;
use App\Services\Dashboard\DashboardPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): View
    {
        $this->authorize('is-admin', request()->user());
        $data = $this->dashboardService->index(request()->user());
         
        return view('admin.dashboard.index', $data);
    }

    public function full(DashboardReportRequest $request, DashboardAnalyticsService $analytics, DashboardFollowUpService $followUp): View
    {
        $period = DashboardPeriod::from($request->validated());
        $data = $analytics->report($period);
        $data['unresolved_total'] = $followUp->unresolvedCount($request->user(), true);
        $data['unresolved_by_employee'] = $followUp->unresolvedByEmployee();

        return view('admin.dashboard.full', $data);
    }

    public function lostCustomers(Request $request, DashboardFollowUpService $followUp): View
    {
        $request->validate(['scope' => ['nullable', 'in:own,all'], 'master_id' => ['nullable', 'integer'], 'search' => ['nullable', 'string', 'max:120']]);

        return view('admin.dashboard.lost-customers', $followUp->lostCustomers($request->user(), $request));
    }

    public function unresolvedAppointments(Request $request, DashboardFollowUpService $followUp): View
    {
        $request->validate(['scope' => ['nullable', 'in:own,all'], 'master_id' => ['nullable', 'integer'], 'search' => ['nullable', 'string', 'max:120']]);

        return view('admin.dashboard.unresolved-appointments', $followUp->unresolvedAppointments($request->user(), $request));
    }
}
