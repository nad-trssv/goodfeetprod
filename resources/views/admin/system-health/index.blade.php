@section('title', __('admin_system_health.title'))

<x-dashboard-layout>
  @php
    $statusClass = ['operational'=>'success','degraded'=>'warning','outage'=>'danger','stale'=>'warning','unknown'=>'secondary'];
    $checkClass = ['ok'=>'success','warning'=>'warning','failing'=>'danger'];
  @endphp
  <div class="content">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
      <div>
        <h2 class="mb-1">{{ __('admin_system_health.title') }}</h2>
        <p class="text-body-secondary mb-0">{{ __('admin_system_health.description') }}</p>
      </div>
      <form method="POST" action="{{ route('admin.system-health.run') }}">
        @csrf
        <button class="btn btn-primary" type="submit"><span data-feather="refresh-cw" class="me-1"></span>{{ __('admin_system_health.run_now') }}</button>
      </form>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-4 border-{{ $statusClass[$currentStatus] }}">
      <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <div class="text-uppercase fs-9 text-body-tertiary fw-bold">{{ __('admin_system_health.current_state') }}</div>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="rounded-circle bg-{{ $statusClass[$currentStatus] }}" style="width:.8rem;height:.8rem"></span>
            <h3 class="mb-0 text-{{ $statusClass[$currentStatus] }}">{{ __('admin_system_health.status.'.$currentStatus) }}</h3>
          </div>
        </div>
        <div class="text-md-end text-body-secondary">
          @if($latest)
            <div>{{ __('admin_system_health.last_check') }}: <strong>{{ $latest->started_at->translatedFormat('d M Y H:i:s') }}</strong></div>
            <div>{{ __('admin_system_health.total_duration') }}: {{ $latest->duration_ms }} {{ __('admin_system_health.ms') }}</div>
          @else
            {{ __('admin_system_health.no_checks') }}
          @endif
        </div>
      </div>
    </div>

    @if($latest)
      <div class="row g-3 mb-4">
        @foreach($latest->checks as $check)
          <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between gap-3 mb-2">
                  <h5 class="mb-0">{{ __('admin_system_health.checks.'.$check->key) }}</h5>
                  <span class="badge badge-phoenix badge-phoenix-{{ $checkClass[$check->status] ?? 'secondary' }}">{{ __('admin_system_health.check_status.'.$check->status) }}</span>
                </div>
                <p class="text-body-secondary mb-2">{{ __($check->message_key, $check->context ?? []) }}</p>
                <small class="text-body-tertiary">{{ $check->duration_ms }} {{ __('admin_system_health.ms') }}</small>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <div class="card mb-4">
      <div class="card-header"><h4 class="mb-1">{{ __('admin_system_health.daily_history') }}</h4><p class="text-body-secondary mb-0">{{ __('admin_system_health.daily_hint', ['days'=>$days]) }}</p></div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>{{ __('admin_system_health.date') }}</th><th>{{ __('admin_system_health.state') }}</th><th>{{ __('admin_system_health.availability') }}</th><th>{{ __('admin_system_health.check_count') }}</th><th>{{ __('admin_system_health.incidents') }}</th><th>{{ __('admin_system_health.average_duration') }}</th><th></th></tr></thead>
          <tbody>
            @forelse($daily as $day)
              <tr>
                <td class="fw-semibold">{{ $day['date']->translatedFormat('d M Y') }}</td>
                <td><span class="badge bg-{{ $statusClass[$day['status']] }}">{{ __('admin_system_health.status.'.$day['status']) }}</span></td>
                <td>{{ number_format($day['availability'], 2) }}%</td>
                <td>{{ $day['runs'] }}</td>
                <td>{{ $day['failed_checks'] }} / {{ $day['warnings'] }}</td>
                <td>{{ $day['average_duration_ms'] }} {{ __('admin_system_health.ms') }}</td>
                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.system-health.index', ['date'=>$day['date']->toDateString()]) }}">{{ __('admin_system_health.details') }}</a></td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center py-5 text-body-secondary">{{ __('admin_system_health.no_history') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if($selectedDate)
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center gap-3"><h4 class="mb-0">{{ __('admin_system_health.day_details', ['date'=>$selectedDate]) }}</h4><a href="{{ route('admin.system-health.index') }}">{{ __('admin_system_health.close_details') }}</a></div>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead><tr><th>{{ __('admin_system_health.time') }}</th><th>{{ __('admin_system_health.state') }}</th><th>{{ __('admin_system_health.source') }}</th><th>{{ __('admin_system_health.components') }}</th><th>{{ __('admin_system_health.total_duration') }}</th></tr></thead>
            <tbody>
              @forelse($details as $run)
                <tr>
                  <td>{{ $run->started_at->format('H:i:s') }}</td>
                  <td><span class="badge bg-{{ $statusClass[$run->status] }}">{{ __('admin_system_health.status.'.$run->status) }}</span></td>
                  <td>{{ __('admin_system_health.sources.'.$run->source) }}</td>
                  <td>
                    <div class="d-flex flex-wrap gap-1">
                      @foreach($run->checks as $check)<span class="badge badge-phoenix badge-phoenix-{{ $checkClass[$check->status] ?? 'secondary' }}" title="{{ __($check->message_key, $check->context ?? []) }}">{{ __('admin_system_health.checks.'.$check->key) }}</span>@endforeach
                    </div>
                  </td>
                  <td>{{ $run->duration_ms }} {{ __('admin_system_health.ms') }}</td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center py-4 text-body-secondary">{{ __('admin_system_health.no_history') }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    @endif
  </div>
</x-dashboard-layout>
