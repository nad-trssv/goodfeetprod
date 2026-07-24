<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Models\User;
use App\Models\UserServices;
use App\Support\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class MasterServiceController extends Controller
{
    /**
     * Поля индивидуальных настроек услуги,
     * изменения которых отслеживаются в журнале.
     */
    private const TRACKED_FIELDS = [
        'price_override',
        'duration_minutes_override',
        'duration_minutes_min_override',
        'buffer_before_minutes',
        'buffer_after_minutes',
    ];

    /**
     * Показать список услуг текущего мастера.
     */
    public function index(Request $request): View
    {
        $user = $this->authenticatedUser($request);

        $search = trim(
            (string) $request->query('search', '')
        );

        $filter = (string) $request->query(
            'filter',
            'all'
        );

        if (!in_array($filter, ['all', 'active'], true)) {
            $filter = 'all';
        }

        $allServices = Services::query()
            ->where('status', true)
            ->where('is_deleted', false)

            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(
                        'name',
                        'like',
                        '%' . $search . '%'
                    );
                }
            )

            ->when(
                $filter === 'active',
                function ($query) use ($user): void {
                    $query->whereExists(
                        function ($subQuery) use ($user): void {
                            $subQuery
                                ->selectRaw('1')
                                ->from('user_services')
                                ->whereColumn(
                                    'user_services.service_id',
                                    'services.id'
                                )
                                ->where(
                                    'user_services.user_id',
                                    $user->id
                                )
                                ->where(
                                    'user_services.is_active',
                                    true
                                );
                        }
                    );
                }
            )

            ->orderBy('name')
            ->get();

        /*
         * Загружаем активные и отключённые связи.
         *
         * Отключённая услуга остаётся в user_services,
         * поэтому её индивидуальные настройки не теряются.
         */
        $masterServices = UserServices::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('service_id');

        return view('admin.master.services', [
            'allServices' => $allServices,
            'masterServices' => $masterServices,
            'search' => $search,
            'filter' => $filter,
        ]);
    }

    /**
     * Подключить или отключить услугу текущего мастера.
     */
    public function toggle(
        Request $request,
        Services $service
    ): RedirectResponse {
        $this->ensureServiceCanBeConfigured($service);

        $user = $this->authenticatedUser($request);

        try {
            $result = DB::transaction(
                function () use ($user, $service): array {
                    $masterService = $this->findOrCreateMasterService(
                        userId: $user->id,
                        serviceId: $service->id
                    );

                    $before = [
                        'is_active' => (bool) $masterService->is_active,
                    ];

                    $masterService->is_active =
                        !$masterService->is_active;

                    $masterService->save();

                    $isActive = (bool) $masterService->is_active;

                    return [
                        'is_active' => $isActive,
                        'user_service_id' => $masterService->id,
                        'before' => $before,
                        'after' => [
                            'is_active' => $isActive,
                        ],
                    ];
                }
            );
        } catch (Throwable $exception) {
            /*
             * Полная техническая ошибка попадёт
             * в стандартный журнал Laravel.
             */
            report($exception);

            /*
             * Журнал действий записываем уже после отката
             * основной транзакции.
             *
             * Если сама запись журнала не сработает,
             * она не должна скрыть первоначальную ошибку.
             */
            $this->writeActivityLogSafely(
                event: 'master_service.toggle_failed',
                message: 'Не удалось изменить состояние услуги',
                type: ActivityLog::TYPE_ERROR,
                service: $service,
                actor: $user,
                properties: [
                    'target_master_id' => $user->id,
                    'target_master_name' => $user->name,
                    'exception_class' => $exception::class,
                    'error' => $exception->getMessage(),
                ]
            );

            return back()->with(
                'error',
                'Не удалось подключить или отключить услугу.'
            );
        }

        /*
         * Успешный лог создаём после завершения транзакции.
         *
         * Ошибка журнала не должна откатывать успешно
         * изменённую услугу.
         */
        $this->writeActivityLogSafely(
            event: $result['is_active']
                ? 'master_service.enabled'
                : 'master_service.disabled',

            message: $result['is_active']
                ? 'Услуга подключена'
                : 'Услуга отключена',

            type: ActivityLog::TYPE_INFO,
            service: $service,
            actor: $user,

            properties: [
                'target_master_id' => $user->id,
                'target_master_name' => $user->name,
                'user_service_id' => $result['user_service_id'],
                'before' => $result['before'],
                'after' => $result['after'],
            ]
        );

        return back()->with(
            'success',
            $result['is_active']
                ? 'Услуга «' . $service->name . '» подключена.'
                : 'Услуга «' . $service->name . '» отключена.'
        );
    }

    /**
     * Обновить индивидуальные настройки услуги мастера.
     */
    public function update(
        Request $request,
        Services $service
    ): RedirectResponse {
        $this->ensureServiceCanBeConfigured($service);

        $user = $this->authenticatedUser($request);

        /*
         * Ошибки обычной валидации формы не записываем
         * в журнал действий.
         *
         * Это пользовательские ошибки ввода, а не ошибки системы.
         */
        $settings = $this->validateSettings(
            request: $request,
            service: $service
        );

        try {
            $result = DB::transaction(
                function () use (
                    $user,
                    $service,
                    $settings
                ): array {
                    $masterService = $this->findOrCreateMasterService(
                        userId: $user->id,
                        serviceId: $service->id
                    );

                    $before = $masterService->only(
                        self::TRACKED_FIELDS
                    );

                    $masterService->fill(
                        $settings['attributes']
                    );

                    $hasChanges = $masterService->isDirty(
                        self::TRACKED_FIELDS
                    );

                    /*
                     * Если значения не изменились,
                     * не выполняем UPDATE и не создаём лог.
                     */
                    if (!$hasChanges) {
                        return [
                            'changed' => false,
                            'user_service_id' => $masterService->id,
                            'before' => $before,
                            'after' => $before,
                            'effective' => $this->effectiveSettings(
                                masterService: $masterService,
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
                        'user_service_id' => $masterService->id,
                        'before' => $before,
                        'after' => $after,
                        'effective' => $this->effectiveSettings(
                            masterService: $masterService,
                            service: $service
                        ),
                    ];
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->writeActivityLogSafely(
                event: 'master_service.settings_update_failed',
                message:
                    'Не удалось изменить индивидуальные настройки услуги',
                type: ActivityLog::TYPE_ERROR,
                service: $service,
                actor: $user,
                properties: [
                    'target_master_id' => $user->id,
                    'target_master_name' => $user->name,
                    'exception_class' => $exception::class,
                    'error' => $exception->getMessage(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Не удалось сохранить настройки услуги.'
                );
        }

        if (!$result['changed']) {
            return back()->with(
                'success',
                'Настройки услуги «'
                    . $service->name
                    . '» не изменились.'
            );
        }

        $this->writeActivityLogSafely(
            event: 'master_service.settings_updated',
            message:
                'Индивидуальные настройки услуги изменены',
            type: ActivityLog::TYPE_INFO,
            service: $service,
            actor: $user,
            properties: [
                'target_master_id' => $user->id,
                'target_master_name' => $user->name,
                'user_service_id' => $result['user_service_id'],
                'before' => $result['before'],
                'after' => $result['after'],
                'effective' => $result['effective'],
            ]
        );

        return back()->with(
            'success',
            'Настройки услуги «'
                . $service->name
                . '» сохранены.'
        );
    }

    /**
     * Проверить и подготовить настройки из формы.
     *
     * @return array{
     *     attributes: array{
     *         price_override: float|null,
     *         duration_minutes_override: int|null,
     *         duration_minutes_min_override: int|null,
     *         buffer_before_minutes: int,
     *         buffer_after_minutes: int
     *     }
     * }
     */
    private function validateSettings(
        Request $request,
        Services $service
    ): array {
        $validated = $request->validate([
            'service_form_id' => [
                'required',
                'integer',
                'in:' . $service->id,
            ],

            'use_default_price' => [
                'required',
                'boolean',
            ],

            'price_override' => [
                'required_if:use_default_price,0',
                'nullable',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],

            'use_default_duration' => [
                'required',
                'boolean',
            ],

            'duration_minutes_override' => [
                'required_if:use_default_duration,0',
                'nullable',
                'integer',
                'min:1',
                'max:1440',
            ],

            'use_default_min_duration' => [
                'required',
                'boolean',
            ],

            'duration_minutes_min_override' => [
                'required_if:use_default_min_duration,0',
                'nullable',
                'integer',
                'min:1',
                'max:1440',
            ],

            'buffer_before_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:1440',
            ],

            'buffer_after_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:1440',
            ],
        ]);

        $useDefaultPrice = $request->boolean(
            'use_default_price'
        );

        $useDefaultDuration = $request->boolean(
            'use_default_duration'
        );

        $useDefaultMinDuration = $request->boolean(
            'use_default_min_duration'
        );

        $durationOverride = $useDefaultDuration
            ? null
            : (int) $validated[
                'duration_minutes_override'
            ];

        $minimumDurationOverride = $useDefaultMinDuration
            ? null
            : (int) $validated[
                'duration_minutes_min_override'
            ];

        $effectiveDuration = $durationOverride
            ?? (int) $service->duration_minutes;

        $effectiveMinimumDuration =
            $minimumDurationOverride
            ?? (
                $service->duration_minutes_min !== null
                    ? (int) $service->duration_minutes_min
                    : null
            );

        if (
            $effectiveMinimumDuration !== null
            && $effectiveMinimumDuration > $effectiveDuration
        ) {
            throw ValidationException::withMessages([
                'duration_minutes_min_override' =>
                    'Минимальная продолжительность не может быть больше основной продолжительности.',
            ]);
        }

        return [
            'attributes' => [
                /*
                 * null означает использование общей цены услуги.
                 */
                'price_override' => $useDefaultPrice
                    ? null
                    : (float) $validated['price_override'],

                /*
                 * null означает использование общей
                 * продолжительности услуги.
                 */
                'duration_minutes_override' =>
                    $durationOverride,

                /*
                 * null означает использование общего
                 * минимального времени услуги.
                 */
                'duration_minutes_min_override' =>
                    $minimumDurationOverride,

                'buffer_before_minutes' =>
                    (int) $validated[
                        'buffer_before_minutes'
                    ],

                'buffer_after_minutes' =>
                    (int) $validated[
                        'buffer_after_minutes'
                    ],
            ],
        ];
    }

    /**
     * Получить итоговые параметры услуги после применения
     * общих и индивидуальных значений.
     *
     * Этот метод пока нужен только для журнала.
     * Когда итоговые настройки начнёт использовать запись
     * клиентов, его нужно будет вынести в отдельный resolver.
     *
     * @return array{
     *     price: float,
     *     duration_minutes: int,
     *     duration_minutes_min: int|null,
     *     buffer_before_minutes: int,
     *     buffer_after_minutes: int
     * }
     */
    private function effectiveSettings(
        UserServices $masterService,
        Services $service
    ): array {
        return [
            'price' =>
                $masterService->price_override !== null
                    ? (float) $masterService->price_override
                    : (float) $service->price,

            'duration_minutes' =>
                $masterService->duration_minutes_override !== null
                    ? (int) $masterService
                        ->duration_minutes_override
                    : (int) $service->duration_minutes,

            'duration_minutes_min' =>
                $masterService->duration_minutes_min_override
                    !== null
                    ? (int) $masterService
                        ->duration_minutes_min_override
                    : (
                        $service->duration_minutes_min !== null
                            ? (int) $service
                                ->duration_minutes_min
                            : null
                    ),

            'buffer_before_minutes' =>
                (int) $masterService->buffer_before_minutes,

            'buffer_after_minutes' =>
                (int) $masterService->buffer_after_minutes,
        ];
    }

    /**
     * Найти связь мастера с услугой или создать её.
     *
     * Метод должен вызываться внутри DB::transaction().
     */
    private function findOrCreateMasterService(
        int $userId,
        int $serviceId
    ): UserServices {
        $now = now();

        /*
         * Требуется уникальный индекс:
         *
         * UNIQUE (user_id, service_id)
         *
         * insertOrIgnore защищает от одновременного
         * создания одинаковой связи.
         */
        DB::table('user_services')->insertOrIgnore([
            'user_id' => $userId,
            'service_id' => $serviceId,
            'is_active' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /*
         * Блокируем строку до окончания транзакции.
         * Это защищает переключение услуги от двух
         * одновременных запросов.
         *
         * Используем get()->first(), чтобы не добавлять
         * SQL LIMIT 1.
         */
        $masterService = UserServices::query()
            ->where('user_id', $userId)
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

    /**
     * Записать событие в журнал действий.
     *
     * Ошибка журнала не должна ломать основное действие.
     */
    private function writeActivityLogSafely(
        string $event,
        string $message,
        int $type,
        Services $service,
        User $actor,
        array $properties = []
    ): void {
        try {
            ActivityLog::make(
                event: $event,
                message: $message,
                module: 'services',
                type: $type,
                subject: $service,
                actor: $actor,
                properties: $properties
            );
        } catch (Throwable $exception) {
            /*
             * Например, таблица activity_logs недоступна.
             * Основное действие при этом не откатываем.
             */
            report($exception);
        }
    }

    /**
     * Проверить, что услугу разрешено настраивать.
     */
    private function ensureServiceCanBeConfigured(
        Services $service
    ): void {
        abort_if(
            !$service->status || $service->is_deleted,
            404
        );
    }

    /**
     * Получить авторизованного пользователя.
     */
    private function authenticatedUser(
        Request $request
    ): User {
        $user = $request->user();

        abort_unless(
            $user instanceof User,
            401
        );

        return $user;
    }
}