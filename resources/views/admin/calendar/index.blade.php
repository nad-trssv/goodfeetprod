@section('title', 'Calendar')
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/locale/et.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/et.js"></script>
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
          <button id="clearEventModal" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addEventModal"><span class="fas fa-plus pe-2 fs-10"></span>Новая запись</button>
        </div>
      </div>
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
                    <button class=" btn p-1 fs-10 text-body" type="button" data-bs-dismiss="modal" aria-label="Close">ЗАКРЫТЬ</button>
                  </div>
                </div>
                <div class="modal-body p-card py-0">
                  <div class="form-floating mb-5">
                    <select class="form-select" id="eventService">
                      <option value="null">Выберите услугу</option>
                      @foreach ($services as $service)
                        @php
                          $duration = $service->effective_duration_minutes ?? $service->duration_minutes;
                        @endphp
                        <option
                          value="{{ $service->id }}"
                          data-duration="{{ $duration }}"
                          data-loop="{{ $loop->index }}"
                        >
                          {{ $service->name }} ( {{ $duration }} мин.)
                        </option>
                      @endforeach
                    </select>                    
                    <label for="eventLabel">Выберите услугу</label>
                    <span id="serviceError" class="text-danger"></span>
                  </div>
                  <div class="form-floating mb-3">
                    <input class="form-control" id="eventPrice" type="text" name="price" placeholder="Price" />
                    <label for="eventPrice">Цена</label>
                    <span id="priceError" class="text-danger"></span>
                  </div>

                  <div class="flatpickr-input-container mb-5">
                    <div class="form-floating">
                      <input class="form-control datetimepicker" id="eventStartDate" type="text" name="startDate" placeholder="yyyy/mm/dd HH:mm" data-options='{"disableMobile":true,"enableTime":"true","dateFormat":"Y-m-d H:i", "time_24hr":true}' />
                      <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                      <label class="ps-6" for="eventStartDate">Начало</label>
                      <span id="dateError" class="text-danger"></span>
                    </div>
                  </div>
                  <div class="flatpickr-input-container mb-5">
                    <div class="form-floating">
                      <input class="form-control datetimepicker" id="eventEndDate" type="text" name="endDate" placeholder="yyyy/mm/dd HH:mm" data-options='{"disableMobile":true,"enableTime":"true","dateFormat":"Y-m-d H:i", "time_24hr":true}' />
                      <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                      <label class="ps-6" for="eventEndDate">Окончание</label>
                      <span id="dateError" class="text-danger"></span>
                    </div>
                  </div>

                  <div class="form-floating mb-5" id="userSelectWrapper">
                    <select class="form-select form-select-short" id="userSelect">
                      <option value="null">Выберите мастера</option>
                    </select>
                  </div>

                  <div class="form-floating mb-3">
                    <input class="form-control" id="client_name" type="text" name="client_name" placeholder="Name" />
                    <label for="client_name">Имя</label>
                    <span id="nameError" class="text-danger"></span>
                  </div>
                  <div class="form-floating mb-3">
                    <input class="form-control" id="client_lastname" type="text" name="client_lastname" placeholder="lastname" />
                    <label for="client_lastname">Фамилия</label>
                    <span id="lastnameError" class="text-danger"></span>
                  </div>
                  <div class="form-floating mb-3">
                    <input class="form-control" id="phone" type="text" name="phone" placeholder="+372" />
                    <label for="phone">Теелфон</label>
                    <span id="phoneError" class="text-danger"></span>
                  </div>
                  <div class="form-floating my-5">
                    <textarea class="form-control" id="eventDescription" placeholder="Leave a comment here" name="description" style="height: 128px"></textarea>
                    <label for="eventDescription">Дополнение к записи *</label>
                    <span id="descriptionError" class="text-danger"></span>
                  </div>

                  <div id="clientImages" class="row col-12 justify-content-end gap-2"></div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center border-0">
                  <button class="btn btn-primary px-4" id="saveEvent" type="submit">Сохранить</button>
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

                $('#modalTitle').html("Добавить новую запись");
                
                $('#calendar').fullCalendar({
                  header:{
                    left:'today',
                    center:'prev, title, next',
                    right:'month,agendaWeek, agendaDay'
                  },
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
                    $('#calendar').fullCalendar('changeView', 'agendaDay', start);
                    clearErrors();
                    modalStatus = 'STORE';
                    $('#modalTitle').html("Добавить новую запись");
                    var clicked_date = moment(start).format('YYYY-MM-DD hh:mm');
                    if(clicked_date) { 
                        $("#eventStartDate").val(clicked_date);
                        $("#eventEndDate").val(clicked_date);
                        $("#client_name").val('');
                        $("#client_lastname").val('');
                        $("#eventDescription").val('');
                        $("#eventPrice").val('');
                        $("#phone").val('');
                        $("#eventService").val('');
                        $('#userSelect').empty();
                        $('#eventDetailBtn').empty();
                        flatpickr($("#eventStartDate"), {
                            enableTime: true,
                            dateFormat: "Y-m-d H:i",
                            time_24hr: true,
                            disableMobile: true,
                            defaultDate: clicked_date,
                            locale: "et",
                            allowInput: true,
                        });
                        flatpickr($("#eventEndDate"), {
                            enableTime: true,
                            dateFormat: "Y-m-d H:i",
                            time_24hr: true,
                            disableMobile: true,
                            defaultDate: clicked_date,
                            locale: "et",
                            allowInput: true,
                        });
                    }
                    $('#addEventModal').modal('toggle');
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
                            if (confirm('Точно ли вы хотите удалить это событие?')) {
                              isDeleting = true;
                                $.ajax({
                                    url: "{{ route('calendar.destroy', '') }}"+'/'+event.id,
                                    type: "DELETE",
                                    dataType: "json",
                                    success: function(response) {
                                        Swal.fire({
                                          position: "top-end",
                                          icon: "success",
                                          title: 'Событие успешно удалено!',
                                          showConfirmButton: false,
                                          iconColor: '#1463b8',
                                          timerProgressBar: true,
                                          timer: 1500,
                                        });
                                        $('#calendar').fullCalendar('removeEvents', event._id);
                                    },
                                    error: function(err) {
                                        console.log('Ошибка при удалении события:', err);
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
                            clearErrors()       
                            const errors = err.responseJSON.errors;
                            if (errors) {
                              $('#phoneError').html(errors.client_phone || "");
                              $('#serviceError').html(errors.service_id || "");
                              $('#nameError').html(errors.client_name || "");
                              $('#lastnameError').html(errors.client_lastname || "");
                              $('#dateError').html(errors.appointment_end || "");
                              $('#descriptionError').html(errors.description || "");
                            }
                            revertFunc();
                          }
                      })
                  },
                  eventClick: function(event, jsEvent, view) {
                            if (String(event.id).startsWith('closed')) {
                              Swal.fire({
                              position: "center",
                              icon: "warning",
                              title: 'Это недоступная для записи дата. Это закрытое окошко, и оно редактируется в настройках.',
                              showConfirmButton: false,
                              iconColor: '#ff8100',
                              timerProgressBar: true,
                              timer: 4500,
                            });
                            return;
                        }

                        modalStatus = 'UPDATE';
                        currentEvent = event;
                        $('#modalTitle').html("Change event");

                        $('#clientImages').empty();
                        $('#eventDetailBtn').empty();
                        
                        let appointmentId = event.id; 
                        let detailUrl = $('#eventDetailBtn').data('url').replace('__APPOINTMENT_ID__', appointmentId);
                        let detailBtnHtml = `
                            <a href="${detailUrl}" class="btn btn-phoenix-primary me-2" id="eventDetail">Подробнее</a>
                        `;
                        $('#eventDetailBtn').append(detailBtnHtml);
                        
                        event.media.forEach(media => {
                            let imageUrl = `public//storage/${media.photo_path}`; 
                            let imageHtml = `
                                <div class="col-6 col-md-4 col-lg-3 img-fluid rounded-2 d-block p-0 overflow-hidden" style="height:80px; width: 80px;">
                                  <img class="object-fit-cover" src="${imageUrl}" alt="Изображение" style="height:80px; width: 80px;">
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

                          $('#modalTitle').html("Change event");
                          $('#clientImages').empty();
                          $('#eventDetailBtn').empty();

                          let appointmentId = event.id; 
                          let detailUrl = $('#eventDetailBtn').data('url').replace('__APPOINTMENT_ID__', appointmentId);
                          let detailBtnHtml = `<a href="${detailUrl}" class="btn btn-phoenix-primary me-2" id="eventDetail">Подробнее</a>`;
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
                          const errors = err.responseJSON.errors;
                          if (errors) {
                            $('#phoneError').html(errors.client_phone || "");
                            $('#serviceError').html(errors.service_id || "");
                            $('#nameError').html(errors.client_name || "");
                            $('#lastnameError').html(errors.client_lastname || "");
                            $('#dateError').html(errors.appointment_end || "");
                            $('#descriptionError').html(errors.description || "");
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
                          $(' #phoneError, #serviceError, #dateError, #descriptionError, #lastnameError, #nameError').html("");          
                          
                          const errors = err.responseJSON.errors;
                          if (errors) {
                            $('#phoneError').html(errors.client_phone || "");
                            $('#serviceError').html(errors.service_id || "");
                            $('#nameError').html(errors.client_name || "");
                            $('#lastnameError').html(errors.client_lastname || "");
                            $('#dateError').html(errors.appointment_end || "");
                            $('#descriptionError').html(errors.description || "");
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
                          title: 'Время в дате было изменено',
                          showConfirmButton: false,
                          iconColor: '#6da37d',
                          timerProgressBar: true,
                          timer: 1300,
                    });

                    if (service.next_rule) {
                      Swal.fire({
                        position: "top",
                        icon: "info",
                        title: `С ${moment(service.next_rule.valid_from).format('DD.MM.YYYY')} услуга будет изменена: ${service.next_rule.duration_minutes} мин, ${service.next_rule.price} €`,
                        showConfirmButton: false,
                        iconColor: '#6da37d',
                        timerProgressBar: true,
                        timer: 3000,
                      });
                    }

                  } else {
                    $('#userSelect').empty();
                    document.getElementById('userSelectWrapper').style.display = 'none';
                    Swal.fire({
                          position: "top",
                          icon: "warning",
                          title: 'Пожалуйста, выберите услугу',
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
                    locale: "et",
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
