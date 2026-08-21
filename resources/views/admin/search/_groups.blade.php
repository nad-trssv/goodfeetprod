@php
  $compact = $compact ?? false;
  $total = collect($results)->sum(fn($items) => $items->count());
@endphp

@if(mb_strlen($term) < 2)
  <div class="p-4 text-center text-body-secondary">{{ __('admin_search.enter_more') }}</div>
@elseif($total === 0)
  <div class="p-4 text-center text-body-secondary">{{ __('admin_search.no_results') }}</div>
@else
  @php($renderedSearchGroup = false)
  @foreach(['services' => 'briefcase', 'customers' => 'users', 'appointments' => 'calendar'] as $type => $icon)
    @if($results[$type]->isNotEmpty())
      <section class="{{ $renderedSearchGroup ? 'border-top' : '' }}">
        <div class="px-3 pt-3 pb-2 d-flex align-items-center gap-2 text-body-secondary text-uppercase fs-10 fw-bold"><span data-feather="{{ $icon }}" style="width:14px;height:14px"></span>{{ __('admin_search.types.'.$type) }} <span class="badge badge-phoenix badge-phoenix-secondary ms-auto">{{ $results[$type]->count() }}</span></div>
        @foreach($results[$type] as $item)
          @if($type === 'services')
            @php($serviceName = $item->translations->firstWhere('locale', app()->getLocale())?->name ?? $item->name)
            <a class="d-flex align-items-center gap-3 px-3 py-2 text-decoration-none hover-bg-100" href="{{ auth()->user()->hasPermission('services.update') ? route('service.edit', $item) : route('service.index') }}">
              <x-ui.service-image :service="$item" :width="$compact ? 34 : 44" :height="$compact ? 34 : 44" class="rounded flex-shrink-0" />
              <span class="min-w-0"><strong class="d-block text-body-emphasis text-truncate">{{ $serviceName }}</strong><small class="text-body-secondary">{{ number_format((float)$item->price, 2, ',', ' ') }} € · {{ $item->status ? __('admin_search.active') : __('admin_search.inactive') }}</small></span>
            </a>
          @elseif($type === 'customers')
            <a class="d-flex align-items-center gap-3 px-3 py-2 text-decoration-none hover-bg-100" href="{{ route(auth()->user()->hasPermission('crm.view') ? 'crm.customers.show' : 'admin.customers.show', $item) }}">
              <span class="rounded-circle bg-body-secondary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:{{ $compact ? 34 : 44 }}px;height:{{ $compact ? 34 : 44 }}px"><span data-feather="user"></span></span>
              <span class="min-w-0"><strong class="d-block text-body-emphasis text-truncate">{{ $item->full_name ?: __('admin_search.unnamed_customer') }}</strong><small class="d-block text-body-secondary text-truncate">{{ $item->email ?: $item->phone }} · {{ trans_choice('admin_search.appointments_count', $item->appointments_count, ['count' => $item->appointments_count]) }}</small></span>
            </a>
          @else
            @php($serviceName = $item->service?->translations?->firstWhere('locale', app()->getLocale())?->name ?? $item->service?->name ?? '—')
            <a class="d-flex align-items-center gap-3 px-3 py-2 text-decoration-none hover-bg-100" href="{{ route('calendar.show', $item) }}">
              <span class="rounded bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:{{ $compact ? 34 : 44 }}px;height:{{ $compact ? 34 : 44 }}px"><span data-feather="calendar"></span></span>
              <span class="min-w-0 flex-grow-1"><strong class="d-block text-body-emphasis text-truncate">{{ trim($item->client_name.' '.$item->client_lastname) ?: __('admin_search.unnamed_customer') }}</strong><small class="d-block text-body-secondary text-truncate">#{{ $item->id }} · {{ $serviceName }} · {{ $item->appointment_start->format('d.m.Y H:i') }}</small></span>
              @unless($compact)<x-appointments.status :status="$item->status" />@endunless
            </a>
          @endif
        @endforeach
      </section>
      @php($renderedSearchGroup = true)
    @endif
  @endforeach
@endif
