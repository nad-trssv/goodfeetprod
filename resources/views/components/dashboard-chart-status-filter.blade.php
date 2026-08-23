@props(['target'])
<div class="d-flex flex-wrap gap-1" data-dashboard-status-filter="{{ $target }}" aria-label="{{ __('admin_dashboard.chart_status_filter') }}">
  <button type="button" class="btn btn-sm btn-phoenix-success active" data-status="completed" aria-pressed="true">{{ __('admin_dashboard.chart_completed') }}</button>
  <button type="button" class="btn btn-sm btn-phoenix-warning" data-status="unresolved" aria-pressed="false">{{ __('admin_dashboard.chart_unresolved') }}</button>
  <button type="button" class="btn btn-sm btn-phoenix-danger" data-status="no_show" aria-pressed="false">{{ __('admin_dashboard.chart_no_show') }}</button>
  <button type="button" class="btn btn-sm btn-phoenix-secondary" data-status="cancelled" aria-pressed="false">{{ __('admin_dashboard.chart_cancelled') }}</button>
</div>
