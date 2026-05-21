@section('title', 'Добавить мастера')
@push('styles')
  <link href="{{ asset('vendors/choices/choices.min.css') }}" rel="stylesheet" />
@endpush

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('member.index') }}">Мастера</a></li>
        <li class="breadcrumb-item active">Добавление мастера</li>
      </ol>
    </nav>

    @if (session('success'))
      <div class="alert alert-outline-success d-flex align-items-center" role="alert">
        <span class="fas fa-check-circle text-success fs-5 me-3"></span>
        <p class="mb-0 flex-1">Новый мастер успешно добавлен!</p>
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <h2 class="mb-4">Создание нового мастера</h2>

    <div class="card theme-wizard mb-5" data-theme-wizard="data-theme-wizard">
      <div class="card-body pt-4 pb-0">
        <div class="row justify-content-between">

          {{-- Шаги навигации --}}
          <div class="col-md-3 order-md-1">
            <div class="scrollbar mb-4">
              <ul class="nav justify-content-between flex-nowrap nav-wizard nav-wizard-vertical-md" role="tablist">
                <li class="nav-item" role="presentation">
                  <a id="stepFirst" class="nav-link active py-0 py-md-3" href="#wizard-tab1" data-bs-toggle="tab" data-wizard-step="1" role="tab">
                    <div class="text-center d-inline-block d-md-flex align-items-center gap-3">
                      <span class="nav-item-circle-parent"><span class="nav-item-circle"><span class="fa-solid fa-user nav-item-icon"></span><span class="fa-solid fa-check check-icon"></span></span></span>
                      <span class="nav-item-title fs-9 fs-xl-8">Основное</span>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a id="stepSec" class="nav-link py-0 py-md-3" href="#wizard-tab2" data-bs-toggle="tab" data-wizard-step="2" role="tab">
                    <div class="text-center d-inline-block d-md-flex align-items-center gap-3">
                      <span class="nav-item-circle-parent"><span class="nav-item-circle"><span class="fa-solid fa-lock nav-item-icon"></span><span class="fa-solid fa-check check-icon"></span></span></span>
                      <span class="nav-item-title fs-9 fs-xl-8">Доступ</span>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a id="stepThird" class="nav-link py-0 py-md-3" href="#wizard-tab3" data-bs-toggle="tab" data-wizard-step="3" role="tab">
                    <div class="text-center d-inline-block d-md-flex align-items-center gap-3">
                      <span class="nav-item-circle-parent"><span class="nav-item-circle"><span class="fa-solid fa-clipboard nav-item-icon"></span><span class="fa-solid fa-check check-icon"></span></span></span>
                      <span class="nav-item-title fs-9 fs-xl-8">Услуги</span>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a id="stepFourth" class="nav-link py-0 py-md-3" href="#wizard-tab4" data-bs-toggle="tab" data-wizard-step="4" role="tab">
                    <div class="text-center d-inline-block d-md-flex align-items-center gap-3">
                      <span class="nav-item-circle-parent"><span class="nav-item-circle"><span class="fa-solid fa-clock nav-item-icon"></span><span class="fa-solid fa-check check-icon"></span></span></span>
                      <span class="nav-item-title fs-9 fs-xl-8">Расписание</span>
                    </div>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#wizard-tab5" class="nav-link py-0 py-md-3" data-bs-toggle="tab" data-wizard-step="5">
                    <div class="text-center d-inline-block d-md-flex align-items-center gap-3">
                      <span class="nav-item-circle-parent"><span class="nav-item-circle"><span class="fa-solid fa-check nav-item-icon"></span><span class="fa-solid fa-check check-icon"></span></span></span>
                      <span class="nav-item-title fs-9 fs-xl-8">Готово</span>
                    </div>
                  </a>
                </li>
              </ul>
            </div>
          </div>

          {{-- Содержимое шагов --}}
          <div class="col-md-8">
            <div class="tab-content">

              {{-- Шаг 1: Основное --}}
              <div class="tab-pane active" role="tabpanel" id="wizard-tab1">
                <div class="row g-4 mb-4" data-dropzone="data-dropzone" data-options='{"maxFiles":1}'>
                  <div class="fallback"><input type="file" name="file" id="file"></div>
                  <div class="col-md-auto">
                    <div class="dz-preview dz-preview-single">
                      <div class="dz-preview-cover d-flex align-items-center justify-content-center mb-2 mb-md-0">
                        <div class="avatar avatar-4xl"><img class="rounded-circle avatar-placeholder" src="{{ asset('assets/img/team/avatar.webp') }}" alt="..." data-dz-thumbnail="data-dz-thumbnail" /></div>
                        <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""></span></div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md">
                    <div class="dz-message dropzone-area px-2 py-3" data-dz-message="data-dz-message">
                      <div class="text-center text-body-emphasis">
                        <h5 class="mb-2"><span class="fa-solid fa-upload me-2"></span>Фото профиля</h5>
                        <p class="mb-0 fs-9 text-body-tertiary lh-sm">JPG 300x300, макс. 400KB</p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mb-2">
                  <label class="form-label text-body">Имя*</label>
                  <input class="form-control" type="text" name="name" id="name" placeholder="Иван Иванов">
                  <div class="invalid-feedback" id="name-error"></div>
                </div>
                <div class="mb-2">
                  <label class="form-label text-body">Никнейм*</label>
                  <div class="input-group">
                    <span class="input-group-text">@</span>
                    <input class="form-control" type="text" name="username" id="username" placeholder="ivan">
                    <div class="invalid-feedback" id="username-error"></div>
                  </div>
                </div>
                <div class="mb-2">
                  <label class="form-label text-body">Роль*</label>
                  <select class="form-select" id="role_id" name="role_id" data-choices="data-choices">
                    @foreach ($roles as $role)
                      <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="mb-2">
                  <label class="form-label text-body">Телефон</label>
                  <input class="form-control" type="text" id="phone" name="phone" placeholder="+372 55 555 555">
                  <div class="invalid-feedback" id="phone-error"></div>
                </div>
                <div class="flatpickr-input-container mb-5">
                  <div class="form-floating">
                    <input class="form-control datetimepicker" id="date_birthday" type="text" name="date_birthday" placeholder="yyyy-mm-dd" data-options='{"disableMobile":true,"dateFormat":"Y-m-d"}' />
                    <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                    <label class="ps-6" for="date_birthday">Дата рождения</label>
                  </div>
                </div>
              </div>

              {{-- Шаг 2: Email и пароль --}}
              <div class="tab-pane" role="tabpanel" id="wizard-tab2">
                <div class="mb-2">
                  <label class="form-label">Email*</label>
                  <input class="form-control" type="email" name="email" id="email" placeholder="email@example.com">
                  <div class="invalid-feedback" id="email-error"></div>
                </div>
                <div class="row g-3 mb-3">
                  <div class="col-sm-6">
                    <label class="form-label text-body">Пароль*</label>
                    <input class="form-control" type="password" name="password" id="password" placeholder="Пароль">
                    <div class="invalid-feedback" id="password-error"></div>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label text-body">Повторите пароль*</label>
                    <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" placeholder="Повторите пароль">
                  </div>
                </div>
              </div>

              {{-- Шаг 3: Услуги --}}
              <div class="tab-pane" role="tabpanel" id="wizard-tab3">
                <h5 class="mb-3">Выберите услуги мастера</h5>
                <p class="text-muted fs-9 mb-3">Отметьте услуги которые оказывает этот мастер</p>
                <div class="row g-2">
                  @foreach ($services as $service)
                    <div class="col-12">
                      <div class="form-check">
                        <input class="form-check-input service-checkbox" type="checkbox" name="services[]" value="{{ $service->id }}" id="service_{{ $service->id }}">
                        <label class="form-check-label d-flex align-items-center gap-2" for="service_{{ $service->id }}">
                          <span>{{ $service->name }}</span>
                          <span class="badge bg-primary fs-10">{{ $service->price }} €</span>
                          <span class="text-muted fs-10">{{ $service->duration_minutes }} мин</span>
                        </label>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>

              {{-- Шаг 4: Расписание --}}
              <div class="tab-pane" role="tabpanel" id="wizard-tab4">
                <h5 class="mb-3">Рабочее время</h5>
                <div class="table-responsive">
                  <table class="table table-sm align-middle">
                    <thead>
                      <tr>
                        <th>День</th>
                        <th>Начало</th>
                        <th>Конец</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ([
                        'monday' => 'Понедельник',
                        'tuesday' => 'Вторник',
                        'wednesday' => 'Среда',
                        'thursday' => 'Четверг',
                        'friday' => 'Пятница',
                        'saturday' => 'Суббота',
                        'sunday' => 'Воскресенье',
                      ] as $day => $label)
                      <tr>
                        <td><strong>{{ $label }}</strong></td>
                        <td>
                          <input type="time" class="form-control form-control-sm" name="{{ $day }}_start" id="{{ $day }}_start" style="width:120px">
                        </td>
                        <td>
                          <input type="time" class="form-control form-control-sm" name="{{ $day }}_end" id="{{ $day }}_end" style="width:120px">
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <h5 class="mt-4 mb-3">Обеденный перерыв</h5>
                <div class="row g-3">
                  <div class="col-sm-4">
                    <label class="form-label">Начало обеда</label>
                    <input type="time" class="form-control" name="lunch_start" id="lunch_start">
                  </div>
                  <div class="col-sm-4">
                    <label class="form-label">Конец обеда</label>
                    <input type="time" class="form-control" name="lunch_end" id="lunch_end">
                  </div>
                </div>
              </div>

              {{-- Шаг 5: Готово --}}
              <div class="tab-pane" role="tabpanel" id="wizard-tab5">
                <div class="row flex-center pb-8 pt-4 gx-3 gy-4">
                  <div class="col-12 col-sm-auto">
                    <div class="text-center text-sm-start">
                      <h5 class="mb-3">Мастер успешно создан!</h5>
                      <p class="text-body-emphasis fs-9">Мастер добавлен в систему и может приступать к работе</p>
                      <a class="btn btn-primary px-6" href="{{ route('member.index') }}">Список мастеров</a>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <div class="card-footer border-top-0" id="wizardFooter">
        <div class="d-flex pager wizard list-inline mb-0">
          <button class="btn btn-link ps-0 d-none" type="button" id="prevBtn">
            <span class="fas fa-chevron-left me-1"></span>Назад
          </button>
          <div class="flex-1 text-end">
            <button class="btn btn-primary px-6" type="button" id="nextBtn">
              Далее <span class="fas fa-chevron-right ms-1"></span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <x-dashboard-footer />
  </div>

  @push('scripts')
  <script src="{{ asset('vendors/choices/choices.min.js') }}"></script>
  <script src="{{ asset('vendors/dropzone/dropzone-min.js') }}"></script>
  <script>
    $(document).ready(function () {
      let currentStep = 1;
      const totalSteps = 5;
      let photo = null;

      // Данные накапливаем по шагам
      let formData_step1 = {};
      let formData_step2 = {};
      let formData_step3 = {};
      let formData_step4 = {};

      function goToStep(step) {
        currentStep = step;
        $('.tab-pane').removeClass('active show');
        $('#wizard-tab' + step).addClass('active show');

        // Кнопка назад
        if (step === 1) {
          $('#prevBtn').addClass('d-none');
        } else {
          $('#prevBtn').removeClass('d-none');
        }

        // Кнопка далее на последнем шаге
        if (step === totalSteps) {
          $('#nextBtn').addClass('d-none');
        } else {
          $('#nextBtn').removeClass('d-none');
        }
      }

      $('#prevBtn').on('click', function () {
        if (currentStep > 1) goToStep(currentStep - 1);
      });

      $('#nextBtn').on('click', function () {
        if (currentStep === 1) {
          validateStep1();
        } else if (currentStep === 2) {
          validateStep2();
        } else if (currentStep === 3) {
          saveStep3();
        } else if (currentStep === 4) {
          saveStep4();
        }
      });

      function validateStep1() {
        let formData = new FormData();
        let username = '@' + $('#username').val();
        formData.append('name', $('#name').val());
        formData.append('username', username);
        formData.append('phone', $('#phone').val());
        formData.append('date_birthday', $('#date_birthday').val());
        formData.append('role_id', $('#role_id').val());

        let dropzoneInstance = Dropzone.instances[0];
        if (dropzoneInstance && dropzoneInstance.files.length > 0) {
          formData.append('photo', dropzoneInstance.files[0]);
        }

        $.ajax({
          url: '/member/store/1',
          type: 'POST',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: formData,
          contentType: false,
          processData: false,
          success: function (response) {
            photo = response.data.photo;
            formData_step1 = response.data;
            clearErrors();
            goToStep(2);
          },
          error: function (xhr) { showErrors(xhr); }
        });
      }

      function validateStep2() {
        let formData = new FormData();
        // Передаём все данные шага 1 для валидации
        formData.append('name', formData_step1.name || $('#name').val());
        formData.append('username', formData_step1.username || '@' + $('#username').val());
        formData.append('phone', formData_step1.phone || $('#phone').val());
        formData.append('role_id', formData_step1.role_id || $('#role_id').val());
        formData.append('profile_photo_path', photo || '');
        formData.append('email', $('#email').val());
        formData.append('password', $('#password').val());
        formData.append('password_confirmation', $('#password_confirmation').val());

        $.ajax({
          url: '/member/store/2',
          type: 'POST',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: formData,
          contentType: false,
          processData: false,
          success: function () {
            formData_step2 = {
              email: $('#email').val(),
              password: $('#password').val(),
              password_confirmation: $('#password_confirmation').val(),
            };
            clearErrors();
            goToStep(3);
          },
          error: function (xhr) { showErrors(xhr); }
        });
      }

      function saveStep3() {
        let services = [];
        $('.service-checkbox:checked').each(function () {
          services.push($(this).val());
        });
        formData_step3 = { services: services };
        goToStep(4);
      }

      function saveStep4() {
        let formData = new FormData();

        // Все данные из предыдущих шагов
        formData.append('name', formData_step1.name || $('#name').val());
        formData.append('username', formData_step1.username || '@' + $('#username').val());
        formData.append('phone', formData_step1.phone || $('#phone').val());
        formData.append('role_id', formData_step1.role_id || $('#role_id').val());
        formData.append('date_birthday', formData_step1.date_birthday || $('#date_birthday').val());
        formData.append('profile_photo_path', photo || '');
        formData.append('email', formData_step2.email || $('#email').val());
        formData.append('password', formData_step2.password || $('#password').val());
        formData.append('password_confirmation', formData_step2.password_confirmation || $('#password_confirmation').val());

        // Услуги
        if (formData_step3.services) {
          formData_step3.services.forEach(function (id) {
            formData.append('services[]', id);
          });
        }

        // Расписание
        const days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        days.forEach(function (day) {
          formData.append(day + '_start', $('#' + day + '_start').val());
          formData.append(day + '_end', $('#' + day + '_end').val());
        });
        formData.append('lunch_start', $('#lunch_start').val());
        formData.append('lunch_end', $('#lunch_end').val());

        $.ajax({
          url: '/member/store/4',
          type: 'POST',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: formData,
          contentType: false,
          processData: false,
          success: function () {
            clearErrors();
            goToStep(5);
          },
          error: function (xhr) { showErrors(xhr); }
        });
      }

      function showErrors(xhr) {
        clearErrors();
        if (xhr.status === 422) {
          let errors = xhr.responseJSON.errors;
          $.each(errors, function (field, messages) {
            let input = $('#' + field);
            input.addClass('is-invalid');
            let errorEl = $('#' + field + '-error');
            if (errorEl.length) errorEl.text(messages[0]).show();
          });
        }
      }

      function clearErrors() {
        $('.is-invalid').removeClass('is-invalid');
      }
    });
  </script>
  @endpush
</x-dashboard-layout>