<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkShift extends Model
{
    protected $fillable = ['user_id', 'started_at', 'ended_at', 'status', 'active_user_id'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(WorkShiftBreak::class)->orderBy('started_at');
    }

    public function workedSeconds(?CarbonInterface $asOf = null): int
    {
        $asOf ??= now();
        $end = $this->ended_at ?? $asOf;
        $gross = max(0, (int) $this->started_at->diffInSeconds($end));
        $breaks = $this->relationLoaded('breaks') ? $this->breaks : $this->breaks()->get();
        $paused = $breaks->sum(fn (WorkShiftBreak $break) => max(
            0,
            (int) $break->started_at->diffInSeconds($break->ended_at ?? $asOf),
        ));

        return max(0, $gross - $paused);
    }
}
