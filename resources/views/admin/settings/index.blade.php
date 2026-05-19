@section('title', 'Settings')
  @push('styles')
  <link href="{{ asset('vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
  @endpush
  <x-dashboard-layout>
    <div class="content">
      <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item active">Настройки</li>
        </ol>
      </nav>
      
        <div class="row">
          <div class="sticky-top" style="margin-top:-72px;padding-top:72px">
            <div class="nav flex-column nav-pills" id="v-pills">
              <a class="nav-link ps-0 ps-sm-3" href="#v-pills-home">Рабочее время</a>
              <a class="nav-link ps-0 ps-sm-3" href="#v-pills-profile">Обед</a>
              <a class="nav-link ps-0 ps-sm-3" href="#v-pills-messages">Нерабочие дни / часы</a>
              <a class="nav-link ps-0 ps-sm-3" href="#v-pills-fix-booking">Фиксированная запись на услуги</a>
              <a class="nav-link ps-0 ps-sm-3" href="#v-pills-bookingLimit">Лимит дней на бронирование</a>
              <a class="nav-link ps-0 ps-sm-3" href="#v-pills-fix-settings">Настройки сайта</a>
            </div>
          </div>
        </div>
        <div class="row">
          {{-- Рабочее время --}}
          <section class="my-2">
            <h3 id="v-pills-home" class="my-2">Рабочее время</h3>
            @php
              $daysInRussian = [
                'monday' => 'Понедельник',
                'tuesday' => 'Вторник',
                'wednesday' => 'Среда',
                'thursday' => 'Четверг',
                'friday' => 'Пятница',
                'saturday' => 'Суббота',
                'sunday' => 'Воскресенье',
              ];
            @endphp

            <form id="workHoursTable" class="row g-3 needs-validation mt-2">
              @csrf
              @foreach ($workHours as $key => $item)
                <div class="row py-4 border-bottom">
                  <div class="col-md-4 d-flex justify-content-start justify-content-lg-end align-items-end mb-2">
                    <h4 class="mx-2 my-0">{{ $daysInRussian[$key] }}:</h4>
                    <div class="d-flex justify-content-end align-items-center ml-4">
                      <input class="form-input my-0" id="{{ $key }}" type="checkbox" value="{{ $key }}" @if ($item['start'] == NULL || $item['end'] == NULL) checked @endif />
                      <label class="form-label px-1" for="{{ $key }}">NULL</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="{{ $key }}_start">Время начала</label>
                    <input class="form-control datetimepicker flatpickr-input @if ($item['start'] == NULL || $item['end'] == NULL) disabled @endif" id="{{ $key }}_start" type="text" placeholder="hour : minute" value="{{ $item['start'] }}"
                      data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true }' readonly @if ($item['start'] == NULL || $item['end'] == NULL) disabled @endif>
                    <div class="invalid-feedback" id="{{ $key }}_start-error"></div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="{{ $key }}_end">Время окончания</label>
                    <input class="form-control datetimepicker flatpickr-input @if ($item['start'] == NULL || $item['end'] == NULL) disabled @endif" id="{{ $key }}_end" type="text" placeholder="hour : minute" value="{{ $item['end'] }}"
                      data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true }' readonly @if ($item['start'] == NULL || $item['end'] == NULL) disabled @endif>
                    <div class="invalid-feedback" id="{{ $key }}_end-error"></div>
                  </div>
                </div>
              @endforeach

              <div class="row mt-2">
                <div class="col-md-12 d-flex justify-content-end">
                  <button class="btn btn-primary w-100" type="submit">Сохранить</button>
                </div>
              </div>
            </form>
          </section>
          <hr>
          {{-- Обед --}}
          <section class="my-2">
            <h3 id="v-pills-profile" class="my-2">Обед</h3>
            <form id="lunchHoursTable" class="row g-3 needs-validation">
              @csrf
              <div class="row">

                <div class="col-md-4 d-flex justify-content-end align-items-end mb-2">
                  <div class="d-flex justify-content-end align-items-center ml-4">
                    <input class="form-input my-0" id="lunch" type="checkbox" value="lunch" @if ($lunchHours['start'] == NULL ||  $lunchHours['end'] == NULL) checked @endif />
                    <label class="form-label px-1" for="lunch">NULL</label>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="lunch_start">Время начала</label>
                  <input class="form-control datetimepicker flatpickr-input @if ($lunchHours['start'] == NULL || $lunchHours['end'] == NULL) disabled @endif" id="lunch_start" type="text" placeholder="hour : minute"  value="{{ $lunchHours['start'] }}"
                    data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true }' readonly @if ($lunchHours['start'] == NULL || $lunchHours['end'] == NULL) disabled @endif >
                  <div class="invalid-feedback" id="lunch_start-error"></div>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="lunch_end">Время окончания</label>
                  <input class="form-control datetimepicker flatpickr-input @if ($lunchHours['start'] == NULL || $lunchHours['end']  == NULL) disabled @endif" id="lunch_end" type="text" placeholder="hour : minute" value="{{ $lunchHours['end'] }}"
                    data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true }' readonly @if ($lunchHours['start'] == NULL || $lunchHours['end'] == NULL) disabled @endif >
                    <div class="invalid-feedback" id="lunch_end-error"></div>
                </div>
              </div>

              <div class="row mt-2">
                <div class="col-md-12 d-flex justify-content-end">
                  <button class="btn btn-primary w-100" type="submit">Сохранить</button>
                </div>
              </div>
            </form>
          </section>
          
          <hr>
          {{-- Нерабочие дни --}}
          <section class="my-2">
            <h3 id="v-pills-messages" class="my-2">Нерабочие дни / часы</h3>
            {{-- Create red day --}}
            <div>
              <div class="row">
                <div class="col-12">
                  <a class="btn btn-phoenix-secondary mt-2 btn-vivid d-flex w-full" data-bs-toggle="collapse" href="#redDays_create" role="button" aria-expanded="false" aria-controls="redDays_create">Добавить новый</a>
                </div>
              </div>
              <div class="collapse" id="redDays_create">
                <form id="newRedDay" class="row g-3 my-2">
                  @csrf
                  <div class="row my-2">
                    <div class="col-md-12 my-2">
                      <label class="form-label text-body" for="bootstrap-vertical-wizard-wizard-name">Название*</label>
                      <input class="form-control" type="text" name="redDay_name" id="redDay_name" placeholder="Christmas">
                      <div class="invalid-feedback" id="name-error"></div>
                    </div>
                  </div>
                  <div class="row my-2">
                    <div class="col-md-12">
                      <label class="form-label" for="redDay_date">Выберите день</label>
                      <input class="form-control datetimepicker flatpickr-input" id="redDay_date" type="text" placeholder="Y-m-d"  value=""
                        data-options='{"dateFormat":"Y-m-d","disableMobile":true  }' readonly>
                      <div class="invalid-feedback" id="date-error"></div>
                    </div>
                  </div>
                  <div class="row my-2">
                    <div class="col-md-12 my-2">
                        <input class="form-input my-0" id="redFullDay" type="checkbox" value="redFullDay" />
                        <label class="form-label px-1" for="redFullDay">Весь день</label>
                    </div>
                  </div>
                  <div class="row my-2">
                    <div class="col-md-6">
                      <label class="form-label" for="redDay_start_time">Начало времени</label>
                      <input class="form-control datetimepicker flatpickr-input" id="redDay_start_time" type="text" placeholder="hour : minute"  value=""
                        data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true }' readonly >
                      <div class="invalid-feedback" id="start_time-error"></div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="redDay_end_time">Конец времени</label>
                      <input class="form-control datetimepicker flatpickr-input" id="redDay_end_time" type="text" placeholder="hour : minute"  value=""
                        data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true }' readonly >
                      <div class="invalid-feedback" id="end_time-error"></div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-12">
                      <div class="form-check form-switch">
                        <input class="form-check-input" id="redDay_repeat" type="checkbox" checked="" />
                        <label class="form-check-label" for="redDay_repeat">Повторять каждый год</label>
                      </div>
                    </div>
                  </div>
                  <div class="row my-2">
                    <div class="col-md-12">                  
                      <label class="form-label" for="redDay_desc">Описание</label>
                      <textarea class="form-control" id="redDay_desc" rows="3"></textarea>
                      <div class="invalid-feedback" id="description-error"></div>
                    </div>
                  </div>
                
                  <div class="row mt-2">
                    <div class="col-md-12 d-flex justify-content-end">
                      <button class="btn btn-primary w-100" type="submit">Сохранить</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            {{-- List red days --}}
            <div>
                <div class="row">
                  <div class="col-12">
                    <a class="btn btn-phoenix-secondary mt-2 btn-vivid d-flex w-full" data-bs-toggle="collapse" href="#redDays_list" role="button" aria-expanded="false" aria-controls="redDays_list">Список</a>
                  </div>
                </div>
              <div class="collapse" id="redDays_list">
                <div id="tableExample" data-list="{&quot;valueNames&quot;:[&quot;name&quot;,&quot;date&quot;,&quot;age&quot;],&quot;page&quot;:10}">
                  <div class="search-box mb-3 mt-3">
                    <form class="position-relative">
                      <input class="form-control search-input search form-control-sm" type="search" placeholder="Search" aria-label="Search" />
                      <span class="fas fa-search search-box-icon"></span>
                    </form>
                  </div>
                  <div class="row">
                    <div class="col-12">
                      <div class="table-responsive">
                        <table id="redDaysTable" class="table table-sm fs-9 mb-0">
                          <thead>
                            <tr>
                              <th class="sort border-top border-translucent ps-3" data-sort="name">Название</th>
                              <th class="sort border-top border-translucent" data-sort="date">Число и время</th>
                              <th class="sort border-top border-translucent" data-sort="date">Повторение</th>
                              <th class="sort text-end align-middle pe-0 border-top border-translucent" scope="col">Действие</th>
                            </tr>
                          </thead>
                          <tbody class="list">
                            <tr>
                              <th></th>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              
              </div>
            </div>
          </section>
          
          <hr>
          {{-- Фиксированные рабочие дни --}}
          <section class="my-2">
            <h3 id="v-pills-fix-booking" class="my-2">Фиксированная запись на услуги</h3>
            <form id="fixHoursTable" class="row g-3 my-2">
              @csrf
              <div class="g-2">
                <p class="col-8">Выберите, как пользователи смогут бронировать время. Если чекбокс включен, клиенты смогут записываться только на указанные ниже временные слоты. Если чекбокс отключен, клиенты смогут выбрать любое удобное для них время.</p>
                <div class="form-check form-switch">
                  <input class="form-check-input" id="isFixed" type="checkbox" />
                  <label class="form-check-label" for="isFixed">Ограничить выбор времени доступными слотами</label>
                </div>
              </div>
              <div id="rows-container">
              </div>
              <div class="row mt-2">
                <div class="col-md-12 d-flex justify-content-end">
                  <button class="btn btn-primary w-100" type="submit">Сохранить</button>
                </div>
              </div>
            </form>
          </section>

          <hr>
          {{-- Лимит дней на бронирование услуг --}}
          <section class="my-2">
            <h3 id="v-pills-bookingLimit" class="my-2">Лимит дней на бронирование</h3>
            <form id="bookingLimitDays" class="row g-3 needs-validation mt-4">
              @csrf
              <div class="row gy-4 mt-0">
                <div class="col-md-6 mt-0">
                  <label class="form-label" for="bookingLimit_days">Количество дней, на которые клиент сможет бронировать</label>
                  <input class="form-control @if ($bookingLimit['active'] == false) bookingLimit_disabled @endif" type="number" name="bookingLimit" id="bookingLimit_days" placeholder="Days" value="{{ $bookingLimit['days'] }}"  @if ($bookingLimit['active'] == false) disabled @endif>
                  <div class="invalid-feedback" id="bookingLimit-error"></div>
                </div>
                {{--<div class="col-md-4 d-flex align-items-center">
                  <input class="form-input my-0" id="bookingLimit" type="checkbox" value="afgd" @if ($bookingLimit['active'] == true) checked @endif />
                  <label class="form-label px-1" for="bookingLimit">Активировать</label>
                </div>--}}
                <div class="col-md-2 d-flex justify-content-left align-items-center">
                  <button class="btn btn-primary w-100" type="submit">Сохранить</button>
                </div>
              </div>
            </form>
          </section>

          <hr>
          {{-- Настройки сайта --}}
          <section class="my-2">
            <h3 id="v-pills-fix-settings" class="my-2">Настройки сайта</h3>
            <form id="updateSettingsTable" class="row g-3 needs-validation was-validated my-2" novalidate="">
              @csrf
              <div class="mb-3 row">
                <h4 class="col-md-12">Моя компания</h4>
                <div class="col-md-6">
                  <label class="form-label" for="company_name">Название</label>
                  <input class="form-control" id="company_name" type="text" value="" required="">
                  <div class="invalid-feedback" id="company_name-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="company_email">Инфо Емайл</label>
                  <input class="form-control" id="company_email" type="email" value="" required="">
                  <div class="invalid-feedback" id="company_email-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="company_phone">Телефон</label>
                  <input class="form-control" id="company_phone" type="text" value="" pattern="^\+.*">
                  <div class="invalid-feedback" id="company_phone-error"></div>
                  <div class="invalid-feedback">Номер должен начинаться с +.</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="company_address">Адрес</label>
                  <input class="form-control" id="company_address" type="text">
                  <div class="invalid-feedback" id="company_address-error"></div>
                </div>
              </div>
              <div class="mb-3 row">
                <h4 class="col-md-12">Карта</h4>
                <p class="my-1">Пожалуйста всавьте iframe код</p>
                <div class="col-md-6">
                  <label class="form-label" for="google">Google</label>
                  <input class="form-control" id="google" type="text">
                  <div class="invalid-feedback" id="google-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="waze">Waze</label>
                  <input class="form-control" id="waze" type="text">
                  <div class="invalid-feedback" id="waze-error"></div>
                </div>
              </div>

              <div class="mb-3 row">
                <h4 class="col-md-12">Социальные сети</h4>
                <div class="col-md-6">
                  <label class="form-label" for="social_media_facebook">Facebook</label>
                  <input class="form-control" id="social_media_facebook" type="text">
                  <div class="invalid-feedback" id="social_media_facebook-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="social_media_youtube">Youtube</label>
                  <input class="form-control" id="social_media_youtube" type="text">
                  <div class="invalid-feedback" id="social_media_youtube-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="social_media_instagram">Instagram</label>
                  <input class="form-control" id="social_media_instagram" type="text">
                  <div class="invalid-feedback" id="social_media_instagram-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="social_media_twitter">Twitter / X</label>
                  <input class="form-control" id="social_media_twitter" type="text">
                  <div class="invalid-feedback" id="social_media_twitter-error"></div>
                </div>
              </div>
              <div class="row mt-2">
                <div class="col-md-12 d-flex justify-content-end">
                  <button class="btn btn-primary w-100" type="submit">Сохранить</button>
                </div>
              </div>
            </form>
          </section>
        </div>

      <x-dashboard-footer />
    </div>
    @push('scripts')
    <script src="{{ asset('vendors/flatpickr/flatpickr.min.js') }}"></script>
    <script src="vendors/sortablejs/Sortable.min.js"></script>
    <script>
      $(document).ready(function() {

        //get api red days
        $.ajax({  
            url: 'api/settings/getRedDays',
            type: "GET",
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response) {
              var redDays = response['fullRedDays'];
              var redDaysArray = redDays.map(function(item) {
                return item.date;
              });
              $('#redDay_date').flatpickr({
                dateFormat: "Y-m-d",
                disableMobile: true,
                disable: redDaysArray,
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                  if (redDaysArray.includes(fp.formatDate(dayElem.dateObj, "Y-m-d"))) {
                    dayElem.classList.add("calendar_daysOff");
                  }
                }
              });
              
              renderRedDaysTable(response['redDays']);
            }
        });

        //get api main settings
        $.ajax({  
            url: 'api/settings/mainSettings',
            type: "GET",
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response) {
              var mainSettings = response['mainSettings'];
              var mainSettingsArray = mainSettings.map(function(item) {
                var inputElement = document.getElementById(item.key);
                if (inputElement) {
                  inputElement.value = item.payload;
                }
              });
              
            }
        });

        $('#workHoursTable').submit(function(event){
          event.preventDefault();
          let formData = {
            monday_start: $("#monday_start").val(),
            monday_end: $("#monday_end").val(),
            tuesday_start: $("#tuesday_start").val(),
            tuesday_end: $("#tuesday_end").val(),
            wednesday_start: $("#wednesday_start").val(),
            wednesday_end: $("#wednesday_end").val(),
            thursday_start: $("#thursday_start").val(),
            thursday_end: $("#thursday_end").val(),
            friday_start: $("#friday_start").val(),
            friday_end: $("#friday_end").val(),
            saturday_start: $("#saturday_start").val(),
            saturday_end: $("#saturday_end").val(),
            sunday_start: $("#sunday_start").val(),
            sunday_end: $("#sunday_end").val(),
          };
        
          $.ajax({  
          url: '/settings/updateWorkHours',
          type: "POST",
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
          },
          data: formData,
          success: function(response) {
            $('.is-invalid').removeClass('is-invalid');
            if (response.status === "success") {
                  Swal.fire({
                    title: 'Отлично!',
                    text: 'Данные успешно сохранены!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                  });
            } else {
                alert("Error: " + response.message);
            }
          },
          error: function(xhr, status, error) {
              Swal.fire({
                title: 'Упс!',
                text: 'Что-то пошло не так. Пожалуйста, попробуйте еще раз.',
                icon: 'error',
                confirmButtonText: 'OK'
              });
              if (xhr.status === 422) {
                  $('.is-invalid').removeClass('is-invalid');
                  let errors = xhr.responseJSON.errors;

                  $.each(errors, function(field, messages) {
                      let input = $('#' + field);
                      input.addClass('is-invalid');

                      let message = $('#' + field+'-error');
                      message.text(messages[0]).show();
                  });
              }
          }
          });
        });

        $('#lunchHoursTable').submit(function(event){
          event.preventDefault();
          let formData = {
          lunch_start: $("#lunch_start").val(),
          lunch_end: $("#lunch_end").val(),
          };
        
          $.ajax({  
          url: '/settings/updateLunchHours',
          type: "POST",
          headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
          },
          data: formData,
          success: function(response) {
            $('.is-invalid').removeClass('is-invalid');
            if (response.status === "success") {
                  Swal.fire({
                    title: 'Отлично!',
                    text: 'Данные успешно сохранены!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                  });
            } else {
                alert("Error: " + response.message);
            }
          },
          error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Упс!',
                    text: 'Что-то пошло не так. Пожалуйста, попробуйте еще раз.',
                  icon: 'error',
                  confirmButtonText: 'OK'
                });
              if (xhr.status === 422) {
                  $('.is-invalid').removeClass('is-invalid');
                  let errors = xhr.responseJSON.errors;

                  $.each(errors, function(field, messages) {
                      let input = $('#lunch_end-error');
                      input.addClass('is-invalid');

                      let message = $('#lunch_end-error');
                      message.text(messages[0]).show();
                  });
              }
          }
          });
        });

        $('#newRedDay').submit(function(event){
          event.preventDefault();
          let repeat = 0;
          if ($('#redDay_repeat').prop('checked')) {
            repeat = 1;
          }

          let formData = {
            name: $("#redDay_name").val(),
            description: $("#redDay_desc").val(),
            date: $("#redDay_date").val(),
            start_time: $("#redDay_start_time").val(),
            end_time: $("#redDay_end_time").val(),
            repeat: repeat,
          };
        
          $.ajax({  
            url: '/settings/storeRedDay',
            type: "POST",
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: formData,
            success: function(response) {
              if (response.status === "success") {
                $('.is-invalid').removeClass('is-invalid');
                  $("#redDay_name").val(''),
                  $("#redDay_desc").val(''),
                  $("#redDay_date").val(''),
                  $("#redDay_start_time").val(''),
                  $("#redDay_end_time").val(''),
                  Swal.fire({
                    title: 'Отлично!',
                    text: 'Данные успешно сохранены!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                  });

                let redDays = response['fullRedDays'];
                let redDaysArray = redDays.map(function(item) {
                  return item.date;
                });
                
                if(response.isFullDay === true){
                  $('#redDay_date').flatpickr({
                    dateFormat: "Y-m-d",
                    disableMobile: true,
                    disable: redDaysArray,
                    onDayCreate: function(dObj, dStr, fp, dayElem) {
                      if (redDaysArray.includes(fp.formatDate(dayElem.dateObj, "Y-m-d"))) {
                        dayElem.classList.add("calendar_daysOff");
                      }
                    }
                  });
                }
                renderRedDaysTable(response['redDays']);

              } else {
                alert("Error: " + response.message);
              }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                  title: 'Упс!',
                  text: 'Что-то пошло не так. Пожалуйста, попробуйте еще раз.',
                  icon: 'error',
                  confirmButtonText: 'OK'
                });
              if (xhr.status === 422) {
                  $('.is-invalid').removeClass('is-invalid');
                  let errors = xhr.responseJSON.errors;

                  $.each(errors, function(field, messages) {
                      let input = $('#redDay_' + field);
                      input.addClass('is-invalid');

                      let message = $('#' + field+'-error');
                      message.text(messages[0]).show();
                  });
              }
            }
          });
        });

        $('input[type="checkbox"]:not(#redFullDay, #redDay_repeat, #isFixed, #bookingLimit)').change(function() {
          if ($(this).prop('checked')) {
            $('#'+$(this).val()+'_start').val(null).prop('readonly', true).prop('disabled', true).addClass('disabled').removeAttr('required').flatpickr().destroy();
            $('#'+$(this).val()+'_end').val(null).prop('readonly', true).prop('disabled', true).addClass('disabled').removeAttr('required').flatpickr().destroy();
          } else {
            $('#'+$(this).val()+'_start').val('').attr('required', 'required').prop('readonly', false).prop('disabled', false).removeClass('disabled').flatpickr({
              enableTime: true,
              noCalendar: true,
              dateFormat: "H:i",
              disableMobile: true,
              time_24hr: true
            });
            $('#'+$(this).val()+'_end').val('').attr('required', 'required').prop('readonly', false).prop('disabled', false).removeClass('disabled').flatpickr({
              enableTime: true,
              noCalendar: true,
              dateFormat: "H:i",
              disableMobile: true,
              time_24hr: true
            });
          }
        });
        $('input[type="checkbox"]#isFixed').change(function() {
          if ($(this).prop('checked')) {
            $('#rows-container').removeClass('disabled');
          } else {
            $('#rows-container').addClass('disabled');
          }
        });
        
        $('input[type="checkbox"]#bookingLimit').change(function() {
          if ($(this).prop('checked')) {
            $('#bookingLimit_days').removeClass('bookingLimit_disabled').prop('disabled', false).attr('required', 'required');
          } else {
            $('#bookingLimit_days').addClass('bookingLimit_disabled').prop('disabled', true).removeAttr('required');
          }
        }); 
        $('#bookingLimitDays').submit(function(event){
          event.preventDefault();
          let active = 0;
          if ($('#bookingLimit').prop('checked')) {
            active = 1;
          }

          let formData = {
            days: $("#bookingLimit_days").val(),
            active: active,
          };
        
          $.ajax({  
            url: '/api/settings/updateLimitDays',
            type: "PUT",
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: formData,
            success: function(response) {
              if (response.status === "success") {
                $('.is-invalid').removeClass('is-invalid');
                  Swal.fire({
                    title: 'Отлично!',
                    text: 'Данные успешно сохранены!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                  });
              } else {
                alert("Error: " + response.message);
              }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                  title: 'Упс!',
                  text: 'Что-то пошло не так. Пожалуйста, попробуйте еще раз.',
                  icon: 'error',
                  confirmButtonText: 'OK'
                });
              if (xhr.status === 422) {
                  $('.is-invalid').removeClass('is-invalid');
                  let errors = xhr.responseJSON.errors;

                  $.each(errors, function(field, messages) {
                      let input = $('#bookingLimit-error');
                      input.addClass('is-invalid');

                      let message = $('#bookingLimit-error');
                      message.text(messages[0]).show();
                  });
              }
            }
          });
        });

        $('#redFullDay').change(function() {
            if ($(this).prop('checked')) {
                $('#redDay_start_time').val(null).prop('readonly', true).prop('disabled', true).addClass('disabled').removeAttr('required').flatpickr().destroy();
                $('#redDay_end_time').val(null).prop('readonly', true).prop('disabled', true).addClass('disabled').removeAttr('required').flatpickr().destroy();
            } else {
                $('#redDay_start_time').val('').attr('required', 'required').prop('readonly', false).prop('disabled', false).removeClass('disabled').flatpickr({
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    disableMobile: true,
                    time_24hr: true,
                });
                $('#redDay_end_time').val('').attr('required', 'required').prop('readonly', false).prop('disabled', false).removeClass('disabled').flatpickr({
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    disableMobile: true,
                    time_24hr: true
                });
            }
        });
        $('#fixHoursTable').submit(function(event){
          event.preventDefault();
          
          let fixedHours = [];
          let isFixed = 0;
          
          if ($('#isFixed').prop('checked')) {
            isFixed = 1;
          }

          $(".fixedBookingTime").each(function () {
              let timeValue = $(this).val().trim();
              if (timeValue !== "") {
                  fixedHours.push(timeValue);
              }
          });
        
          $.ajax({  
            url: '/settings/updateFixedBooking',
            type: "POST",
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            contentType: "application/json",
            data: JSON.stringify({ fixedBooking: fixedHours, fixedBookingStatus: isFixed }),
            success: function(response) {
              $('.is-invalid').removeClass('is-invalid');
              if (response.status === "success") {
                    Swal.fire({
                      title: 'Отлично!',
                      text: 'Данные успешно сохранены!',
                      icon: 'success',
                      confirmButtonText: 'OK'
                    });
              } else {
                  alert("Error: " + response.message);
              }
            },
            error: function(xhr, status, error) {
                  Swal.fire({
                    title: 'Упс!',
                    text: 'Что-то пошло не так. Пожалуйста, попробуйте еще раз.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                  });
                if (xhr.status === 422) {
                    $('.is-invalid').removeClass('is-invalid');
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function(field, messages) {
                        let input = $('#lunch_end-error');
                        input.addClass('is-invalid');

                        let message = $('#lunch_end-error');
                        message.text(messages[0]).show();
                    });
                }
            }
          });
        });
        $('#updateSettingsTable').submit(function(e){
          e.preventDefault();
          let formData = {
            company_name: $("#company_name").val(),
            company_email: $("#company_email").val(),
            company_phone: $("#company_phone").val() || null,
            company_address: $("#company_address").val() || null,
            google: $("#google").val() || null,
            waze: $("#waze").val() || null,
            social_media_facebook: $("#social_media_facebook").val() || null,
            social_media_youtube: $("#social_media_youtube").val() || null,
            social_media_instagram: $("#social_media_instagram").val() || null,
            social_media_twitter: $("#social_media_twitter").val() || null,
          };
        
          $.ajax({  
            url: '/settings/updateMainSettings',
            type: "POST",
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: formData,
            success: function(response) {
              $('.is-invalid').removeClass('is-invalid');
              if (response.status === "success") {
                    Swal.fire({
                      title: 'Отлично!',
                      text: 'Данные успешно сохранены!',
                      icon: 'success',
                      confirmButtonText: 'OK'
                    });
              } else {
                  alert("Error: " + response.message);
              }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                  title: 'Упс!',
                  text: 'Что-то пошло не так. Пожалуйста, попробуйте еще раз.',
                  icon: 'error',
                  confirmButtonText: 'OK'
                });
                if (xhr.status === 422) {
                    $('.is-invalid').removeClass('is-invalid');
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function(field, messages) {
                        let input = $('#' + field);
                        input.addClass('is-invalid');

                        let message = $('#' + field+'-error');
                        message.text(messages[0]).show();
                    });
                }
            }
          });
        });

        function renderRedDaysTable(res){
          let tbody = $("#redDaysTable tbody");
          tbody.empty();
          if (res.length === 0) {
            tbody.append(`<tr class="no-data"><td colspan="4" class="text-center">No records found</td></tr>`);
          } else {
            res.forEach(function(day) {
            let startTime = (day.start_time != null && day.end_time != null) ? `(${day.start_time}-${day.end_time})` : '';
            let row = `
                    <tr>
                        <td class="align-middle ps-3 name">${day.name}</td>
                        <td class="align-middle date">${day.date} ${startTime}</td>
                        <td class="align-middle date">${day.repeat === 'yes' ? '✅' : '❌'}</td>
                        <td class="align-middle white-space-nowrap text-end pe-0">
                          <span class="badge badge-phoenix fs-10 badge-phoenix-danger delete-red-day cursor-pointer" data-id="${day.id}">
                            <span class="badge-label">Remove</span><span class="ms-1" data-feather="x" style="height:12.8px;width:12.8px;"></span>
                            <i class="fas fa-trash-alt"></i>
                          </span>
                        </td>
                    </tr>
              `;
              tbody.append(row);
            });
          }
          updateListJS();
        }
        $(document).on("click", ".delete-red-day", function() {
            let button = $(this);
            let redDayId = button.data("id");

            Swal.fire({
                title: "Вы уверены?",
                text: "Это действие нельзя будет отменить!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Да, удалить!",
                cancelButtonText: "Отмена"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `api/settings/deleteRedDay/${redDayId}`,
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                        success: function(response) {
                            if (response.status === "success") {
                                Swal.fire("Удалено!", "День успешно удален.", "success");
                                button.closest("tr").remove();
                            } else {
                                Swal.fire("Ошибка!", response.message, "error");
                            }
                        },
                        error: function(xhr) {
                            Swal.fire("Ошибка!", "Не удалось удалить запись.", "error");
                        }
                    });
                }
            });
        });

        const rowsContainer = document.getElementById("rows-container");
        function initializeFlatpickr() {
          document.querySelectorAll(".datetimepicker").forEach((input) => {
            if (!input._flatpickr) { 
                flatpickr(input, {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    disableMobile: true,
                    time_24hr: true,
                });
            }
          });
        }
        function renderButtons() {
          const rows = rowsContainer.querySelectorAll(".row");
          rows.forEach((row, index) => {
              const button = row.querySelector(".add-row-btn");
              if (index === rows.length - 1) {
                  button.textContent = "Добавить строку";
                  button.classList.replace("badge-phoenix-danger", "badge-phoenix-secondary");
                  button.removeEventListener("click", deleteRow);
                  button.addEventListener("click", addRow);
              } else {
                  button.textContent = "Удалить строку";
                  button.classList.replace("badge-phoenix-secondary", "badge-phoenix-danger");
                  button.removeEventListener("click", addRow);
                  button.addEventListener("click", deleteRow);
              }
          });
        }
        function addRow() {
            const rows = rowsContainer.querySelectorAll(".row");
            const lastRow = rows[rows.length - 1];
            const newIndex = rows.length + 1;
            const newRow = lastRow.cloneNode(true);
        
            newRow.querySelector("[id$='_start']").id = `${newIndex}_start`;
            newRow.querySelector("[id$='_start-error']").id = `${newIndex}_start-error`;
        
            newRow.querySelector("[id$='_start']").value = "";
        
            rowsContainer.appendChild(newRow);
            initializeFlatpickr(); 
            renderButtons();
        }
        function deleteRow(event) {
            if (rowsContainer.children.length > 1) {
                event.target.closest(".row").remove();
                renderButtons();
            }
        }
        function getFixedBooking() {
          //get api fixedBooking
          $.ajax({  
              url: 'api/settings/getFixedBooking',
              type: "GET",
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
              },
              success: function(response) {
                let fixedHours = [];

                if (typeof response.fixedBooking === "string") {
                    try {
                        fixedHours = JSON.parse(response.fixedBooking);
                        
                        if(fixedHours[0]){
                          $("#isFixed").prop("checked", true);
                        } else {
                          $("#isFixed").prop("checked", false);
                        }
                    } catch (error) {
                        console.error("Ошибка парсинга JSON:", error);
                    }
                } else if (Array.isArray(response.fixedBooking)) {
                    fixedHours = response.fixedBooking;
                    if(response.fixedBookingStatus === 1){
                      $("#isFixed").prop("checked", true);
                      $('#rows-container').removeClass('disabled');
                    } else {
                      $("#isFixed").prop("checked", false);
                      $('#rows-container').addClass('disabled');
                    }
                }
              
                rowsContainer.innerHTML = "";

                fixedHours.forEach((time, index) => {
                    rowsContainer.innerHTML += `
                        <div class="row my-1">
                            <div class="col-md-4">
                                <label class="form-label" for="${index + 1}_start">Время начала</label>
                                <input class="form-control datetimepicker fixedBookingTime flatpickr-input" id="${index + 1}_start" type="text" 
                                       placeholder="hour : minute" value="${time}" 
                                       data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true }' readonly>
                                <div class="invalid-feedback" id="${index + 1}_start-error"></div>
                            </div>
                            <div class="col-md-2 d-flex align-items-center justify-content-end">
                                <button type="button" class="badge badge-phoenix badge-phoenix-danger fixed_hrs__btn add-row-btn mt-2">
                                    Удалить строку <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });

                initializeFlatpickr(); 
                renderButtons();
              }
          });
        }
        getFixedBooking();
      });
      
      let listObj;
      function updateListJS() {
        
          listObj = new List('tableExample', {
              valueNames: ['name', 'date', 'repeat'],
              page: 10,
          });
      }
    </script>
    @endpush
  </x-dashboard-layout>
