<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): \Illuminate\View\View
    {
        $this->authorize('is-admin', Auth::user());
        $data = $this->dashboardService->index();
         
        return view('admin.dashboard.index', [
            'appointments' => $data['appointments'],
            'services' => $data['services'],
            'stats' => $data['stats'],
            'chartDataByDay' => $data['chartDataByDay'],
            'chartDataByMonth' => $data['chartDataByMonth'],
            'events' => $data['events']['my'],
            'allEvents' => $data['events']['all'],
            'activity' => $data['activity'],
        ]);
    }
}
