<section class="booking-alternative booking-master-first booking-template--wizard py-6 py-lg-9 ff_secondary" data-booking-template="wizard" data-booking-flow="master-first">
  <div class="container-xl">
    <header class="row align-items-end g-3 mb-5"><div class="col-lg-7"><span class="text-uppercase text-body-secondary fs-10 fw-bold">{{ __('msg.booking_master_first_label') }}</span><h1 class="fs-3 fs-md-2 mt-2 mb-2">{{ __('msg.booking_choose_specialist_title') }}</h1><p class="text-body-secondary mb-0">{{ __('msg.booking_master_first_intro') }}</p></div><div class="col-lg-5"><div class="booking-flow-pills"><span class="is-active">{{ __('msg.master') }}</span><span>{{ __('msg.service') }}</span><span>{{ __('msg.choose_date') }}</span></div></div></header>
    <div class="row g-4">
      <aside class="col-12 col-lg-4 col-xl-3"><div class="master-directory"><h2 class="fs-6 mb-3">{{ __('msg.choose_master') }}</h2><div id="altMasters">
        @foreach($bookingMasters as $master)
          <button type="button" class="alt-master master-directory__item" data-master-id="{{ $master['id'] }}">@if(($settings['show_master_images'] ?? true))<img src="{{ $master['profile_photo_url'] }}" alt="{{ $master['name'] }}" width="84" height="84" loading="lazy" decoding="async" class="master-directory__photo">@else<span class="master-directory__avatar">{{ mb_strtoupper(mb_substr($master['name'], 0, 1)) }}</span>@endif<span><strong>{{ $master['name'] }} @if($master['vacation'] ?? null)<span title="В отпуске {{ $master['vacation']['from'] }}–{{ $master['vacation']['to'] }}">🌴</span>@endif</strong><small>{{ $master['professional_title'] ?: __('msg.booking_view_services') }}</small>@if($master['vacation'] ?? null)<small>В отпуске {{ $master['vacation']['from'] }}–{{ $master['vacation']['to'] }}</small>@endif</span><i class="fa-solid fa-chevron-right"></i></button>
        @endforeach
      </div></div></aside>
      <main class="col-12 col-lg-8 col-xl-9">
        <section class="master-workspace is-locked" id="altServicesPanel"><div class="master-workspace__title"><span>2</span><div><h2>{{ __('msg.choose_service') }}</h2><p>{{ __('msg.booking_services_by_master') }}</p></div></div><div class="service-tiles" id="altServices"><div class="booking-placeholder">{{ __('msg.booking_choose_master_first') }}</div></div></section>
        <section class="master-workspace is-locked mt-4" id="altDatePanel"><div class="master-workspace__title"><span>3</span><div><h2>{{ __('msg.choose_date') }}</h2><p>{{ __('msg.booking_live_availability') }}</p></div></div><div class="row g-4"><div class="col-12 col-xl-8"><div id="altCalendar"></div></div><aside class="col-12 col-xl-4"><h3 id="altDateName" class="fs-7 mb-3"></h3><div id="altSlots" class="alt-slots"></div></aside></div></section>
      </main>
    </div>
    <aside class="booking-selection-bar" aria-live="polite"><div><small>{{ __('msg.master') }}</small><strong id="altSummaryMaster">—</strong></div><div><small>{{ __('msg.service') }}</small><strong id="altSummaryService">—</strong></div><div><small>{{ __('msg.price') }}</small><strong id="altSummaryPrice">—</strong></div></aside>
  </div>
</section>
@include('pages.booking.templates.alternative-script')
