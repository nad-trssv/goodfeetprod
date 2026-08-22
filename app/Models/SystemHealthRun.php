<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemHealthRun extends Model
{
    protected $fillable = [
        'status',
        'source',
        'duration_ms',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function checks()
    {
        return $this->hasMany(SystemHealthCheck::class);
    }
}
