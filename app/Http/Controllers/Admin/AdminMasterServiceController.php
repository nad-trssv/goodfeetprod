<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMasterServiceSettingsRequest;
use App\Models\Services;
use App\Models\User;
use App\Services\MasterServiceManager;
use App\Services\MasterServiceCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminMasterServiceController extends Controller
{
    private const MASTER_ROLE_ID = 2;

    public function __construct(
        private readonly MasterServiceManager $manager,
        private readonly MasterServiceCatalog $catalog,
    ) {
    }

    public function index(Request $request): View
    {
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

        $masters = User::query()
            ->where(
                'role_id',
                self::MASTER_ROLE_ID
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'username',
            ]);

        $selectedMasterId = (int) $request->query(
            'master_id',
            0
        );

        $master = null;
        $allServices = collect();
        $masterServices = collect();

        if ($selectedMasterId > 0) {
            $master = $masters->firstWhere(
                'id',
                $selectedMasterId
            );

            abort_unless(
                $master instanceof User,
                404,
                'Мастер не найден.'
            );

            $catalog = $this->catalog->get($master, $search, $filter);
            $allServices = $catalog['services'];
            $masterServices = $catalog['settings'];
            $filter = $catalog['filter'];
        }

        return view('admin.master-services.index', [
            'masters' => $masters,
            'master' => $master,
            'allServices' => $allServices,
            'masterServices' => $masterServices,
            'selectedMasterId' => $selectedMasterId,
            'search' => $search,
            'filter' => $filter,
        ]);
    }

    public function toggle(
        Request $request,
        int $masterId,
        int $serviceId
    ): RedirectResponse {
        $master = $this->masterOrFail($masterId);
        $service = $this->serviceOrFail($serviceId);
        $actor = $this->authenticatedUser($request);

        try {
            $isActive = $this->manager->toggle(
                master: $master,
                service: $service,
                actor: $actor
            );
        } catch (Throwable) {
            return back()->with(
                'error',
                'Не удалось изменить состояние услуги мастера.'
            );
        }

        return back()->with(
            'success',
            $isActive
                ? 'Услуга «'
                    . $service->name
                    . '» подключена мастеру «'
                    . $master->name
                    . '».'
                : 'Услуга «'
                    . $service->name
                    . '» отключена у мастера «'
                    . $master->name
                    . '».'
        );
    }

    public function update(
        UpdateMasterServiceSettingsRequest $request,
        int $masterId,
        int $serviceId
    ): RedirectResponse {
        $master = $this->masterOrFail($masterId);
        $service = $this->serviceOrFail($serviceId);
        $actor = $this->authenticatedUser($request);

        $attributes = $request->settingsAttributes(
            $service
        );

        try {
            $changed = $this->manager->updateSettings(
                master: $master,
                service: $service,
                actor: $actor,
                attributes: $attributes
            );
        } catch (Throwable) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Не удалось сохранить настройки услуги мастера.'
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
                . '» для мастера «'
                . $master->name
                . '» сохранены.'
        );
    }

    private function masterOrFail(
        int $masterId
    ): User {
        $master = User::query()
            ->where('id', $masterId)
            ->where(
                'role_id',
                self::MASTER_ROLE_ID
            )
            ->get()
            ->first();

        abort_unless(
            $master instanceof User,
            404,
            'Мастер не найден.'
        );

        return $master;
    }

    private function serviceOrFail(
        int $serviceId
    ): Services {
        $service = Services::query()
            ->where('id', $serviceId)
            ->where('status', true)
            ->where('is_deleted', false)
            ->get()
            ->first();

        abort_unless(
            $service instanceof Services,
            404,
            'Услуга не найдена.'
        );

        return $service;
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
