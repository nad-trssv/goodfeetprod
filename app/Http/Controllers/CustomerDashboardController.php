<?php

namespace App\Http\Controllers;

use App\Models\Services;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Booking\CustomerCancellationService;

class CustomerDashboardController extends Controller
{
    public function __invoke(Request $request, CustomerCancellationService $cancellations)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'service_id' => ['nullable', 'integer'],
            'master_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort' => ['nullable', 'in:service,master,date'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $customer = Auth::guard('customer')->user();
        $base = $customer->appointments()->with(['service.translations', 'user', 'rescheduleRequests' => fn ($query) => $query->where('status', 'pending')]);

        $upcoming = (clone $base)
            ->where('appointment_start', '>=', now())
            ->where('status', 'not like', 'cancelled%')
            ->orderBy('appointment_start')
            ->get();

        $historyQuery = (clone $base)->where(function (Builder $query) {
            $query->where('appointment_start', '<', now())
                ->orWhere('status', 'like', 'cancelled%');
        });

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $historyQuery->where(function (Builder $query) use ($search) {
                $query->whereHas('service', fn (Builder $service) => $service
                    ->where('name', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn (Builder $translation) => $translation->where('name', 'like', "%{$search}%")))
                    ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', "%{$search}%"));
            });
        }

        $historyQuery
            ->when($filters['service_id'] ?? null, fn (Builder $query, $id) => $query->where('service_id', $id))
            ->when($filters['master_id'] ?? null, fn (Builder $query, $id) => $query->where('user_id', $id))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $date) => $query->whereDate('appointment_start', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $date) => $query->whereDate('appointment_start', '<=', $date));

        $sort = $filters['sort'] ?? 'date';
        $direction = $filters['direction'] ?? 'desc';
        match ($sort) {
            'service' => $historyQuery->orderBy(
                Services::select('name')->whereColumn('services.id', 'appointments.service_id'),
                $direction,
            ),
            'master' => $historyQuery->orderBy(
                User::select('name')->whereColumn('users.id', 'appointments.user_id'),
                $direction,
            ),
            default => $historyQuery->orderBy('appointment_start', $direction),
        };

        $history = $historyQuery
            ->orderByDesc('appointments.id')
            ->paginate(20)
            ->withQueryString();
        $historyOptions = $customer->appointments()
            ->where(function (Builder $query) {
                $query->where('appointment_start', '<', now())->orWhere('status', 'like', 'cancelled%');
            });
        $serviceIds = (clone $historyOptions)->select('service_id')->distinct()->pluck('service_id');
        $masterIds = (clone $historyOptions)->select('user_id')->distinct()->pluck('user_id');

        return view('pages.customer.dashboard', [
            'customer' => $customer,
            'upcoming' => $upcoming,
            'history' => $history,
            'services' => Services::with('translations')->whereIn('id', $serviceIds)->orderBy('name')->get(),
            'masters' => User::whereIn('id', $masterIds)->orderBy('name')->get(),
            'filters' => $filters,
            'cancellationNoticeHours' => $cancellations->noticeHours(),
            'customerCancellationEnabled' => $cancellations->enabled(),
        ]);
    }
}
