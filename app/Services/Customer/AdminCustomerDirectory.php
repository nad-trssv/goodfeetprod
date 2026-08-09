<?php

namespace App\Services\Customer;

use App\Models\Appointments;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AdminCustomerDirectory
{
    public function paginate(User $viewer, array $filters): array
    {
        $masterId = $viewer->hasAllAppointmentsScope()
            ? ($filters['master_id'] ?? null)
            : $viewer->id;
        $search = $filters['search'] ?? null;
        $scope = fn (Builder $query) => $query->when($masterId, fn (Builder $query) => $query->where('user_id', $masterId));

        $query = Customer::query()
            ->whereHas('appointments', $scope)
            ->when($search, function (Builder $query, string $search) use ($scope) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function (Builder $identity) use ($like, $scope) {
                    $identity->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhereHas('appointments', function (Builder $appointments) use ($like, $scope) {
                            $scope($appointments);
                            $appointments->where(function (Builder $contacts) use ($like) {
                                $contacts->where('client_name', 'like', $like)
                                    ->orWhere('client_lastname', 'like', $like)
                                    ->orWhere('client_email', 'like', $like)
                                    ->orWhere('client_phone', 'like', $like);
                            });
                        });
                });
            })
            ->withCount([
                'appointments' => $scope,
                'appointments as completed_count' => fn (Builder $query) => $scope($query)->where('status', 'completed'),
                'appointments as no_show_count' => fn (Builder $query) => $scope($query)->where('status', 'no_show'),
                'appointments as upcoming_count' => fn (Builder $query) => $scope($query)
                    ->whereIn('status', Appointments::BLOCKING_STATUSES)
                    ->where('appointment_start', '>=', now()),
            ])
            ->withMax(['appointments as last_appointment_at' => $scope], 'appointment_start');

        match ($filters['sort'] ?? 'recent') {
            'name' => $query->orderBy('first_name')->orderBy('last_name'),
            'appointments' => $query->orderByDesc('appointments_count')->orderBy('first_name'),
            default => $query->orderByDesc('last_appointment_at')->orderBy('first_name'),
        };

        $customers = $query->paginate(25)->withQueryString();
        $this->attachMasterBreakdown($customers, $masterId);

        $masters = $viewer->hasAllAppointmentsScope()
            ? User::query()->whereHas('role', fn (Builder $query) => $query->where('is_service_provider', true)->orWhereIn('id', [1, 2]))
                ->whereHas('appointments', fn (Builder $query) => $query->whereNotNull('customer_id'))
                ->orderBy('name')->get(['id', 'name', 'profile_photo_path'])
            : collect([$viewer]);

        return compact('customers', 'masters', 'masterId');
    }

    private function attachMasterBreakdown(LengthAwarePaginator $customers, ?int $masterId): void
    {
        $customerIds = $customers->getCollection()->pluck('id');
        if ($customerIds->isEmpty()) {
            return;
        }

        $rows = Appointments::query()
            ->select(['customer_id', 'user_id'])
            ->selectRaw('COUNT(*) as appointments_count')
            ->whereIn('customer_id', $customerIds)
            ->when($masterId, fn (Builder $query) => $query->where('user_id', $masterId))
            ->groupBy('customer_id', 'user_id')
            ->with('user:id,name,profile_photo_path')
            ->get()
            ->groupBy('customer_id');

        $customers->getCollection()->each(function (Customer $customer) use ($rows) {
            $customer->setAttribute('master_breakdown', $rows->get($customer->id, collect()));
            $customer->setAttribute('has_account', filled($customer->getRawOriginal('password')));
        });
    }
}
