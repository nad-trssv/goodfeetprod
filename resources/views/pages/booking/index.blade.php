@section('title', 'GoodFeet Booking')
@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/et.js"></script>
@endpush
<x-app-layout>
      <section class="pt-6 pt-md-10 pb-10 calendar_bg ff_secondary">
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
                <img class="rounded-2 mb-3 mx-auto" src="{{ asset('assets/img/logos/logo.svg') }}" alt="GoodFeet" width="60%" class="logoLarge logoContainer" />
                <p class="mb-1 text-body-tertiary fs-7 text-center">{{ $settings['company_name'] }}</p>
                <p class="mb-1 text-body-tertiary fs-9 text-center">{{ $settings['company_address'] }}</p>
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
      
    @push('scripts')
    <script>
        $(document).ready(function() {
          function updateServiceInfoForDate(serviceId, dateStr) {
              if (!serviceId || !dateStr) return;
              $.ajax({
                  url: "{{ route('api.service.effective') }}",
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

            var services = @json($services);
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
              stepRules(bookingStep, 'inc');
              scrollToTop();
            }
            function prevStep(){
              bookingStep--;
              stepRules(bookingStep, 'dec');
              scrollToTop();
            }
            function scrollToTop() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
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
                              <div class="col-11 m-0">
                                  <div class="m-0">
                                      <h4 class="mb-0 fw-medium fs-8 m-0">${user.name}</h4>
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
                moment.locale('et');
                let formattedDate = moment(choose_date).format('dddd D MMMM');
                $('#dateName').text(formattedDate);

                if (closedDays.includes(choose_date)) {
                        return false;
                } else {
                  $.ajax({
                    url: "{{  route('api.booking.getFullyBooked') }}",
                    type:"POST",
                    dataType: 'json',
                    data:{ service_id, user_id, choose_date},
                    success:function(response)
                    {
                      bookedSlots = response.map(item => ({
                          start: item.start,
                          end: item.end,
                      }));
                      
                      $('#availableSlots').empty();
                      if(bookedSlots.length === 0){
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
                    url: "{{ route('api.booking.getBusyDays') }}",
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
</x-app-layout>
