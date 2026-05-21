@section('title', 'Редактировать мастера')
@push('styles')
  <link href="{{ asset('vendors/choices/choices.min.css') }}" rel="stylesheet" />
@endpush

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('member.index') }}">Мастера</a></li>
        <li class="breadcrumb-item active">{{ $member->name }}</li>
      </ol>
    </nav>

    <div id="successAlert" class="alert alert-success d-none" role="alert">
      <span class="fas fa-check-circle me-2"></span>Данные успешно сохранены!
    </div>
    <div id="errorAlert" class="alert alert-danger d-none" role="alert">
      <span class="fas fa-times-circle me-2"></span><span id="errorText"></span>
    </div>

    <div class="row g-4">

      {{-- Левая колонка: фото + основное --}}
      <div class="col-12 col-xl-4">
        <div class="card mb-4">
          <div class="card-body text-center">
            <img src="{{ asset('storage/' . $member->profile_photo_path) }}"
                 class="rounded-circle mb-3"
                 style="width:120px;height:120px;object-fit:cover;"
                 id="photoPreview"
                 alt="{{ $member->name }}">
            <h5 class="mb-1">{{ $member->name }}</h5>
            <p class="text-muted fs-10 mb-3">{{ $member->username }}</p>
            <label class="btn btn-outline-primary btn-sm">
              <span class="fas fa-upload me-1"></span>Сменить фото
              <input type="file" id="photoInput" accept="image/*" class="d-none">
            </label>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h6 class="mb-0">Смена пароля</h6></div>
          <div class="card-body">
            <div class="mb-2">
              <label class="form-label">Новый пароль</label>
              <input type="password" class="form-control" id="password" placeholder="Минимум 8 символов">
            </div>
            <div class="mb-2">
              <label class="form-label">Повторите пароль</label>
              <input type="password" class="form-control" id="password_confirmation">
            </div>
          </div>
        </div>
      </div>

      {{-- Правая колонка: табы --}}
      <div class="col-12 col-xl-8">
        <div class="card">
          <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="editTabs">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-info">
                  <span class="fas fa-user me-1"></span>Основное
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-services">
                  <span class="fas fa-clipboard me-1"></span>Услуги
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-schedule">
                  <span class="fas fa-clock me-1"></span>Расписание
                </a>
              </li>
            </ul>
          </div>
          <div class="card-body">
            <div class="tab-content">

              {{-- Таб 1: Основная информация --}}
              <div class="tab-pane fade show active" id="tab-info">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Имя*</label>
                    <input type="text" class="form-control" id="name" value="{{ $member->name }}">
                    <div class="invalid-feedback" id="name-error"></div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Никнейм*</label>
                    <div class="input-group">
                      <span class="input-group-text">@</span>
                      <input type="text" class="form-control" id="username" value="{{ ltrim($member->username, '@') }}">
                    </div>
                    <div class="invalid-feedback" id="username-error"></div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email*</label>
                    <input type="email" class="form-control" id="email" value="{{ $member->email }}">
                    <div class="invalid-feedback" id="email-error"></div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Телефон</label>
                    <input type="text" class="form-control" id="phone" value="{{ $member->phone }}">
                    <div class="invalid-feedback" id="phone-error"></div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Роль*</label>
                    <select class="form-select" id="role_id">
                      @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ $member->role_id == $role->id ? 'selected' : '' }}>
                          {{ $role->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Дата рождения</label>
                    <input type="date" class="form-control" id="date_birthday"
                           value="{{ $member->date_birthday ? \Carbon\Carbon::parse($member->date_birthday)->format('Y-m-d') : '' }}">
                  </div>
                </div>
              </div>

              {{-- Таб 2: Услуги --}}
              <div class="tab-pane fade" id="tab-services">
                <h6 class="mb-3">Услуги мастера</h6>
                @php $memberServiceIds = $member->services->pluck('id')->toArray(); @endphp
                <div class="row g-2">
                  @foreach ($services as $service)
                    <div class="col-12">
                      <div class="form-check">
                        <input class="form-check-input service-checkbox" type="checkbox"
                               value="{{ $service->id }}"
                               id="service_{{ $service->id }}"
                               {{ in_array($service->id, $memberServiceIds) ? 'checked' : '' }}>
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

              {{-- Таб 3: Расписание --}}
              <div class="tab-pane fade" id="tab-schedule">
                <h6 class="mb-3">Рабочее время</h6>
                @php $schedule = $member->schedule; @endphp
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
                      @foreach ([
                        'monday' => 'Понедельник',
                        'tuesday' => 'Вторник',
                        'wednesday' => 'Среда',
                        'thursday' => 'Четверг',
                        'friday' => 'Пятница',
                        'saturday' => 'Суббота',
                        'sunday' => 'Воскресенье',
                      ] as $day => $label)
                      @php $isDayOff = !$schedule || !$schedule->{$day.'_start'} || !$schedule->{$day.'_end'}; @endphp
                      <tr>
                        <td><strong>{{ $label }}</strong></td>
                        <td>
                          <input type="time" class="form-control form-control-sm day-time" 
                                id="{{ $day }}_start"
                                value="{{ $schedule ? $schedule->{$day.'_start'} : '' }}"
                                style="width:120px"
                                {{ $isDayOff ? 'disabled' : '' }}>
                        </td>
                        <td>
                          <input type="time" class="form-control form-control-sm day-time"
                                id="{{ $day }}_end"
                                value="{{ $schedule ? $schedule->{$day.'_end'} : '' }}"
                                style="width:120px"
                                {{ $isDayOff ? 'disabled' : '' }}>
                        </td>
                        <td>
                          <div class="form-check">
                            <input class="form-check-input day-off-checkbox" type="checkbox"
                                  id="{{ $day }}_off"
                                  data-day="{{ $day }}"
                                  {{ $isDayOff ? 'checked' : '' }}>
                            <label class="form-check-label text-muted fs-10" for="{{ $day }}_off">Выходной</label>
                          </div>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <h6 class="mt-4 mb-3">Обеденный перерыв</h6>
                <div class="row g-3">
                  <div class="col-sm-4">
                    <label class="form-label">Начало обеда</label>
                    <input type="time" class="form-control" id="lunch_start"
                           value="{{ $schedule ? $schedule->lunch_start : '' }}">
                  </div>
                  <div class="col-sm-4">
                    <label class="form-label">Конец обеда</label>
                    <input type="time" class="form-control" id="lunch_end"
                           value="{{ $schedule ? $schedule->lunch_end : '' }}">
                  </div>
                </div>
              </div>

            </div>
          </div>
          <div class="card-footer text-end">
            <a href="{{ route('member.index') }}" class="btn btn-outline-secondary me-2">Отмена</a>
            <button class="btn btn-primary" id="saveBtn">
              <span class="fas fa-save me-1"></span>Сохранить
            </button>
          </div>
        </div>
      </div>

    </div>
    <x-dashboard-footer />
  </div>

  @push('scripts')
  <script src="{{ asset('vendors/choices/choices.min.js') }}"></script>
  <script>
    $(document).ready(function () {

      // Превью фото
      $('#photoInput').on('change', function () {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = e => $('#photoPreview').attr('src', e.target.result);
          reader.readAsDataURL(file);
        }
      });

      // Сохранить
      $('#saveBtn').on('click', function () {
        clearErrors();
        let formData = new FormData();

        formData.append('_method', 'PUT');
        formData.append('name', $('#name').val());
        formData.append('username', '@' + $('#username').val());
        formData.append('email', $('#email').val());
        formData.append('phone', $('#phone').val());
        formData.append('role_id', $('#role_id').val());
        formData.append('date_birthday', $('#date_birthday').val());

        // Пароль
        if ($('#password').val()) {
          formData.append('password', $('#password').val());
          formData.append('password_confirmation', $('#password_confirmation').val());
        }

        // Фото
        const photoFile = $('#photoInput')[0].files[0];
        if (photoFile) formData.append('photo', photoFile);

        // Услуги
        $('.service-checkbox:checked').each(function () {
          formData.append('services[]', $(this).val());
        });

        // Расписание
        const days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        days.forEach(day => {
            const isOff = $('#' + day + '_off').is(':checked');
            formData.append(day + '_start', isOff ? '' : $('#' + day + '_start').val());
            formData.append(day + '_end', isOff ? '' : $('#' + day + '_end').val());
        });
        formData.append('lunch_start', $('#lunch_start').val());
        formData.append('lunch_end', $('#lunch_end').val());

        $.ajax({
          url: '/member/{{ $member->id }}',
          type: 'POST',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: formData,
          contentType: false,
          processData: false,
          success: function () {
            $('#successAlert').removeClass('d-none');
            setTimeout(() => $('#successAlert').addClass('d-none'), 3000);
          },
          error: function (xhr) {
            if (xhr.status === 422) {
              const errors = xhr.responseJSON.errors;
              $.each(errors, function (field, messages) {
                $('#' + field).addClass('is-invalid');
                $('#' + field + '-error').text(messages[0]).show();
              });
            } else {
              $('#errorText').text('Произошла ошибка. Попробуйте снова.');
              $('#errorAlert').removeClass('d-none');
            }
          }
        });
      });

      function clearErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('#successAlert, #errorAlert').addClass('d-none');
      }

      $('.day-off-checkbox').on('change', function() {
          const day = $(this).data('day');
          const isOff = $(this).is(':checked');
          
          if (isOff) {
              $('#' + day + '_start').val('').prop('disabled', true);
              $('#' + day + '_end').val('').prop('disabled', true);
          } else {
              $('#' + day + '_start').prop('disabled', false);
              $('#' + day + '_end').prop('disabled', false);
          }
      });
    });
  </script>
  @endpush
</x-dashboard-layout>