@section('title', __('admin_appointments.today_title'))
<x-dashboard-layout>
<div class="content">
  <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div><h2 class="mb-1">{{ __('admin_appointments.today_title') }}</h2><p class="text-body-tertiary mb-0">{{ now()->translatedFormat('l, d F Y') }}</p></div>
    <div class="d-flex gap-2"><a class="btn btn-outline-secondary btn-sm" href="{{ route('calendar.index') }}">{{ __('admin_staff.calendar') }}</a><a class="btn btn-primary btn-sm" href="{{ route('calendar.create') }}">{{ __('admin_appointments.new') }}</a></div>
  </div>
  @if($unresolvedCount)
    <div class="alert alert-warning d-flex align-items-center gap-3"><span class="fas fa-exclamation-triangle fs-6"></span><div><strong>{{ __('admin_appointments.result_needed') }}</strong> {{ __('admin_appointments.unresolved_count',['count'=>$unresolvedCount]) }}</div></div>
  @endif
  <div class="d-flex flex-wrap gap-3 mb-4 fs-9">
    <span><i class="d-inline-block rounded-circle bg-success me-1" style="width:9px;height:9px"></i>{{ __('admin_appointments.past') }}</span><span><i class="d-inline-block rounded-circle bg-primary me-1" style="width:9px;height:9px"></i>{{ __('admin_appointments.in_progress') }}</span><span><i class="d-inline-block rounded-circle bg-secondary me-1" style="width:9px;height:9px"></i>{{ __('admin_appointments.future') }}</span><span><i class="d-inline-block rounded-circle bg-warning me-1" style="width:9px;height:9px"></i>{{ __('admin_appointments.status_needed') }}</span>
  </div>
  <div class="card"><div class="card-body p-3 p-md-4">
  @forelse($appointments as $appointment)
    @php
      $inactive=in_array($appointment->status,['cancelled_by_client','cancelled_by_business','rescheduled'],true);
      $needsResult=!$inactive && $appointment->appointment_end->isPast() && in_array($appointment->status,['pending','confirmed','checked_in','in_progress'],true);
      $phase=$inactive?'inactive':($needsResult?'needs-result':($appointment->appointment_start->isFuture()?'future':($appointment->appointment_end->isFuture()?'current':'past')));
      $accent=match($phase){'current'=>'primary','needs-result'=>'warning','past'=>'success',default=>'secondary'};
      $phaseLabel=$inactive ? __('appointment_statuses.'.$appointment->status) : match($phase){'current'=>__('admin_common.phases.current'),'future'=>__('admin_common.phases.future'),'needs-result'=>__('admin_common.phases.needs-result'),'past'=>__('admin_common.phases.past'),default=>__('appointment_statuses.'.$appointment->status)};
    @endphp
    <article class="border rounded-3 p-3 p-lg-4 mb-3 {{ $needsResult?'border-warning bg-warning-subtle':($phase==='current'?'border-primary bg-primary-subtle':'') }}">
      <div class="row g-3 align-items-center">
        <div class="col-5 col-sm-2 col-xl-1"><div class="fs-7 fw-bold text-{{ $accent }}">{{ $appointment->appointment_start->format('H:i') }}</div><div class="text-body-tertiary fs-9">{{ __('admin_appointments.until',['time'=>$appointment->appointment_end->format('H:i')]) }}</div></div>
        <div class="col-7 col-sm-10 col-lg-4"><div class="d-flex gap-2"><span class="mt-1 rounded-circle bg-{{ $accent }} flex-shrink-0" style="width:10px;height:10px"></span><div class="min-w-0"><h5 class="mb-1 text-break">{{ $appointment->service->name }}</h5><div class="text-body-tertiary fs-9">{{ __('admin_appointments.minutes',['count'=>$appointment->service->duration_minutes]) }} · {{ number_format((float)$appointment->price,2,',',' ') }} €</div></div></div></div>
        <div class="col-12 col-lg-3"><div class="fw-semibold">{{ trim($appointment->client_name.' '.$appointment->client_lastname) }}</div><div class="d-flex flex-wrap gap-2 fs-9">@if($appointment->client_phone)<a href="tel:{{ $appointment->client_phone }}">{{ $appointment->client_phone }}</a>@endif @if($appointment->room)<span class="text-body-tertiary">{{ $appointment->room->name }}</span>@endif</div></div>
        <div class="col-12 col-sm-4 col-lg-2"><span class="badge badge-phoenix badge-phoenix-{{ $accent }}">{{ $phaseLabel }}</span></div>
        <div class="col-12 col-sm-8 col-lg-2"><div class="d-flex flex-wrap justify-content-sm-end gap-2">@include('admin.calendar._status-actions',['appointment'=>$appointment])<a class="btn btn-sm btn-outline-secondary" href="{{ route('calendar.show',$appointment) }}">{{ __('admin_appointments.details') }}</a></div></div>
      </div>
      @if($needsResult)<div class="mt-3 pt-3 border-top border-warning fw-semibold text-warning-emphasis">{{ __('admin_appointments.result_hint',['time'=>$appointment->appointment_end->format('H:i')]) }}</div>@endif
    </article>
  @empty
    <div class="text-center py-7"><span class="far fa-calendar-check fs-3 text-body-tertiary"></span><h4 class="mt-3">{{ __('admin_appointments.none_today') }}</h4><p class="text-body-tertiary mb-0">{{ __('admin_appointments.none_today_hint') }}</p></div>
  @endforelse
  </div></div>
  <x-dashboard-footer />
</div>
@push('scripts')@include('admin.calendar._status-script')@endpush
</x-dashboard-layout>
