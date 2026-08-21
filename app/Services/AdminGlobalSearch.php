<?php

namespace App\Services;

use App\Models\Appointments;
use App\Models\Customer;
use App\Models\Services;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminGlobalSearch
{
    public function search(User $viewer, string $term, int $limit = 20): array
    {
        $term = trim(mb_substr($term, 0, 100));
        if (mb_strlen($term) < 2) {
            return $this->emptyResults();
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        return [
            'services' => $viewer->hasPermission('services.view')
                ? $this->services($like, $limit)
                : collect(),
            'customers' => $viewer->hasPermission('customers.view')
                ? $this->customers($viewer, $like, $limit)
                : collect(),
            'appointments' => $viewer->hasPermission('appointments.view')
                ? $this->appointments($viewer, $term, $like, $limit)
                : collect(),
        ];
    }

    private function services(string $like, int $limit): Collection
    {
        return Services::query()
            ->with('translations')
            ->where('is_deleted', false)
            ->where(function (Builder $query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('short_description', 'like', $like)
                    ->orWhereHas('translations', fn (Builder $translations) => $translations
                        ->where('name', 'like', $like)
                        ->orWhere('short_description', 'like', $like));
            })
            ->orderByDesc('status')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    private function customers(User $viewer, string $like, int $limit): Collection
    {
        return Customer::query()
            ->whereHas('appointments', fn (Builder $appointments) => $this->scopeAppointments($appointments, $viewer))
            ->where(function (Builder $query) use ($like) {
                $query->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhereHas('appointments', fn (Builder $appointments) => $appointments
                        ->where('client_name', 'like', $like)
                        ->orWhere('client_lastname', 'like', $like)
                        ->orWhere('client_email', 'like', $like)
                        ->orWhere('client_phone', 'like', $like));
            })
            ->withCount(['appointments' => fn (Builder $appointments) => $this->scopeAppointments($appointments, $viewer)])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit($limit)
            ->get();
    }

    private function appointments(User $viewer, string $term, string $like, int $limit): Collection
    {
        return Appointments::query()
            ->with(['service.translations', 'user:id,name,profile_photo_path', 'customer:id,first_name,last_name,email,phone'])
            ->tap(fn (Builder $query) => $this->scopeAppointments($query, $viewer))
            ->where(function (Builder $query) use ($term, $like) {
                if (ctype_digit($term)) {
                    $query->orWhereKey((int) $term);
                }
                $query->orWhere('public_uuid', $term)
                    ->orWhere('client_name', 'like', $like)
                    ->orWhere('client_lastname', 'like', $like)
                    ->orWhere('client_email', 'like', $like)
                    ->orWhere('client_phone', 'like', $like)
                    ->orWhere('appointment_start', 'like', $like)
                    ->orWhereHas('service', function (Builder $service) use ($like) {
                        $service->where('name', 'like', $like)
                            ->orWhereHas('translations', fn (Builder $translations) => $translations->where('name', 'like', $like));
                    });
            })
            ->orderByDesc('appointment_start')
            ->limit($limit)
            ->get();
    }

    private function scopeAppointments(Builder $query, User $viewer): Builder
    {
        return $query->when(
            ! $viewer->hasAllAppointmentsScope(),
            fn (Builder $appointments) => $appointments->where('user_id', $viewer->id)
        );
    }

    private function emptyResults(): array
    {
        return ['services' => collect(), 'customers' => collect(), 'appointments' => collect()];
    }
}
