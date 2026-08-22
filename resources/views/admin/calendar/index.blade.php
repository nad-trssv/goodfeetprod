@section('title', __('admin_staff.calendar'))
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/locale-all.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/et.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
    <style>
      #calendar .fc-day-header { padding: .65rem .25rem; font-weight: 700; text-transform: capitalize; }
      #calendar .fc-day-top { border: 1px solid var(--phoenix-border-color, #e3e6ed); padding: .35rem .45rem; }
      #calendar .fc-day-top .fc-day-number { float: none; display: inline-flex; min-width: 1.75rem; min-height: 1.75rem; align-items: center; justify-content: center; }
      #calendar .fc-bg table,
      #calendar .fc-content-skeleton table { width: 100%; table-layout: fixed; }
      #calendar .fc-event { white-space: normal; line-height: 1.25; padding: .15rem .25rem; }
      #calendar .fc-title { font-weight: 600; }
    </style>
@endpush
<x-dashboard-layout>
    <div class="content">
      <div class="row g-0 mb-4 align-items-center">
        <div class="col-5 col-md-6">
          <h4 class="mb-0 text-body-emphasis fw-bold fs-md-6">
            <span id="currentDayOfWeek" class="calendar-day d-block d-md-inline mb-1"></span>
            <span class="px-3 fw-thin text-body-quaternary d-none d-md-inline">|</span>
            <span id="currentDate" class="calendar-date"></span></h4>
        </div>
        <div class="col-7 col-md-6 d-flex justify-content-end">
          <a href="{{ route('calendar.create') }}" class="btn btn-primary btn-sm"><span class="fas fa-plus pe-2 fs-10"></span>{{ __('admin_appointments.new') }}</a>
        </div>
      </div>
        @isset($master)
          <div class="alert alert-subtle-primary d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div><strong>{{ $master->name }}</strong> — {{ __('admin_appointments.personal_calendar') }}</div>
            <span>{{ __('admin_appointments.count', ['count' => count($appointments)]) }}</span>
          </div>
          @if(count($appointments) === 0)
            <div class="alert alert-subtle-secondary mb-3">{{ __('admin_appointments.no_personal_appointments') }}</div>
          @endif
        @endisset
        <div class="calendar-outline mt-6 mb-9 fc fc-media-screen fc-direction-ltr fc-theme-standard" id="calendar"></div>
        <div id="destroyEvent"><span class="text-danger fs-5 uil uil-trash-alt"></span></div>
        <div class="modal fade" id="eventDetailsModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border border-translucent"></div>
          </div>
        </div>
        <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content border border-translucent">
              <form id="addEventForm" autocomplete="off">
                @csrf
                <div class="modal-header px-card border-0">
                  <div class="w-100 d-flex justify-content-between align-items-start">
                    <div>
                      <h5 id="modalTitle" class="mb-0 lh-sm text-body-highlight"></h5>
                    </div>
                    <button class=" btn p-1 fs-10 text-body" type="button" data-bs-dismiss="modal" aria-label="{{ __('admin_appointments.close') }}">{{ mb_strtoupper(__('admin_appointments.close')) }}</button>
                  </div>
                </div>
                <div class="modal-body p-card py-0">
                  <div class="form-floating mb-5">
                    <select class="form-select" id="eventService">
                      <option value="null">{{ __('admin_appointments.select_service') }}</option>
                      @foreach ($services as $service)
                        @php
                          $duration = $service->effective_duration_minutes ?? $service->duration_minutes;
                        @endphp
                        <option
                          value="{{ $service->id }}"
                          data-duration="{{ $duration }}"
                          data-loop="{{ $loop->index }}"
                        >
                          {{ $service->name }} ({{ __('admin_appointments.minutes_short', ['count' => $duration]) }})
                        </option>
                      @endforeach
                    </select>                    
                    <label for="eventLabel">{{ __('admin_appointments.select_service') }}</label>
                    <span id="serviceError" class="text-danger"></span>
                  </div>
                  <div class="form-floating mb-3">
                    <input class="form-control" id="eventPrice" type="text" name="price" placeholder="{{ __('admin_appointments.price') }}" />
                    <label for="eventPrice">{{ __('admin_appointments.price') }}</label>
                    <span id="priceError" class="text-danger"></span>
                  </div>

                  <div class="flatpickr-input-container mb-5">
                    <div class="form-floating">
                      <input class="form-control datetimepicker" id="eventStartDate" type="text" name="startDate" placeholder="yyyy/mm/dd HH:mm" data-options='{"disableMobile":true,"enableTime":"true","dateFormat":"Y-m-d H:i", "time_24hr":true}' />
                      <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                      <label class="ps-6" for="eventStartDate">{{ __('admin_appointments.start') }}</label>
                      <span id="dateError" class="text-danger"></span>
                    </div>
                  </div>
                  <div class="flatpickr-input-container mb-5">
                    <div class="form-floating">
                      <input class="form-control datetimepicker" id="eventEndDate" type="text" name="endDate" placeholder="yyyy/mm/dd HH:mm" data-options='{"disableMobile":true,"enableTime":"true","dateFormat":"Y-m-d H:i", "time_24hr":true}' />
                      <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                      <label class="ps-6" for="eventEndDate">{{ __('admin_appointments.end') }}</label>
                      <span id="dateError" class="text-danger"></span>
                    </div>
                  </div>

                  <div class="form-floating mb-5" id="userSelectWrapper">
                    <select class="form-select form-select-short" id="userSelect">
                      <option value="null">{{ __('admin_appointments.select_employee') }}</option>
                    </select>
                  </div>

                  <div class="form-floating mb-3">
                    <input class="form-control" id="client_name" type="text" name="client_name" placeholder="{{ __('admin_staff.name') }}" />
                    <label for="client_name">{{ __('admin_staff.name') }}</label>
                    <span id="nameError" class="text-danger"></span>
                  </div>
                  <div class="form-floating mb-3">
                    <input class="form-control" id="client_lastname" type="text" name="client_lastname" placeholder="{{ __('admin_appointments.surname') }}" />
                    <label for="client_lastname">{{ __('admin_appointments.surname') }}</label>
                    <span id="lastnameError" class="text-danger"></span>
                  </div>
                  <div class="form-floating mb-3">
                    <input class="form-control" id="phone" type="text" name="phone" placeholder="+372" />
                    <label for="phone">{{ __('admin_appointments.phone') }}</label>
                    <span id="phoneError" class="text-danger"></span>
                  </div>
                  <div class="form-floating my-5">
                    <textarea class="form-control" id="eventDescription" placeholder="{{ __('admin_appointments.appointment_note') }}" name="description" style="height: 128px"></textarea>
                    <label for="eventDescription">{{ __('admin_appointments.appointment_note') }} *</label>
                    <span id="descriptionError" class="text-danger"></span>
                  </div>

                  <div id="clientImages" class="row col-12 justify-content-end gap-2"></div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center border-0">
                  <button class="btn btn-primary px-4" id="saveEvent" type="submit">{{ __('admin_appointments.save') }}</button>
                  <div id="eventDetailBtn" data-url="{{ route('calendar.show', ['appointment' => '__APPOINTMENT_ID__']) }}"></div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <x-dashboard-footer></x-dashboard-footer>
      </div>
        
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/et.js"></script>
        <script>
            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    }
                });
                var appointments = @json($closedDays);
                var services = @json($services);
                var users = @json($users);
                var redDays = @json($redDays); 
                var redDates = redDays.map(function(day) {
                    return day.date; 
                });
                
                var globalRevertFunc;
                var isDeleting = false;
                var lastEventState = null;
                var modalStatus = 'STORE';
                var currentEvent = null;
                var serviceUsers = null;

                $('#modalTitle').text(@json(__('admin_appointments.add_new')));
                
                $('#calendar').fullCalendar({
                  locale: @json(app()->getLocale()),
                  header:{
                    left:'today',
                    center:'prev, title, next',
                    right:'month,agendaWeek, agendaDay'
                  },
                  columnFormat: 'dddd',
                  events: appointments,
                  timeFormat: 'HH:mm',
                  selectable: true,
                  selectHelper: true,
                  editable: true,
                  initialView: 'dayGridMonth',
                  eventDurationEditable: false,
                  viewRender: function(view, element) {
                    redDates.forEach(date => {
                        const element = document.querySelector(`td.fc-day[data-date="${date}"]`);
                        const elementDay = document.querySelector(`td.fc-day-top[data-date="${date}"]`);
                        const elementDayWeek = document.querySelector(`.fc-widget-header[data-date="${date}"]`);

                        if (element) {
                            element.classList.add("red_day");
                        }
                        if (elementDay) {
                          elementDay.classList.add("red_day");
                        }
                        if (elementDayWeek) {
                          elementDayWeek.classList.add("red_day");
                        }
                    });
                    updateCalendarInfo(view);
                  },
                  eventRender: function(event, element) {
                      var circle = $('<span>').css({
                          width: '7px',
                          height: '7px',
                          borderRadius: '50%',
                          backgroundColor: event.textColor,
                          display: 'inline-block',
                          marginRight: '10px'
                      });
                      element.find('.fc-time').prepend(circle);
                      if (event.master) {
                          element.find('.fc-title').text(event.master + ' · ' + event.title);
                          element.attr('title', event.master + ' — ' + event.title);
                      }

                      var eventDate = event.start.format('YYYY-MM-DD'); 
                      if (redDates.includes(eventDate)) {
                          element.addClass('red-day'); 
                      }
                  },
                  datesSet: function() {
                    
                      const todayButton = document.querySelector('.fc-today-button');
                      if (todayButton) {
                        todayButton.removeAttribute('disabled');
                        todayButton.classList.remove('fc-state-disabled');
                      }
                  },
                  select: function(start, end, allDay) {
                      var date = moment(start).format('YYYY-MM-DD');
                      window.location.href = "{{ route('calendar.create') }}?date=" + date;
                  },
                  eventDragStart: function(event, jsEvent, ui, view) {
                      $('#destroyEvent').show();
                  },
                  eventDragStop: function(event, jsEvent, ui, view) {
                        const destroyEvent = $('#destroyEvent');
                        const destroyEventOffset = destroyEvent.offset();
                        const destroyEventWidth = destroyEvent.outerWidth();
                        const destroyEventHeight = destroyEvent.outerHeight();
                        var x1 = destroyEventOffset.left;
                        var x2 = destroyEventOffset.left + destroyEventWidth;
                        var y1 = destroyEventOffset.top;
                        var y2 = destroyEventOffset.top + destroyEventHeight;
                        if (jsEvent.pageX >= x1 && jsEvent.pageX <= x2 && jsEvent.pageY >= y1 && jsEvent.pageY <= y2) {
                            if (confirm(@json(__('admin_appointments.cancel_confirm')))) {
                              const reason = window.prompt(@json(__('admin_appointments.cancel_reason')));
                              if (reason === null || reason.trim().length < 3) {
                                isDeleting = false;
                                $('#calendar').fullCalendar('refetchEvents');
                                return;
                              }
                              isDeleting = true;
                                $.ajax({
                                    url: "{{ route('calendar.destroy', '') }}"+'/'+event.id,
                                    type: "DELETE",
                                    data: { reason: reason.trim() },
                                    dataType: "json",
                                    success: function(response) {
                                        Swal.fire({
                                          position: "top-end",
                                          icon: "success",
                                          title: @json(__('admin_appointments.cancelled')),
                                          showConfirmButton: false,
                                          iconColor: '#1463b8',
                                          timerProgressBar: true,
                                          timer: 1500,
                                        });
                                        $('#calendar').fullCalendar('removeEvents', event._id);
                                    },
                                    error: function(err) {
                                        console.log(@json(__('admin_appointments.delete_log_error')), err);
                                    }
                                });
                            } else {
                              isDeleting = true;
                              if (lastEventState) {
                                  event.start = lastEventState.start;
                                  event.end = lastEventState.end;
                                  $('#calendar').fullCalendar('updateEvent', event);
                              }
                              $('#calendar').fullCalendar('refetchEvents');
                            }
                        }
                      
                        $('#destroyEvent').hide();
                  },
                  eventDrop: function(event, delta, revertFunc) {
                      if (isDeleting) {
                          isDeleting = false;
                          revertFunc();
                          return;
                      }
                      globalRevertFunc = revertFunc;
                      
                      lastEventState = {
                          start: event.start.clone(),
                          end: event.end ? event.end.clone() : null
                      };
                      var id = event.id;
                      var client_phone = event.client_phone;
                      var client_name = event.client_name;
                      var client_lastname = event.client_lastname;
                      var service_id = event.service_id;
                      var user_id = event.user_id;
                      var price = event.price;
                      var appointment_start = moment(event.start).format('YYYY-MM-DD H:mm');
                      var appointment_end = moment(event.end).format('YYYY-MM-DD H:mm');
                      var description = $('#eventDescription').val();
                      
                      $.ajax({
                          url: "/calendar/" + id,
                          type: "PATCH",
                          dataType: 'json',
                          data:{ id, appointment_start, appointment_end, price, client_phone, client_name, client_lastname, service_id, user_id, description},
                          success:function(response)
                          {
                            Swal.fire({
                              position: "top-end",
                              icon: "success",
                              title: response.message,
                              showConfirmButton: false,
                              iconColor: '#1463b8',
                              timerProgressBar: true,
                              timer: 1500,
                            });
                          $('#addEventModal').modal('hide');
                          $('#addEventModal').find('form').trigger('reset');
                          Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: response.message,
                            showConfirmButton: false,
                            iconColor: '#1463b8',
                            timerProgressBar: true,
                            timer: 1500,
                          });
                          clearErrors()
                          },
                          error:function(err)
                          {
                            clearErrors();
                            revertFunc();
                            if (!err.responseJSON) return;
                            const errors = err.responseJSON.errors;
                            if (errors) {
                              if (errors.appointment_start) {
                                Swal.fire({
                                  position: "top",
                                  icon: "error",
                                  title: errors.appointment_start[0],
                                  showConfirmButton: false,
                                  timerProgressBar: true,
                                  timer: 3000,
                                });
                              }
                              $('#phoneError').html(errors.client_phone || "");
                              $('#serviceError').html(errors.service_id || "");
                              $('#nameError').html(errors.client_name || "");
                              $('#lastnameError').html(errors.client_lastname || "");
                              $('#dateError').html(errors.appointment_start || errors.appointment_end || "");
                              $('#descriptionError').html(errors.description || "");
                            }
                          }
                      })
                  },
                  eventClick: function(event, jsEvent, view) {
                            if (String(event.id).startsWith('closed')) {
                              Swal.fire({
                              position: "center",
                              icon: "warning",
                              title: @json(__('admin_appointments.closed_slot_hint')),
                              showConfirmButton: false,
                              iconColor: '#ff8100',
                              timerProgressBar: true,
                              timer: 4500,
                            });
                            return;
                        }

                        modalStatus = 'UPDATE';
                        currentEvent = event;
                        $('#modalTitle').text(@json(__('admin_appointments.change_event')));

                        $('#clientImages').empty();
                        $('#eventDetailBtn').empty();
                        
                        let appointmentId = event.id; 
                        let detailUrl = $('#eventDetailBtn').data('url').replace('__APPOINTMENT_ID__', appointmentId);
                        let detailBtnHtml = `
                            <a href="${detailUrl}" class="btn btn-phoenix-primary me-2" id="eventDetail">${@json(__('admin_appointments.details'))}</a>
                        `;
                        $('#eventDetailBtn').append(detailBtnHtml);
                        
                        event.media.forEach(media => {
                            let imageUrl = `public//storage/${media.photo_path}`; 
                            let imageHtml = `
                                <div class="col-6 col-md-4 col-lg-3 img-fluid rounded-2 d-block p-0 overflow-hidden" style="height:80px; width: 80px;">
                                  <img class="object-fit-cover" src="${imageUrl}" alt="${@json(__('admin_appointments.image_alt'))}" style="height:80px; width: 80px;">
                                </div>
                            `;
                            $('#clientImages').append(imageHtml);
                        });
                        
                        $('#eventTitle').val(event.title);
                        $('#phone').val(event.client_phone);
                        $('#client_name').val(event.client_name);
                        $('#client_lastname').val(event.client_lastname);
                        $('#eventDescription').val(event.description);
                        $('#eventPrice').val(event.price);
                        $('#eventStartDate').val(event.start.format('YYYY-MM-DD H:mm'));
                        $('#eventEndDate').val(event.end.format('YYYY-MM-DD H:mm'));
                        $('#addEventModal').modal('toggle');
                        document.getElementById('userSelectWrapper').style.display = 'block';

                        $('#eventService option').each(function() {
                          if ($(this).val() == event.service_id) {
                            $(this).prop('selected', true);
                          } else {
                            $(this).prop('selected', false);
                          }
                        });
                        
                        const selectedOption = document.querySelector('#eventService').selectedOptions[0];
                        const loopValue = selectedOption.getAttribute('data-loop');

                        $('#userSelect').empty();
                          for (let user of services[loopValue].users) {
                              let option = new Option(user.name, user.id);
                              $('#userSelect').append(option);
                          }
                        $('#userSelect option').each(function() {
                          if ($(this).val() == event.user_id) {
                            $(this).prop('selected', true);
                          } else {
                            $(this).prop('selected', false);
                          }
                        });
                  },
                });
                $('#saveEvent').click(function(event) {
                  event.preventDefault();

                    var appointment_start = $('#eventStartDate').val();
                    var appointment_end = $('#eventEndDate').val();
                    var client_phone = $('#phone').val();
                    var client_name = $('#client_name').val();
                    var client_lastname = $('#client_lastname').val();
                    var service_id = $('#eventService').val();
                    var user_id = $('#userSelect').val();
                    var description = $('#eventDescription').val();
                    var price = $('#eventPrice').val();
                  
                  if(modalStatus === 'STORE'){
                    $.ajax({
                      url: "{{  route('calendar.store') }}",
                      type:"POST",
                      dataType: 'json',
                      data:{ appointment_start, appointment_end, client_phone, client_name, client_lastname, service_id, user_id, description, price },
                      success:function(response)
                      {
                        $('#calendar').fullCalendar('renderEvent', {
                          'id': response.id,
                          'title': response.title,
                          'price': response.price,
                          'user_id': response.user_id,
                          'client_name': response.client_name,
                          'client_lastname': response.client_lastname,
                          'service_id': response.service_id,
                          'client_phone': response.client_phone,
                          'description': response.description,
                          'textColor': response.backgroundColor,
                          'start': response.appointment_start,
                          'end': response.appointment_end,
                        }, true)

                        $('#addEventModal').modal('hide');

                        $('#addEventModal').find('form').trigger('reset');

                        Swal.fire({
                          position: "top-end",
                          icon: "success",
                          title: response.message,
                          showConfirmButton: false,
                          iconColor: '#1463b8',
                          timerProgressBar: true,
                          timer: 1500,
                        });
                        $('#calendar').fullCalendar('option', 'eventClick', function(event) {
                          modalStatus = 'UPDATE';
                          currentEvent = event;

                          $('#modalTitle').text(@json(__('admin_appointments.change_event')));
                          $('#clientImages').empty();
                          $('#eventDetailBtn').empty();

                          let appointmentId = event.id; 
                          let detailUrl = $('#eventDetailBtn').data('url').replace('__APPOINTMENT_ID__', appointmentId);
                          let detailBtnHtml = `<a href="${detailUrl}" class="btn btn-phoenix-primary me-2" id="eventDetail">${@json(__('admin_appointments.details'))}</a>`;
                          $('#eventDetailBtn').append(detailBtnHtml);

                          $('#eventTitle').val(event.title);
                          $('#phone').val(event.client_phone);
                          $('#client_name').val(event.client_name);
                          $('#client_lastname').val(event.client_lastname);
                          $('#eventDescription').val(event.description);
                          $('#eventPrice').val(event.price);
                          $('#eventStartDate').val(event.start.format('YYYY-MM-DD HH:mm'));
                          $('#eventEndDate').val(event.end.format('YYYY-MM-DD HH:mm'));
                          $('#addEventModal').modal('toggle');
                          document.getElementById('userSelectWrapper').style.display = 'block';

                          $('#eventService option').each(function() {
                              if ($(this).val() == event.service_id) {
                                  $(this).prop('selected', true);
                              } else {
                                  $(this).prop('selected', false);
                              }
                          });

                          const selectedOption = document.querySelector('#eventService').selectedOptions[0];
                          const loopValue = selectedOption.getAttribute('data-loop');

                          $('#userSelect').empty();
                          for (let user of services[loopValue].users) {
                              let option = new Option(user.name, user.id);
                              $('#userSelect').append(option);
                          }

                          $('#userSelect option').each(function() {
                              if ($(this).val() == event.user_id) {
                                  $(this).prop('selected', true);
                              } else {
                                  $(this).prop('selected', false);
                              }
                          });

                        });
                        clearErrors();
                        $('#calendar').fullCalendar('refetchEvents');

                      },
                      error:function(err)
                          {
                            clearErrors()
                            if (!err.responseJSON) return;
                            const errors = err.responseJSON.errors;
                            if (errors) {
                              $('#phoneError').html(errors.client_phone || "");
                              $('#serviceError').html(errors.service_id || "");
                              $('#nameError').html(errors.client_name || "");
                              $('#lastnameError').html(errors.client_lastname || "");
                              $('#dateError').html(errors.appointment_start || errors.appointment_end || "");
                              $('#descriptionError').html(errors.description || "");
                              if (errors.appointment_start) {
                                Swal.fire({
                                  position: "top",
                                  icon: "error",
                                  title: errors.appointment_start[0],
                                  showConfirmButton: false,
                                  timerProgressBar: true,
                                  timer: 6000,
                                });
                              }
                            }
                          }
                    });
                  }
                  if(modalStatus === 'UPDATE'){
                    var id = currentEvent.id;
                    $.ajax({
                        url: "/calendar/" + id,
                        type: "PATCH",
                        dataType: 'json',
                        data:{ id, appointment_start, appointment_end, client_phone, client_name, client_lastname, service_id, user_id, description, price},
                        success:function(response)
                        {
                          $('#addEventModal').modal('hide');

                          $('#addEventModal').find('form').trigger('reset');

                          clearErrors();

                          var updatedEvent = {
                            id: response.appointment.id,
                            title: response.title,
                            user_id: response.appointment.user_id,
                            client_phone: response.appointment.client_phone,
                            client_name: response.appointment.client_name,
                            client_lastname: response.appointment.client_lastname,
                            service_id: response.appointment.service_id,
                            description: response.appointment.description,
                            price: response.appointment.price,
                            textColor: response.backgroundColor,
                            start: appointment_start,
                            end: appointment_end,
                          };

                          var event = $('#calendar').fullCalendar('clientEvents', updatedEvent.id)[0];
                          if (event) {
                              event.title = updatedEvent.title;
                              event.start = updatedEvent.start;
                              event.end = updatedEvent.end;
                              event.client_phone = updatedEvent.client_phone;
                              event.client_name = updatedEvent.client_name;
                              event.client_lastname = updatedEvent.client_lastname;
                              event.service_id = updatedEvent.service_id;
                              event.description = updatedEvent.description;
                              event.price = updatedEvent.price;
                              event.textColor = updatedEvent.textColor;

                              $('#calendar').fullCalendar('updateEvent', event);
                          }

                          Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: response.message,
                            showConfirmButton: false,
                            iconColor: '#1463b8',
                            timerProgressBar: true,
                            timer: 1500,
                          });
                        },
                        error:function(err)
                          {
                            clearErrors()
                            if (!err.responseJSON) return;
                            const errors = err.responseJSON.errors;
                            if (errors) {
                              $('#phoneError').html(errors.client_phone || "");
                              $('#serviceError').html(errors.service_id || "");
                              $('#nameError').html(errors.client_name || "");
                              $('#lastnameError').html(errors.client_lastname || "");
                              $('#dateError').html(errors.appointment_start || errors.appointment_end || "");
                              $('#descriptionError').html(errors.description || "");
                              if (errors.appointment_start) {
                                Swal.fire({
                                  position: "top",
                                  icon: "error",
                                  title: errors.appointment_start[0],
                                  showConfirmButton: false,
                                  timerProgressBar: true,
                                  timer: 6000,
                                });
                              }
                            }
                          }
                    })
                  }
                });
                $('#eventService').on('change', function () {
                  const selectedValue = $(this).val();
                  const selectedOption = $(this).find('option:selected');
                  const duration = selectedOption.data('duration');
                  const loop = selectedOption.data('loop');

                  if (selectedValue !== "null") {
                    $('#userSelect').empty();

                    const service = services[loop];
                    const basePrice = service.effective_price ?? service.price;

                    $('#eventPrice').val(basePrice);

                    for (let user of service.users) {
                        let option = new Option(user.name, user.id);
                        $('#userSelect').append(option);
                    }

                    let startDate = moment($('#eventStartDate').val(), 'YYYY-MM-DD H:mm');
                    let endDate = startDate.add(duration, 'minutes');

                    $('#eventEndDate').val(endDate.format('YYYY-MM-DD H:mm'));
                    document.getElementById('userSelectWrapper').style.display = 'block';

                    Swal.fire({
                          position: "top",
                          icon: "info",
                          title: @json(__('admin_appointments.time_changed')),
                          showConfirmButton: false,
                          iconColor: '#6da37d',
                          timerProgressBar: true,
                          timer: 1300,
                    });

                    if (service.next_rule) {
                      Swal.fire({
                        position: "top",
                        icon: "info",
                        title: @json(__('admin_appointments.future_rule'))
                          .replace(':date', moment(service.next_rule.valid_from).format('DD.MM.YYYY'))
                          .replace(':duration', service.next_rule.duration_minutes)
                          .replace(':price', service.next_rule.price),
                        showConfirmButton: false,
                        iconColor: '#6da37d',
                        timerProgressBar: true,
                        timer: 6000,
                      });
                    }

                  } else {
                    $('#userSelect').empty();
                    document.getElementById('userSelectWrapper').style.display = 'none';
                    Swal.fire({
                          position: "top",
                          icon: "warning",
                          title: @json(__('admin_appointments.select_service_prompt')),
                          showConfirmButton: false,
                          iconColor: '#d37d17',
                          timerProgressBar: true,
                          timer: 1300,
                    });
                  }
                });
                $('#clearEventModal').click(function(event) {
                  event.preventDefault();
                  clearErrors();
                  $('#eventDetailBtn').empty();
                  $('#addEventModal').find('form').trigger('reset');
                });
                function updateCalendarInfo(view) {
                      var currentDate = view.calendar.getDate();

                      var dayOfWeek = currentDate.format('dddd');
                      var date = currentDate.format('DD MMM, YYYY');

                      $('#currentDate').text(date);
                      $('#currentDayOfWeek').text(dayOfWeek);
                };
                function clearErrors() {
                  $('#phoneError, #serviceError, #dateError, #descriptionError, #lastnameError, #nameError').html(""); 
                }

                flatpickr(".datetimepicker", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    disableMobile: true,
                    locale: @json(app()->getLocale()),
                    allowInput: true,
                    onClose: function(selectedDates, dateStr, instance) {
                        if (!dateStr) {
                            instance.setDate(instance.input.defaultValue, false);
                        }
                    },
                    onOpen: function(selectedDates, dateStr, instance) {
                      if (dateStr) {
                          instance.setDate(dateStr, true);
                      }
                    },
                });
            });
        </script>
    @endpush
  </x-dashboard-layout>
