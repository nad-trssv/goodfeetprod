<?php

namespace App\Services;

use App\Models\Services;
use App\Models\User;
use App\Models\UserServices;
use App\Support\ActivityLog;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class MasterServiceManager
{
    private const TRACKED_FIELDS = [
        'price_override',
        'duration_minutes_override',
        'duration_minutes_min_override',
        'buffer_before_minutes',
        'buffer_after_minutes',
    ];

    /**
     * Подключить или отключить услугу мастера.
     */
    public function toggle(
        User $master,
        Services $service,
        User $actor
    ): bool {
        try {
            $result = DB::transaction(
                function () use (
                    $master,
                    $service
                ): array {
                    $masterService =
                        $this->findOrCreateMasterService(
                            masterId: $master->id,
                            serviceId: $service->id
                        );

                    $before = [
                        'is_active' =>
                            (bool) $masterService->is_active,
                    ];

                    $masterService->is_active =
                        !$masterService->is_active;

                    $masterService->save();

                    return [
                        'is_active' =>
                            (bool) $masterService->is_active,

                        'user_service_id' =>
                            $masterService->id,

                        'before' => $before,

                        'after' => [
                            'is_active' =>
                                (bool) $masterService->is_active,
                        ],
                    ];
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->writeActivityLogSafely(
                event: 'master_service.toggle_failed',
                message:
                    'Не удалось изменить состояние услуги мастера',
                type: ActivityLog::TYPE_ERROR,
                service: $service,
                actor: $actor,
                master: $master,
                properties: [
                    'exception_class' => $exception::class,
                    'error' => mb_substr(
                        $exception->getMessage(),
                        0,
                        1000
                    ),
                ]
            );

            throw $exception;
        }

        $isOwnAction =
            (int) $actor->id === (int) $master->id;

        $message = match (true) {
            $result['is_active'] && $isOwnAction =>
                'Услуга подключена',

            !$result['is_active'] && $isOwnAction =>
                'Услуга отключена',

            $result['is_active'] =>
                'Услуга мастера подключена',

            default =>
                'Услуга мастера отключена',
        };

        $this->writeActivityLogSafely(
            event: $result['is_active']
                ? 'master_service.enabled'
                : 'master_service.disabled',

            message: $message,
            type: ActivityLog::TYPE_INFO,
            service: $service,
            actor: $actor,
            master: $master,

            properties: [
                'user_service_id' =>
                    $result['user_service_id'],

                'before' =>
                    $result['before'],

                'after' =>
                    $result['after'],
            ]
        );

        return $result['is_active'];
    }

    /**
     * Обновить индивидуальные настройки услуги мастера.
     *
     * Возвращает true, когда значения действительно изменились.
     */
    public function updateSettings(
        User $master,
        Services $service,
        User $actor,
        array $attributes
    ): bool {
        try {
            $result = DB::transaction(
                function () use (
                    $master,
                    $service,
                    $attributes
                ): array {
                    $masterService =
                        $this->findOrCreateMasterService(
                            masterId: $master->id,
                            serviceId: $service->id
                        );

                    $before = $masterService->only(
                        self::TRACKED_FIELDS
                    );

                    $masterService->fill($attributes);

                    if (
                        !$masterService->isDirty(
                            self::TRACKED_FIELDS
                        )
                    ) {
                        return [
                            'changed' => false,
                            'user_service_id' =>
                                $masterService->id,
                            'before' => $before,
                            'after' => $before,
                            'effective' =>
                                $this->effectiveSettings(
                                    masterService:
                                        $masterService,
                                    service: $service
                                ),
                        ];
                    }

                    $masterService->save();

                    $after = $masterService->only(
                        self::TRACKED_FIELDS
                    );

                    return [
                        'changed' => true,

                        'user_service_id' =>
                            $masterService->id,

                        'before' => $before,

                        'after' => $after,

                        'effective' =>
                            $this->effectiveSettings(
                                masterService:
                                    $masterService,
                                service: $service
                            ),
                    ];
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->writeActivityLogSafely(
                event:
                    'master_service.settings_update_failed',

                message:
                    'Не удалось изменить индивидуальные настройки услуги мастера',

                type: ActivityLog::TYPE_ERROR,
                service: $service,
                actor: $actor,
                master: $master,

                properties: [
                    'exception_class' => $exception::class,

                    'error' => mb_substr(
                        $exception->getMessage(),
                        0,
                        1000
                    ),
                ]
            );

            throw $exception;
        }

        if (!$result['changed']) {
            return false;
        }

        $isOwnAction =
            (int) $actor->id === (int) $master->id;

        $this->writeActivityLogSafely(
            event: 'master_service.settings_updated',

            message: $isOwnAction
                ? 'Индивидуальные настройки услуги изменены'
                : 'Индивидуальные настройки услуги мастера изменены',

            type: ActivityLog::TYPE_INFO,
            service: $service,
            actor: $actor,
            master: $master,

            properties: [
                'user_service_id' =>
                    $result['user_service_id'],

                'before' =>
                    $result['before'],

                'after' =>
                    $result['after'],

                'effective' =>
                    $result['effective'],
            ]
        );

        return true;
    }

    /**
     * Получить или создать связь мастера с услугой.
     *
     * Метод вызывается внутри DB::transaction().
     */
    private function findOrCreateMasterService(
        int $masterId,
        int $serviceId
    ): UserServices {
        $now = now();

        DB::table('user_services')->insertOrIgnore([
            'user_id' => $masterId,
            'service_id' => $serviceId,
            'is_active' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $masterService = UserServices::query()
            ->where('user_id', $masterId)
            ->where('service_id', $serviceId)
            ->lockForUpdate()
            ->get()
            ->first();

        if (!$masterService instanceof UserServices) {
            throw new RuntimeException(
                'Не удалось получить настройки услуги мастера.'
            );
        }

        return $masterService;
    }

    private function effectiveSettings(
        UserServices $masterService,
        Services $service
    ): array {
        return [
            'price' =>
                $masterService->price_override !== null
                    ? (string) $masterService
                        ->price_override
                    : (string) $service->price,

            'duration_minutes' =>
                $masterService
                    ->duration_minutes_override !== null
                    ? (int) $masterService
                        ->duration_minutes_override
                    : (int) $service
                        ->duration_minutes,

            'duration_minutes_min' =>
                $masterService
                    ->duration_minutes_min_override !== null
                    ? (int) $masterService
                        ->duration_minutes_min_override
                    : (
                        $service
                            ->duration_minutes_min !== null
                            ? (int) $service
                                ->duration_minutes_min
                            : null
                    ),

            'buffer_before_minutes' =>
                (int) $masterService
                    ->buffer_before_minutes,

            'buffer_after_minutes' =>
                (int) $masterService
                    ->buffer_after_minutes,
        ];
    }

    /**
     * Запись лога не должна ломать основную операцию.
     */
    private function writeActivityLogSafely(
        string $event,
        string $message,
        int $type,
        Services $service,
        User $actor,
        User $master,
        array $properties = []
    ): void {
        try {
            $isOwnAction =
                (int) $actor->id === (int) $master->id;

            ActivityLog::make(
                event: $event,
                message: $message,

                module: $isOwnAction
                    ? 'services'
                    : 'employees/services',

                type: $type,
                subject: $service,
                actor: $actor,

                properties: array_merge(
                    [
                        'target_master_id' =>
                            (int) $master->id,

                        'target_master_name' =>
                            $master->name,

                        'service_id' =>
                            (int) $service->id,

                        'is_own_action' =>
                            $isOwnAction,
                    ],
                    $properties
                )
            );
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}