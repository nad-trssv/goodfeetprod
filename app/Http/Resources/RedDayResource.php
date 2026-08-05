<?php
namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
class RedDayResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'date' => $this->date ? Carbon::parse($this->date)->format('Y-m-d') : null,
            'date_to' => $this->date_to ? Carbon::parse($this->date_to)->format('Y-m-d') : ($this->date ? Carbon::parse($this->date)->format('Y-m-d') : null),
            'type' => $this->type ?: 'other',
            'type_label' => $this->typeLabel(),
            'start_time' => $this->start_time ? Carbon::parse($this->start_time)->format('H:i') : null,
            'end_time' => $this->end_time ? Carbon::parse($this->end_time)->format('H:i') : null,
            'description' => $this->description,
            'repeat' => $this->repeat ? 'yes' : 'no',
            'user_id' => $this->user_id,
            'master_name' => $this->user ? $this->user->name : 'Общий',
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->toDateTimeString() : null,
        ];
    }
}
