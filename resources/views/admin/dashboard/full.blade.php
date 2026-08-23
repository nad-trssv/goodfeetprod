@section('title', __('admin_dashboard.full_title'))
<x-dashboard-layout>
  <div class="content pt-8">
    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4">
      <div><h2 class="mb-1">{{ __('admin_dashboard.full_title') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_dashboard.full_subtitle') }}</p></div>
      <a class="btn btn-phoenix-secondary align-self-start" href="{{ route('dashboard') }}"><span data-feather="arrow-left" class="me-2"></span>{{ __('admin_dashboard.back_operational') }}</a>
    </div>

    <form method="GET" action="{{ route('admin.dashboard.full') }}" class="card card-body mb-4">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-4 col-xl-3"><label class="form-label" for="dashboard-period">{{ __('admin_dashboard.period') }}</label><select class="form-select" id="dashboard-period" name="period"><option value="current_month" @selected($period->preset==='current_month')>{{ __('admin_dashboard.period_current_month') }}</option><option value="last_30_days" @selected($period->preset==='last_30_days')>{{ __('admin_dashboard.period_last_30') }}</option><option value="previous_month" @selected($period->preset==='previous_month')>{{ __('admin_dashboard.period_previous_month') }}</option><option value="custom" @selected($period->preset==='custom')>{{ __('admin_dashboard.period_custom') }}</option></select></div>
        <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="dashboard-from">{{ __('admin_dashboard.from') }}</label><input class="form-control" type="date" id="dashboard-from" name="from" value="{{ $period->start->toDateString() }}"></div>
        <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="dashboard-to">{{ __('admin_dashboard.to') }}</label><input class="form-control" type="date" id="dashboard-to" name="to" value="{{ $period->end->toDateString() }}"></div>
        <div class="col-12 col-md-2"><button class="btn btn-primary w-100" type="submit">{{ __('admin_dashboard.apply') }}</button></div>
        <div class="col-12 col-xl ms-xl-auto text-xl-end text-body-tertiary fs-9">{{ $period->start->translatedFormat('d M Y') }} — {{ $period->end->translatedFormat('d M Y') }} · {{ trans_choice('admin_dashboard.days_count', $period->days(), ['count' => $period->days()]) }}</div>
      </div>
      @error('to')<div class="text-danger fs-9 mt-2">{{ $message }}</div>@enderror
    </form>

    @php
      $changeLabel = function(array $change) {
        if ($change['percent'] === null) return __('admin_dashboard.new_growth');
        return ($change['percent'] > 0 ? '+' : '').number_format($change['percent'], 1, ',', ' ').'% '.__('admin_dashboard.vs_previous_period');
      };
    @endphp
    <div class="row g-3 mb-5">
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.revenue')" :value="number_format($summary['revenue'], 2, ',', ' ').' €'" icon="trending-up" tone="success" :hint="$changeLabel($comparison['revenue'])" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.unique_clients')" :value="$summary['unique_clients']" icon="users" :hint="$changeLabel($comparison['clients'])" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.completed')" :value="$summary['completed']" icon="check-circle" tone="success" :hint="$changeLabel($comparison['completed'])" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.average_check')" :value="number_format($summary['average_check'], 2, ',', ' ').' €'" icon="credit-card" tone="info" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.worked_time_total')" :value="\App\Services\WorkTimeReportService::formatDuration($work_time['worked_seconds'])" icon="clock" tone="info" :hint="__('admin_dashboard.employees_with_shifts', ['count' => $work_time['employee_count']])" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.no_show_loss')" :value="number_format($summary['lost_revenue'], 2, ',', ' ').' €'" icon="user-x" tone="danger" :hint="__('admin_dashboard.no_show_count', ['count' => $summary['no_show']])" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.cancelled_value')" :value="number_format($summary['cancelled_value'], 2, ',', ' ').' €'" icon="x-circle" tone="warning" :hint="__('admin_dashboard.cancelled_count', ['count' => $summary['cancelled']])" /></div>
      <div class="col-6 col-xl-3"><x-dashboard-metric-card :label="__('admin_dashboard.completion_rate')" :value="number_format($summary['completion_rate'], 1, ',', ' ').'%'" icon="target" tone="success" :hint="__('admin_dashboard.no_show_rate_value', ['value' => number_format($summary['no_show_rate'], 1, ',', ' ')])" /></div>
    </div>

    <div class="card border-warning-subtle mb-5"><div class="card-body"><div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3"><div><div class="text-body-tertiary fs-9 text-uppercase fw-semibold">{{ __('admin_dashboard.unresolved_all') }}</div><div class="fs-4 fw-bold">{{ $unresolved_total }}</div></div><a class="btn btn-warning align-self-start" href="{{ route('dashboard.appointments.unresolved', ['scope'=>'all']) }}">{{ __('admin_dashboard.open_unresolved') }}</a></div>@if($unresolved_by_employee->isNotEmpty())<div class="d-flex flex-wrap gap-2">@foreach($unresolved_by_employee as $row)<a class="badge badge-phoenix badge-phoenix-warning text-decoration-none d-inline-flex align-items-center gap-2 p-2" href="{{ route('dashboard.appointments.unresolved', ['scope'=>'all','master_id'=>$row->user_id]) }}"><x-ui.avatar :user="$row->user" :size="24" />{{ $row->user?->name ?? '—' }}: {{ $row->unresolved_count }}</a>@endforeach</div>@endif</div></div>

    <div class="row g-3 mb-5">
      <div class="col-12 col-xxl-8">
        <div class="card h-100">
          <div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2"><h4 class="mb-0">{{ __('admin_dashboard.performance_chart') }}</h4><x-dashboard-chart-status-filter target="dashboard-performance-chart" /></div>
          <div class="card-body p-3 p-lg-4"><div id="dashboard-performance-chart" style="height:320px" role="img" aria-label="{{ __('admin_dashboard.performance_chart') }}"></div></div>
        </div>
      </div>
      <div class="col-12 col-xxl-4">
        <div class="card h-100">
          <div class="card-header d-flex align-items-center justify-content-between gap-2"><h4 class="mb-0">{{ __('admin_dashboard.customer_dynamics') }}</h4><a class="btn btn-sm btn-phoenix-primary" href="{{ route('dashboard.customers.lost', ['scope'=>'all']) }}">{{ __('admin_dashboard.customer_report') }}</a></div>
          <div class="card-body p-3 p-lg-4">
            <div class="row g-3">
              <div class="col-6"><div class="border rounded-3 p-3 h-100"><div class="fs-5 fw-bold text-success">{{ $customers['new'] }}</div><div class="fs-9">{{ __('admin_dashboard.new_clients') }}</div></div></div>
              <div class="col-6"><div class="border rounded-3 p-3 h-100"><div class="fs-5 fw-bold text-primary">{{ $customers['returning'] }}</div><div class="fs-9">{{ __('admin_dashboard.returning_clients') }}</div></div></div>
              <div class="col-6"><div class="border rounded-3 p-3 h-100"><div class="fs-5 fw-bold">{{ $customers['regular'] }}</div><div class="fs-9">{{ __('admin_dashboard.regular_clients') }}</div></div></div>
              <div class="col-6"><div class="border rounded-3 p-3 h-100"><div class="fs-5 fw-bold text-danger">{{ $customers['lost'] }}</div><div class="fs-9">{{ __('admin_dashboard.lost_clients') }}</div></div></div>
              <div class="col-6"><div class="border rounded-3 p-3 h-100"><div class="fs-5 fw-bold">{{ $customers['registered'] }}</div><div class="fs-9">{{ __('admin_dashboard.new_accounts') }}</div></div></div>
              <div class="col-6"><div class="border rounded-3 p-3 h-100"><div class="fs-5 fw-bold">{{ number_format($customers['retention_rate'], 1, ',', ' ') }}%</div><div class="fs-9">{{ __('admin_dashboard.retention_rate') }}</div></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-5"><div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2"><h4 class="mb-0">{{ __('admin_dashboard.monthly_chart') }}</h4><x-dashboard-chart-status-filter target="dashboard-monthly-chart" /></div><div class="card-body p-3 p-lg-4"><div id="dashboard-monthly-chart" style="height:340px" role="img" aria-label="{{ __('admin_dashboard.monthly_chart') }}"></div></div></div>

    <div class="row g-3 mb-5">
      <div class="col-12 col-xl-5">
        <div class="card h-100"><div class="card-header"><h4 class="mb-0">{{ __('admin_dashboard.top_services') }}</h4></div><div class="list-group list-group-flush">
          @forelse($top_services as $row)
            <div class="list-group-item d-flex align-items-center gap-3 py-3">
              <x-ui.service-image :service="$row->service" :width="48" :height="48" class="rounded-2 flex-shrink-0" />
              <div class="min-w-0 flex-grow-1"><div class="fw-semibold text-truncate">{{ $row->service?->getTranslation(app()->getLocale(), 'name') ?? $row->service?->name ?? '—' }}</div><small class="text-body-tertiary">{{ __('admin_dashboard.completed_count', ['count' => $row->completed_count]) }}</small></div>
              <div class="fw-bold text-nowrap">{{ number_format($row->revenue, 2, ',', ' ') }} €</div>
            </div>
          @empty<div class="p-4 text-center text-body-tertiary">{{ __('admin_dashboard.no_report_data') }}</div>@endforelse
        </div></div>
      </div>
      <div class="col-12 col-xl-7">
        <div class="card h-100"><div class="card-header"><h4 class="mb-0">{{ __('admin_dashboard.employee_results') }}</h4></div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="ps-3">{{ __('admin_dashboard.employee') }}</th><th>{{ __('admin_dashboard.worked_time') }}</th><th>{{ __('admin_dashboard.completed') }}</th><th>{{ __('admin_dashboard.no_show') }}</th><th class="text-end pe-3">{{ __('admin_dashboard.revenue') }}</th></tr></thead><tbody>
          @forelse($employees as $row)<tr><td class="ps-3"><div class="d-flex align-items-center gap-2"><x-ui.avatar :user="$row['employee']" :size="34" /><span class="fw-semibold">{{ $row['employee']->name }}</span></div></td><td class="text-nowrap">{{ \App\Services\WorkTimeReportService::formatDuration($row['worked_seconds']) }}</td><td>{{ $row['completed'] }}</td><td>{{ $row['no_show'] }}</td><td class="text-end pe-3 fw-semibold">{{ number_format($row['revenue'], 2, ',', ' ') }} €</td></tr>@empty<tr><td colspan="5" class="text-center text-body-tertiary py-4">{{ __('admin_dashboard.no_report_data') }}</td></tr>@endforelse
        </tbody></table></div></div>
      </div>
    </div>

    <div class="accordion mb-5" id="dashboardDefinitions"><div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardDefinitionsBody">{{ __('admin_dashboard.metric_definitions') }}</button></h2><div id="dashboardDefinitionsBody" class="accordion-collapse collapse" data-bs-parent="#dashboardDefinitions"><div class="accordion-body"><ul class="mb-0 d-grid gap-2"><li>{{ __('admin_dashboard.definition_revenue') }}</li><li>{{ __('admin_dashboard.definition_lost') }}</li><li>{{ __('admin_dashboard.definition_regular') }}</li><li>{{ __('admin_dashboard.definition_lost_client') }}</li><li>{{ __('admin_dashboard.definition_retention') }}</li></ul></div></div></div></div>
    <x-dashboard-footer />
  </div>
  @push('scripts')
    <script>
      (() => {
        if (typeof echarts === 'undefined') return;
        const labels = {revenue:@json(__('admin_dashboard.revenue')),completed:@json(__('admin_dashboard.chart_completed')),unresolved:@json(__('admin_dashboard.chart_unresolved')),no_show:@json(__('admin_dashboard.chart_no_show')),cancelled:@json(__('admin_dashboard.chart_cancelled')),empty:@json(__('admin_dashboard.chart_empty'))};
        const colors = {completed:'var(--phoenix-success)',unresolved:'var(--phoenix-warning)',no_show:'var(--phoenix-danger)',cancelled:'var(--phoenix-secondary-color)'};
        const render = (id, rows, category) => {
          const container = document.getElementById(id);
          if (!container) return;
          const chart = echarts.init(container);
          const selected = new Set(['completed']);
          const update = () => {
            const hasData = rows.some(row => [...selected].some(status => Number(row[status] || 0) > 0));
            const series = [...selected].map(status => ({name:labels[status],type:'bar',stack:'appointments',data:rows.map(row=>row[status]),itemStyle:{color:colors[status]}}));
            if (selected.has('completed')) series.unshift({name:labels.revenue,type:'line',smooth:true,yAxisIndex:1,data:rows.map(row=>row.revenue),itemStyle:{color:'var(--phoenix-primary)'}});
            chart.setOption({tooltip:{trigger:'axis'},legend:{show:false},grid:{left:45,right:50,top:hasData?20:55,bottom:40},xAxis:{type:'category',data:rows.map(row=>row[category]),axisLabel:{hideOverlap:true}},yAxis:[{type:'value',minInterval:1},{type:'value',name:'€'}],series,graphic:hasData?[]:[{type:'text',left:'center',top:18,style:{text:labels.empty,fill:'var(--phoenix-secondary-color)',fontSize:13}}]},true);
          };
          document.querySelectorAll(`[data-dashboard-status-filter="${id}"] [data-status]`).forEach(button => button.addEventListener('click', () => {
            const status = button.dataset.status;
            if (selected.has(status) && selected.size === 1) return;
            selected.has(status) ? selected.delete(status) : selected.add(status);
            button.setAttribute('aria-pressed', selected.has(status) ? 'true' : 'false');
            button.classList.toggle('active', selected.has(status));
            update();
          }));
          update();
          window.addEventListener('resize',()=>chart.resize(),{passive:true});
        };
        render('dashboard-performance-chart', @json($daily), 'date');
        render('dashboard-monthly-chart', @json($monthly), 'month');
      })();
    </script>
  @endpush
</x-dashboard-layout>
