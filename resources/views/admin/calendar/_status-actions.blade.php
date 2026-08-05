@php
  $statusValue = (is_array($appointment) ? ($appointment['status'] ?? null) : $appointment->status) ?: null;
  $appointmentId = is_array($appointment) ? $appointment['id'] : $appointment->id;
  $startValue = is_array($appointment) ? $appointment['start'] : $appointment->appointment_start;
  $endValue = is_array($appointment) ? $appointment['end'] : $appointment->appointment_end;
  $started = \Carbon\Carbon::parse($startValue)->lte(now());
  $ended = \Carbon\Carbon::parse($endValue)->lte(now());
  $terminal = ['completed','no_show','cancelled_by_client','cancelled_by_business','rescheduled'];
  $options = match($statusValue) {
      'pending' => ['confirmed'],
      'confirmed' => $ended ? ['completed','no_show'] : ($started ? ['checked_in','in_progress'] : []),
      'checked_in' => $ended ? ['in_progress','completed'] : ['in_progress'],
      'in_progress' => $ended ? ['completed'] : [],
      'completed' => ['no_show','confirmed'],
      'no_show' => ['completed','confirmed'],
      'cancelled_by_client','cancelled_by_business','rescheduled' => $ended ? ['completed','no_show'] : [],
      default => [],
  };
  $badge = match($statusValue) {
      'completed' => 'success', 'no_show' => 'danger',
      'cancelled_by_client', 'cancelled_by_business', 'rescheduled' => 'secondary',
      'checked_in', 'in_progress' => 'info', default => 'primary'
  };
@endphp
<div class="d-flex flex-wrap align-items-center justify-content-end gap-1" data-appointment-status-wrap>
  <span class="badge badge-phoenix badge-phoenix-{{ $badge }}">{{ $statusValue ? __('appointment_statuses.'.$statusValue) : 'Статус не указан' }}</span>
  @if($options)
    <select class="form-select form-select-sm appointment-status-select" style="width:auto;max-width:180px" data-id="{{ $appointmentId }}" data-current="{{ $statusValue }}" aria-label="{{ __('appointment_statuses.change_status') }}">
      <option value="" selected>{{ __('appointment_statuses.change') }}</option>
      @foreach($options as $option)<option value="{{ $option }}">{{ $option === 'confirmed' && in_array($statusValue,$terminal,true) ? __('appointment_statuses.restore_confirmed') : __('appointment_statuses.'.$option) }}</option>@endforeach
    </select>
  @endif
</div>
