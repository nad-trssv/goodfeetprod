<?php

namespace App\Support;

use App\Models\ActivityLogEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

final class ActivityLog
{
    public const TYPE_INFO = 1;
    public const TYPE_WARNING = 2;
    public const TYPE_ERROR = 3;

    /**
     * Создать запись в журнале действий.
     *
     * type:
     * 1 — информационное действие;
     * 2 — предупреждение;
     * 3 — ошибка.
     *
     * @param array<string, mixed> $properties
     */
    public static function make(
        string $event,
        string $message,
        string $module,
        int $type = self::TYPE_INFO,
        ?Model $subject = null,
        User|int|null $actor = null,
        array $properties = [],
    ): ActivityLogEntry {
        self::validateType($type);

        $actor ??= Auth::user();

        $actorModel = match (true) {
            $actor instanceof User => $actor,

            is_int($actor) => User::query()
                ->whereKey($actor)
                ->first(),

            default => null,
        };

        $request = app()->bound('request')
            ? request()
            : null;

        return ActivityLogEntry::query()->create([
            'type' => $type,

            'actor_id' => $actorModel?->id,
            'actor_name' => $actorModel?->name,

            /*
             * Технический код остаётся в базе,
             * но не показывается пользователю.
             */
            'event' => $event,

            'module' => $module,
            'message' => $message,

            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),

            /*
             * Название объекта можно сохранить для истории,
             * даже если на странице выводится только ID.
             */
            'subject_name' => self::resolveSubjectName(
                $subject
            ),

            'properties' => $properties !== []
                ? $properties
                : null,

            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    private static function validateType(int $type): void
    {
        if (!in_array(
            $type,
            [
                self::TYPE_INFO,
                self::TYPE_WARNING,
                self::TYPE_ERROR,
            ],
            true
        )) {
            throw new InvalidArgumentException(
                'Тип журнала должен быть числом от 1 до 3.'
            );
        }
    }

    private static function resolveSubjectName(
        ?Model $subject
    ): ?string {
        if ($subject === null) {
            return null;
        }

        foreach ([
            'name',
            'title',
            'client_name',
            'description',
        ] as $attribute) {
            $value = $subject->getAttribute($attribute);

            if (
                is_string($value)
                && trim($value) !== ''
            ) {
                return trim($value);
            }
        }

        return class_basename($subject)
            . ' #'
            . $subject->getKey();
    }
}