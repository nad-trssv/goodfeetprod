@section('title', __('admin_settings.title'))
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
    box-shadow: 0 0 0 3px rgba(var(--phoenix-primary-rgb), .16);
    background: rgba(var(--phoenix-primary-rgb), .04);
  }
  .booking-template-option .template-selected { display:none; }
  .btn-check:checked + .booking-template-option .template-selected { display:inline-flex; }
</style>
@endpush

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">{{ __('admin_settings.title') }}</li>
      </ol>
    </nav>

    <ul class="nav settings-tabs mb-4" id="settingsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-booking" type="button">
          <span class="fas fa-lock me-1"></span>{{ __('admin_settings.booking') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-site" type="button">
          <span class="fas fa-cog me-1"></span>{{ __('admin_settings.site') }}
        </button>
      </li>
    </ul>

    <div class="tab-content">

      {{-- ТАБ 3: Бронирование --}}
      <div class="tab-pane active" id="tab-booking">
        <div class="row g-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header"><h5 class="mb-0">{{ __('admin_settings.fixed_booking') }}</h5></div>
              <div class="card-body">
                <p class="text-muted fs-9 mb-3">{{ __('admin_settings.fixed_booking_hint') }}</p>
                <form id="fixHoursTable">
                  @csrf
                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" id="isFixed" type="checkbox">
                    <label class="form-check-label" for="isFixed">{{ __('admin_settings.enable_fixed') }}</label>
                  </div>
                  <div id="rows-container" class="disabled"></div>
                  <div class="text-end mt-3">
                    <button class="btn btn-primary" type="submit">{{ __('admin_staff.save') }}</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="card">
              <div class="card-header"><h5 class="mb-0">{{ __('admin_settings.booking_limit') }}</h5></div>
              <div class="card-body">
                <form id="bookingLimitDays">
                  @csrf
                  <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                      <label class="form-label">{{ __('admin_settings.booking_limit_label') }}</label>
                      <input class="form-control"
                            type="number" id="bookingLimit_days"
                            value="{{ $bookingLimit['days'] }}"
                            min="1" max="365">
                      <div class="invalid-feedback" id="bookingLimit-error"></div>
                    </div>
                    <div class="col-md-2">
                      <button class="btn btn-primary w-100" type="submit">{{ __('admin_staff.save') }}</button>
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
          <div class="card-header"><h5 class="mb-0">{{ __('admin_settings.site') }}</h5></div>
          <div class="card-body">
            <form id="updateSettingsTable" enctype="multipart/form-data">
              @csrf
              <h6 class="mb-3">{{ __('admin_settings.company') }}</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label">{{ __('admin_settings.company_name') }}</label>
                  <input class="form-control" id="company_name" type="text">
                  <div class="invalid-feedback" id="company_name-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ __('admin_staff.email') }}</label>
                  <input class="form-control" id="company_email" type="email">
                  <div class="invalid-feedback" id="company_email-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ __('admin_staff.phone') }}</label>
                  <input class="form-control" id="company_phone" type="text" pattern="^\+.*">
                  <div class="invalid-feedback" id="company_phone-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ __('admin_settings.address') }}</label>
                  <input class="form-control" id="company_address" type="text">
                  <div class="invalid-feedback" id="company_address-error"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ __('admin_settings.registration_number') }} <span class="text-muted">({{ __('admin_settings.optional') }})</span></label>
                  <input class="form-control" id="company_registration_number" name="company_registration_number" type="text">
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ __('admin_settings.bank_account') }} <span class="text-muted">({{ __('admin_settings.optional') }})</span></label>
                  <input class="form-control" id="company_bank_account" name="company_bank_account" type="text">
                </div>
              </div>

              <div class="card border mb-5">
                <div class="card-body">
                  <h6 class="mb-2">{{ __('admin_settings.accent_color') }}</h6>
                  <p class="text-muted fs-9">{{ __('admin_settings.accent_hint') }}</p>
                  <div class="row g-3 align-items-end">
                    <div class="col-auto">
                      <label class="form-label" for="primary_accent_color_picker">{{ __('admin_settings.select_color') }}</label>
                      <input class="form-control form-control-color" id="primary_accent_color_picker" type="color" value="#3874FF" title="{{ __('admin_settings.select_color') }}">
                    </div>
                    <div class="col-sm-5 col-lg-4">
                      <label class="form-label" for="primary_accent_color">{{ __('admin_settings.hex') }}</label>
                      <input class="form-control text-uppercase" id="primary_accent_color" name="primary_accent_color" type="text" value="#3874FF" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$" required>
                      <div class="invalid-feedback" id="primary_accent_color-error"></div>
                    </div>
                  </div>
                </div>
              </div>

              <h6 class="mb-3">{{ __('admin_settings.booking_template') }}</h6>
              <p class="text-muted fs-9">{{ __('admin_settings.booking_template_hint') }}</p>
              <div class="row g-3 mb-4">
                @foreach([
                  'classic' => [__('admin_settings.templates.classic.title'), __('admin_settings.templates.classic.description')],
                  'wizard' => [__('admin_settings.templates.wizard.title'), __('admin_settings.templates.wizard.description')],
                  'compact' => [__('admin_settings.templates.compact.title'), __('admin_settings.templates.compact.description')],
                ] as $template => [$title, $description])
                  <div class="col-md-4">
                    <input class="btn-check" type="radio" name="booking_template" id="booking_template_{{ $template }}" value="{{ $template }}" {{ $template === 'classic' ? 'checked' : '' }}>
                    <label class="booking-template-option card h-100 border cursor-pointer" for="booking_template_{{ $template }}">
                      <span class="card-body"><span class="d-flex justify-content-between fw-semibold mb-2">{{ $title }} <span class="template-selected badge badge-phoenix badge-phoenix-primary">{{ __('admin_settings.selected') }}</span></span><span class="text-muted fs-9">{{ $description }}</span></span>
                    </label>
                  </div>
                @endforeach
                <div class="invalid-feedback" id="booking_template-error"></div>
              </div>
              <div class="text-end mb-5">
                <button class="btn btn-primary" type="submit"><span class="fas fa-check me-2"></span>{{ __('admin_settings.save_template') }}</button>
              </div>

              <div class="card border mb-5">
                <div class="card-body">
                  <h6 class="mb-2">{{ __('admin_settings.cancellation_rules') }}</h6>
                  <p class="text-muted fs-9">{{ __('admin_settings.cancellation_rules_hint') }}</p>
                  <div class="form-check form-switch border rounded-3 p-3 ps-6 mb-3">
                    <input type="hidden" name="allow_customer_cancellation" value="0">
                    <input class="form-check-input" id="allow_customer_cancellation" name="allow_customer_cancellation" type="checkbox" value="1" checked>
                    <label class="form-check-label fw-semibold" for="allow_customer_cancellation">{{ __('admin_settings.allow_cancellation') }}</label>
                    <div class="text-muted fs-9 mt-1">{{ __('admin_settings.allow_cancellation_hint') }}</div>
                  </div>
                  <div class="row g-3"><div class="col-md-6">
                  <label class="form-label" for="cancellation_notice_hours">{{ __('admin_settings.cancellation_notice') }}</label>
                  <div class="input-group"><input class="form-control" id="cancellation_notice_hours" name="cancellation_notice_hours" type="number" min="0" max="8760" value="24" required><span class="input-group-text">{{ __('admin_settings.hours') }}</span></div>
                  <div class="form-text">{{ __('admin_settings.cancellation_notice_hint') }}</div>
                  <div class="invalid-feedback" id="cancellation_notice_hours-error"></div>
                  </div></div>
                </div>
              </div>

              <h6 class="mb-3">{{ __('admin_settings.catalog_images') }}</h6>
              <div class="row g-3 mb-5">
                <div class="col-md-6">
                  <div class="form-check form-switch border rounded-3 p-3 ps-6 h-100">
                    <input type="hidden" name="show_service_images" value="0">
                    <input class="form-check-input" id="show_service_images" name="show_service_images" type="checkbox" value="1" checked>
                    <label class="form-check-label fw-semibold" for="show_service_images">{{ __('admin_settings.show_service_images') }}</label>
                    <div class="text-muted fs-9 mt-1">{{ __('admin_settings.show_service_images_hint') }}</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check form-switch border rounded-3 p-3 ps-6 h-100">
                    <input type="hidden" name="show_master_images" value="0">
                    <input class="form-check-input" id="show_master_images" name="show_master_images" type="checkbox" value="1" checked>
                    <label class="form-check-label fw-semibold" for="show_master_images">{{ __('admin_settings.show_master_images') }}</label>
                    <div class="text-muted fs-9 mt-1">{{ __('admin_settings.show_master_images_hint') }}</div>
                  </div>
                </div>
              </div>

              <h6 class="mb-3">{{ __('admin_settings.company_description') }}</h6>
              <p class="text-muted fs-9">{{ __('admin_settings.company_description_hint') }}</p>
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

              <h6 class="mb-3">{{ __('admin_settings.logos') }}</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-4"><label class="form-label">{{ __('admin_settings.header_logo') }}</label><div class="border rounded p-2 mb-2 text-center"><img id="logo-preview" class="img-fluid" style="height:70px;display:none" alt="{{ __('admin_settings.installed_logo') }}"></div><input class="form-control" id="logo" name="logo" type="file" accept=".png,.jpg,.jpeg,.webp,.svg"><div class="invalid-feedback" id="logo-error"></div></div>
                <div class="col-md-4"><label class="form-label">{{ __('admin_settings.footer_logo') }}</label><div class="border rounded p-2 mb-2 text-center"><img id="footer_logo-preview" class="img-fluid" style="height:70px;display:none" alt="{{ __('admin_settings.installed_footer_logo') }}"></div><input class="form-control" id="footer_logo" name="footer_logo" type="file" accept=".png,.jpg,.jpeg,.webp,.svg"><div class="invalid-feedback" id="footer_logo-error"></div></div>
                <div class="col-md-4"><label class="form-label">Favicon</label><div class="border rounded p-2 mb-2 text-center"><img id="favicon-preview" class="img-fluid" style="height:70px;display:none" alt="{{ __('admin_settings.installed_favicon') }}"></div><input class="form-control" id="favicon" name="favicon" type="file" accept=".png,.ico,.svg"><div class="invalid-feedback" id="favicon-error"></div></div>
              </div>

              <h6 class="mb-3">{{ __('admin_settings.map') }} <small class="text-muted fw-normal">({{ __('admin_settings.iframe_hint') }})</small></h6>
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

              <h6 class="mb-3">{{ __('admin_settings.social') }}</h6>
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
                <button class="btn btn-primary" type="submit">{{ __('admin_settings.save_settings') }}</button>
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
            <h5 class="modal-title">{{ __('admin_settings.edit_closed_day') }}</h5>
            <button class="btn btn-close p-1" type="button" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="edit_rd_id">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">{{ __('admin_staff.closure_name') }}*</label>
                <input type="text" class="form-control" id="edit_rd_name">
              </div>
              <div class="col-12">
                <label class="form-label">{{ __('admin_settings.date') }}*</label>
                <input type="date" class="form-control" id="edit_rd_date">
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="edit_rd_fullday">
                  <label class="form-check-label" for="edit_rd_fullday">{{ __('admin_staff.full_day') }}</label>
                </div>
              </div>
              <div id="edit_rd_time_block" class="col-6">
                <label class="form-label">{{ __('admin_staff.start') }}</label>
                <input type="time" class="form-control" id="edit_rd_start">
              </div>
              <div id="edit_rd_time_block2" class="col-6">
                <label class="form-label">{{ __('admin_staff.end') }}</label>
                <input type="time" class="form-control" id="edit_rd_end">
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="edit_rd_repeat">
                  <label class="form-check-label" for="edit_rd_repeat">{{ __('admin_staff.repeat_yearly') }}</label>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label">{{ __('admin_staff.description') }}</label>
                <input type="text" class="form-control" id="edit_rd_description">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">{{ __('admin_staff.cancel') }}</button>
            <button class="btn btn-primary" type="button" id="saveEditRedDay">{{ __('admin_staff.save') }}</button>
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
            if (['show_service_images', 'show_master_images', 'allow_customer_cancellation'].includes(item.key)) {
              $('#' + item.key).prop('checked', item.payload === true || item.payload === 1 || item.payload === '1');
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
            if (item.key === 'primary_accent_color' && /^#[0-9A-Fa-f]{6}$/.test(item.payload || '')) {
              $('#primary_accent_color_picker').val(item.payload);
            }
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
          title: @json(__('admin_settings.are_you_sure')), icon: "warning", showCancelButton: true,
          confirmButtonColor: "#d33", cancelButtonColor: "#3085d6",
          confirmButtonText: @json(__('admin_staff.delete')), cancelButtonText: @json(__('admin_staff.cancel'))
        }).then(result => {
          if (result.isConfirmed) {
            $.ajax({
              url: `{{ url('settings/red-days') }}/${id}`, type: "DELETE",
              headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              success: () => { row.remove(); showSuccess(); },
              error: () => Swal.fire(@json(__('admin_settings.error')), @json(__('admin_settings.delete_failed')), "error")
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
            button.textContent = @json(__('admin_settings.add_slot'));
            button.classList.replace("badge-phoenix-danger", "badge-phoenix-secondary");
            button.onclick = addRow;
          } else {
            button.textContent = @json(__('admin_staff.delete'));
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
                  <button type="button" class="badge badge-phoenix badge-phoenix-danger add-row-btn mt-1">{{ __('admin_staff.delete') }}</button>
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
      $('#primary_accent_color_picker').on('input', function() {
        $('#primary_accent_color').val(this.value.toUpperCase()).trigger('input');
      });
      $('#primary_accent_color').on('input', function() {
        const value = this.value.trim();
        if (/^#[0-9A-Fa-f]{6}$/.test(value)) $('#primary_accent_color_picker').val(value);
      });

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
          success: () => {
            showSuccess();
            window.setTimeout(() => window.location.reload(), 500);
          },
          error: (xhr) => showError(xhr)
        });
      });

      function renderRedDaysTable(res) {
          let tbody = $("#redDaysTable tbody");
          tbody.empty();
          if (!res.length) {
              tbody.append(`<tr><td colspan="5" class="text-center text-muted">${@json(__('admin_settings.no_records'))}</td></tr>`);
              return;
          }
          res.forEach(day => {
              let timeStr = (day.start_time && day.end_time) ? `<span class="text-muted fs-10">(${day.start_time}–${day.end_time})</span>` : '';
              let masterBadge = day.user_id
                  ? `<span class="badge badge-phoenix badge-phoenix-primary fs-10">${day.master_name}</span>`
                  : `<span class="badge badge-phoenix badge-phoenix-secondary fs-10">${@json(__('admin_settings.global'))}</span>`;
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
        Swal.fire({ title: @json(__('admin_common.success')), text: @json(__('admin_common.saved')), icon: 'success', confirmButtonText: 'OK' });
      }
      function showError(xhr) {
        Swal.fire({ title: @json(__('admin_common.oops')), text: @json(__('admin_common.something_wrong')), icon: 'error', confirmButtonText: 'OK' });
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
                      : `<span class="badge badge-phoenix badge-phoenix-secondary fs-10">${@json(__('admin_settings.global'))}</span>`;

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
