<section class="booking-template booking-template--wizard pt-6 pt-md-10 pb-10 ff_secondary" data-booking-template="wizard">
  <div class="container">
    <header class="text-center mb-5">
      <span class="badge badge-phoenix badge-phoenix-primary mb-3">{{ __('msg.booking_three_steps') }}</span>
      <h1 class="fs-3 fs-md-2 mb-2">{{ __('msg.menu_booking') }}</h1>
      <p class="text-body-secondary">{{ __('msg.booking_wizard_intro') }}</p>
      <div class="booking-progress mt-4" aria-label="Booking progress">
        <div class="booking-progress__item is-active" data-progress-step="1"><span class="booking-progress__number">1</span><span class="booking-progress__label">{{ __('msg.choose_service') }}</span></div>
        <div class="booking-progress__item" data-progress-step="2"><span class="booking-progress__number">2</span><span class="booking-progress__label">{{ __('msg.choose_master') }}</span></div>
        <div class="booking-progress__item" data-progress-step="3"><span class="booking-progress__number">3</span><span class="booking-progress__label">{{ __('msg.choose_date') }}</span></div>
      </div>
    </header>

    <div class="row g-4 align-items-start">
      <div id="steps" class="col-12 col-xl-8">
        <div class="bookingSteps">
          <div id="step1" class="step1 booking_form">
            <h2 class="fs-4 mb-4">{{ __('msg.choose_service') }}</h2>
            <div class="row g-3">
              @foreach($services as $item)
                <div class="serviceChoose choose__item col-12" data-duration="{{ $item['effective_duration_minutes'] }}" data-id="{{ $item['id'] }}" data-loop="{{ $loop->index }}" data-price="{{ $item['effective_price'] }}" data-name="{{ $item['translation']['name'] ?? $item['name'] }}">
                  <div class="d-flex justify-content-between gap-3 align-items-center">
                    <div>
                      <h3 class="fs-7 fw-semibold mb-2">{{ $item['translation']['name'] ?? $item['name'] }}</h3>
                      <div class="d-flex flex-wrap gap-3 text-body-secondary fs-9">
                        <span><i class="fa-regular fa-clock me-1"></i>{{ $item['effective_duration_minutes'] }} {{ __('msg.min') }}</span>
                        <span><i class="fa-solid fa-tag me-1"></i>@if($item['price_can_change']){{ __('msg.price_from') }} @endif{{ $item['effective_price'] }} &euro;</span>
                      </div>
                    </div>
                    <span class="d-flex align-items-center justify-content-center rounded-circle bg-body-secondary" style="width:42px;height:42px"><i class="fa-solid fa-arrow-right"></i></span>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <div id="step2" class="step2">
            <h2 class="fs-4 mb-4">{{ __('msg.choose_master') }}</h2>
            <div id="mastersContainer" class="booking_form"></div>
            <button class="backButton btn btn-link px-0 mt-3" type="button"><i class="fa-solid fa-arrow-left me-2"></i>{{ __('msg.back') }}</button>
          </div>

          <div id="step3" class="step3">
            <div class="d-flex align-items-center gap-3 mb-4"><button class="backButton btn btn-outline-secondary btn-sm" type="button"><i class="fa-solid fa-arrow-left"></i></button><h2 class="fs-4 mb-0">{{ __('msg.choose_date') }}</h2></div>
            <div class="row g-4 booking">
              <div class="col-12 col-lg-8 booking_calendar"><div class="bookingCalendar calendar-outline fc-theme-standard" id="bookingCalendar"></div></div>
              <aside class="col-12 col-lg-4 booking_time"><h3 id="dateName" class="fs-6 mb-3"></h3><div class="booking_time__bl" id="availableSlots"></div></aside>
            </div>
          </div>
        </div>
      </div>

      <aside id="info" class="col-12 col-xl-4">
        <div class="card"><div class="card-body p-4">
          <div class="d-flex align-items-center gap-3 mb-4"><img src="{{ $settings['logo_url'] ?? asset('assets/img/logos/logo.svg') }}" alt="{{ $settings['company_name'] ?? config('app.name') }}" style="max-width:120px;max-height:55px"><div><strong>{{ $settings['company_name'] ?? config('app.name') }}</strong>@if(!empty($settings['company_address']))<div class="text-body-secondary fs-10">{{ $settings['company_address'] }}</div>@endif</div></div>
          <h3 class="fs-7 mb-3">{{ __('msg.booking_your_choice') }}</h3>
          <p id="bookingServiceName" class="mb-2 fs-9"></p><p id="bookingServiceDuration" class="mb-2 fs-9"></p><p id="bookingServicePrice" class="mb-2 fs-9"></p><p id="bookingServiceMaster" class="mb-0 fs-9"></p>
        </div></div>
      </aside>
    </div>
  </div>
</section>
