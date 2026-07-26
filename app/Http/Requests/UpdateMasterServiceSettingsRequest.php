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
                'Укажите индивидуальную цену.',

            'price_override.numeric' =>
                'Цена должна быть числом.',

            'price_override.min' =>
                'Цена не может быть отрицательной.',

            'duration_minutes_override.required_if' =>
                'Укажите индивидуальную продолжительность.',

            'duration_minutes_override.min' =>
                'Продолжительность должна быть не меньше одной минуты.',

            'duration_minutes_min_override.required_if' =>
                'Укажите индивидуальную минимальную продолжительность.',

            'duration_minutes_min_override.min' =>
                'Минимальная продолжительность должна быть не меньше одной минуты.',

            'buffer_before_minutes.required' =>
                'Укажите техническое время до услуги.',

            'buffer_before_minutes.min' =>
                'Техническое время до услуги не может быть отрицательным.',

            'buffer_after_minutes.required' =>
                'Укажите техническое время после услуги.',

            'buffer_after_minutes.min' =>
                'Техническое время после услуги не может быть отрицательным.',
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
                    'Выбранная услуга не совпадает с отправленной формой.',
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
                    'Минимальная продолжительность не может быть больше основной продолжительности.',
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