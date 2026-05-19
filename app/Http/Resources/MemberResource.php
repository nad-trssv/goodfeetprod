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
        return [
            'id'                    => $this->id,
            'username'              => $this->username,
            'name'                  => $this->name,
            'phone'                 => $this->phone,
            'email'                 => $this->email,
            'role_id'               => $this->role_id,
            'last_active'           => $this->last_active,
            'date_birthday'         => $this->date_birthday,
            'profile_photo_url'     => asset('storage/' . $this->profile_photo_path),
            'create_date'           => Carbon::parse($this->create_date)->format(env('DATETIME_FORMAT')),
            'update_date'           => Carbon::parse($this->create_date)->format(env('DATETIME_FORMAT')),
        ];
    }
}
