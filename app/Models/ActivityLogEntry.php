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
            self::TYPE_INFO => __('admin_activity.types.info'),
            self::TYPE_WARNING => __('admin_activity.types.warning'),
            self::TYPE_ERROR => __('admin_activity.types.error'),
            default => __('admin_activity.types.unknown'),
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
    public function localizedMessage(): string
    {
        $isOwnAction = (int) $this->actor_id === (int) ($this->properties['target_master_id'] ?? 0);
        $key = match ($this->event) {
            'master_service.enabled' => $isOwnAction ? 'service_enabled_own' : 'service_enabled_employee',
            'master_service.disabled' => $isOwnAction ? 'service_disabled_own' : 'service_disabled_employee',
            'master_service.settings_updated' => $isOwnAction ? 'settings_updated_own' : 'settings_updated_employee',
            'master_service.toggle_failed' => 'toggle_failed',
            'master_service.settings_update_failed' => 'settings_update_failed',
            default => null,
        };

        return $key ? __('admin_activity_events.'.$key) : $this->message;
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
