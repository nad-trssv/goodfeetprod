<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'client_name' => $this->client_name,
            'client_lastname' => $this->client_lastname,
            'description' => $this->description,
            'client_phone' => $this->client_phone,
            'service_id' => $this->service_id,
            'user_id' => $this->user_id,
            'price' => $this->price,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
