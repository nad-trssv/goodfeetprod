<?php

namespace App\Services\Dashboard;

use App\Models\Appointments;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardFollowUpService
{
    public function lostCustomers(User $viewer, Request $request): array
    {
        $allScope = $this->allScope($viewer, $request);
        $masterId = $allScope && $request->integer('master_id') > 0 ? $request->integer('master_id') : null;
        $scopeId = $allScope ? $masterId : $viewer->id;
        $cutoff = now()->subDays(90)->endOfDay();
        $appointmentScope = fn (Builder $query) => $query->when($scopeId, fn (Builder $appointments) => $appointments->where('user_id', $scopeId));
        $search = trim((string) $request->query('search', ''));

        $customers = Customer::query()
            ->whereHas('appointments', fn (Builder $query) => $appointmentScope($query)
                ->where('status', 'completed')->where('appointment_start', '<', $cutoff))
            ->whereDoesntHave('appointments', fn (Builder $query) => $appointmentScope($query)
                ->where('status', 'completed')->where('appointment_start', '>=', $cutoff))
            ->whereDoesntHave('appointments', fn (Builder $query) => $appointmentScope($query)
                ->whereIn('status', Appointments::BLOCKING_STATUSES)->where('appointment_start', '>', now()))
            ->when($search !== '', function (Builder $query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(fn (Builder $identity) => $identity
                    ->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like));
            })
            ->withCount([
                'appointments as completed_count' => fn (Builder $query) => $appointmentScope($query)->where('status', 'completed'),
                'appointments as no_show_count' => fn (Builder $query) => $appointmentScope($query)->where('status', 'no_show'),
                'appointments as cancelled_count' => fn (Builder $query) => $appointmentScope($query)->whereIn('status', ['cancelled_by_client', 'cancelled_by_business']),
            ])
            ->withMax(['appointments as last_visit_at' => fn (Builder $query) => $appointmentScope($query)->where('status', 'completed')], 'appointment_start')
            ->withSum(['appointments as revenue' => fn (Builder $query) => $appointmentScope($query)->where('status', 'completed')], 'price')
            ->orderBy('last_visit_at')
            ->paginate(25)
            ->withQueryString();

        return [
            'customers' => $customers,
            'allScope' => $allScope,
            'masterId' => $masterId,
            'masters' => $allScope ? $this->masters() : collect([$viewer]),
            'cutoff' => $cutoff,
            'search' => $search,
        ];
    }

    public function unresolvedAppointments(User $viewer, Request $request): array
    {
        $allScope = $this->allScope($viewer, $request);
        $masterId = $allScope && $request->integer('master_id') > 0 ? $request->integer('master_id') : null;
        $scopeId = $allScope ? $masterId : $viewer->id;
        $search = trim((string) $request->query('search', ''));
        $base = Appointments::query()
            ->where('appointment_end', '<', now())
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhereIn('status', Appointments::BLOCKING_STATUSES))
            ->when($scopeId, fn (Builder $query) => $query->where('user_id', $scopeId));

        $employeeCounts = (clone $base)
            ->select('user_id')
            ->selectRaw('COUNT(*) as unresolved_count')
            ->groupBy('user_id')
            ->with('user:id,name,profile_photo_path')
            ->orderByDesc('unresolved_count')
            ->get();

        $appointments = $base
            ->when($search !== '', function (Builder $query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(fn (Builder $identity) => $identity
                    ->where('client_name', 'like', $like)
                    ->orWhere('client_lastname', 'like', $like)
                    ->orWhere('client_email', 'like', $like)
                    ->orWhere('client_phone', 'like', $like));
            })
            ->with(['service.translations', 'user:id,name,profile_photo_path', 'customer:id,first_name,last_name,email,phone'])
            ->orderBy('appointment_end')
            ->paginate(25)
            ->withQueryString();

        return [
            'appointments' => $appointments,
            'employeeCounts' => $employeeCounts,
            'allScope' => $allScope,
            'masterId' => $masterId,
            'masters' => $allScope ? $this->masters() : collect([$viewer]),
            'search' => $search,
        ];
    }

    public function unresolvedCount(User $viewer, bool $allScope = false): int
    {
        return Appointments::query()
            ->when(! $allScope, fn (Builder $query) => $query->where('user_id', $viewer->id))
            ->where('appointment_end', '<', now())
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhereIn('status', Appointments::BLOCKING_STATUSES))
            ->count();
    }

    public function unresolvedByEmployee(): \Illuminate\Support\Collection
    {
        return Appointments::query()
            ->where('appointment_end', '<', now())
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhereIn('status', Appointments::BLOCKING_STATUSES))
            ->select('user_id')
            ->selectRaw('COUNT(*) as unresolved_count')
            ->groupBy('user_id')
            ->with('user:id,name,profile_photo_path')
            ->orderByDesc('unresolved_count')
            ->get();
    }

    private function allScope(User $viewer, Request $request): bool
    {
        return $request->query('scope') === 'all'
            && $viewer->hasAllAppointmentsScope()
            && $viewer->hasPermission('dashboard.full');
    }

    private function masters()
    {
        return User::query()
            ->whereHas('role', fn (Builder $query) => $query->where('is_service_provider', true))
            ->orderBy('name')
            ->get(['id', 'name', 'profile_photo_path']);
    }
}
