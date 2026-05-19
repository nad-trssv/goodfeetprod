<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MainSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'group'                 => $this->group,
            'key'                   => $this->key,
            'payload'               => json_decode($this->payload, true),
            'created_at'            => Carbon::parse($this->created_at)->format(env('DATETIME_FORMAT', 'Y-m-d H:i:s')),
            'updated_at'            => Carbon::parse($this->updated_at)->format(env('DATETIME_FORMAT', 'Y-m-d H:i:s')),
        ];
    }
}
