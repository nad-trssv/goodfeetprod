<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WorkTimeReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkTimeReportController extends Controller
{
    public function index(Request $request, WorkTimeReportService $service): View
    {
        return $this->render($request, $service);
    }

    public function employee(Request $request, User $member, WorkTimeReportService $service): View
    {
        abort_unless($member->isStaff(), 404);

        return $this->render($request, $service, $member);
    }

    private function render(Request $request, WorkTimeReportService $service, ?User $member = null): View
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'employee_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $from = isset($validated['date_from']) ? Carbon::parse($validated['date_from']) : now()->startOfMonth();
        $to = isset($validated['date_to']) ? Carbon::parse($validated['date_to']) : now()->endOfMonth();
        abort_if($from->diffInDays($to) > 366, 422, __('admin_work_time.errors.period_too_long'));

        if (! $member && ! empty($validated['employee_id'])) {
            $candidate = User::with('role')->findOrFail($validated['employee_id']);
            abort_unless($candidate->isStaff(), 404);
            $member = $candidate;
        }

        $report = $service->build($from, $to, $member);
        $allEmployees = User::query()->with('role')->orderBy('name')->get()->filter(fn (User $user) => $user->isStaff());

        return view('admin.work-time.index', $report + [
            'dateFrom' => $from->toDateString(),
            'dateTo' => $to->toDateString(),
            'selectedEmployee' => $member,
            'allEmployees' => $allEmployees,
        ]);
    }
}
