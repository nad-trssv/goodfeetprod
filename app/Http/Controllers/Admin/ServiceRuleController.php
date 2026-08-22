<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Models\ServiceRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceRuleController extends Controller
{
    /**
     * Страница с правилами для услуги:
     * список + форма добавления нового правила.
     */
    public function edit(Services $service)
    {
        $rules = $service->rules()
            ->orderBy('valid_from')
            ->get();

        return view('admin.service.update.rules', [
            'service' => $service,
            'rules'   => $rules,
        ]);
    }

    /**
     * Сохранение нового правила (из формы в админке).
     */
    public function store(Request $request, Services $service)
    {
        $data = $request->validate([
            'valid_from'           => ['required', 'date'],
            'valid_to'             => ['nullable', 'date', 'after_or_equal:valid_from'],
            'price'                => ['nullable', 'numeric', 'min:0'],
            'duration_minutes'     => ['nullable', 'integer', 'min:1'],
            'duration_minutes_min' => ['nullable', 'integer', 'min:1'],
            'time_from'            => ['nullable', 'date_format:H:i'],
            'time_to'              => ['nullable', 'date_format:H:i'],
            'has_fixed_time'       => ['nullable', 'boolean'],
        ]);

        try {
            DB::transaction(function () use ($service, $data) {
                $data['service_id'] = $service->id;
                $data['has_fixed_time'] = !empty($data['has_fixed_time']);

                ServiceRule::create($data);
            });

            return redirect()
                ->route('service.rules.edit', $service->id)
                ->with('success', __('admin_messages.rule_saved'));
        } catch (\Throwable $e) {
            Log::error('Ошибка при сохранении правила услуги', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('service.rules.edit', $service->id)
                ->with('error', __('admin_messages.rule_save_failed'));
        }
    }

    /**
     * Удаление правила.
     */
    public function destroy(ServiceRule $rule)
    {
        $serviceId = $rule->service_id;

        try {
            $rule->delete();

            return redirect()
                ->route('service.rules.edit', $serviceId)
                ->with('success', __('admin_messages.rule_deleted'));
        } catch (\Throwable $e) {
            Log::error('Ошибка при удалении правила услуги', [
                'rule_id' => $rule->id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()
                ->route('service.rules.edit', $serviceId)
                ->with('error', __('admin_messages.rule_delete_failed'));
        }
    }
}
