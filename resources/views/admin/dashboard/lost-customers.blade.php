@section('title', __('admin_dashboard.lost_customers_title'))
<x-dashboard-layout>
  <div class="content pt-8">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
      <div><h2 class="mb-1">{{ __('admin_dashboard.lost_customers_title') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_dashboard.lost_customers_subtitle', ['date' => $cutoff->translatedFormat('d.m.Y')]) }}</p></div>
      <a class="btn btn-phoenix-secondary align-self-start" href="{{ $allScope ? route('admin.dashboard.full') : route('dashboard') }}"><span data-feather="arrow-left" class="me-2"></span>{{ __('admin_dashboard.back_to_dashboard') }}</a>
    </div>

    @can('dashboard.full')
      <div class="nav nav-pills mb-3 gap-2"><a class="nav-link {{ !$allScope ? 'active' : '' }}" href="{{ route('dashboard.customers.lost', ['scope' => 'own']) }}">{{ __('admin_dashboard.my_clients_scope') }}</a><a class="nav-link {{ $allScope ? 'active' : '' }}" href="{{ route('dashboard.customers.lost', ['scope' => 'all']) }}">{{ __('admin_dashboard.all_clients_scope') }}</a></div>
    @endcan

    <form method="GET" action="{{ route('dashboard.customers.lost') }}" class="card card-body mb-4">
      <input type="hidden" name="scope" value="{{ $allScope ? 'all' : 'own' }}">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-lg"><label class="form-label" for="lost-search">{{ __('admin_dashboard.search_clients') }}</label><input class="form-control" id="lost-search" name="search" value="{{ $search }}" placeholder="{{ __('admin_dashboard.search_clients_placeholder') }}"></div>
        @if($allScope)<div class="col-12 col-md-5 col-lg-3"><label class="form-label" for="lost-master">{{ __('admin_dashboard.employee') }}</label><select class="form-select" id="lost-master" name="master_id"><option value="">{{ __('admin_dashboard.all_employees') }}</option>@foreach($masters as $master)<option value="{{ $master->id }}" @selected($masterId===$master->id)>{{ $master->name }}</option>@endforeach</select></div>@endif
        <div class="col-12 col-md-auto"><button class="btn btn-primary w-100" type="submit">{{ __('admin_dashboard.find') }}</button></div>
      </div>
    </form>

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between gap-2"><h4 class="mb-0">{{ __('admin_dashboard.lost_clients') }}</h4><span class="badge badge-phoenix badge-phoenix-danger">{{ $customers->total() }}</span></div>
      <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-3">{{ __('admin_dashboard.client') }}</th><th>{{ __('admin_dashboard.contacts') }}</th><th>{{ __('admin_dashboard.last_visit') }}</th><th>{{ __('admin_dashboard.visit_statistics') }}</th><th>{{ __('admin_dashboard.customer_revenue') }}</th><th></th></tr></thead><tbody>
        @forelse($customers as $customer)
          <tr><td class="ps-3"><div class="fw-semibold">{{ $customer->full_name }}</div><small class="text-body-tertiary">{{ filled($customer->getRawOriginal('password')) ? __('admin_dashboard.registered_client') : __('admin_dashboard.guest_client') }}</small></td><td><div><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></div><div><a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a></div></td><td class="text-nowrap">{{ $customer->last_visit_at ? \Carbon\Carbon::parse($customer->last_visit_at)->translatedFormat('d.m.Y') : '—' }}</td><td><div>{{ __('admin_dashboard.completed_short') }}: {{ $customer->completed_count }}</div><small class="text-body-tertiary">{{ __('admin_dashboard.no_show_short') }}: {{ $customer->no_show_count }} · {{ __('admin_dashboard.cancelled_short') }}: {{ $customer->cancelled_count }}</small></td><td class="fw-semibold text-nowrap">{{ number_format((float)$customer->revenue, 2, ',', ' ') }} €</td><td class="text-end pe-3">@can('crm.view')<a class="btn btn-sm btn-phoenix-primary" href="{{ route('crm.customers.show', $customer) }}">{{ __('admin_dashboard.open_client') }}</a>@endcan</td></tr>
        @empty<tr><td colspan="6" class="text-center text-body-tertiary py-5">{{ __('admin_dashboard.no_lost_clients') }}</td></tr>@endforelse
      </tbody></table></div>
      @if($customers->hasPages())<div class="card-footer">{{ $customers->links() }}</div>@endif
    </div>
    <x-dashboard-footer />
  </div>
</x-dashboard-layout>
