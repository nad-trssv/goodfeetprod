@section('title', $customer->full_name)
@php($activeTab = request('tab', 'overview'))
<x-dashboard-layout>
<div class="content">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div><a class="text-decoration-none fs-9" href="{{ route('admin.customers.index') }}">← {{ __('admin_customers.back_to_list') }}</a><h2 class="mt-2 mb-1">{{ $customer->full_name }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_customers.profile_subtitle') }}</p></div>
    <a class="btn btn-outline-primary" href="{{ route('calendarList',['customer_id'=>$customer->id]) }}"><span class="fas fa-calendar-alt me-2"></span>{{ __('admin_customers.open_bookings') }}</a>
  </div>

  <div class="row g-4">
    <aside class="col-12 col-xl-4 col-xxl-3">
      <section class="card position-sticky" style="top:1rem"><div class="card-body p-4">
        <div class="text-center mb-4"><x-ui.avatar :name="$customer->full_name" :size="88" class="mb-3" /><h4 class="mb-2">{{ $customer->full_name }}</h4><span class="badge badge-phoenix badge-phoenix-{{ filled($customer->getRawOriginal('password')) ? 'success':'secondary' }}">{{ filled($customer->getRawOriginal('password')) ? __('admin_customers.account'):__('admin_customers.guest') }}</span></div>
        <div class="d-grid gap-3">
          <div><small class="text-body-tertiary d-block">Email</small><a class="text-break" href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></div>
          <div><small class="text-body-tertiary d-block">{{ __('admin_customers.phone') }}</small><a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a></div>
          <div><small class="text-body-tertiary d-block">{{ __('admin_customers.first_seen') }}</small><strong>{{ $customer->created_at->format('d.m.Y H:i') }}</strong></div>
          @if($customer->account_registered_at)<div><small class="text-body-tertiary d-block">{{ __('admin_customers.registered_at') }}</small><strong>{{ $customer->account_registered_at->format('d.m.Y H:i') }}</strong></div>@endif
          <div><small class="text-body-tertiary d-block">{{ __('admin_customers.language') }}</small><strong>{{ strtoupper($customer->locale) }}</strong></div>
        </div>
      </div></section>
    </aside>

    <div class="col-12 col-xl-8 col-xxl-9">
      <nav class="card card-body p-2 mb-4" aria-label="{{ __('admin_customers.profile_sections') }}"><div class="nav nav-pills flex-column flex-sm-row gap-2" role="tablist">
        <button class="nav-link flex-sm-fill @if($activeTab==='overview') active @endif" data-bs-toggle="tab" data-bs-target="#customer-overview" type="button">{{ __('admin_customers.overview') }}</button>
        <button class="nav-link flex-sm-fill @if($activeTab==='appointments') active @endif" data-bs-toggle="tab" data-bs-target="#customer-appointments" type="button">{{ __('admin_customers.bookings') }} <span class="badge bg-body-secondary text-body ms-1">{{ (int)$summary->bookings_count }}</span></button>
        <button class="nav-link flex-sm-fill @if($activeTab==='preferences') active @endif" data-bs-toggle="tab" data-bs-target="#customer-preferences" type="button">{{ __('admin_customers.preferences') }}</button>
        @if(filled($customer->getRawOriginal('password')))<button class="nav-link flex-sm-fill @if($activeTab==='possible') active @endif" data-bs-toggle="tab" data-bs-target="#customer-possible" type="button">{{ __('admin_customers.possible_matches') }} @if($possibleCount)<span class="badge bg-warning text-dark ms-1">{{ $possibleCount }}</span>@endif</button>@endif
      </div></nav>

      <div class="tab-content">
        <section id="customer-overview" class="tab-pane fade @if($activeTab==='overview') show active @endif">
          <div class="row g-3 mb-4">
            @foreach([
              [__('admin_customers.revenue'), number_format((float)$summary->revenue,2,',',' ').' €', 'success'],
              [__('admin_customers.bookings'), (int)$summary->bookings_count, 'primary'],
              [__('admin_customers.completed'), (int)$summary->completed_count, 'success'],
              [__('admin_customers.average_check'), number_format((float)$summary->average_check,2,',',' ').' €', 'info'],
              [__('admin_customers.no_show'), (int)$summary->no_show_count, 'danger'],
              [__('admin_customers.upcoming'), (int)$summary->upcoming_count, 'primary'],
            ] as [$label,$value,$tone])
              <div class="col-6 col-lg-4"><div class="card h-100 border-start border-3 border-{{ $tone }}"><div class="card-body p-3"><small class="text-body-tertiary d-block mb-1">{{ $label }}</small><strong class="fs-6 text-body-emphasis">{{ $value }}</strong></div></div></div>
            @endforeach
          </div>
          <section class="card"><div class="card-body p-4"><h4 class="mb-4">{{ __('admin_customers.activity') }}</h4><div class="row g-4">
            <div class="col-sm-6"><small class="text-body-tertiary d-block">{{ __('admin_customers.first_booking') }}</small><strong>{{ $summary->first_booking_at ? \Carbon\Carbon::parse($summary->first_booking_at)->format('d.m.Y H:i') : '—' }}</strong></div>
            <div class="col-sm-6"><small class="text-body-tertiary d-block">{{ __('admin_customers.last_booking') }}</small><strong>{{ $summary->last_booking_at ? \Carbon\Carbon::parse($summary->last_booking_at)->format('d.m.Y H:i') : '—' }}</strong></div>
            <div class="col-sm-6"><small class="text-body-tertiary d-block">{{ __('admin_customers.visit_frequency') }}</small><strong>{{ $visitFrequencyDays ? __('admin_customers.every_days',['days'=>$visitFrequencyDays]) : __('admin_customers.not_enough_data') }}</strong></div>
            <div class="col-sm-6"><small class="text-body-tertiary d-block">{{ __('admin_customers.cancelled') }}</small><strong>{{ (int)$summary->cancelled_count }}</strong></div>
          </div></div></section>
        </section>

        <section id="customer-appointments" class="tab-pane fade @if($activeTab==='appointments') show active @endif">
          <div class="card overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0" style="min-width:850px"><thead><tr><th class="ps-4">{{ __('admin_customers.date') }}</th><th>{{ __('admin_customers.service') }}</th><th>{{ __('admin_customers.specialist') }}</th><th>{{ __('admin_customers.price') }}</th><th>{{ __('admin_customers.status') }}</th><th>{{ __('admin_customers.identity') }}</th><th></th></tr></thead><tbody>
          @forelse($appointments as $appointment)@php($serviceName=$appointment->service?->translations?->firstWhere('locale',app()->getLocale())?->name ?? $appointment->service?->name ?? '—')<tr><td class="ps-4 text-nowrap">{{ $appointment->appointment_start->format('d.m.Y H:i') }}</td><td>{{ $serviceName }}</td><td><div class="d-flex align-items-center gap-2"><x-ui.avatar :user="$appointment->user" :size="34" /><span>{{ $appointment->user?->name ?? '—' }}</span></div></td><td class="text-nowrap">{{ number_format((float)$appointment->price,2,',',' ') }} €</td><td><x-appointments.status :status="$appointment->status" /></td><td>@if($appointment->customer_identity_verified)<span class="badge badge-phoenix badge-phoenix-success">{{ __('admin_customers.account_booking') }}</span>@else<span class="badge badge-phoenix badge-phoenix-warning">{{ __('admin_customers.contact_match') }}</span>@endif</td><td class="pe-4 text-end"><a href="{{ route('calendar.show',$appointment) }}">{{ __('admin_customers.open') }}</a></td></tr>
          @empty<tr><td colspan="7" class="text-center py-5 text-body-tertiary">{{ __('admin_customers.no_bookings') }}</td></tr>@endforelse
          </tbody></table></div></div>
          @if($appointments->hasPages())<div class="mt-3">{{ $appointments->appends(['tab'=>'appointments'])->onEachSide(1)->links() }}</div>@endif
        </section>

        <section id="customer-preferences" class="tab-pane fade @if($activeTab==='preferences') show active @endif">
          <div class="row g-4"><div class="col-12 col-lg-6"><section class="card h-100"><div class="card-body p-4"><h4 class="mb-4">{{ __('admin_customers.favorite_services') }}</h4>
            @forelse($favoriteServices as $item)@php($serviceName=$item->service?->translations?->firstWhere('locale',app()->getLocale())?->name ?? $item->service?->name ?? '—')<div class="d-flex justify-content-between align-items-center gap-3 border-bottom py-3"><div class="d-flex align-items-center gap-3 min-w-0"><x-ui.service-image :service="$item->service" :width="44" :height="44" class="rounded" /><strong class="text-break">{{ $serviceName }}</strong></div><span class="badge badge-phoenix badge-phoenix-primary">{{ trans_choice('admin_customers.visit_count',$item->visits_count,['count'=>$item->visits_count]) }}</span></div>@empty<p class="text-body-tertiary">{{ __('admin_customers.no_completed') }}</p>@endforelse
          </div></section></div><div class="col-12 col-lg-6"><section class="card h-100"><div class="card-body p-4"><h4 class="mb-4">{{ __('admin_customers.favorite_masters') }}</h4>
            @forelse($favoriteMasters as $item)<div class="d-flex justify-content-between align-items-center gap-3 border-bottom py-3"><div class="d-flex align-items-center gap-3"><x-ui.avatar :user="$item->user" :size="44" /><strong>{{ $item->user?->name ?? '—' }}</strong></div><span class="badge badge-phoenix badge-phoenix-primary">{{ trans_choice('admin_customers.visit_count',$item->visits_count,['count'=>$item->visits_count]) }}</span></div>@empty<p class="text-body-tertiary">{{ __('admin_customers.no_completed') }}</p>@endforelse
          </div></section></div></div>
        </section>

        @if(filled($customer->getRawOriginal('password')))<section id="customer-possible" class="tab-pane fade @if($activeTab==='possible') show active @endif">
          <div class="alert alert-warning"><strong>{{ __('admin_customers.possible_title') }}</strong><div class="mt-1">{{ __('admin_customers.possible_help') }}</div></div>
          <div class="card overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0" style="min-width:850px"><thead><tr><th class="ps-4">{{ __('admin_customers.date') }}</th><th>{{ __('admin_customers.service') }}</th><th>{{ __('admin_customers.specialist') }}</th><th>{{ __('admin_customers.match_by') }}</th><th>{{ __('admin_customers.link_state') }}</th><th></th></tr></thead><tbody>
          @forelse($possibleAppointments as $appointment)@php($serviceName=$appointment->service?->translations?->firstWhere('locale',app()->getLocale())?->name ?? $appointment->service?->name ?? '—')<tr><td class="ps-4 text-nowrap">{{ $appointment->appointment_start->format('d.m.Y H:i') }}</td><td>{{ $serviceName }}</td><td><div class="d-flex align-items-center gap-2"><x-ui.avatar :user="$appointment->user" :size="34" /><span>{{ $appointment->user?->name ?? '—' }}</span></div></td><td><span class="badge badge-phoenix badge-phoenix-warning">{{ __('admin_customers.match_'.$appointment->identity_match) }}</span></td><td>{{ $appointment->already_linked ? __('admin_customers.linked_by_contacts') : __('admin_customers.needs_review') }}</td><td class="pe-4 text-end"><a href="{{ route('calendar.show',$appointment) }}">{{ __('admin_customers.open') }}</a></td></tr>
          @empty<tr><td colspan="6" class="text-center py-5 text-body-tertiary">{{ __('admin_customers.no_possible') }}</td></tr>@endforelse
          </tbody></table></div></div>
          @if($possibleAppointments?->hasPages())<div class="mt-3">{{ $possibleAppointments->appends(['tab'=>'possible'])->onEachSide(1)->links() }}</div>@endif
        </section>@endif
      </div>
    </div>
  </div>
  <x-dashboard-footer />
</div>
</x-dashboard-layout>
