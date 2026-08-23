<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('dashboard.full') === true
            && $this->user()->hasAllAppointmentsScope();
    }

    public function rules(): array
    {
        return [
            'period' => ['sometimes', Rule::in(['current_month', 'last_30_days', 'previous_month', 'custom'])],
            'from' => ['required_if:period,custom', 'nullable', 'date_format:Y-m-d'],
            'to' => ['required_if:period,custom', 'nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    public function messages(): array
    {
        return [
            'to.after_or_equal' => __('admin_dashboard.validation_to_after_from'),
        ];
    }
}
