<section class="booking-alternative booking-single-page booking-template--classic py-6 py-lg-9 ff_secondary" data-booking-template="classic" data-booking-flow="service-first">
  <div class="container-xl">
    <header class="mb-5 text-center"><h1 class="fs-3 fs-md-2 mb-2">{{ __('msg.menu_booking') }}</h1><p class="text-body-secondary mb-0">{{ __('msg.booking_all_in_one_intro') }}</p></header>
    <div class="booking-board">
      <section class="booking-board__panel" aria-labelledby="service-panel-title">
        <div class="booking-board__heading"><span>1</span><div><h2 id="service-panel-title">{{ __('msg.choose_service') }}</h2><small>{{ __('msg.booking_select_to_continue') }}</small></div></div>
        <div class="booking-board__scroll" id="altServices">
          @foreach($services as $item)
            <button type="button" class="alt-service booking-option" data-service-id="{{ $item['id'] }}">
              @if(($settings['show_service_images'] ?? true))<img src="{{ $item['image_url'] }}" alt="{{ $item['translation']['name'] ?? $item['name'] }}" width="192" height="128" loading="lazy" decoding="async" class="booking-option__image">@endif
              <span><strong>{{ $item['translation']['name'] ?? $item['name'] }}</strong><small>{{ $item['effective_duration_minutes'] }} {{ __('msg.min') }}</small></span>
              <b>@if($item['price_can_change']){{ __('msg.price_from') }} @endif{{ $item['effective_price'] }} &euro;</b>
            </button>
          @endforeach
        </div>
      </section>
      <section class="booking-board__panel is-locked" id="altMastersPanel" aria-labelledby="master-panel-title">
        <div class="booking-board__heading"><span>2</span><div><h2 id="master-panel-title">{{ __('msg.choose_master') }}</h2><small>{{ __('msg.booking_only_eligible_masters') }}</small></div></div>
        <div class="booking-board__scroll" id="altMasters"><div class="booking-placeholder">{{ __('msg.booking_choose_service_first') }}</div></div>
      </section>
      <section class="booking-board__panel booking-board__calendar is-locked" id="altDatePanel" aria-labelledby="date-panel-title">
        <div class="booking-board__heading"><span>3</span><div><h2 id="date-panel-title">{{ __('msg.choose_date') }}</h2><small>{{ __('msg.booking_live_availability') }}</small></div></div>
        <div class="row g-3"><div class="col-12 col-lg-8"><div id="altCalendar"></div></div><div class="col-12 col-lg-4"><h3 id="altDateName" class="fs-7 mb-3"></h3><div id="altSlots" class="alt-slots"></div></div></div>
      </section>
    </div>
    <aside class="booking-selection-bar" aria-live="polite"><div><small>{{ __('msg.service') }}</small><strong id="altSummaryService">—</strong></div><div><small>{{ __('msg.master') }}</small><strong id="altSummaryMaster">—</strong></div><div><small>{{ __('msg.price') }}</small><strong id="altSummaryPrice">—</strong></div></aside>
  </div>
</section>
@include('pages.booking.templates.alternative-script')
