<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminCustomerDuplicateDetector
{
    public function __construct(private readonly CustomerIdentityService $identity)
    {
    }

    public function find(User $viewer, array $input): Collection
    {
        $email = filled($input['email'] ?? null) ? $this->identity->normalizeEmail($input['email']) : null;
        $phone = filled($input['phone'] ?? null) ? $this->identity->normalizePhone($input['phone']) : null;
        $firstName = trim((string) ($input['first_name'] ?? ''));
        $lastName = trim((string) ($input['last_name'] ?? ''));

        if (! $email && ! $phone && mb_strlen($firstName.' '.$lastName) < 4) {
            return collect();
        }

        return Customer::query()
            ->when(! $viewer->hasAllAppointmentsScope(), fn (Builder $query) => $query->whereHas(
                'appointments', fn (Builder $appointments) => $appointments->where('user_id', $viewer->id)
            ))
            ->where(function (Builder $query) use ($email, $phone, $firstName, $lastName) {
                if ($email) {
                    $query->orWhereRaw('LOWER(TRIM(email)) = ?', [$email]);
                }
                if ($phone) {
                    $query->orWhere('phone', $phone);
                }
                if ($firstName && $lastName) {
                    $query->orWhere(fn (Builder $name) => $name
                        ->whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower($firstName)])
                        ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower($lastName)]));
                }
            })
            ->withCount('appointments')
            ->orderByDesc('appointments_count')
            ->limit(5)
            ->get()
            ->map(function (Customer $customer) use ($email, $phone, $firstName, $lastName) {
                $reasons = [];
                if ($email && $this->identity->normalizeEmail((string) $customer->email) === $email) {
                    $reasons[] = 'email';
                }
                if ($phone && $this->identity->normalizePhone((string) $customer->phone) === $phone) {
                    $reasons[] = 'phone';
                }
                if ($firstName && $lastName
                    && mb_strtolower(trim($customer->first_name)) === mb_strtolower($firstName)
                    && mb_strtolower(trim((string) $customer->last_name)) === mb_strtolower($lastName)) {
                    $reasons[] = 'name';
                }

                return [
                    'id' => $customer->id,
                    'name' => $customer->full_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'has_account' => filled($customer->getRawOriginal('password')),
                    'appointments_count' => $customer->appointments_count,
                    'reasons' => $reasons,
                ];
            });
    }
}
