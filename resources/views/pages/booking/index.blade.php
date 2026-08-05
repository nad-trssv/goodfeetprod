@section('title', 'GoodFeet Booking')
@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/et.js"></script>
<style>
  .booking-template .booking-template__intro { display:none; }
  .booking-template--wizard .booking-template__intro,
  .booking-template--compact .booking-template__intro { display:block; }
  .booking-progress { display:flex; justify-content:center; gap:0; max-width:760px; margin:0 auto 2rem; }
  .booking-progress__item { flex:1; position:relative; text-align:center; color:#7d8590; font-size:.875rem; }
  .booking-progress__item:not(:last-child)::after { content:""; position:absolute; height:2px; background:#d8dde3; top:17px; left:58%; right:-42%; }
  .booking-progress__number { display:flex; width:36px; height:36px; margin:0 auto .5rem; align-items:center; justify-content:center; border-radius:50%; border:2px solid #d8dde3; background:#fff; position:relative; z-index:1; font-weight:700; }
  .booking-progress__item.is-active, .booking-progress__item.is-complete { color:var(--phoenix-primary); }
  .booking-progress__item.is-active .booking-progress__number, .booking-progress__item.is-complete .booking-progress__number { border-color:var(--phoenix-primary); }
  .booking-progress__item.is-active .booking-progress__number { background:var(--phoenix-primary); color:#fff; }
  .booking-progress__item.is-complete::after { background:var(--phoenix-primary); }

  .booking-template--wizard { background:linear-gradient(180deg, rgba(238,242,246,.9), #fff 420px); }
  .booking-template--wizard > .row { max-width:1180px; margin:auto; }
  .booking-template--wizard #steps { margin:auto; }
  .booking-template--wizard .bookingSteps { background:#fff; border-radius:24px; padding:2rem; box-shadow:0 18px 60px rgba(28,39,49,.09); }
  .booking-template--wizard .choose__item { border:1px solid #e1e6eb; border-radius:16px; padding:1rem 1.25rem; transition:.2s ease; }
  .booking-template--wizard .choose__item:hover { transform:translateY(-2px); border-color:var(--phoenix-primary); box-shadow:0 10px 25px rgba(28,39,49,.08); }
  .booking-template--wizard #info { margin:auto; }
  .booking-template--wizard #info .card { border:0; border-radius:20px; box-shadow:0 12px 35px rgba(28,39,49,.08); }

  .booking-template--compact { background:#f7f8fa; }
  .booking-template--compact > .row { max-width:1400px; margin:auto; align-items:flex-start; }
  .booking-template--compact .bookingSteps { background:#fff; border:1px solid #e3e6ea; border-radius:14px; padding:1.25rem; }
  .booking-template--compact #step1 > .row:last-child,
  .booking-template--compact #mastersContainer { display:grid !important; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; }
  .booking-template--compact .choose__item { width:100% !important; margin:0 !important; border:1px solid #e3e6ea; border-radius:10px; padding:.75rem; cursor:pointer; }
  .booking-template--compact .choose__item:hover { border-color:var(--phoenix-primary); background:rgba(var(--phoenix-primary-rgb),.04); }
  .booking-template .choose__item.is-selected { border-color:var(--phoenix-primary); box-shadow:0 0 0 2px rgba(var(--phoenix-primary-rgb),.12); }
  .booking-template--compact #info .card { position:sticky; top:110px; border-radius:14px; }
  .booking-template--compact .booking_calendar { margin-top:0; }
  @media(max-width:767px) {
    .booking-template--wizard .bookingSteps { padding:1rem; border-radius:16px; }
    .booking-template--compact #step1 > .row:last-child,
    .booking-template--compact #mastersContainer { grid-template-columns:1fr; }
    .booking-progress__label { font-size:.72rem; }
  }
  .booking-alternative { background:#f6f7f9; min-height:70vh; }
  .booking-template, .booking-template *, .booking-alternative, .booking-alternative * { box-sizing:border-box; }
  .booking-template, .booking-alternative { max-width:100%; overflow-x:clip; }
  .booking-template .row, .booking-alternative .row { max-width:100%; }
  .booking-template img, .booking-alternative img { max-width:100%; height:auto; }
  .booking-alternative button, .booking-alternative strong, .booking-alternative small, .booking-alternative h1, .booking-alternative h2, .booking-alternative h3, .booking-alternative p { min-width:0; overflow-wrap:anywhere; }
  .booking-board { display:grid; grid-template-columns:minmax(260px,.8fr) minmax(260px,.8fr) minmax(480px,1.6fr); gap:1rem; align-items:start; }
  .booking-board__panel, .master-directory, .master-workspace { background:#fff; border:1px solid #e1e5ea; border-radius:18px; padding:1.25rem; }
  .booking-board__panel, .master-directory, .master-workspace, #altCalendar, #altSlots { min-width:0; max-width:100%; }
  #altCalendar { overflow-x:auto; }
  #altCalendar .fc-toolbar { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; }
  #altCalendar .fc-toolbar > * { float:none; min-width:0; }
  #altCalendar .fc-left { flex:1 1 150px; }
  #altCalendar .fc-right { flex:0 0 auto; }
  #altCalendar .fc-view-container { min-width:280px; }
  #altCalendar .fc-row table { width:100% !important; table-layout:fixed !important; }
  #altCalendar td.fc-day,
  #altCalendar td.fc-day-top,
  #altCalendar th.fc-day-header { width:14.285714% !important; max-width:14.285714% !important; overflow:hidden; }
  #altCalendar td.fc-day-top { padding:0 !important; }
  #altCalendar td.fc-day-top .fc-day-number {
    float:none !important;
    display:flex !important;
    width:100% !important;
    min-width:0 !important;
    height:2.35rem;
    padding:.45rem .2rem !important;
    align-items:center;
    justify-content:center;
    border-radius:0;
    overflow:hidden;
  }
  #altCalendar { position:relative; }
  #altCalendar.is-loading-availability::after {
    content:'';
    position:absolute;
    top:.85rem;
    right:5.5rem;
    width:1rem;
    height:1rem;
    border:2px solid rgba(61,116,255,.2);
    border-top-color:#3d74ff;
    border-radius:50%;
    animation:booking-calendar-spin .7s linear infinite;
    pointer-events:none;
  }
  @keyframes booking-calendar-spin { to { transform:rotate(360deg); } }
  #altCalendar td.disabled-day { background:rgba(125,133,144,.08); cursor:not-allowed; pointer-events:none; }
  #altCalendar td.disabled-day .fc-day-number { text-decoration:line-through; opacity:.38; }
  .booking-template #bookingCalendar td.disabled-day { background:rgba(125,133,144,.08); cursor:not-allowed; pointer-events:none; }
  .booking-template #bookingCalendar td.disabled-day .fc-day-number { text-decoration:line-through; opacity:.38; }
  #altCalendar td.selected-day { box-shadow:inset 0 0 0 2px var(--phoenix-primary); }
  .booking-board__panel.is-locked, .master-workspace.is-locked { opacity:.48; pointer-events:none; }
  .booking-board__heading, .master-workspace__title { display:flex; gap:.75rem; align-items:center; margin-bottom:1rem; }
  .booking-board__heading > span, .master-workspace__title > span { display:flex; width:34px; height:34px; flex:0 0 34px; align-items:center; justify-content:center; border-radius:50%; background:var(--phoenix-primary); color:#fff; font-weight:700; }
  .booking-board__heading h2, .master-workspace__title h2 { font-size:1rem; margin:0; }
  .booking-board__heading small, .master-workspace__title p { color:#77808b; font-size:.75rem; margin:0; }
  .booking-board__scroll { max-height:520px; overflow:auto; padding-right:.25rem; }
  .booking-option, .master-directory__item { display:flex; width:100%; border:1px solid #e3e7eb; background:#fff; border-radius:12px; padding:.85rem; margin-bottom:.6rem; text-align:left; justify-content:space-between; align-items:center; gap:.75rem; transition:.18s ease; }
  .booking-option:hover, .booking-option.is-selected, .master-directory__item:hover, .master-directory__item.is-selected { border-color:var(--phoenix-primary); background:rgba(var(--phoenix-primary-rgb),.045); box-shadow:0 0 0 2px rgba(var(--phoenix-primary-rgb),.1); }
  .booking-option span, .master-directory__item span:nth-child(2) { min-width:0; }
  .booking-option__image { width:64px; height:48px; flex:0 0 64px; border-radius:8px; object-fit:cover; }
  .master-directory__photo { width:42px; height:42px; flex:0 0 42px; border-radius:50%; object-fit:cover; }
  .booking-option strong, .booking-option small, .master-directory__item strong, .master-directory__item small { display:block; }
  .booking-option small, .master-directory__item small { color:#77808b; font-size:.72rem; margin-top:.2rem; }
  .booking-placeholder { padding:2rem 1rem; text-align:center; color:#8a929b; border:1px dashed #ccd2d8; border-radius:12px; }
  .booking-selection-bar { position:sticky; bottom:1rem; z-index:10; display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-top:1rem; padding:1rem 1.25rem; background:rgba(25,31,38,.94); color:#fff; border-radius:16px; box-shadow:0 12px 35px rgba(0,0,0,.18); backdrop-filter:blur(10px); }
  #altMastersPanel, #altServicesPanel, #altDatePanel { scroll-margin-top:90px; }
  .booking-selection-bar small, .booking-selection-bar strong { display:block; }
  .booking-selection-bar small { opacity:.65; font-size:.68rem; text-transform:uppercase; }
  .booking-selection-bar strong { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .alt-slots { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.5rem; }
  .alt-slot { border:1px solid var(--phoenix-primary); color:var(--phoenix-primary); background:#fff; border-radius:9px; padding:.65rem .4rem; font-weight:700; }
  .alt-slot:hover { background:var(--phoenix-primary); color:#fff; }
  .booking-flow-pills { display:flex; justify-content:flex-end; gap:.5rem; flex-wrap:wrap; }
  .booking-flow-pills span { padding:.55rem .85rem; border-radius:999px; background:#e8ebef; color:#6d7580; font-size:.78rem; }
  .booking-flow-pills span.is-active { background:var(--phoenix-primary); color:#fff; }
  .master-directory { position:sticky; top:105px; }
  .master-directory__avatar { display:flex; width:42px; height:42px; flex:0 0 42px; border-radius:50%; align-items:center; justify-content:center; background:#eef1f5; font-weight:700; }
  .master-directory__item i { margin-left:auto; }
  .service-tiles { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; }
  .service-tiles .booking-option { margin:0; }
  @media(max-width:1199px) { .booking-board { grid-template-columns:1fr 1fr; }.booking-board__calendar { grid-column:1/-1; }.master-directory { position:static; } }
  @media(max-width:767px) { .booking-board { grid-template-columns:minmax(0,1fr); }.booking-board__calendar { grid-column:auto; }.service-tiles { grid-template-columns:minmax(0,1fr); }.booking-selection-bar { position:relative; grid-template-columns:minmax(0,1fr); gap:.45rem; bottom:auto; width:100%; }.booking-board__scroll { max-height:none; }.booking-flow-pills { justify-content:flex-start; }.alt-slots { grid-template-columns:repeat(3,minmax(0,1fr)); }.booking-template .row, .booking-alternative .row { width:100%; margin-left:0; margin-right:0; }.booking-template [class*="col-"], .booking-alternative [class*="col-"] { min-width:0; padding-left:.4rem; padding-right:.4rem; } }
  @media(max-width:420px) { .alt-slots { grid-template-columns:repeat(2,minmax(0,1fr)); }.booking-board__panel, .master-directory, .master-workspace { padding:1rem; border-radius:14px; } }
</style>
@endpush
<x-app-layout>
      @if($bookingTemplate === 'classic')
        @include('pages.booking.templates.single-page')
      @elseif($bookingTemplate === 'wizard')
        @include('pages.booking.templates.master-first')
      @else
        @include('pages.booking.templates.compact')
      @endif

      @if(false)
      @if($bookingTemplate === 'classic')
      <section class="booking-template booking-template--classic pt-6 pt-md-10 pb-10 calendar_bg ff_secondary" data-booking-template="classic">
        <div class="booking-template__intro container mb-5 text-center">
          <h1 class="fs-4 fs-md-2 mb-2">{{ __('msg.menu_booking') }}</h1>
          <p class="text-body-secondary mb-4">{{ __('msg.choose_service') }} · {{ __('msg.choose_master') }} · {{ __('msg.choose_date') }}</p>
          <div class="booking-progress" aria-label="Booking progress">
            <div class="booking-progress__item is-active" data-progress-step="1"><span class="booking-progress__number">1</span><span class="booking-progress__label">{{ __('msg.choose_service') }}</span></div>
            <div class="booking-progress__item" data-progress-step="2"><span class="booking-progress__number">2</span><span class="booking-progress__label">{{ __('msg.choose_master') }}</span></div>
            <div class="booking-progress__item" data-progress-step="3"><span class="booking-progress__number">3</span><span class="booking-progress__label">{{ __('msg.choose_date') }}</span></div>
          </div>
        </div>
        <div class="row col-12 d-flex justify-content-end">
          <div id="steps" class="row col-12 col-xl-8 p-0">
            <div class="bookingSteps">
              <div id="step1" class="step1 container-small d-flex row col-12 booking_form justify-content-center">
                <div class="container-small row">
                  <h3>{{ __('msg.choose_service') }}</h3>
                </div>
                <div class="row col-12 w-100">
                  @foreach ($services as $item)
                  <div class="serviceChoose choose__item row col-12 g-3 mb-4 w-100"
                    data-duration="{{ $item['effective_duration_minutes'] }}"
                    data-id="{{ $item['id'] }}"
                    data-loop="{{ $loop->index }}"
                    data-price="{{ $item['effective_price'] }}"
                    data-name="{{ (isset($item['translation']['name'])) ? $item['translation']['name']: $item['name'] }}">
                      <div class="col-11 m-0">
                        <div class="row flex-lg-nowrap g-3 mb-2 mt-0">
                            <h4 class="mb-0 fw-medium fs-8 m-0">
                              @if (isset($item->translation['name']))
                                {{ $item->translation['name'] }}
                              @else
                                {{ $item['name'] }}
                              @endif
                              @if ($item['has_fixed_time'] == 1)
                                (в {{ $item['time_from'] }})
                              @endif
                            </h4>
                        </div>
                        @if ($item['has_fixed_time'] == 1)
                          <div class="mb-0 d-flex align-items-center gap-2">
                            <p class="d-flex align-items-center justify-content-start gap-1 fs-8 fw-light mb-0">
                              {{ __('msg.booking_available_only_in') }} {{ $item['time_from'] }}
                            </p>
                          </div>
                        @endif
                        <div class="mb-0 d-flex align-items-center gap-2">
                          <p class="d-flex align-items-center justify-content-start gap-1 fs-8 fw-light mb-0">
                            {{ $item['effective_duration_minutes'] }} {{ __('msg.min') }}
                          </p>
                          <h3 class="d-flex align-items-center justify-content-start gap-1 fs-8 fw-light mb-0">
                            @if ($item['price_can_change'])
                              <span>{{ __('msg.price_from') }} </span>
                            @endif
                              {{ $item['effective_price'] }}
                            <span>&euro;</span>
                          </h3>
                        </div>
                        
                        @if (!empty($item['next_rule']))
                        <p class="mb-0 mt-1 fs-9 text-danger fw-semibold">
                          <i class="fa-solid fa-triangle-exclamation me-1"></i>
                          {{ __('msg.service_changes_from') }}
                          {{ \Carbon\Carbon::parse($item['next_rule']['valid_from'])->format('d.m.Y') }}:
                          {{ $item['next_rule']['duration_minutes'] }} {{ __('msg.min') }},
                          {{ $item['next_rule']['price'] }} €
                        </p>
                        
                        @endif                        
                      </div>
                      <div class="col-1 d-flex align-items-center justify-content-end m-0">
                        <i class="fa-solid fa-chevron-right"></i>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
              
              <div id="step2" class="step2">
                <div class="container-small row">
                  <h3>{{ __('msg.choose_master') }}</h3>
                </div>
                <div id="mastersContainer" class="container-small d-flex row col-12 booking_form"></div>
                <div class="step_arrows container-small d-flex row justify-content-between w-100">
                  <div class="backButton col-1 d-flex align-items-center justify-content-start m-0 cursor-pointer">
                    <i class="fa-solid fa-chevron-left"></i> {{ __('msg.back') }}
                  </div>
                </div>
              </div>
      
              <div id="step3" class="step3">
                <div class="step_arrows container-small d-flex row justify-content-between w-100 mb-4">
                  <div class="backButton col-12 gap-4 d-flex align-items-center justify-content-start m-0 cursor-pointer">
                    <div><i class="fa-solid fa-chevron-left mr-4"></i> {{ __('msg.back') }}</div>
                    <h3>{{ __('msg.choose_date') }}</h3>
                  </div>
                </div>
      
                <div class="container-small d-flex row col-12 booking">
                  <div class="col-12 col-lg-8 booking_calendar">
                    <div class="bookingCalendar calendar-outline mt-6 mb-9 fc-theme-standard" id="bookingCalendar"></div>
                  </div>
                  <div class="col-12 col-lg-4 booking_time">
                      <h3 id="dateName" class="mb-4 fw-light text-nowrap"></h3>
                      <div class="booking_time__bl flex" id="availableSlots"></div>
                  </div>
                </div>
                <div class="step_arrows container-small d-flex row justify-content-between w-100 mt-4">
                  <div class="backButton col-1 d-flex align-items-center justify-content-start m-0 cursor-pointer">
                    <i class="fa-solid fa-chevron-left"></i> {{ __('msg.back') }}
                  </div>
                </div>
              </div>
      
            </div>
          </div>
          <div id="info" class="row col-12 col-xl-4">
            <div class="card mt-5 mt-lg-0">
              <div class="card-body d-flex flex-column align-items-between">
                <img class="rounded-2 mb-3 mx-auto logoLarge logoContainer" src="{{ $settings['logo_url'] ?? asset('assets/img/logos/logo.svg') }}" alt="{{ $settings['company_name'] ?? config('app.name') }}" width="60%" />
                <p class="mb-1 text-body-tertiary fs-7 text-center">{{ $settings['company_name'] ?? config('app.name') }}</p>
                @if(!empty($settings['company_address']))<p class="mb-1 text-body-tertiary fs-9 text-center">{{ $settings['company_address'] }}</p>@endif
                <p class="mb-5 text-body-tertiary fs-9 text-center">
                  <span class="fw-semibold">{{ __('msg.book__working_hourstitle') }}: </span>
                  {{ __('msg.book__working_hours') }}
                </p>
                
                <p id="bookingServiceName" class="mb-1 text-body-tertiary fs-9"></p>
                <p id="bookingServiceDuration" class="mb-1 text-body-tertiary fs-9"></p>
                <p id="bookingServicePrice" class="mb-1 text-body-tertiary fs-9"></p>
                <p id="bookingServiceMaster" class="mb-1 text-body-tertiary fs-9"></p>
              </div>
            </div>
          </div>
        </div>
      </section>
      @elseif($bookingTemplate === 'wizard')
        @include('pages.booking.templates.wizard')
      @else
        @include('pages.booking.templates.compact')
      @endif
      @endif
      
    @if($bookingTemplate === 'compact')
    @push('scripts')
    <script>
        $(document).ready(function() {
          function updateServiceInfoForDate(serviceId, dateStr) {
              if (!serviceId || !dateStr) return;
              $.ajax({
                  url: "{{ route('booking.service.effective') }}",
                  type: "POST",
                  dataType: "json",
                  data: {
                      service_id: serviceId,
                      date: dateStr
                  },
                  headers: {
                      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                  },
                  success: function (data) {
                      $('#bookingServiceDuration').html(
                          `<span class="fw-semibold">${durationText}: </span>${data.duration_minutes} ${minText}`
                      );

                      if (data.price_can_change === 1) {
                          $('#bookingServicePrice').html(
                              `<span class="fw-semibold">${priceText}: </span> ${price_fromText} ${data.price} &euro;`
                          );
                      } else {
                          $('#bookingServicePrice').html(
                              `<span class="fw-semibold">${priceText}: </span>${data.price} &euro;`
                          );
                      }
                  }
              });
          }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
            
            const serviceText = @json(__('msg.service'));
            const durationText = @json(__('msg.duration'));
            const priceText = @json(__('msg.price'));
            const price_fromText = @json(__('msg.price_from'));
            const chooseserviceText = @json(__('msg.choose_service_please'));
            const chooseserviceandmasterText = @json(__('msg.choose_serviceandmaster_please'));
            const bookingerrorText = @json(__('msg.choose_bookingerrorserviceandmaster_please'));
            const masterText = @json(__('msg.master'));
            const choosemasterText = @json(__('msg.choose_master_please'));
            const minText = @json(__('msg.min'));

            let bookingStep = 1;
            let service_id = null;
            let appointment_start = null;
            let appointment_end = null;
            let user_id = null;
            let price = null;
            let choose_date = null;
            let choose_price = null;
            var bookedSlots = [];
            var compactSlotsRequest = null;
            var compactSelectedDate = null;

            var services = @json($services);
            const showMasterImages = @json((bool)($settings['show_master_images'] ?? true));
            var redDays = @json($redDays); 
            var partiallyOpenDays = @json($redDaysTime);
            var bookLimit = @json($bookLimit);
            var today = @json($today);
            var limitDate = @json($limitDate);
            var workHours = @json($workHours);
            
            var dayMap = {
                "esmaspäev": "monday",
                "teisipäev": "tuesday",
                "kolmapäev": "wednesday",
                "neljapäev": "thursday",
                "reede": "friday",
                "laupäev": "saturday",
                "pühapäev": "sunday"
            };
            var busyDays = [];

            function updateBookingProgress() {
              $('.booking-progress__item').each(function() {
                const step = Number($(this).data('progressStep'));
                $(this).toggleClass('is-active', step === bookingStep);
                $(this).toggleClass('is-complete', step < bookingStep);
              });
            }

            let chooseService = @json($chooseService);

            if (chooseService !== null) {
                const container = document.getElementById("mastersContainer");
                $(container).empty();
                
                chooseService['users'].forEach(user => {
                    const masterBlock = document.createElement("div");
                    masterBlock.className = "masterChoose choose__item row col-12 g-3 mb-4";
                    masterBlock.dataset.userId = user.id;
                    masterBlock.dataset.userName = user.name;
                    masterBlock.innerHTML = `
                        <div class="col-11 m-0">
                            <div class="m-0">
                                  <h4 class="mb-0 fw-medium fs-8 m-0">${user.name}</h4>
                                  <small class="text-body-secondary">${user.professional_title || ''}</small>
                            </div>
                        </div>
                        <div class="col-1 d-flex align-items-center justify-content-end m-0">
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                    `;
                    container.appendChild(masterBlock);
                });

                service_id = chooseService['id'];
                choose_price = chooseService['effective_price'];

                $('#bookingServiceName').html(`<span class="fw-semibold">${serviceText}: </span>${chooseService['name']}`);
                $('#bookingServiceDuration').html(`<span class="fw-semibold">${durationText}: </span>${chooseService['effective_duration_minutes']} ${minText}`);

                if (chooseService['price_can_change'] === 1) {
                    $('#bookingServicePrice').html(
                      `<span class="fw-semibold">${priceText}: </span> ${price_fromText} ${chooseService['effective_price']} &euro;`
                    );
                } else {
                    $('#bookingServicePrice').html(
                      `<span class="fw-semibold">${priceText}: </span>${chooseService['effective_price']} &euro;`
                    );
                }
                nextStep();
            }

            var closedDays = redDays.map(function(day) {
                return day.date; 
            });

            function nextStep(){
              bookingStep++;
              updateBookingProgress();
              stepRules(bookingStep, 'inc');
              scrollToCurrentStep();
            }
            function prevStep(){
              bookingStep--;
              updateBookingProgress();
              stepRules(bookingStep, 'dec');
              scrollToTop();
            }
            function scrollToCurrentStep() {
                const isMobile = window.matchMedia('(max-width: 767px)').matches;
                if (!isMobile && bookingStep !== 3) return;

                window.setTimeout(function () {
                    document.getElementById(`step${bookingStep}`)?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 550);
            }

            function scrollToTop() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            function stepRules(step, operator){
              let $step1 = $("#step1");
              let $step2 = $("#step2");
              let $step3 = $("#step3");
              
                if (step === 2 && operator === 'inc') {
                  if ($step1.length && $step2.length) {
                      $step1.animate({
                          opacity: 0,
                          right: "100%",
                      }, 500, function () {
                          $step1.css({ position: "absolute" });
                          $step1.hide();
                          
                          $step2.css({ right: "-100%", display: "block", opacity: 0, position: "relative" });

                          $step2.animate({
                              opacity: 1,
                              right: "0"
                          }, 500);
                      });
                  }
                }
                if (step === 1 && operator === 'dec') {
                  $('#bookingServiceName').html('');
                  $('#bookingServiceDuration').html('');
                  $('#bookingServicePrice').html('');
                  $step2.animate({
                      opacity: 0,
                      right: "-100%"
                  }, 500, function () {
                      $step2.css({ position: "absolute" });
                      $step2.hide();
                      $step1.css({ right: "-100%", display: "block", opacity: 0, position: "relative" });
                      $step1.animate({
                          opacity: 1,
                          right: "0"
                      }, 500);
                  });
                }
                if(step === 3 && operator === 'inc'){
                  renderBusyDays(service_id, user_id);
                  if ($step2.length && $step3.length) {
                      $step2.animate({
                          opacity: 0,
                          right: "100%"
                      }, 500, function () {
                          $step2.css({ position: "absolute" });
                          $step2.hide();
                          
                          $step3.css({ right: "-100%", display: "block", opacity: 0, position: "relative" });

                          $step3.animate({
                              opacity: 1,
                              right: "0",
                          }, 500);
                      });
                  }
                }
                if (step === 2 && operator === 'dec') {
                  $('#bookingServiceMaster').html('');
                  $step3.animate({
                      opacity: 0,
                      right: "-100%"
                  }, 500, function () {
                      $step3.css({ position: "absolute" });
                      $step3.hide();

                      $step2.css({ right: "-100%", display: "block", opacity: 0, position: "relative" });
                      $step2.animate({
                          opacity: 1,
                          right: "0"
                      }, 500);
                  });
                }
            }
            $(".backButton").on("click", function () {
              prevStep();
            });
            
            $('.serviceChoose').on('click', function () {
                    $('.serviceChoose').removeClass('is-selected');
                    $(this).addClass('is-selected');
                    const loop = $(this).data('loop'); 
                    const name = $(this).data('name'); 
                    service_id = $(this).data('id'); 

                    if (service_id !== "null") {
                      const service = services[loop];
                      choose_price = service['effective_price'];

                      const container = document.getElementById("mastersContainer");
                      $(container).empty();
                      service.users.forEach(user => {
                          const masterBlock = document.createElement("div");
                          masterBlock.className = "masterChoose choose__item row col-12 g-3 mb-4";
                          masterBlock.dataset.userId = user.id;
                          masterBlock.dataset.userName = user.name;
                          masterBlock.innerHTML = `
                              ${showMasterImages ? `<div class="col-auto m-0"><img src="${user.profile_photo_url}" alt="${user.name}" width="84" height="84" loading="lazy" decoding="async" class="master-directory__photo"></div>` : ''}
                              <div class="col-11 m-0">
                                  <div class="m-0">
                                      <h4 class="mb-0 fw-medium fs-8 m-0">${user.name} ${user.vacation ? '🌴' : ''}</h4>
                                      <small class="text-body-secondary">${user.professional_title || ''}</small>
                                      ${user.vacation ? `<small class="d-block text-body-secondary">🌴 В отпуске ${user.vacation.from}–${user.vacation.to}</small>` : ''}
                                  </div>
                              </div>
                              <div class="col-1 d-flex align-items-center justify-content-end m-0">
                                  <i class="fa-solid fa-chevron-right"></i>
                              </div>
                          `;
                          container.appendChild(masterBlock);
                      });

                      $('#bookingServiceName').html(`<span class="fw-semibold">${serviceText}: </span>${name}`);
                      $('#bookingServiceDuration').html(
                        `<span class="fw-semibold">${durationText}: </span>${service['effective_duration_minutes']} ${minText}`
                      );
                      if (service['price_can_change'] === 1) {
                        $('#bookingServicePrice').html(
                          `<span class="fw-semibold">${priceText}: </span> ${price_fromText} ${service['effective_price']} &euro;`
                        );
                      } else {
                        $('#bookingServicePrice').html(
                          `<span class="fw-semibold">${priceText}: </span>${service['effective_price']} &euro;`
                        );
                      }
                      nextStep();
                    } else {
                      $('#userSelect').empty();
                      document.getElementById('userSelectWrapper').style.display = 'none';
                      Swal.fire({
                            position: "top",
                            icon: "warning",
                            title: chooseserviceText,
                            showConfirmButton: false,
                            iconColor: '#d37d17',
                            timerProgressBar: true,
                            timer: 1300,
                      });
                    }
            });

            $('#mastersContainer').on('click', '.masterChoose', function (e) {
              e.preventDefault();
              $('.masterChoose').removeClass('is-selected');
              $(this).addClass('is-selected');
              user_id= $(this).data('userId');
              user_name= $(this).data('userName');
              
              if (service_id === "null" || user_id === "null") {
                  Swal.fire({
                      position: "top",
                      icon: "warning",
                      title: chooseserviceandmasterText,
                      showConfirmButton: false,
                      iconColor: '#d37d17',
                      timerProgressBar: true,
                      timer: 1300,
                  });
                  return;
              }
              $('#bookingServiceMaster').html(`<span class="fw-semibold">${masterText}: </span>${user_name}`);
              nextStep();
            });

            function renderSlots(service_id, user_id, choose_date){
                compactSelectedDate = choose_date;
                if (compactSlotsRequest) compactSlotsRequest.abort();
                moment.locale('et');
                let formattedDate = moment(choose_date).format('dddd D MMMM');
                $('#dateName').text(formattedDate);

                if (closedDays.includes(choose_date)) {
                        return false;
                } else {
                  compactSlotsRequest = $.ajax({
                    url: "{{ route('booking.slots') }}",
                    type:"POST",
                    dataType: 'json',
                    data:{ service_id, user_id, choose_date},
                    success:function(response)
                    {
                      if (compactSelectedDate !== choose_date) return;
                      bookedSlots = response.map(item => ({
                          start: item.start,
                          end: item.end,
                      }));
                      
                      $('#availableSlots').empty();
                      if(bookedSlots.length === 0){
                        if (!busyDays.includes(choose_date)) busyDays.push(choose_date);
                        $(`#bookingCalendar td[data-date="${choose_date}"]`).addClass('disabled-day');
                        $('#availableSlots').html(`<div class="alert-message">${bookingerrorText}</div>`);
                      }

                      bookedSlots.forEach(slot => {
                          $('#availableSlots').append(`<div class="slot-time" data-start="${slot.start}" data-end="${slot.end}">${slot.start}</div>`);
                      });
                      $('.slot-time').on('click', function () {
                          appointment_start = $(this).data('start');
                          appointment_end = $(this).data('end');
                          
                          let url = `/booking/create?start=${appointment_start}&end=${appointment_end}&user_id=${user_id}&service_id=${service_id}&choose_date=${choose_date}`;
                          window.location.href = url;
                      });
                    },
                    error:function(xhr, status)
                    {
                      if (status === 'abort' || compactSelectedDate !== choose_date) return;
                      if (!busyDays.includes(choose_date)) busyDays.push(choose_date);
                      $(`#bookingCalendar td[data-date="${choose_date}"]`).addClass('disabled-day').removeClass('selected-day');
                      $('#availableSlots').html(`<div class="alert-message">${bookingerrorText}</div>`);
                    },
                  });
                }
            }
            function calendarRender() { 
                var currentDate = moment().format('YYYY-MM-DD');
                if (!busyDays.includes(currentDate) && !closedDays.includes(currentDate)) {
                    renderSlots(service_id, user_id, currentDate);
                }

                $('#bookingCalendar').fullCalendar({
                  header:{
                    left:'title',
                    center:'',
                    right:'prev, next'
                  },
                  firstDay: 1,
                  editable: false,
                  selectable: true,
                  dayRender: function (date, cell) {
                    var dateStr = date.format('YYYY-MM-DD');

                    
                    if (busyDays.includes(dateStr)) {
                        $('td[data-date="' + dateStr + '"]').addClass('disabled-day');
                    }

                    // workHours теперь определяется через busyDays для конкретного мастера
                    // поэтому этот блок убираем

                    if (closedDays.includes(dateStr)) {
                      $('td[data-date="' + dateStr + '"]').addClass('disabled-day'); 
                    } 

                    if (date.isAfter(limitDate)) { 
                      $('td[data-date="' + dateStr + '"]').addClass('disabled-day');
                    }
                  },
                  viewRender: function(view, element) {
                    updateNavigationButtons(view);
                    var currentMonth = view.intervalStart.format('MMMM YYYY');
                    var isCurrentMonth = moment().isSame(view.intervalStart, 'month');

                    if (isCurrentMonth) {
                      $('.fc-prev-button').attr('disabled', true).addClass('prev_notAllowed');
                    } else {
                      $('.fc-prev-button').attr('disabled', false).removeClass('prev_notAllowed');
                    }
                  },
                  dayClick: function(date, jsEvent, view) {
                      choose_date = date.format('YYYY-MM-DD');

                      if (choose_date) {
                        $('td.fc-day-top').removeClass('fc-choosed');
                        $('td[data-date="' + choose_date + '"]').addClass('fc-choosed');
                      }

                      if(user_id == null){
                          Swal.fire({
                              position: "top",
                              icon: "warning",
                              title: choosemasterText,
                              showConfirmButton: false,
                              iconColor: '#d37d17',
                              timerProgressBar: true,
                              timer: 1300,
                          });
                          return false;
                      }

                      if(choose_date < today || date.isAfter(limitDate) || closedDays.includes(choose_date)){
                          $('#availableSlots').html('<div class="alert-message">{{ __('msg.validation_busyerror') }}</div>');
                          jsEvent.stopPropagation();
                          return false;
                      }

                      var openDay = partiallyOpenDays.find(d => d.date === choose_date);
                      if (openDay) {
                          var now = moment();
                          var startTime = moment(openDay.start_time, 'HH:mm');
                          var endTime = moment(openDay.end_time, 'HH:mm');

                          if (now.isBefore(startTime) || now.isAfter(endTime)) {
                              renderSlots(service_id, user_id, choose_date);
                              jsEvent.stopPropagation();
                              return false;
                          }
                      }

                      updateServiceInfoForDate(service_id, choose_date);

                      renderSlots(service_id, user_id, choose_date);
                  },
                });
            }
            function updateNavigationButtons() {
              $('.fc-prev-button').html('<i class="fas fa-chevron-left"></i>');
              $('.fc-next-button').html('<i class="fas fa-chevron-right"></i>');
            }
            function isWorkingDay(dateStr) {
              return workHours.hasOwnProperty(dateStr) && workHours[dateStr].start !== null && workHours[dateStr].end !== null;
            }
            
            function renderBusyDays(service_id, user_id){
                $('#bookingCalendar').empty();
                $('#availableSlots').empty();
                $('#dateName').text('');
                
                try {
                    $('#bookingCalendar').fullCalendar('destroy');
                } catch(e) {}

                $('#bookingCalendar').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fs-4"></i></div>');
                
                $.ajax({
                    url: "{{ route('booking.busy-days') }}",
                    type: "POST",
                    dataType: 'json',
                    data: { service_id, user_id },
                    success: function(response) {
                        busyDays = response;
                        $('#bookingCalendar').empty();
                        calendarRender();
                    },
                });
            }
        });

    </script>
@endpush
    @endif
</x-app-layout>
