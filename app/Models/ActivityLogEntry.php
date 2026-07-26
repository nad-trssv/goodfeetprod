<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLogEntry extends Model
{
    public const TYPE_INFO = 1;
    public const TYPE_WARNING = 2;
    public const TYPE_ERROR = 3;

    protected $table = 'activity_logs';

    public const UPDATED_AT = null;

    protected $fillable = [
        'type',
        'actor_id',
        'actor_name',
        'event',
        'module',
        'subject_type',
        'subject_id',
        'subject_name',
        'message',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'type' => 'integer',
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'actor_id'
        );
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function isInformation(): bool
    {
        return $this->type === self::TYPE_INFO;
    }

    public function isWarning(): bool
    {
        return $this->type === self::TYPE_WARNING;
    }

    public function isError(): bool
    {
        return $this->type === self::TYPE_ERROR;
    }
    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_INFO => 'Информация',
            self::TYPE_WARNING => 'Предупреждение',
            self::TYPE_ERROR => 'Ошибка',
            default => 'Неизвестный тип',
        };
    }

    public function typeCssClass(): string
    {
        return match ($this->type) {
            self::TYPE_INFO => 'activity-log-type--info',
            self::TYPE_WARNING => 'activity-log-type--warning',
            self::TYPE_ERROR => 'activity-log-type--error',
            default => 'activity-log-type--unknown',
        };
    }
    public function objectLabel(): string
    {
        if ($this->module === 'employees/services') {
            $targetMasterId = $this->properties[
                'target_master_id'
            ] ?? null;

            if (
                $targetMasterId !== null
                && $this->subject_id !== null
            ) {
                return (int) $targetMasterId
                    . '/'
                    . (int) $this->subject_id;
            }
        }

        if ($this->subject_id !== null) {
            return (string) $this->subject_id;
        }

        return '—';
    }
}