<?php

namespace App\Http\Requests;

use App\Models\Services;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateMasterServiceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        /*
         * Доступ проверяется middleware маршрута:
         *
         * - admin;
         * - super-admin для административной страницы.
         */
        return true;
    }

    public function rules(): array
    {
        return [
            'service_form_id' => [
                'required',
                'integer',
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
        ];
    }

    public function messages(): array
    {
        return [
            'price_override.required_if' =>
                __('admin_validation.master_service.price_required'),

            'price_override.numeric' =>
                __('admin_validation.master_service.price_numeric'),

            'price_override.min' =>
                __('admin_validation.master_service.price_min'),

            'duration_minutes_override.required_if' =>
                __('admin_validation.master_service.duration_required'),

            'duration_minutes_override.min' =>
                __('admin_validation.master_service.duration_min'),

            'duration_minutes_min_override.required_if' =>
                __('admin_validation.master_service.minimum_required'),

            'duration_minutes_min_override.min' =>
                __('admin_validation.master_service.minimum_min'),

            'buffer_before_minutes.required' =>
                __('admin_validation.master_service.buffer_before_required'),

            'buffer_before_minutes.min' =>
                __('admin_validation.master_service.buffer_before_min'),

            'buffer_after_minutes.required' =>
                __('admin_validation.master_service.buffer_after_required'),

            'buffer_after_minutes.min' =>
                __('admin_validation.master_service.buffer_after_min'),
        ];
    }

    /**
     * Подготовить значения для модели UserServices.
     */
    public function settingsAttributes(
        Services $service
    ): array {
        $validated = $this->validated();

        if (
            (int) $validated['service_form_id']
            !== (int) $service->id
        ) {
            throw ValidationException::withMessages([
                'service_form_id' =>
                    __('admin_validation.master_service.service_mismatch'),
            ]);
        }

        $useDefaultPrice = $this->boolean(
            'use_default_price'
        );

        $useDefaultDuration = $this->boolean(
            'use_default_duration'
        );

        $useDefaultMinDuration = $this->boolean(
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
                    __('admin_validation.master_service.minimum_exceeds_duration'),
            ]);
        }

        return [
            'price_override' => $useDefaultPrice
                ? null
                : (string) $validated['price_override'],

            'duration_minutes_override' =>
                $durationOverride,

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
        ];
    }
}
