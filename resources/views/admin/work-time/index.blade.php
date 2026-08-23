@section('title', __('admin_work_time.title'))
@push('styles')
<style>
  .work-time-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem}.work-time-metric{border:1px solid var(--phoenix-border-color);border-radius:14px;padding:1rem;background:var(--phoenix-body-bg)}.work-time-metric__value{font-size:1.5rem;font-weight:750;line-height:1.15}.work-time-track{position:relative;width:210px;height:10px;border-radius:999px;background:var(--phoenix-emphasis-bg);overflow:hidden}.work-time-track__shift{position:absolute;top:0;bottom:0;border-radius:999px;background:var(--phoenix-primary)}
  @media(max-width:991.98px){.work-time-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:575.98px){.work-time-summary{grid-template-columns:1fr}.work-time-track{width:150px}}
</style>
@endpush

<x-dashboard-layout>
  <div class="content">
    @php($quickEmployee = $selectedEmployee && request()->routeIs('admin.work-time.index') ? ['employee_id'=>$selectedEmployee->id] : [])
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div><h2 class="mb-1">{{ $selectedEmployee ? __('admin_work_time.employee_report') : __('admin_work_time.title') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_work_time.subtitle') }}</p></div>
      @if($selectedEmployee)<a class="btn btn-outline-secondary" href="{{ route('admin.work-time.index', ['date_from'=>$dateFrom,'date_to'=>$dateTo]) }}"><span data-feather="users" class="me-2"></span>{{ __('admin_work_time.all_employees') }}</a>@endif
    </div>

    <div class="card mb-4"><div class="card-body">
      <div class="d-flex flex-wrap gap-2 mb-3">
        <a class="btn btn-sm btn-phoenix-secondary" href="{{ request()->url().'?'.http_build_query($quickEmployee+['date_from'=>now()->startOfWeek()->toDateString(),'date_to'=>now()->endOfWeek()->toDateString()]) }}">{{ __('admin_work_time.this_week') }}</a>
        <a class="btn btn-sm btn-phoenix-secondary" href="{{ request()->url().'?'.http_build_query($quickEmployee+['date_from'=>now()->startOfMonth()->toDateString(),'date_to'=>now()->endOfMonth()->toDateString()]) }}">{{ __('admin_work_time.this_month') }}</a>
        <a class="btn btn-sm btn-phoenix-secondary" href="{{ request()->url().'?'.http_build_query($quickEmployee+['date_from'=>now()->startOfQuarter()->toDateString(),'date_to'=>now()->endOfQuarter()->toDateString()]) }}">{{ __('admin_work_time.this_quarter') }}</a>
      </div>
      <form class="row g-3 align-items-end" method="GET" action="{{ $selectedEmployee ? route('admin.work-time.employee',$selectedEmployee) : route('admin.work-time.index') }}">
        @unless($selectedEmployee)<div class="col-12 col-lg-4"><label class="form-label" for="employee_id">{{ __('admin_work_time.employee') }}</label><select class="form-select" id="employee_id" name="employee_id"><option value="">{{ __('admin_work_time.all_employees') }}</option>@foreach($allEmployees as $employee)<option value="{{ $employee->id }}" @selected((int)request('employee_id')===$employee->id)>{{ $employee->name }}</option>@endforeach</select></div>@endunless
        <div class="col-6 col-lg-3"><label class="form-label" for="date_from">{{ __('admin_work_time.date_from') }}</label><input class="form-control" id="date_from" type="date" name="date_from" value="{{ $dateFrom }}" required></div>
        <div class="col-6 col-lg-3"><label class="form-label" for="date_to">{{ __('admin_work_time.date_to') }}</label><input class="form-control" id="date_to" type="date" name="date_to" value="{{ $dateTo }}" required></div>
        <div class="col-12 col-lg-2"><button class="btn btn-primary w-100" type="submit">{{ __('admin_work_time.apply') }}</button></div>
      </form>
    </div></div>

    @if($selectedEmployee)
      <div class="card mb-4"><div class="card-body d-flex align-items-center gap-3"><x-ui.avatar :user="$selectedEmployee" :size="58" /><div><h4 class="mb-1">{{ $selectedEmployee->name }}</h4><div class="text-body-tertiary">{{ $selectedEmployee->role?->displayName() }} · {{ $dateFrom }} — {{ $dateTo }}</div></div></div></div>
    @endif

    <div class="work-time-summary mb-4">
      <div class="work-time-metric"><div class="work-time-metric__value">{{ \App\Services\WorkTimeReportService::formatDuration($totals['worked_seconds']) }}</div><small class="text-body-tertiary">{{ __('admin_work_time.total_worked') }} · {{ __('admin_work_time.hours_minutes') }}</small></div>
      <div class="work-time-metric"><div class="work-time-metric__value">{{ \App\Services\WorkTimeReportService::formatDuration($totals['break_seconds']) }}</div><small class="text-body-tertiary">{{ __('admin_work_time.total_breaks') }}</small></div>
      <div class="work-time-metric"><div class="work-time-metric__value">{{ $totals['shift_count'] }}</div><small class="text-body-tertiary">{{ __('admin_work_time.shifts') }}</small></div>
      <div class="work-time-metric"><div class="work-time-metric__value">{{ $totals['employee_count'] }}</div><small class="text-body-tertiary">{{ __('admin_work_time.employees') }}</small></div>
    </div>

    @if($selectedEmployee)
      <div class="card"><div class="card-header"><h5 class="mb-0">{{ __('admin_work_time.daily_graph') }}</h5></div><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th class="ps-3">{{ __('admin_work_time.date') }}</th><th>{{ __('admin_work_time.arrival') }}</th><th>{{ __('admin_work_time.departure') }}</th><th>{{ __('admin_work_time.worked') }}</th><th>{{ __('admin_work_time.breaks') }}</th><th>{{ __('admin_work_time.timeline') }}</th></tr></thead><tbody>
        @forelse($days as $day)<tr><td class="ps-3 text-nowrap fw-semibold">{{ $day['date']->translatedFormat('D, d.m.Y') }}</td><td>{{ $day['first_arrival']?->format('H:i') ?: '—' }}</td><td>@if($day['has_active_shift'])<span class="badge badge-phoenix badge-phoenix-success">{{ __('admin_work_time.active_shift') }}</span>@else{{ $day['last_departure']?->format('H:i') ?: '—' }}@endif</td><td class="fw-semibold">{{ \App\Services\WorkTimeReportService::formatDuration($day['worked_seconds']) }}</td><td>{{ \App\Services\WorkTimeReportService::formatDuration($day['break_seconds']) }}</td><td><div class="work-time-track" title="{{ $day['first_arrival']?->format('H:i') }}–{{ $day['last_departure']?->format('H:i') }}"><span class="work-time-track__shift" style="left:{{ $day['timeline_left'] }}%;width:{{ min($day['timeline_width'],100-$day['timeline_left']) }}%"></span></div></td></tr>
        @empty<tr><td class="text-center text-body-tertiary py-5" colspan="6">{{ __('admin_work_time.no_data') }}</td></tr>@endforelse
      </tbody></table></div></div>
    @else
      <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-3">{{ __('admin_work_time.employee') }}</th><th>{{ __('admin_work_time.first_arrival') }}</th><th>{{ __('admin_work_time.last_departure') }}</th><th>{{ __('admin_work_time.shifts') }}</th><th>{{ __('admin_work_time.total_breaks') }}</th><th>{{ __('admin_work_time.total_worked') }}</th><th>{{ __('admin_work_time.average_shift') }}</th><th></th></tr></thead><tbody>
        @forelse($rows as $row)<tr><td class="ps-3"><div class="d-flex align-items-center gap-2"><x-ui.avatar :user="$row['employee']" :size="36" /><div><div class="fw-semibold">{{ $row['employee']->name }}</div><small class="text-body-tertiary">{{ $row['employee']->role?->displayName() }}</small></div></div></td><td class="text-nowrap">{{ $row['first_arrival']?->format('d.m H:i') ?: '—' }}</td><td class="text-nowrap">@if($row['has_active_shift'])<span class="badge badge-phoenix badge-phoenix-success">{{ __('admin_work_time.active_shift') }}</span>@else{{ $row['last_departure']?->format('d.m H:i') ?: '—' }}@endif</td><td>{{ $row['shift_count'] }}</td><td>{{ \App\Services\WorkTimeReportService::formatDuration($row['break_seconds']) }}</td><td class="fw-semibold">{{ \App\Services\WorkTimeReportService::formatDuration($row['worked_seconds']) }}</td><td>{{ \App\Services\WorkTimeReportService::formatDuration($row['average_seconds']) }}</td><td class="text-end pe-3"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.work-time.employee',[$row['employee'],'date_from'=>$dateFrom,'date_to'=>$dateTo]) }}">{{ __('admin_work_time.details') }}</a></td></tr>
        @empty<tr><td class="text-center text-body-tertiary py-5" colspan="8">{{ __('admin_work_time.no_data') }}</td></tr>@endforelse
      </tbody></table></div></div>
    @endif
    <x-dashboard-footer />
  </div>
</x-dashboard-layout>
