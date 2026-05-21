@section('title', 'Settings')
@push('styles')
<link href="{{ asset('vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
<style>
  .settings-tabs .nav-link {
    color: var(--phoenix-body-color);
    border-radius: 0;
    border-bottom: 2px solid transparent;
    padding: 0.75rem 1.25rem;
    font-weight: 500;
    white-space: nowrap;
  }
  .settings-tabs .nav-link.active {
    color: var(--phoenix-primary);
    border-bottom: 2px solid var(--phoenix-primary);
    background: transparent;
  }
  .settings-tabs {
    border-bottom: 1px solid var(--phoenix-border-color);
    overflow-x: auto;
    flex-wrap: nowrap;
  }
  .tab-content > .tab-pane {
    display: none;
  }
  .tab-content > .tab-pane.active {
    display: block;
  }
</style>
@endpush

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">Настройки</li>
      </ol>
    </nav>

    {{-- Табы --}}
    <ul class="nav settings-tabs mb-4" id="settingsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-workhours" type="button">
          <span class="fas fa-clock me-1"></span>Рабочее время
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-reddays" type="button">
          <span class="fas fa-calendar-times me-1"></span>Нерабочие дни
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-booking" type="button">
          <span class="fas fa-lock me-1"></span>Бронирование
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-site" type="button">
          <span class="fas fa-cog me-1"></span>Настройки сайта
        </button>
      </li>
    </ul>

    <div class="tab-content">

      {{-- ТАБ 1: Рабочее время + Обед --}}
      <div class="tab-pane active" id="tab-workhours">
        <div class="row g-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header"><h5 class="mb-0">Рабочее время</h5></div>
              <div class="card-body">
                <form id="workHoursTable">
                  @csrf
                  @php
                    $daysInRussian = [
                      'monday' => 'Понедельник', 'tuesday' => 'Вторник',
                      'wednesday' => 'Среда', 'thursday' => 'Четверг',
                      'friday' => 'Пятница', 'saturday' => 'Суббота', 'sunday' => 'Воскресенье',
                    ];
                  @endphp
                  <div class="table-responsive">
                    <table class="table table-sm align-middle">
                      <thead>
                        <tr>
                          <th>День</th>
                          <th>Начало</th>
                          <th>Конец</th>
                          <th>Выходной</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($workHours as $key => $item)
                        <tr>
                          <td><strong>{{ $daysInRussian[$key] }}</strong></td>
                          <td>
                            <input class="form-control form-control-sm datetimepicker flatpickr-input {{ $item['start'] == null ? 'disabled' : '' }}"
                                   id="{{ $key }}_start" type="text" placeholder="HH:mm"
                                   value="{{ $item['start'] }}"
                                   data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true}'
                                   style="width:120px" readonly
                                   {{ $item['start'] == null ? 'disabled' : '' }}>
                          </td>
                          <td>
                            <input class="form-control form-control-sm datetimepicker flatpickr-input {{ $item['end'] == null ? 'disabled' : '' }}"
                                   id="{{ $key }}_end" type="text" placeholder="HH:mm"
                                   value="{{ $item['end'] }}"
                                   data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true}'
                                   style="width:120px" readonly
                                   {{ $item['end'] == null ? 'disabled' : '' }}>
                          </td>
                          <td>
                            <div class="form-check">
                              <input class="form-check-input day-null-checkbox" type="checkbox"
                                     id="{{ $key }}" value="{{ $key }}"
                                     {{ $item['start'] == null ? 'checked' : '' }}>
                              <label class="form-check-label text-muted fs-10" for="{{ $key }}">Выходной</label>
                            </div>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  <div class="text-end mt-3">
                    <button class="btn btn-primary" type="submit">Сохранить рабочее время</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="card">
              <div class="card-header"><h5 class="mb-0">Обеденный перерыв</h5></div>
              <div class="card-body">
                <form id="lunchHoursTable">
                  @csrf
                  <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                      <label class="form-label">Начало обеда</label>
                      <input class="form-control datetimepicker flatpickr-input {{ $lunchHours['start'] == null ? 'disabled' : '' }}"
                             id="lunch_start" type="text" placeholder="HH:mm"
                             value="{{ $lunchHours['start'] }}"
                             data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true}'
                             readonly {{ $lunchHours['start'] == null ? 'disabled' : '' }}>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Конец обеда</label>
                      <input class="form-control datetimepicker flatpickr-input {{ $lunchHours['end'] == null ? 'disabled' : '' }}"
                             id="lunch_end" type="text" placeholder="HH:mm"
                             value="{{ $lunchHours['end'] }}"
                             data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true}'
                             readonly {{ $lunchHours['end'] == null ? 'disabled' : '' }}>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="lunch"
                               value="lunch" {{ $lunchHours['start'] == null ? 'checked' : '' }}>
                        <label class="form-check-label" for="lunch">Нет обеда</label>
                      </div>
                    </div>
                  </div>
                  <div class="text-end mt-3">
                    <button class="btn btn-primary" type="submit">Сохранить обед</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ТАБ 2: Нерабочие дни --}}
      <div class="tab-pane" id="tab-reddays">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Нерабочие дни / часы</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#redDays_create">
              <span class="fas fa-plus me-1"></span>Добавить
            </button>
          </div>
          <div class="card-body">
            {{-- Форма добавления --}}
            <div class="collapse mb-4" id="redDays_create">
              <div class="card card-body bg-light">
                <form id="newRedDay" class="row g-3">
                  @csrf
                  <div class="col-md-6">
                    <label class="form-label">Название*</label>
                    <input class="form-control" type="text" id="redDay_name" placeholder="Рождество">
                    <div class="invalid-feedback" id="name-error"></div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Дата*</label>
                    <input class="form-control datetimepicker flatpickr-input" id="redDay_date" type="text"
                           placeholder="Y-m-d" data-options='{"dateFormat":"Y-m-d","disableMobile":true}' readonly>
                    <div class="invalid-feedback" id="date-error"></div>
                  </div>
                  <div class="col-md-3 d-flex align-items-center">
                    <div class="form-check">
                      <input class="form-check-input" id="redFullDay" type="checkbox">
                      <label class="form-check-label" for="redFullDay">Весь день</label>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Начало</label>
                    <input class="form-control datetimepicker flatpickr-input" id="redDay_start_time" type="text"
                           placeholder="HH:mm" data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true}' readonly>
                    <div class="invalid-feedback" id="start_time-error"></div>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Конец</label>
                    <input class="form-control datetimepicker flatpickr-input" id="redDay_end_time" type="text"
                           placeholder="HH:mm" data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true}' readonly>
                    <div class="invalid-feedback" id="end_time-error"></div>
                  </div>
                  <div class="col-md-3 d-flex align-items-center">
                    <div class="form-check mt-3">
                      <input class="form-check-input" id="redDay_repeat" type="checkbox" checked>
                      <label class="form-check-label" for="redDay_repeat">Повторять ежегодно</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Привязать к мастеру</label>
                    <select class="form-select" id="redDay_user_id">
                      <option value="">Общий (для всех)</option>
                      @foreach(\App\Models\User::whereIn('role_id', [1, 2])->orderBy('name')->get() as $master)
                        <option value="{{ $master->id }}">{{ $master->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Описание</label>
                    <input class="form-control" type="text" id="redDay_desc" placeholder="Необязательно">
                  </div>
                  <div class="col-12 text-end">
                    <button class="btn btn-primary" type="submit">Сохранить</button>
                  </div>
                </form>
              </div>
            </div>

            {{-- Список --}}
            <div class="search-box mb-3">
              <form class="position-relative">
                <input class="form-control search-input search form-control-sm" type="search" placeholder="Поиск..." />
                <span class="fas fa-search search-box-icon"></span>
              </form>
            </div>
            <div id="tableExample" data-list='{"valueNames":["name","date","repeat"],"page":15}'>
              <div class="table-responsive">
                <table id="redDaysTable" class="table table-sm fs-9 mb-0">
                  <thead>
                    <tr>
                      <th class="sort ps-3" data-sort="name">Название</th>
                      <th class="sort" data-sort="date">Дата и время</th>
                      <th>Мастер</th>
                      <th class="sort" data-sort="repeat">Повтор</th>
                      <th class="text-end pe-0">Действие</th>
                    </tr>
                  </thead>
                  <tbody class="list">
                    <tr><td colspan="5" class="text-center text-muted">Загрузка...</td></tr>
                  </tbody>
                </table>
              </div>
              <div class="row align-items-center justify-content-between py-2 fs-9">
                <div class="col-auto d-flex">
                  <p class="mb-0 d-none d-sm-block me-3 fw-semibold" data-list-info></p>
                  <a class="fw-semibold" href="#!" data-list-view="*">Показать все</a>
                  <a class="fw-semibold d-none" href="#!" data-list-view="less">Свернуть</a>
                </div>
                <div class="col-auto d-flex">
                  <button class="page-link" data-list-pagination="prev"><span class="fas fa-chevron-left"></span></button>
                  <ul class="mb-0 pagination"></ul>
                  <button class="page-link pe-0" data-list-pagination="next"><span class="fas fa-chevron-right"></span></button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ТАБ 3: Бронирование --}}
      <div class="tab-pane" id="tab-booking">
        <div class="row g-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header"><h5 class="mb-0">Фиксированная запись на услуги</h5></div>
              <div class="card-body">
                <p class="text-muted fs-9 mb-3">Если включено — клиенты смогут записываться только на указанные слоты. Это глобальная настройка для мастеров без персонального расписания.</p>
                <form id="fixHoursTable">
                  @csrf
                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" id="isFixed" type="checkbox">
                    <label class="form-check-label" for="isFixed">Включить фиксированное время</label>
                  </div>
                  <div id="rows-container" class="disabled"></div>
                  <div class="text-end mt-3">
                    <button class="btn btn-primary" type="submit">Сохранить</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="card">
              <div class="card-header"><h5 class="mb-0">Лимит дней на бронирование</h5></div>
              <div class="card-body">
                <form id="bookingLimitDays">
                  @csrf
                  <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                      <label class="form-label">Количество дней вперёд для бронирования</label>
                      <input class="form-control {{ $bookingLimit['active'] == false ? 'bookingLimit_disabled' : '' }}"
                             type="number" id="bookingLimit_days"
                             value="{{ $bookingLimit['days'] }}"
                             {{ $bookingLimit['active'] == false ? 'disabled' : '' }}>
                      <div class="invalid-feedback" id="bookingLimit-error"></div>
                    </div>
                    <div class="col-md-2">
                      <button class="btn btn-primary w-100" type="submit">Сохранить</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ТАБ 4: Настройки сайта --}}
      <div class="tab-pane" id="tab-site">
        <div class="card">
          <div class="card-header"><h5 class="mb-0">Настройки сайта</h5></div>
          <div class="card-body">
            <form id="updateSettingsTable">
              @csrf
              <h6 class="mb-3">Компания</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label">Название</label>
                  <input class="form-control" id="company_name" type="text">
                  <div class="invalid-feedback" id="company_name-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input class="form-control" id="company_email" type="email">
                  <div class="invalid-feedback" id="company_email-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Телефон</label>
                  <input class="form-control" id="company_phone" type="text" pattern="^\+.*">
                  <div class="invalid-feedback" id="company_phone-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Адрес</label>
                  <input class="form-control" id="company_address" type="text">
                  <div class="invalid-feedback" id="company_address-error"></div>
                </div>
              </div>

              <h6 class="mb-3">Карта <small class="text-muted fw-normal">(вставьте iframe код)</small></h6>
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label">Google Maps</label>
                  <input class="form-control" id="google" type="text">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Waze</label>
                  <input class="form-control" id="waze" type="text">
                </div>
              </div>

              <h6 class="mb-3">Социальные сети</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label">Facebook</label>
                  <input class="form-control" id="social_media_facebook" type="text">
                </div>
                <div class="col-md-6">
                  <label class="form-label">YouTube</label>
                  <input class="form-control" id="social_media_youtube" type="text">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Instagram</label>
                  <input class="form-control" id="social_media_instagram" type="text">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Twitter / X</label>
                  <input class="form-control" id="social_media_twitter" type="text">
                </div>
              </div>

              <div class="text-end">
                <button class="btn btn-primary" type="submit">Сохранить настройки</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
    <x-dashboard-footer />
  </div>

  @push('scripts')
  <script src="{{ asset('vendors/flatpickr/flatpickr.min.js') }}"></script>
  <script src="vendors/sortablejs/Sortable.min.js"></script>
  <script>
    $(document).ready(function() {

      // Инициализация flatpickr
      flatpickr(".datetimepicker", {
        enableTime: true, noCalendar: true, dateFormat: "H:i",
        disableMobile: true, time_24hr: true,
        onClose: function(selectedDates, dateStr, instance) {
          if (!dateStr) instance.setDate(instance.input.defaultValue, false);
        }
      });

      // Загрузка нерабочих дней
      $.ajax({
        url: 'api/settings/getRedDays', type: "GET",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
          var redDaysArray = response['fullRedDays'].map(item => item.date);
          flatpickr('#redDay_date', {
            dateFormat: "Y-m-d", disableMobile: true,
            onDayCreate: function(dObj, dStr, fp, dayElem) {
              if (redDaysArray.includes(fp.formatDate(dayElem.dateObj, "Y-m-d")))
                dayElem.classList.add("calendar_daysOff");
            }
          });
          renderRedDaysTable(response['redDays']);
        }
      });

      // Загрузка настроек сайта
      $.ajax({
        url: 'api/settings/mainSettings', type: "GET",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
          response['mainSettings'].forEach(function(item) {
            let el = document.getElementById(item.key);
            if (el) el.value = item.payload;
          });
        }
      });

      // Чекбокс выходного дня
      $('.day-null-checkbox').change(function() {
        const val = $(this).val();
        if ($(this).prop('checked')) {
          $('#'+val+'_start').val(null).prop('disabled', true).addClass('disabled');
          if ($('#'+val+'_start')[0]._flatpickr) $('#'+val+'_start')[0]._flatpickr.destroy();
          $('#'+val+'_end').val(null).prop('disabled', true).addClass('disabled');
          if ($('#'+val+'_end')[0]._flatpickr) $('#'+val+'_end')[0]._flatpickr.destroy();
        } else {
          ['_start','_end'].forEach(suffix => {
            const el = $('#'+val+suffix);
            el.val('').prop('disabled', false).removeClass('disabled');
            flatpickr(el[0], { enableTime: true, noCalendar: true, dateFormat: "H:i", disableMobile: true, time_24hr: true });
          });
        }
      });

      // Чекбокс обеда
      $('#lunch').change(function() {
        if ($(this).prop('checked')) {
          $('#lunch_start, #lunch_end').val(null).prop('disabled', true).addClass('disabled');
        } else {
          $('#lunch_start, #lunch_end').prop('disabled', false).removeClass('disabled');
          flatpickr('#lunch_start', { enableTime: true, noCalendar: true, dateFormat: "H:i", disableMobile: true, time_24hr: true });
          flatpickr('#lunch_end', { enableTime: true, noCalendar: true, dateFormat: "H:i", disableMobile: true, time_24hr: true });
        }
      });

      // Весь день
      $('#redFullDay').change(function() {
        if ($(this).prop('checked')) {
          $('#redDay_start_time, #redDay_end_time').val(null).prop('disabled', true).addClass('disabled');
        } else {
          $('#redDay_start_time, #redDay_end_time').prop('disabled', false).removeClass('disabled');
          flatpickr('#redDay_start_time', { enableTime: true, noCalendar: true, dateFormat: "H:i", disableMobile: true, time_24hr: true });
          flatpickr('#redDay_end_time', { enableTime: true, noCalendar: true, dateFormat: "H:i", disableMobile: true, time_24hr: true });
        }
      });

      // Сохранить рабочее время
      $('#workHoursTable').submit(function(e) {
        e.preventDefault();
        let formData = {};
        ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'].forEach(d => {
          formData[d+'_start'] = $('#'+d+'_start').val();
          formData[d+'_end'] = $('#'+d+'_end').val();
        });
        $.ajax({
          url: '/settings/updateWorkHours', type: "POST",
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: formData,
          success: () => showSuccess(),
          error: (xhr) => showError(xhr)
        });
      });

      // Сохранить обед
      $('#lunchHoursTable').submit(function(e) {
        e.preventDefault();
        $.ajax({
          url: '/settings/updateLunchHours', type: "POST",
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: { lunch_start: $("#lunch_start").val(), lunch_end: $("#lunch_end").val() },
          success: () => showSuccess(),
          error: (xhr) => showError(xhr)
        });
      });

      // Добавить нерабочий день
      $('#newRedDay').submit(function(e) {
        e.preventDefault();
        let repeat = $('#redDay_repeat').prop('checked') ? 1 : 0;
        $.ajax({
          url: '/settings/storeRedDay', type: "POST",
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: {
            name: $("#redDay_name").val(), description: $("#redDay_desc").val(),
            date: $("#redDay_date").val(), start_time: $("#redDay_start_time").val(),
            end_time: $("#redDay_end_time").val(), repeat: repeat,
            user_id: $("#redDay_user_id").val() || null,
          },
          success: function(response) {
            if (response.status === "success") {
              $("#redDay_name, #redDay_desc, #redDay_start_time, #redDay_end_time").val('');
              renderRedDaysTable(response['redDays']);
              $('#redDays_create').collapse('hide');
              showSuccess();
            }
          },
          error: (xhr) => showError(xhr)
        });
      });

      // Удалить нерабочий день
      $(document).on("click", ".delete-red-day", function() {
        let id = $(this).data("id");
        let row = $(this).closest("tr");
        Swal.fire({
          title: "Вы уверены?", icon: "warning", showCancelButton: true,
          confirmButtonColor: "#d33", cancelButtonColor: "#3085d6",
          confirmButtonText: "Удалить", cancelButtonText: "Отмена"
        }).then(result => {
          if (result.isConfirmed) {
            $.ajax({
              url: `api/settings/deleteRedDay/${id}`, type: "DELETE",
              headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              success: () => { row.remove(); showSuccess(); },
              error: () => Swal.fire("Ошибка!", "Не удалось удалить.", "error")
            });
          }
        });
      });

      // Фиксированное время
      $('#isFixed').change(function() {
        $('#rows-container').toggleClass('disabled', !$(this).prop('checked'));
      });

      const rowsContainer = document.getElementById("rows-container");

      function initializeFlatpickr() {
        document.querySelectorAll(".datetimepicker:not(.flatpickr-input)").forEach(input => {
          if (!input._flatpickr) {
            flatpickr(input, { enableTime: true, noCalendar: true, dateFormat: "H:i", disableMobile: true, time_24hr: true });
          }
        });
      }

      function renderButtons() {
        const rows = rowsContainer.querySelectorAll(".row");
        rows.forEach((row, index) => {
          const button = row.querySelector(".add-row-btn");
          if (!button) return;
          if (index === rows.length - 1) {
            button.textContent = "Добавить слот";
            button.classList.replace("badge-phoenix-danger", "badge-phoenix-secondary");
            button.onclick = addRow;
          } else {
            button.textContent = "Удалить";
            button.classList.replace("badge-phoenix-secondary", "badge-phoenix-danger");
            button.onclick = deleteRow;
          }
        });
      }

      function addRow() {
        const rows = rowsContainer.querySelectorAll(".row");
        const newIndex = rows.length + 1;
        const newRow = rows[rows.length - 1].cloneNode(true);
        newRow.querySelector("[id$='_start']").id = `${newIndex}_start`;
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

      // Загрузка фиксированного бронирования
      $.ajax({
        url: 'api/settings/getFixedBooking', type: "GET",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
          let fixedHours = Array.isArray(response.fixedBooking) ? response.fixedBooking : [];
          try { if (typeof response.fixedBooking === "string") fixedHours = JSON.parse(response.fixedBooking); } catch(e) {}

          const isFixed = response.fixedBookingStatus === 1;
          $("#isFixed").prop("checked", isFixed);
          $('#rows-container').toggleClass('disabled', !isFixed);

          rowsContainer.innerHTML = "";
          fixedHours.forEach((time, index) => {
            rowsContainer.innerHTML += `
              <div class="row my-1">
                <div class="col-md-4">
                  <input class="form-control form-control-sm datetimepicker fixedBookingTime" id="${index+1}_start" type="text" placeholder="HH:mm" value="${time}" readonly>
                </div>
                <div class="col-md-2 d-flex align-items-center">
                  <button type="button" class="badge badge-phoenix badge-phoenix-danger add-row-btn mt-1">Удалить</button>
                </div>
              </div>`;
          });
          initializeFlatpickr();
          renderButtons();
        }
      });

      $('#fixHoursTable').submit(function(e) {
        e.preventDefault();
        let fixedHours = [];
        $(".fixedBookingTime").each(function() {
          let v = $(this).val().trim();
          if (v) fixedHours.push(v);
        });
        $.ajax({
          url: '/settings/updateFixedBooking', type: "POST",
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          contentType: "application/json",
          data: JSON.stringify({ fixedBooking: fixedHours, fixedBookingStatus: $('#isFixed').prop('checked') ? 1 : 0 }),
          success: () => showSuccess(),
          error: (xhr) => showError(xhr)
        });
      });

      // Лимит дней
      $('#bookingLimitDays').submit(function(e) {
        e.preventDefault();
        $.ajax({
          url: '/api/settings/updateLimitDays', type: "PUT",
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: { days: $("#bookingLimit_days").val(), active: 0 },
          success: () => showSuccess(),
          error: (xhr) => showError(xhr)
        });
      });

      // Настройки сайта
      $('#updateSettingsTable').submit(function(e) {
        e.preventDefault();
        $.ajax({
          url: '/settings/updateMainSettings', type: "POST",
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: {
            company_name: $("#company_name").val(), company_email: $("#company_email").val(),
            company_phone: $("#company_phone").val() || null, company_address: $("#company_address").val() || null,
            google: $("#google").val() || null, waze: $("#waze").val() || null,
            social_media_facebook: $("#social_media_facebook").val() || null,
            social_media_youtube: $("#social_media_youtube").val() || null,
            social_media_instagram: $("#social_media_instagram").val() || null,
            social_media_twitter: $("#social_media_twitter").val() || null,
          },
          success: () => showSuccess(),
          error: (xhr) => showError(xhr)
        });
      });

      function renderRedDaysTable(res) {
        let tbody = $("#redDaysTable tbody");
        tbody.empty();
        if (!res.length) {
          tbody.append('<tr><td colspan="5" class="text-center text-muted">Нет записей</td></tr>');
          return;
        }
        res.forEach(day => {
          let timeStr = (day.start_time && day.end_time) ? `<span class="text-muted fs-10">(${day.start_time}–${day.end_time})</span>` : '';
          let masterBadge = day.user_id
            ? `<span class="badge badge-phoenix badge-phoenix-primary fs-10">${day.master_name}</span>`
            : `<span class="badge badge-phoenix badge-phoenix-secondary fs-10">Общий</span>`;
          tbody.append(`
            <tr>
              <td class="ps-3 name">${day.name}</td>
              <td class="date">${day.date} ${timeStr}</td>
              <td>${masterBadge}</td>
              <td class="repeat">${day.repeat === 'yes' ? '✅' : '❌'}</td>
              <td class="text-end pe-0">
                <button class="btn btn-sm btn-outline-danger delete-red-day" data-id="${day.id}">
                  <span class="fas fa-trash"></span>
                </button>
              </td>
            </tr>
          `);
        });
        updateListJS();
      }

      function showSuccess() {
        Swal.fire({ title: 'Отлично!', text: 'Данные успешно сохранены!', icon: 'success', confirmButtonText: 'OK' });
      }
      function showError(xhr) {
        Swal.fire({ title: 'Упс!', text: 'Что-то пошло не так.', icon: 'error', confirmButtonText: 'OK' });
        if (xhr.status === 422) {
          $.each(xhr.responseJSON.errors, function(field, messages) {
            $('#'+field).addClass('is-invalid');
            $('#'+field+'-error').text(messages[0]).show();
          });
        }
      }

      let listObj;
      function updateListJS() {
        if (listObj) listObj.reIndex();
        else listObj = new List('tableExample', { valueNames: ['name', 'date', 'repeat'], page: 15 });
      }
    });
  </script>
  @endpush
</x-dashboard-layout>