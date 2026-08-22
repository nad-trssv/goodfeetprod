<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemHealth\SystemHealthDashboard;
use App\Services\SystemHealth\SystemHealthMonitor;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class SystemHealthController extends Controller
{
    public function index(Request $request, SystemHealthDashboard $dashboard): View
    {
        $selectedDate = $this->validDate($request->query('date'));

        return view('admin.system-health.index', $dashboard->data($selectedDate));
    }

    public function run(SystemHealthMonitor $monitor): RedirectResponse
    {
        $result = $monitor->run('manual');

        return redirect()
            ->route('admin.system-health.index')
            ->with('success', __('admin_system_health.manual_complete', [
                'status' => __('admin_system_health.status.'.$result->status),
            ]));
    }

    private function validDate(mixed $date): ?string
    {
        if (! is_string($date)) {
            return null;
        }

        try {
            $parsed = CarbonImmutable::createFromFormat('!Y-m-d', $date);

            return $parsed && $parsed->format('Y-m-d') === $date ? $date : null;
        } catch (Throwable) {
            return null;
        }
    }
}
