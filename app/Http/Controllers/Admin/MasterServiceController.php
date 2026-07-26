<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMasterServiceSettingsRequest;
use App\Models\Services;
use App\Models\User;
use App\Models\UserServices;
use App\Services\MasterServiceManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class MasterServiceController extends Controller
{
    public function __construct(
        private readonly MasterServiceManager $manager
    ) {
    }

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

    public function toggle(
        Request $request,
        Services $service
    ): RedirectResponse {
        $this->ensureServiceCanBeConfigured($service);

        $user = $this->authenticatedUser($request);

        try {
            $isActive = $this->manager->toggle(
                master: $user,
                service: $service,
                actor: $user
            );
        } catch (Throwable) {
            return back()->with(
                'error',
                'Не удалось подключить или отключить услугу.'
            );
        }

        return back()->with(
            'success',
            $isActive
                ? 'Услуга «'
                    . $service->name
                    . '» подключена.'
                : 'Услуга «'
                    . $service->name
                    . '» отключена.'
        );
    }

    public function update(
        UpdateMasterServiceSettingsRequest $request,
        Services $service
    ): RedirectResponse {
        $this->ensureServiceCanBeConfigured($service);

        $user = $this->authenticatedUser($request);

        /*
         * Ошибки валидации не записываются как системные.
         */
        $attributes = $request->settingsAttributes(
            $service
        );

        try {
            $changed = $this->manager->updateSettings(
                master: $user,
                service: $service,
                actor: $user,
                attributes: $attributes
            );
        } catch (Throwable) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Не удалось сохранить настройки услуги.'
                );
        }

        if (!$changed) {
            return back()->with(
                'success',
                'Настройки услуги «'
                    . $service->name
                    . '» не изменились.'
            );
        }

        return back()->with(
            'success',
            'Настройки услуги «'
                . $service->name
                . '» сохранены.'
        );
    }

    private function ensureServiceCanBeConfigured(
        Services $service
    ): void {
        abort_if(
            !$service->status || $service->is_deleted,
            404
        );
    }

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