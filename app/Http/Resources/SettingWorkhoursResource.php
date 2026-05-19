<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingWorkhoursResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $workHours = json_decode($this->work_hours, true);

        return [
            'monday' => [
                'start' => $workHours['monday']['start'] ?? null,
                'end' => $workHours['monday']['end'] ?? null
            ],
            'tuesday' => [
                'start' => $workHours['tuesday']['start'] ?? null,
                'end' => $workHours['tuesday']['end'] ?? null
            ],
            'wednesday' => [
                'start' => $workHours['wednesday']['start'] ?? null,
                'end' => $workHours['wednesday']['end'] ?? null
            ],
            'thursday' => [
                'start' => $workHours['thursday']['start'] ?? null,
                'end' => $workHours['thursday']['end'] ?? null
            ],
            'friday' => [
                'start' => $workHours['friday']['start'] ?? null,
                'end' => $workHours['friday']['end'] ?? null
            ],
            'saturday' => [
                'start' => $workHours['saturday']['start'] ?? null,
                'end' => $workHours['saturday']['end'] ?? null
            ],
            'sunday' => [
                'start' => $workHours['sunday']['start'] ?? null,
                'end' => $workHours['sunday']['end'] ?? null
            ]
        ];
    }
}
