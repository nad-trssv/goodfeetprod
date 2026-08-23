<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkShift;
use App\Models\WorkShiftBreak;
use App\Support\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkTimeService
{
    public function state(User $user): array
    {
        $shift = WorkShift::query()
            ->with('breaks')
            ->where('user_id', $user->id)
            ->whereNotNull('active_user_id')
            ->latest('started_at')
            ->first();

        return $this->payload($shift);
    }

    public function start(User $user): array
    {
        $shift = DB::transaction(function () use ($user) {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            if (WorkShift::query()->where('user_id', $user->id)->whereNotNull('active_user_id')->exists()) {
                throw ValidationException::withMessages(['shift' => __('admin_work_time.errors.already_started')]);
            }

            return WorkShift::create([
                'user_id' => $user->id,
                'started_at' => now(),
                'status' => 'running',
                'active_user_id' => $user->id,
            ]);
        });

        $this->log('work_time.started', 'Work shift started', $shift, $user);

        return $this->payload($shift->load('breaks'));
    }

    public function pause(User $user): array
    {
        $shift = DB::transaction(function () use ($user) {
            $shift = $this->lockActiveShift($user);
            if ($shift->status !== 'running') {
                throw ValidationException::withMessages(['shift' => __('admin_work_time.errors.cannot_pause')]);
            }
            WorkShiftBreak::create([
                'work_shift_id' => $shift->id,
                'started_at' => now(),
                'active_shift_id' => $shift->id,
            ]);
            $shift->update(['status' => 'paused']);

            return $shift;
        });

        $this->log('work_time.paused', 'Work shift paused', $shift, $user);

        return $this->payload($shift->load('breaks'));
    }

    public function resume(User $user): array
    {
        $shift = DB::transaction(function () use ($user) {
            $shift = $this->lockActiveShift($user);
            if ($shift->status !== 'paused') {
                throw ValidationException::withMessages(['shift' => __('admin_work_time.errors.cannot_resume')]);
            }
            $break = WorkShiftBreak::query()
                ->where('work_shift_id', $shift->id)
                ->whereNotNull('active_shift_id')
                ->lockForUpdate()
                ->firstOrFail();
            $break->update(['ended_at' => now(), 'active_shift_id' => null]);
            $shift->update(['status' => 'running']);

            return $shift;
        });

        $this->log('work_time.resumed', 'Work shift resumed', $shift, $user);

        return $this->payload($shift->load('breaks'));
    }

    public function stop(User $user): array
    {
        $shift = DB::transaction(function () use ($user) {
            $shift = $this->lockActiveShift($user);
            if ($shift->status === 'paused') {
                WorkShiftBreak::query()
                    ->where('work_shift_id', $shift->id)
                    ->whereNotNull('active_shift_id')
                    ->update(['ended_at' => now(), 'active_shift_id' => null, 'updated_at' => now()]);
            }
            $shift->update(['ended_at' => now(), 'status' => 'completed', 'active_user_id' => null]);

            return $shift;
        });

        $this->log('work_time.stopped', 'Work shift stopped', $shift, $user);

        return $this->payload($shift->load('breaks'));
    }

    private function lockActiveShift(User $user): WorkShift
    {
        $shift = WorkShift::query()
            ->where('user_id', $user->id)
            ->whereNotNull('active_user_id')
            ->lockForUpdate()
            ->first();

        if (! $shift) {
            throw ValidationException::withMessages(['shift' => __('admin_work_time.errors.not_started')]);
        }

        return $shift;
    }

    private function payload(?WorkShift $shift): array
    {
        if (! $shift || $shift->status === 'completed' || $shift->active_user_id === null) {
            return ['status' => 'inactive', 'shift_id' => null, 'started_at' => null, 'worked_seconds' => 0];
        }

        return [
            'status' => $shift->status,
            'shift_id' => $shift->id,
            'started_at' => $shift->started_at->toIso8601String(),
            'worked_seconds' => $shift->workedSeconds(),
        ];
    }

    private function log(string $event, string $message, WorkShift $shift, User $actor): void
    {
        ActivityLog::make($event, $message, 'work_time', subject: $shift, actor: $actor, properties: [
            'shift_id' => $shift->id,
            'employee_id' => $actor->id,
            'status' => $shift->status,
        ]);
    }
}
