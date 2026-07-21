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
  .booking-template-option { transition: .2s ease; }
  .btn-check:checked + .booking-template-option {
    border-color: var(--phoenix-primary) !important;
    box-shadow: 0 0 0 3px rgba(56, 116, 255, .16);
    background: rgba(56, 116, 255, .04);
  }
  .booking-template-option .template-selected { display:none; }
  .btn-check:checked + .booking-template-option .template-selected { display:inline-flex; }
</style>
@endpush

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">Настройки</li>
      </ol>
    </nav>

    <ul class="nav settings-tabs mb-4" id="settingsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-booking" type="button">
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

      {{-- ТАБ 3: Бронирование --}}
      <div class="tab-pane active" id="tab-booking">
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
                      <input class="form-control"
                            type="number" id="bookingLimit_days"
                            value="{{ $bookingLimit['days'] }}"
                            min="1" max="365">
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
            <form id="updateSettingsTable" enctype="multipart/form-data">
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
                <div class="col-md-6">
                  <label class="form-label">Регистрационный номер <span class="text-muted">(необязательно)</span></label>
                  <input class="form-control" id="company_registration_number" name="company_registration_number" type="text">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Расчётный счёт <span class="text-muted">(необязательно)</span></label>
                  <input class="form-control" id="company_bank_account" name="company_bank_account" type="text">
                </div>
              </div>

              <h6 class="mb-3">Шаблон записи на услугу</h6>
              <p class="text-muted fs-9">Выберите, как клиент будет проходить шаги услуги, мастера и времени.</p>
              <div class="row g-3 mb-4">
                @foreach([
                  'classic' => ['На одном экране', 'Услуга, мастер, календарь и время без переходов между этапами.'],
                  'wizard' => ['Сначала специалист', 'Сначала клиент выбирает мастера, затем видит только его услуги и время.'],
                  'compact' => ['Компактный', 'Плотная сетка для быстрого выбора на одном экране.'],
                ] as $template => [$title, $description])
                  <div class="col-md-4">
                    <input class="btn-check" type="radio" name="booking_template" id="booking_template_{{ $template }}" value="{{ $template }}" {{ $template === 'classic' ? 'checked' : '' }}>
                    <label class="booking-template-option card h-100 border cursor-pointer" for="booking_template_{{ $template }}">
                      <span class="card-body"><span class="d-flex justify-content-between fw-semibold mb-2">{{ $title }} <span class="template-selected badge badge-phoenix badge-phoenix-primary">Выбран</span></span><span class="text-muted fs-9">{{ $description }}</span></span>
                    </label>
                  </div>
                @endforeach
                <div class="invalid-feedback" id="booking_template-error"></div>
              </div>
              <div class="text-end mb-5">
                <button class="btn btn-primary" type="submit"><span class="fas fa-check me-2"></span>Сохранить шаблон</button>
              </div>

              <h6 class="mb-3">Краткое описание компании</h6>
              <p class="text-muted fs-9">Показывается в подвале сайта рядом с логотипом.</p>
              <ul class="nav nav-tabs mb-3" role="tablist">
                @foreach($supportedLocales as $locale => $language)
                  <li class="nav-item" role="presentation"><button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#description-{{ $locale }}" type="button">{{ $language }}</button></li>
                @endforeach
              </ul>
              <div class="tab-content mb-4">
                @foreach($supportedLocales as $locale => $language)
                  <div class="tab-pane {{ $loop->first ? 'active' : '' }}" id="description-{{ $locale }}">
                    <textarea class="tinyarea" id="company_short_description_{{ $locale }}" name="company_short_description[{{ $locale }}]" data-locale="{{ $locale }}" data-tinymce="{}"></textarea>
                    <div class="invalid-feedback" id="company_short_description.{{ $locale }}-error"></div>
                  </div>
                @endforeach
              </div>

              <h6 class="mb-3">Логотипы и favicon</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-4"><label class="form-label">Логотип в шапке</label><div class="border rounded p-2 mb-2 text-center"><img id="logo-preview" class="img-fluid" style="height:70px;display:none" alt="Установленный логотип"></div><input class="form-control" id="logo" name="logo" type="file" accept=".png,.jpg,.jpeg,.webp,.svg"><div class="invalid-feedback" id="logo-error"></div></div>
                <div class="col-md-4"><label class="form-label">Логотип в подвале</label><div class="border rounded p-2 mb-2 text-center"><img id="footer_logo-preview" class="img-fluid" style="height:70px;display:none" alt="Установленный логотип подвала"></div><input class="form-control" id="footer_logo" name="footer_logo" type="file" accept=".png,.jpg,.jpeg,.webp,.svg"><div class="invalid-feedback" id="footer_logo-error"></div></div>
                <div class="col-md-4"><label class="form-label">Favicon</label><div class="border rounded p-2 mb-2 text-center"><img id="favicon-preview" class="img-fluid" style="height:70px;display:none" alt="Установленный favicon"></div><input class="form-control" id="favicon" name="favicon" type="file" accept=".png,.ico,.svg"><div class="invalid-feedback" id="favicon-error"></div></div>
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
    {{-- Модалка редактирования нерабочего дня --}}
    <div class="modal fade" id="editRedDayModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Редактировать нерабочий день</h5>
            <button class="btn btn-close p-1" type="button" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="edit_rd_id">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Название*</label>
                <input type="text" class="form-control" id="edit_rd_name">
              </div>
              <div class="col-12">
                <label class="form-label">Дата*</label>
                <input type="date" class="form-control" id="edit_rd_date">
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="edit_rd_fullday">
                  <label class="form-check-label" for="edit_rd_fullday">Весь день</label>
                </div>
              </div>
              <div id="edit_rd_time_block" class="col-6">
                <label class="form-label">Начало</label>
                <input type="time" class="form-control" id="edit_rd_start">
              </div>
              <div id="edit_rd_time_block2" class="col-6">
                <label class="form-label">Конец</label>
                <input type="time" class="form-control" id="edit_rd_end">
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="edit_rd_repeat">
                  <label class="form-check-label" for="edit_rd_repeat">Повторять ежегодно</label>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label">Описание</label>
                <input type="text" class="form-control" id="edit_rd_description">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Отмена</button>
            <button class="btn btn-primary" type="button" id="saveEditRedDay">Сохранить</button>
          </div>
        </div>
      </div>
    </div>
    <x-dashboard-footer />
  </div>

  @push('scripts')
  <script src="{{ asset('vendors/flatpickr/flatpickr.min.js') }}"></script>
  <script src="vendors/sortablejs/Sortable.min.js"></script>
  <script src="{{ asset('assets/js/tiny.js') }}"></script>
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
        url: '{{ route('settings.red-days.index') }}', type: "GET",
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
        url: '{{ route('settings.main') }}', type: "GET",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
          response['mainSettings'].forEach(function(item) {
            if (item.key === 'booking_template') {
              $('input[name="booking_template"][value="' + item.payload + '"]').prop('checked', true);
              return;
            }
            if (item.key === 'company_short_description' && item.payload) {
              Object.entries(item.payload).forEach(function([locale, html]) {
                const editorId = 'company_short_description_' + locale;
                const editor = tinymce.get(editorId);
                if (editor) editor.setContent(html || '');
                else $('#' + editorId).val(html || '');
              });
              return;
            }
            if (item.public_url && ['logo', 'footer_logo', 'favicon'].includes(item.key)) {
              $('#' + item.key + '-preview').attr('src', item.public_url).show();
              return;
            }
            let el = document.getElementById(item.key);
            if (el && el.type !== 'file') el.value = item.payload ?? '';
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
              url: `{{ url('settings/red-days') }}/${id}`, type: "DELETE",
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
        url: '{{ route('settings.fixed-booking') }}', type: "GET",
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
          url: '{{ route('settings.booking-limit.update') }}', type: "PUT",
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: { days: $("#bookingLimit_days").val(), active: 0 },
          success: () => showSuccess(),
          error: (xhr) => showError(xhr)
        });
      });

      // Настройки сайта
      $('#updateSettingsTable').submit(function(e) {
        e.preventDefault();
        tinymce.triggerSave();
        const formData = new FormData(this);
        ['company_name', 'company_email', 'company_phone', 'company_address',
         'company_registration_number', 'company_bank_account',
         'google', 'waze', 'social_media_facebook', 'social_media_youtube',
         'social_media_instagram', 'social_media_twitter'].forEach(function(key) {
          formData.set(key, $('#' + key).val() || '');
        });
        $.ajax({
          url: '{{ route('settings.main.update') }}', type: "POST",
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: formData,
          processData: false,
          contentType: false,
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
                  <tr id="rd_row_${day.id}">
                      <td class="ps-3 name">${day.name}</td>
                      <td class="date">${day.date} ${timeStr}</td>
                      <td>${masterBadge}</td>
                      <td class="repeat">${day.repeat === 'yes' ? '✅' : '❌'}</td>
                      <td class="text-end pe-0">
                          <button class="btn btn-sm btn-outline-primary edit-red-day me-1"
                              data-id="${day.id}"
                              data-name="${day.name}"
                              data-date="${day.date}"
                              data-start="${day.start_time || ''}"
                              data-end="${day.end_time || ''}"
                              data-repeat="${day.repeat === 'yes' ? 1 : 0}"
                              data-description="${day.description || ''}"
                              data-fullday="${(!day.start_time && !day.end_time) ? 1 : 0}">
                              <span class="fas fa-edit"></span>
                          </button>
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
      // Открыть модалку редактирования
      $(document).on('click', '.edit-red-day', function () {
          const id = $(this).data('id');
          const fullDay = $(this).data('fullday') == 1;

          $('#edit_rd_id').val(id);
          $('#edit_rd_name').val($(this).data('name'));
          $('#edit_rd_date').val($(this).data('date'));
          $('#edit_rd_start').val($(this).data('start'));
          $('#edit_rd_end').val($(this).data('end'));
          $('#edit_rd_repeat').prop('checked', $(this).data('repeat') == 1);
          $('#edit_rd_description').val($(this).data('description'));
          $('#edit_rd_fullday').prop('checked', fullDay);
          $('#edit_rd_time_block, #edit_rd_time_block2').toggle(!fullDay);

          $('#editRedDayModal').modal('show');
      });

      // Чекбокс весь день в модалке
      $('#edit_rd_fullday').on('change', function () {
          const isFullDay = $(this).is(':checked');
          $('#edit_rd_time_block, #edit_rd_time_block2').toggle(!isFullDay);
          if (isFullDay) {
              $('#edit_rd_start, #edit_rd_end').val('');
          }
      });

      // Сохранить редактирование
      $('#saveEditRedDay').on('click', function () {
          const id = $('#edit_rd_id').val();
          const isFullDay = $('#edit_rd_fullday').is(':checked');

          $.ajax({
              url: `{{ url('settings/red-days') }}/${id}`,
              type: 'PUT',
              headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              data: {
                  name: $('#edit_rd_name').val(),
                  date: $('#edit_rd_date').val(),
                  start_time: isFullDay ? '' : $('#edit_rd_start').val(),
                  end_time: isFullDay ? '' : $('#edit_rd_end').val(),
                  repeat: $('#edit_rd_repeat').is(':checked') ? 1 : 0,
                  description: $('#edit_rd_description').val(),
              },
              success: function (response) {
                  $('#editRedDayModal').modal('hide');
                  // Обновляем строку в таблице
                  const day = response.redDay;
                  const row = $(`#rd_row_${day.id}`);
                  const timeStr = (day.start_time && day.end_time) ? `<span class="text-muted fs-10">(${day.start_time}–${day.end_time})</span>` : '';
                  const masterBadge = day.user_id
                      ? `<span class="badge badge-phoenix badge-phoenix-primary fs-10">${day.master_name}</span>`
                      : `<span class="badge badge-phoenix badge-phoenix-secondary fs-10">Общий</span>`;

                  row.find('.name').text(day.name);
                  row.find('.date').html(`${day.date} ${timeStr}`);
                  row.find('.repeat').text(day.repeat === 'yes' ? '✅' : '❌');

                  // Обновляем data атрибуты кнопки
                  row.find('.edit-red-day')
                      .data('name', day.name)
                      .data('date', day.date)
                      .data('start', day.start_time || '')
                      .data('end', day.end_time || '')
                      .data('repeat', day.repeat === 'yes' ? 1 : 0)
                      .data('fullday', (!day.start_time && !day.end_time) ? 1 : 0);

                  showSuccess();
              },
              error: function () {
                  showError({ status: 500 });
              }
          });
      });
    });
  </script>
  @endpush
</x-dashboard-layout>
