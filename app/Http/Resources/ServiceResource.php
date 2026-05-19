<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $nextRule = $this->next_rule ?? null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'eventColor' => $this->eventColor,
            'price' => $this->price,
            'price_can_change' => $this->price_can_change,
            'duration_minutes_min' => $this->duration_minutes_min,
            'duration_minutes' => $this->duration_minutes,
            'short_description' => $this->short_description,
            'full_description' => $this->full_description,
            'status' => $this->status,
            'time_from' => $this->time_from ? Carbon::parse($this->time_from)->format('H:i') : null,
            'time_to' => $this->time_to ? Carbon::parse($this->time_to)->format('H:i') : null,
            'has_fixed_time' => $this->has_fixed_time,
            'is_deleted' => $this->is_deleted,
            'translation' => $this->translation,
            'users' => $this->users,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'effective_price' => $this->effective_price ?? $this->price,
            'effective_duration_minutes' => $this->effective_duration_minutes ?? $this->duration_minutes,
            'next_rule' => $nextRule ? [
                'valid_from' => $nextRule->valid_from ? Carbon::parse($nextRule->valid_from)->toDateString() : null,
                'price' => $nextRule->price,
                'duration_minutes' => $nextRule->duration_minutes,
            ] : null,
        ];
    }
}
