@section('title', __('admin_dashboard.title'))
<x-dashboard-layout>
  <div class="content pt-8">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
      <div>
        <h2 class="mb-1">{{ __('admin_dashboard.operational_title') }}</h2>
        <p class="text-body-tertiary mb-0">{{ __('admin_dashboard.today_clients', ['count' => $today['total']]) }}</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        @can('dashboard.full')
          <a class="btn btn-phoenix-primary" href="{{ route('admin.dashboard.full') }}"><span data-feather="bar-chart-2" class="me-2"></span>{{ __('admin_dashboard.open_full') }}</a>
        @endcan
        @can('appointments.create')
          <a class="btn btn-primary" href="{{ route('calendar.create', ['date' => today()->toDateString()]) }}"><span data-feather="plus" class="me-2"></span>{{ __('admin_nav.new_appointment') }}</a>
        @endcan
      </div>
    </div>

    @if($action_required > 0)
      <div class="alert alert-subtle-warning d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <span><span data-feather="alert-triangle" class="me-2"></span>{{ __('admin_dashboard.action_required', ['count' => $action_required]) }}</span>
        <a class="btn btn-sm btn-warning" href="{{ route('dashboard.appointments.unresolved', ['scope'=>'own']) }}">{{ __('admin_dashboard.resolve_statuses') }}</a>
      </div>
    @endif

    <h4 class="mb-3">{{ __('admin_dashboard.today_section') }}</h4>
    <div class="row g-3 mb-5">
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.appointments')" :value="$today['total']" icon="calendar" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.completed')" :value="$today['completed']" icon="check-circle" tone="success" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.today_revenue')" :value="number_format($today['revenue'], 2, ',', ' ').' €'" icon="credit-card" tone="success" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.no_show_loss')" :value="number_format($today['lost_revenue'], 2, ',', ' ').' €'" icon="user-x" tone="danger" /></div>
    </div>

    <div class="row g-3 mb-5">
      <div class="col-12 col-xl-8">
        <div class="card h-100">
          <div class="card-header d-flex align-items-center justify-content-between gap-2">
            <h4 class="mb-0">{{ __('admin_dashboard.today_schedule') }}</h4>
            <a class="btn btn-sm btn-phoenix-secondary" href="{{ route('appointments.today') }}">{{ __('admin_dashboard.open_today') }}</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead><tr><th class="ps-3">{{ __('admin_dashboard.time') }}</th><th>{{ __('admin_dashboard.client') }}</th><th>{{ __('admin_dashboard.service') }}</th><th>{{ __('admin_dashboard.employee') }}</th><th>{{ __('appointment_statuses.status') }}</th><th></th></tr></thead>
              <tbody>
                @forelse($appointments as $appointment)
                  <tr>
                    <td class="ps-3 fw-semibold text-nowrap">{{ $appointment['start']->format('H:i') }}–{{ $appointment['end']->format('H:i') }}</td>
                    <td>{{ trim($appointment['client_name'].' '.$appointment['client_lastname']) ?: '—' }}</td>
                    <td>{{ $appointment['title'] ?: '—' }}</td>
                    <td>{{ $appointment['master'] ?: '—' }}</td>
                    <td><x-appointments.status :status="$appointment['status']" /></td>
                    <td class="text-end pe-3"><a class="btn btn-sm btn-phoenix-primary" href="{{ route('calendar.show', $appointment['id']) }}" aria-label="{{ __('admin_dashboard.open_appointment') }}"><span data-feather="arrow-right"></span></a></td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="text-center text-body-tertiary py-5">{{ __('admin_dashboard.no_appointments_today') }}</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-4">
        <div class="d-grid gap-3 h-100">
          @foreach([['label' => __('admin_dashboard.current_appointment'), 'item' => $current_appointment, 'tone' => 'success'], ['label' => __('admin_dashboard.next_appointment'), 'item' => $next_appointment, 'tone' => 'primary']] as $spotlight)
            <div class="card border-start border-4 border-{{ $spotlight['tone'] }}">
              <div class="card-body p-3">
                <div class="text-body-tertiary fs-9 text-uppercase fw-semibold mb-2">{{ $spotlight['label'] }}</div>
                @if($spotlight['item'])
                  <div class="fw-bold">{{ $spotlight['item']->appointment_start->format('H:i') }} · {{ $spotlight['item']->service?->getTranslation(app()->getLocale(), 'name') ?? $spotlight['item']->service?->name }}</div>
                  <div class="text-body-secondary fs-9 mt-1">{{ trim($spotlight['item']->client_name.' '.$spotlight['item']->client_lastname) }}</div>
                  <a class="stretched-link" href="{{ route('calendar.show', $spotlight['item']) }}"><span class="visually-hidden">{{ __('admin_dashboard.open_appointment') }}</span></a>
                @else
                  <div class="text-body-tertiary">{{ __('admin_dashboard.none') }}</div>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
      <div><h4 class="mb-1">{{ __('admin_dashboard.my_month') }}</h4><p class="text-body-tertiary mb-0">{{ __('admin_dashboard.my_stats_hint') }}</p></div>
      <span class="badge badge-phoenix badge-phoenix-secondary">{{ now()->translatedFormat('F Y') }}</span>
    </div>
    <div class="row g-3">
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.revenue')" :value="number_format($personal_month['revenue'], 2, ',', ' ').' €'" icon="trending-up" tone="success" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.unique_clients')" :value="$personal_month['unique_clients']" icon="users" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.worked_time')" :value="\App\Services\WorkTimeReportService::formatDuration($work_time['worked_seconds'])" icon="clock" tone="info" :hint="__('admin_dashboard.hours_minutes')" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.my_no_shows')" :value="$personal_month['no_show']" icon="user-minus" tone="danger" :hint="number_format($personal_month['lost_revenue'], 2, ',', ' ').' €'" /></div>
    </div>

    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2 mt-5 mb-3">
      <div><h4 class="mb-1">{{ __('admin_dashboard.my_customer_segments') }}</h4><p class="text-body-tertiary mb-0">{{ __('admin_dashboard.customer_segments_hint') }}</p></div>
      <a class="btn btn-sm btn-phoenix-primary align-self-start" href="{{ route('dashboard.customers.lost', ['scope'=>'own']) }}">{{ __('admin_dashboard.open_lost_clients') }}</a>
    </div>
    <div class="row g-3 mb-5">
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.new_clients')" :value="$personal_customers['new']" icon="user-plus" tone="success" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.returning_clients')" :value="$personal_customers['returning']" icon="repeat" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.regular_clients')" :value="$personal_customers['regular']" icon="heart" tone="info" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.lost_clients')" :value="$personal_customers['lost']" icon="user-x" tone="danger"><a class="stretched-link" href="{{ route('dashboard.customers.lost', ['scope'=>'own']) }}"><span class="visually-hidden">{{ __('admin_dashboard.open_lost_clients') }}</span></a></x-dashboard-metric-card></div>
    </div>

    <div class="card border-warning-subtle mb-5"><div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3"><div class="d-flex align-items-center gap-3"><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning" style="width:2.75rem;height:2.75rem"><span data-feather="alert-circle"></span></span><div><div class="fs-5 fw-bold">{{ $action_required }}</div><div>{{ __('admin_dashboard.unresolved_my_count') }}</div></div></div><a class="btn btn-warning" href="{{ route('dashboard.appointments.unresolved', ['scope'=>'own']) }}">{{ __('admin_dashboard.open_unresolved') }}</a></div></div>

    <div class="row g-3 mb-5">
      <div class="col-12 col-xl-6"><div class="card"><div class="card-body"><div id="dashboard-personal-week-chart" style="height:320px" role="img" aria-label="{{ __('admin_dashboard.last_week_chart') }}"></div></div></div></div>
      <div class="col-12 col-xl-6"><div class="card"><div class="card-body"><div id="dashboard-personal-month-chart" style="height:320px" role="img" aria-label="{{ __('admin_dashboard.current_month_chart') }}"></div></div></div></div>
    </div>
    <x-dashboard-footer />
  </div>
  @push('scripts')
    <script>
      (() => {
        if (typeof echarts === 'undefined') return;
        const render = (id, rows, title) => {
          const container = document.getElementById(id);
          if (!container) return;
          const chart = echarts.init(container);
          chart.setOption({title:{text:title},tooltip:{trigger:'axis'},legend:{top:30,data:[@json(__('admin_dashboard.revenue')),@json(__('admin_dashboard.completed'))]},grid:{left:45,right:45,top:75,bottom:35},xAxis:{type:'category',data:rows.map(row=>row.date),axisLabel:{hideOverlap:true}},yAxis:[{type:'value',name:'€'},{type:'value',minInterval:1}],series:[{name:@json(__('admin_dashboard.revenue')),type:'line',smooth:true,data:rows.map(row=>row.revenue),itemStyle:{color:'var(--phoenix-success)'}},{name:@json(__('admin_dashboard.completed')),type:'bar',yAxisIndex:1,data:rows.map(row=>row.completed)}]});
          window.addEventListener('resize',()=>chart.resize(),{passive:true});
        };
        render('dashboard-personal-week-chart', @json($personal_charts['week']), @json(__('admin_dashboard.last_week_chart')));
        render('dashboard-personal-month-chart', @json($personal_charts['month']), @json(__('admin_dashboard.current_month_chart')));
      })();
    </script>
  @endpush
</x-dashboard-layout>
