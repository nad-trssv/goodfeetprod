<section class="booking-template booking-template--compact py-6 py-md-8 ff_secondary" data-booking-template="compact">
  <div class="container-fluid px-3 px-xl-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div><h1 class="fs-4 mb-1">{{ __('msg.menu_booking') }}</h1><p class="text-body-secondary mb-0">{{ __('msg.booking_compact_intro') }}</p></div>
      <div class="booking-progress mb-0" style="width:min(100%,520px)">
        <div class="booking-progress__item is-active" data-progress-step="1"><span class="booking-progress__number">1</span><span class="booking-progress__label">{{ __('msg.choose_service') }}</span></div>
        <div class="booking-progress__item" data-progress-step="2"><span class="booking-progress__number">2</span><span class="booking-progress__label">{{ __('msg.choose_master') }}</span></div>
        <div class="booking-progress__item" data-progress-step="3"><span class="booking-progress__number">3</span><span class="booking-progress__label">{{ __('msg.choose_date') }}</span></div>
      </div>
    </div>

    <div class="row g-3 align-items-start">
      <div id="steps" class="col-12 col-xl-9">
        <div class="bookingSteps">
          <div id="step1" class="step1 booking_form">
            <h2 class="fs-6 mb-3">{{ __('msg.choose_service') }}</h2>
            <div class="row g-2">
              @foreach($services as $item)
                <div class="serviceChoose choose__item" data-duration="{{ $item['effective_duration_minutes'] }}" data-id="{{ $item['id'] }}" data-loop="{{ $loop->index }}" data-price="{{ $item['effective_price'] }}" data-name="{{ $item['translation']['name'] ?? $item['name'] }}">
                  <div class="d-flex justify-content-between gap-2">
                    @if(($settings['show_service_images'] ?? true))<img src="{{ $item['image_url'] }}" alt="{{ $item['translation']['name'] ?? $item['name'] }}" width="192" height="128" loading="lazy" decoding="async" class="booking-option__image">@endif
                    <div class="min-w-0 w-100"><h3 class="fs-9 fw-semibold mb-1" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:3;overflow:hidden;">{{ $item['translation']['name'] ?? $item['name'] }}</h3><span class="text-body-secondary fs-10">{{ $item['effective_duration_minutes'] }} {{ __('msg.min') }}</span></div>
                    <strong class="text-nowrap fs-9">@if($item['price_can_change']){{ __('msg.price_from') }} @endif{{ $item['effective_price'] }} &euro;</strong>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <div id="step2" class="step2">
            <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="fs-6 mb-0">{{ __('msg.choose_master') }}</h2><button class="backButton btn btn-sm btn-outline-secondary" type="button"><i class="fa-solid fa-arrow-left me-1"></i>{{ __('msg.back') }}</button></div>
            <div id="mastersContainer" class="booking_form"></div>
          </div>

          <div id="step3" class="step3">
            <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="fs-6 mb-0">{{ __('msg.choose_date') }}</h2><button class="backButton btn btn-sm btn-outline-secondary" type="button"><i class="fa-solid fa-arrow-left me-1"></i>{{ __('msg.back') }}</button></div>
            <div class="row g-3 booking"><div class="col-12 col-lg-9 booking_calendar"><div class="bookingCalendar calendar-outline fc-theme-standard" id="bookingCalendar"></div></div><aside class="col-12 col-lg-3 booking_time"><h3 id="dateName" class="fs-7 mb-3"></h3><div class="booking_time__bl" id="availableSlots"></div></aside></div>
          </div>
        </div>
      </div>

      <aside id="info" class="col-12 col-xl-3">
        <div class="card"><div class="card-body p-3">
          <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom"><img src="{{ $settings['logo_url'] ?? asset('assets/img/logos/logo.svg') }}" alt="" style="width:48px;height:48px;object-fit:contain"><div class="min-w-0"><strong class="d-block text-truncate">{{ $settings['company_name'] ?? config('app.name') }}</strong><span class="text-body-secondary fs-10">{{ __('msg.booking_details') }}</span></div></div>
          <p id="bookingServiceName" class="mb-2 fs-10"></p><p id="bookingServiceDuration" class="mb-2 fs-10"></p><p id="bookingServicePrice" class="mb-2 fs-10"></p><p id="bookingServiceMaster" class="mb-0 fs-10"></p>
        </div></div>
      </aside>
    </div>
  </div>
</section>
