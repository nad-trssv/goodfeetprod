<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TriggerMessageAttempt extends Model
{
    protected $fillable = [
        'dispatch_id', 'provider', 'status', 'recipient', 'external_id', 'error',
        'response', 'attempted_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return ['response' => 'array', 'attempted_at' => 'datetime', 'delivered_at' => 'datetime'];
    }

    public function dispatch()
    {
        return $this->belongsTo(TriggerMessageDispatch::class, 'dispatch_id');
    }
}
