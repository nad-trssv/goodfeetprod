<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemHealthCheck extends Model
{
    protected $fillable = [
        'key',
        'status',
        'duration_ms',
        'message_key',
        'context',
    ];

    protected function casts(): array
    {
        return ['context' => 'array'];
    }

    public function run()
    {
        return $this->belongsTo(SystemHealthRun::class, 'system_health_run_id');
    }
}
