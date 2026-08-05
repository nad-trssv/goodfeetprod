<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $vacation=$this->currentVacation();
        return [
            'id'                    => $this->id,
            'username'              => $this->username,
            'name'                  => $this->name,
            'phone'                 => $this->phone,
            'email'                 => $this->email,
            'role_id'               => $this->role_id,
            'last_active'           => $this->last_active,
            'is_online'             => $this->isOnline(),
            'last_seen'             => $this->lastSeen(),
            'date_birthday'         => $this->date_birthday,
            'profile_photo_url'     => $this->profile_photo_path ? asset('storage/' . $this->profile_photo_path) : null,
            'profile_initial'       => mb_strtoupper(mb_substr(trim($this->name), 0, 1)),
            'services_count'        => (int) ($this->services_count ?? 0),
            'work_state'            => $this->work_state,
            'today_hours'           => $this->todayHours(),
            'current_appointment'   => $this->appointmentSummary($this->whenLoaded('currentAppointment')),
            'next_appointment'      => $this->appointmentSummary($this->whenLoaded('nextAppointment')),
            'action_required'       => $this->appointmentSummary($this->whenLoaded('actionRequiredAppointment'), true),
            'today_stats'           => $this->today_stats,
            'vacation'              => $vacation ? ['from'=>Carbon::parse($vacation->date)->format('d.m.Y'),'to'=>$vacation->endDate()->format('d.m.Y')] : null,
            'create_date'           => $this->created_at?->format(config('app.datetime_format', 'd.m.Y H:i')),
            'update_date'           => $this->updated_at?->format(config('app.datetime_format', 'd.m.Y H:i')),
        ];
    }

    private function todayHours(): ?string
    {
        if (! $this->relationLoaded('schedule') || ! $this->schedule) return null;
        $day = strtolower(now()->format('l'));
        $start = $this->schedule->{$day.'_start'};
        $end = $this->schedule->{$day.'_end'};
        return $start && $end ? substr($start, 0, 5).'–'.substr($end, 0, 5) : null;
    }

    private function appointmentSummary($appointment, bool $includeId = false): ?array
    {
        if (! $appointment || ! $appointment instanceof \App\Models\Appointments) return null;
        return [
            'id' => $includeId ? $appointment->id : null,
            'service' => $appointment->service?->name,
            'client' => trim($appointment->client_name.' '.$appointment->client_lastname),
            'start' => $appointment->appointment_start->format('d.m H:i'),
            'end' => $appointment->appointment_end->format('H:i'),
        ];
    }
}
