@section('title', __('customer.my_bookings'))
<x-app-layout>
  <section class="py-7 py-lg-9"><div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-5">
      <div><h1 class="fs-4 mb-1">{{ __('customer.my_bookings') }}</h1><p class="text-body-secondary mb-0">{{ $customer->full_name }}</p></div>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @error('appointment')<div class="alert alert-danger">{{ $message }}</div>@enderror
    <h2 class="fs-6 mb-1">{{ __('customer.upcoming') }}</h2>
    <p class="small text-body-secondary">{{ $customerCancellationEnabled ? __('customer.cancel_notice', ['hours' => $cancellationNoticeHours]) : __('customer.cancellation_disabled') }}</p>
    <div class="row g-3 mb-6">
      @forelse($upcoming as $appointment)
        <div class="col-12 col-lg-6"><article class="card h-100" data-appointment="{{ $appointment->public_uuid }}"><div class="card-body">
          <div class="d-flex justify-content-between gap-3"><div class="d-flex align-items-start gap-3"><img src="{{ $appointment->service->image_url }}" alt="" width="56" height="56" class="rounded object-fit-cover flex-shrink-0"><div><h3 class="fs-7 mb-2">{{ $appointment->service->getTranslation(app()->getLocale(), 'name') ?? $appointment->service->name }}</h3><div class="d-flex align-items-center gap-2"><img src="{{ $appointment->user->profile_photo_path ? asset('storage/'.$appointment->user->profile_photo_path) : asset('assets/img/team/avatar.webp') }}" alt="" width="32" height="32" class="rounded-circle object-fit-cover flex-shrink-0"><span>{{ $appointment->user->name }}</span></div></div></div><span class="badge bg-primary-subtle text-primary">{{ $appointment->status }}</span></div>
          <p class="mt-3 mb-2 fw-semibold">{{ $appointment->appointment_start->format('d.m.Y H:i') }}</p>
          @if($appointment->rescheduleRequests->isNotEmpty())
            <div class="alert alert-warning py-2 mb-0">{{ __('customer.pending_master_approval') }}: {{ $appointment->rescheduleRequests->first()->requested_start->format('d.m.Y H:i') }}</div>
          @elseif(now()->lt($appointment->appointment_start->copy()->subHours($cancellationNoticeHours)))
            <div class="d-flex flex-wrap gap-2 mt-3"><a class="btn btn-outline-primary" href="{{ route('customer.appointments.reschedule', ['appointment'=>$appointment->public_uuid]) }}">{{ __('customer.reschedule') }}</a></div>
            @if($customerCancellationEnabled)<form method="POST" action="{{ route('customer.appointments.destroy', ['appointment' => $appointment->public_uuid]) }}" onsubmit="return confirm(@js(__('customer.cancel_confirm')))" class="mt-3">@csrf @method('DELETE')<label class="form-label small" for="cancel-reason-{{ $appointment->id }}">{{ __('customer.cancel_reason') }}</label><div class="d-flex flex-column flex-sm-row gap-2"><input class="form-control" id="cancel-reason-{{ $appointment->id }}" name="reason" maxlength="500"><button class="btn btn-outline-danger text-nowrap" type="submit">{{ __('customer.cancel') }}</button></div></form>@endif
          @endif
        </div></article></div>
      @empty
        <div class="col-12"><div class="alert alert-light border">{{ __('customer.no_upcoming') }} <a href="{{ route('serviceBooking') }}">{{ __('customer.book') }}</a></div></div>
      @endforelse
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
      <div><h2 class="fs-6 mb-1">{{ __('customer.booking_history') }}</h2><p class="small text-body-secondary mb-0">{{ __('customer.records_found', ['count' => $history->total()]) }}</p></div>
    </div>

    <form method="GET" action="{{ route('customer.dashboard') }}" class="card card-body mb-3">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-lg-4"><label class="form-label" for="history-search">{{ __('customer.search') }}</label><input id="history-search" class="form-control" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('customer.service') }} / {{ __('customer.master') }}"></div>
        <div class="col-12 col-sm-6 col-lg-2"><label class="form-label" for="history-service">{{ __('customer.service') }}</label><select id="history-service" class="form-select" name="service_id"><option value="">{{ __('customer.all_services') }}</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected(($filters['service_id'] ?? null) == $service->id)>{{ $service->getTranslation(app()->getLocale(), 'name') ?? $service->name }}</option>@endforeach</select></div>
        <div class="col-12 col-sm-6 col-lg-2"><label class="form-label" for="history-master">{{ __('customer.master') }}</label><select id="history-master" class="form-select" name="master_id"><option value="">{{ __('customer.all_masters') }}</option>@foreach($masters as $master)<option value="{{ $master->id }}" @selected(($filters['master_id'] ?? null) == $master->id)>{{ $master->name }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-2"><label class="form-label" for="history-from">Дата от</label><input id="history-from" class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
        <div class="col-6 col-lg-2"><label class="form-label" for="history-to">Дата до</label><input id="history-to" class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
        <div class="col-12 d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit">{{ __('customer.find') }}</button><a class="btn btn-outline-secondary" href="{{ route('customer.dashboard') }}">{{ __('customer.reset') }}</a></div>
      </div>
    </form>

    @php
      $sortLink = function (string $column) use ($filters) {
          $current = $filters['sort'] ?? 'date';
          $direction = $current === $column && ($filters['direction'] ?? 'desc') === 'asc' ? 'desc' : 'asc';
          return route('customer.dashboard', array_merge(request()->except(['sort', 'direction']), ['sort' => $column, 'direction' => $direction]));
      };
    @endphp
    <div class="table-responsive border rounded-3">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th><a href="{{ $sortLink('service') }}">{{ __('customer.service') }}</a></th><th><a href="{{ $sortLink('master') }}">{{ __('customer.master') }}</a></th><th><a href="{{ $sortLink('date') }}">{{ __('customer.date') }}</a></th><th>{{ __('customer.status') }}</th></tr></thead>
        <tbody>
          @forelse($history as $appointment)
            <tr data-appointment="{{ $appointment->public_uuid }}">
              <td class="ps-3"><div class="d-flex align-items-center gap-2"><img src="{{ $appointment->service->image_url }}" alt="" width="44" height="44" loading="lazy" decoding="async" class="rounded object-fit-cover flex-shrink-0"><span>{{ $appointment->service->getTranslation(app()->getLocale(), 'name') ?? $appointment->service->name }}</span></div></td>
              <td><div class="d-flex align-items-center gap-2"><img src="{{ $appointment->user->profile_photo_path ? asset('storage/'.$appointment->user->profile_photo_path) : asset('assets/img/team/avatar.webp') }}" alt="" width="40" height="40" loading="lazy" decoding="async" class="rounded-circle object-fit-cover flex-shrink-0"><span>{{ $appointment->user->name }}</span></div></td>
              <td class="text-nowrap">{{ $appointment->appointment_start->format('d.m.Y H:i') }}</td><td>{{ $appointment->status }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="py-4 text-center text-body-secondary">{{ __('customer.no_results') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($history->hasPages())
      <nav class="mt-3" aria-label="Страницы истории записей">{{ $history->onEachSide(1)->links() }}</nav>
    @endif

  </div></section>
</x-app-layout>
