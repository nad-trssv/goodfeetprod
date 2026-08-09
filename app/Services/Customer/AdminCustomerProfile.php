<?php

namespace App\Services\Customer;

use App\Models\Appointments;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class AdminCustomerProfile
{
    public function get(Customer $customer, User $viewer): array
    {
        $base = $this->appointments($customer, $viewer);
        $summary = (clone $base)->selectRaw(
            "COUNT(*) as bookings_count,
             SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
             SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show_count,
             SUM(CASE WHEN status IN ('cancelled_by_client','cancelled_by_business') THEN 1 ELSE 0 END) as cancelled_count,
             SUM(CASE WHEN status IN ('pending','confirmed','checked_in','in_progress') AND appointment_start >= ? THEN 1 ELSE 0 END) as upcoming_count,
             COALESCE(SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END), 0) as revenue,
             COALESCE(AVG(CASE WHEN status = 'completed' THEN price END), 0) as average_check,
             MIN(appointment_start) as first_booking_at,
             MAX(appointment_start) as last_booking_at",
            [now()]
        )->first();

        $completedRange = (clone $base)
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as visits, MIN(appointment_start) as first_visit_at, MAX(appointment_start) as last_visit_at')
            ->first();

        $visitFrequencyDays = null;
        if ((int) $completedRange->visits > 1) {
            $first = Carbon::parse($completedRange->first_visit_at);
            $last = Carbon::parse($completedRange->last_visit_at);
            $visitFrequencyDays = max(1, (int) round($first->diffInDays($last) / ((int) $completedRange->visits - 1)));
        }

        $appointments = (clone $base)
            ->with(['service.translations', 'user:id,name,profile_photo_path'])
            ->latest('appointment_start')
            ->paginate(20, ['*'], 'appointments_page')
            ->withQueryString();

        $favoriteServices = (clone $base)
            ->where('status', 'completed')
            ->whereNotNull('service_id')
            ->select(['service_id'])
            ->selectRaw('COUNT(*) as visits_count, COALESCE(SUM(price), 0) as revenue')
            ->groupBy('service_id')
            ->orderByDesc('visits_count')
            ->limit(5)
            ->with('service.translations')
            ->get();

        $favoriteMasters = (clone $base)
            ->where('status', 'completed')
            ->whereNotNull('user_id')
            ->select(['user_id'])
            ->selectRaw('COUNT(*) as visits_count, COALESCE(SUM(price), 0) as revenue')
            ->groupBy('user_id')
            ->orderByDesc('visits_count')
            ->limit(5)
            ->with('user:id,name,profile_photo_path')
            ->get();

        [$possibleAppointments, $possibleCount] = $this->possibleAppointments($customer, $viewer);

        return compact(
            'customer',
            'summary',
            'visitFrequencyDays',
            'appointments',
            'favoriteServices',
            'favoriteMasters',
            'possibleAppointments',
            'possibleCount',
        );
    }

    public function canView(Customer $customer, User $viewer): bool
    {
        return $viewer->hasAllAppointmentsScope()
            || $customer->appointments()->where('user_id', $viewer->id)->exists();
    }

    private function appointments(Customer $customer, User $viewer): Builder
    {
        return Appointments::query()
            ->where('customer_id', $customer->id)
            ->when(! $viewer->hasAllAppointmentsScope(), fn (Builder $query) => $query->where('user_id', $viewer->id));
    }

    private function possibleAppointments(Customer $customer, User $viewer): array
    {
        if (! filled($customer->getRawOriginal('password'))) {
            return [null, 0];
        }

        $email = mb_strtolower(trim($customer->email));
        $phone = $this->normalizePhone($customer->phone);
        $phoneExpression = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(client_phone), ' ', ''), '-', ''), '(', ''), ')', ''), '.', ''), '/', '')";

        $query = Appointments::query()
            ->where(function (Builder $identity) use ($customer) {
                $identity->where(function (Builder $linked) use ($customer) {
                    $linked->where('customer_id', $customer->id)
                        ->where('customer_identity_verified', false);
                })->orWhere('customer_id', '!=', $customer->id)
                    ->orWhereNull('customer_id');
            })
            ->where(function (Builder $contact) use ($email, $phone, $phoneExpression) {
                $contact->whereRaw('LOWER(TRIM(client_email)) = ?', [$email])
                    ->orWhereRaw($phoneExpression.' = ?', [$phone]);
            })
            ->when(! $viewer->hasAllAppointmentsScope(), fn (Builder $query) => $query->where('user_id', $viewer->id));

        $count = (clone $query)->count();
        $appointments = $query
            ->with(['service.translations', 'user:id,name,profile_photo_path'])
            ->latest('appointment_start')
            ->paginate(20, ['*'], 'possible_page')
            ->withQueryString();

        $appointments->getCollection()->each(function (Appointments $appointment) use ($customer, $email, $phone) {
            $emailMatches = mb_strtolower(trim((string) $appointment->client_email)) === $email;
            $phoneMatches = $this->normalizePhone((string) $appointment->client_phone) === $phone;
            $appointment->setAttribute('identity_match', $emailMatches && $phoneMatches ? 'both' : ($emailMatches ? 'email' : 'phone'));
            $appointment->setAttribute('already_linked', (int) $appointment->customer_id === (int) $customer->id);
        });

        return [$appointments, $count];
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', trim($phone));

        return str_starts_with($phone, '+') ? $phone : '+'.ltrim($phone, '+');
    }
}
