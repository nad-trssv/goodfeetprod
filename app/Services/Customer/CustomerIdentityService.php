<?php

namespace App\Services\Customer;

use App\Models\Appointments;
use App\Models\AppointmentAudit;
use App\Models\AppointmentStatusHistory;
use App\Models\Customer;
use Illuminate\Validation\ValidationException;

class CustomerIdentityService
{
    public function claimForRegistration(string $firstName, ?string $lastName, string $email, string $phone): Customer
    {
        $email = $this->normalizeEmail($email);
        $phone = $this->normalizePhone($phone);
        $byEmail = Customer::where('email', $email)->lockForUpdate()->first();
        $byPhone = Customer::where('phone', $phone)->lockForUpdate()->first();

        foreach (array_filter([$byEmail, $byPhone]) as $existing) {
            if ($existing->password) {
                throw ValidationException::withMessages([
                    $existing->email === $email ? 'email' : 'phone' => 'Аккаунт с этими контактными данными уже существует. Войдите в него или восстановите пароль.',
                ]);
            }
        }

        $customer = $byEmail ?? $byPhone;
        if (! $customer) {
            $customer = Customer::create([
                'first_name' => trim($firstName),
                'last_name' => trim((string) $lastName) ?: null,
                'email' => $email,
                'phone' => $phone,
                'locale' => app()->getLocale(),
            ]);
        }

        if ($byEmail && $byPhone && ! $byEmail->is($byPhone)) {
            Appointments::where('customer_id', $byPhone->id)->update(['customer_id' => $byEmail->id]);
            AppointmentAudit::where('actor_type', $byPhone->getMorphClass())
                ->where('actor_id', $byPhone->id)
                ->update(['actor_id' => $byEmail->id]);
            AppointmentStatusHistory::where('changed_by_type', $byPhone->getMorphClass())
                ->where('changed_by_id', $byPhone->id)
                ->update(['changed_by_id' => $byEmail->id]);
            $byPhone->delete();
            $customer = $byEmail;
        }

        // Older guest bookings may not have customer_id because their contact
        // formatting differed. Email is normalized before the comparison.
        Appointments::whereNull('customer_id')
            ->whereRaw('LOWER(TRIM(client_email)) = ?', [$email])
            ->update(['customer_id' => $customer->id]);

        $customer->update([
            'first_name' => trim($firstName),
            'last_name' => trim((string) $lastName) ?: null,
            'email' => $email,
            'phone' => $phone,
        ]);

        return $customer;
    }

    public function resolveForBooking(string $firstName, ?string $lastName, string $email, string $phone): Customer
    {
        $email = $this->normalizeEmail($email);
        $phone = $this->normalizePhone($phone);
        $byEmail = Customer::where('email', $email)->lockForUpdate()->first();
        $byPhone = Customer::where('phone', $phone)->lockForUpdate()->first();

        if ($byEmail && $byPhone && ! $byEmail->is($byPhone)) {
            throw ValidationException::withMessages([
                'client_email' => 'Этот email и телефон принадлежат разным клиентским аккаунтам.',
            ]);
        }
        if (($byEmail && ! $byPhone) || ($byPhone && ! $byEmail)) {
            throw ValidationException::withMessages([
                $byEmail ? 'client_phone' : 'client_email' => 'Email и телефон должны совпадать с данными существующего аккаунта.',
            ]);
        }

        $customer = $byEmail ?? $byPhone;
        if ($customer) {
            return $customer;
        }

        return Customer::create([
            'first_name' => trim($firstName),
            'last_name' => trim((string) $lastName) ?: null,
            'email' => $email,
            'phone' => $phone,
            'locale' => app()->getLocale(),
        ]);
    }

    public function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', trim($phone));
        if (! str_starts_with($phone, '+')) {
            $phone = '+'.ltrim($phone, '+');
        }

        return $phone;
    }
}
