@props(['status' => null])
@php
    $tone = match($status) {
        'completed' => 'success', 'no_show' => 'danger',
        'cancelled_by_client', 'cancelled_by_business', 'rescheduled' => 'secondary',
        'pending' => 'warning', 'checked_in', 'in_progress' => 'info', default => 'primary',
    };
    $key = $status ? 'appointment_statuses.'.$status : null;
    $label = !$status ? 'Статус не указан' : (\Illuminate\Support\Facades\Lang::has($key) ? __($key) : $status);
@endphp
<span {{ $attributes->class(['badge','badge-phoenix','badge-phoenix-'.$tone]) }}>{{ $label }}</span>
