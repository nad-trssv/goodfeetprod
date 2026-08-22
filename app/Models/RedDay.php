<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedDay extends Model
{
    use HasFactory;

    public const TYPES = [
        'personal_unpaid' => 'Личное время / за свой счёт',
        'paid_vacation' => 'Оплачиваемый отпуск',
        'sick_leave' => 'Больничный',
        'training' => 'Обучение',
        'business_trip' => 'Командировка',
        'company_closure' => 'Компания закрыта',
        'other' => 'Другая причина',
    ];

    public static function typeOptions(): array
    {
        return collect(array_keys(self::TYPES))->mapWithKeys(fn (string $type) => [
            $type => __('admin_staff.closure_types.'.$type),
        ])->all();
    }

    public function typeLabel(): string { return __('admin_staff.closure_types.'.($this->type ?: 'other')); }
    public function endDate(): \Carbon\Carbon { return \Carbon\Carbon::parse($this->date_to ?: $this->date); }

    protected $fillable = [
        'name',
        'type',
        'date',
        'date_to',
        'start_time',
        'end_time',
        'full_day',
        'description',
        'repeat',
        'user_id',
    ];

    protected $dates = [
        'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Общие красные дни (без привязки к мастеру)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Индивидуальные красные дни конкретного мастера
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Общие + индивидуальные для конкретного мастера
     */
    public function scopeVisibleFor($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id')
              ->orWhere('user_id', $userId);
        });
    }
}
