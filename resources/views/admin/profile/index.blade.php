@section('title', 'Profile')
  <x-dashboard-layout>
    <div class="content">
      <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item active">Профиль</li>
        </ol>
      </nav>

      
      <div class="mb-9">
        <div class="row g-6">
          <div class="col-12">
            <div class="border-bottom mb-4">
                <form id="profileForm" class="needs-validation" novalidate="">
                    @csrf
                    <input id="user_id" name="user_id" type="number" placeholder="" value="{{ $profile['id'] }}" hidden>
                    <div class="mb-6">
                        <h4 class="mb-4">Персональная информация</h4>
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <div class="form-icon-container">
                                <div class="form-floating">
                                    <input class="form-control form-icon-input" id="name" name="name" type="text" placeholder="" value="{{ $profile['name'] }}" required>
                                    <label class="text-body-tertiary form-icon-label" for="name">Имя</label>
                                    <div class="invalid-feedback">Это поле обязательное</div>
                                </div><svg class="svg-inline--fa fa-user text-body fs-9 form-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"></path></svg><!-- <span class="fa-solid fa-user text-body fs-9 form-icon"></span> Font Awesome fontawesome.com -->
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-icon-container">
                                <div class="form-floating">
                                    <input class="form-control form-icon-input" id="username" name="username" type="text" placeholder="" value="{{ $profile['username'] }}" required>
                                    <label class="text-body-tertiary form-icon-label" for="username">Никнейм</label>
                                    <div class="invalid-feedback">Это поле обязательное</div>
                                </div><svg class="svg-inline--fa fa-user text-body fs-9 form-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"></path></svg><!-- <span class="fa-solid fa-user text-body fs-9 form-icon"></span> Font Awesome fontawesome.com -->
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-icon-container">
                                <div class="form-floating">
                                    <input class="form-control form-icon-input" id="emailSocial" type="email" name="email" placeholder="" value="{{ $profile['email'] }}" required>
                                    <label class="text-body-tertiary form-icon-label" for="emailSocial">введите EMAIL</label>
                                    <div class="invalid-feedback">Это поле обязательное</div>
                                </div><svg class="svg-inline--fa fa-envelope text-body fs-9 form-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="envelope" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"></path></svg><!-- <span class="fa-solid fa-envelope text-body fs-9 form-icon"></span> Font Awesome fontawesome.com -->
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-icon-container">
                                <div class="form-floating">
                                    <input class="form-control form-icon-input" id="phone" name="phone" type="tel" placeholder="" value="{{ $profile['phone'] }}" pattern="^\+[\d\s]+$" required>
                                    <div class="invalid-feedback">Телефон должен начинаться с "+" и содержать только цифры</div>
                                    <label class="text-body-tertiary form-icon-label" for="phone">введите телефон</label>
                                </div><svg class="svg-inline--fa fa-phone text-body fs-9 form-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="phone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z"></path></svg><!-- <span class="fa-solid fa-phone text-body fs-9 form-icon"></span> Font Awesome fontawesome.com -->
                                </div>
                            </div>
                            <div class="flatpickr-input-container mb-5">
                              <div class="form-floating">
                                <input class="form-control datetimepicker" id="date_birthday" type="text" value="{{ $profile['date_birthday'] }}" name="date_birthday" placeholder="yyyy/mm/dd" data-options='{"disableMobile":true,"enableTime":"false","dateFormat":"Y-m-d}' />
                                <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                                <label class="ps-6" for="date_birthday">Birthday date</label>
                                <div class="invalid-feedback" id="date_birthday-error"></div>
                              </div>
                            </div>
                            <div class="col-12 col-sm-6">
                              <label class="form-label" for="locale">{{ __('admin_nav.interface_language') }}</label>
                              <select class="form-select" id="locale" name="locale">
                                @foreach(config('supported_locales') as $localeCode => $language)
                                  <option value="{{ $localeCode }}" @selected(($profile->locale ?? config('app.locale')) === $localeCode)>{{ $language }}</option>
                                @endforeach
                              </select>
                              <div class="form-text">{{ __('admin_nav.interface_language_hint') }}</div>
                            </div>
                            <div class="col-12">
                              <label class="form-label mb-2">Специализация мастера</label>
                              <ul class="nav nav-pills gap-1 mb-2" role="tablist">
                                @foreach(config('supported_locales') as $locale => $label)
                                  <li class="nav-item"><button class="nav-link py-1 px-3 {{ $loop->first ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#profile-title-{{ $locale }}" type="button">{{ $label }}</button></li>
                                @endforeach
                              </ul>
                              <div class="tab-content">
                                @foreach(config('supported_locales') as $locale => $label)
                                  <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="profile-title-{{ $locale }}">
                                    <input class="form-control profile-professional-title" data-locale="{{ $locale }}" maxlength="120" value="{{ $profile->professional_titles[$locale] ?? '' }}" placeholder="Например: мастер по педикюру">
                                  </div>
                                @endforeach
                              </div>
                              <div class="form-text">Эта подпись отображается клиенту под вашим именем при выборе мастера.</div>
                            </div>
                        </div>
                    </div>
                    <div class="row gx-3 mb-6 gy-6 gy-sm-3">
                        <div class="col-12 col-sm-6">
                            <div class="row g-4 mb-4" data-dropzone="data-dropzone" data-options='{"maxFiles":1,"data":[{"name":"{{ $profile['profile_photo_storageFileName'] }}","size":"54kb","url":"{{ asset($profile['profile_photo_storage']) }}"}]}'>
                                <div class="fallback">
                                <input type="file" name="file" id="file">
                                </div>
                                <div class="col-md-auto">
                                    <div class="dz-preview dz-preview-single">
                                        <div class="dz-preview-cover d-flex align-items-center justify-content-center mb-2 mb-md-0">
                                        <div class="avatar avatar-4xl">
                                            <img class="rounded-circle avatar-placeholder" src="{{ asset($profile['profile_photo_fullpath']) }}"  alt="..." data-dz-thumbnail="data-dz-thumbnail" />
                                        </div>
                                        <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""></span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="dz-message dropzone-area px-2 py-3" data-dz-message="data-dz-message">
                                        <div class="text-center text-body-emphasis">
                                        <h5 class="mb-2"><span class="fa-solid fa-upload me-2"></span>Upload Profile Picture</h5>
                                        <p class="mb-0 fs-9 text-body-tertiary text-opacity-85 lh-sm">Upload a 300x300 jpg image with <br />a maximum size of 400KB</p>
                                        </div>
                                    </div>
                                </div>
                          </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <h4 class="mb-4">Смена пароля</h4>
                            <div class="form-icon-container mb-3">
                                <div class="form-floating">
                                <input class="form-control form-icon-input" id="oldPassword" type="password" name="oldPassword" placeholder="Old password">
                                <label class="text-body-tertiary form-icon-label" for="oldPassword">Старый пароль</label>
                                <div id="error-oldPassword" class="invalid-feedback"></div>
                                </div><svg class="svg-inline--fa fa-lock text-body fs-9 form-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="lock" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"></path></svg><!-- <span class="fa-solid fa-lock text-body fs-9 form-icon"></span> Font Awesome fontawesome.com -->
                            </div>
                            <div class="form-icon-container mb-3">
                                <div class="form-floating">
                                <input class="form-control form-icon-input" id="password" type="password" name="password" placeholder="New password">
                                <label class="text-body-tertiary form-icon-label" for="password">Новый пароль</label>
                                <div id="error-password" class="invalid-feedback"></div>
                                </div><svg class="svg-inline--fa fa-key text-body fs-9 form-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="key" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M336 352c97.2 0 176-78.8 176-176S433.2 0 336 0S160 78.8 160 176c0 18.7 2.9 36.8 8.3 53.7L7 391c-4.5 4.5-7 10.6-7 17v80c0 13.3 10.7 24 24 24h80c13.3 0 24-10.7 24-24V448h40c13.3 0 24-10.7 24-24V384h40c6.4 0 12.5-2.5 17-7l33.3-33.3c16.9 5.4 35 8.3 53.7 8.3zM376 96a40 40 0 1 1 0 80 40 40 0 1 1 0-80z"></path></svg><!-- <span class="fa-solid fa-key text-body fs-9 form-icon"></span> Font Awesome fontawesome.com -->
                            </div>
                            <div class="form-icon-container">
                                <div class="form-floating">
                                <input class="form-control form-icon-input" id="password_confirm" type="password" name="password_confirm" placeholder="Confirm New password">
                                <label class="text-body-tertiary form-icon-label" for="password_confirm">Повторите новый пароль</label>
                                <div id="error-password_confirm" class="invalid-feedback"></div>
                                </div><svg class="svg-inline--fa fa-key text-body fs-9 form-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="key" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M336 352c97.2 0 176-78.8 176-176S433.2 0 336 0S160 78.8 160 176c0 18.7 2.9 36.8 8.3 53.7L7 391c-4.5 4.5-7 10.6-7 17v80c0 13.3 10.7 24 24 24h80c13.3 0 24-10.7 24-24V448h40c13.3 0 24-10.7 24-24V384h40c6.4 0 12.5-2.5 17-7l33.3-33.3c16.9 5.4 35 8.3 53.7 8.3zM376 96a40 40 0 1 1 0 80 40 40 0 1 1 0-80z"></path></svg><!-- <span class="fa-solid fa-key text-body fs-9 form-icon"></span> Font Awesome fontawesome.com -->
                            </div>
                        </div>
                    </div>
                    @if($services->count() > 0)
                        <div class="mb-6">
                            <h4 class="mb-3">Мои услуги</h4>
                            <div class="row g-2">
                                @foreach($services as $service)
                                <div class="col-auto">
                                    <div class="card border px-3 py-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fas fa-check-circle text-success"></span>
                                            <span class="fw-semibold fs-9">{{ $service->name }}</span>
                                            <span class="badge bg-primary fs-10">{{ $service->price }} €</span>
                                            <span class="text-muted fs-10">{{ $service->duration_minutes }} мин</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <p class="text-muted fs-10 mt-2">
                                <span class="fas fa-info-circle me-1"></span>
                                Управляйте своими услугами на странице <a href="{{ route('master.services.index') }}">Мои услуги</a>
                            </p>
                        </div>
                    @endif
                    <div class="text-end mb-6">
                        <div>
                        <button class="btn btn-phoenix-primary">Сохранить</button>
                        </div>
                    </div>
                </form>
            </div>
          </div>
        </div>
      </div>
      <x-dashboard-footer />
    </div>
    @push('scripts')
    <script src="{{ asset('public/vendors/choices/choices.min.js') }}"></script>
    <script src="{{ asset('public/vendors/dropzone/dropzone-min.js') }}"></script>
    
    <script>
        $(document).ready(function() {
          
              $('#profileForm').submit(function(e){
                e.preventDefault();
                let formData = new FormData();
                let username = '@' + $("#username").val();
                
                formData.append("user_id", $("#user_id").val());
                formData.append("name", $("#name").val());
                formData.append("username", username); 
                formData.append("email", $("#emailSocial").val());
                formData.append("phone", $("#phone").val());
                 formData.append("date_birthday", $("#date_birthday").val());
                 formData.append("locale", $("#locale").val());
                $('.profile-professional-title').each(function () {
                    formData.append(`professional_titles[${$(this).data('locale')}]`, $(this).val());
                });
                formData.append("oldPassword", $("#oldPassword").val());
                formData.append("password", $("#password").val());
                formData.append("password_confirm", $("#password_confirm").val());
                let dropzoneInstance = Dropzone.instances[0];
                if (dropzoneInstance) {
                    if (dropzoneInstance.files.length > 0) {
                        formData.append("profile_photo_path", dropzoneInstance.files[0]);
                    }
                }


                $.ajax({
                  url: '{{ route('profile.ajax.update') }}',
                  type: "POST",
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                  },
                  data: formData,
                  contentType: false,
                  processData: false,
                  success: function(response) {
                    $('#error-password_confirm').text('').hide();
                    $('#error-password').text('').hide();
                    $('#error-oldPassword').text('').hide();
                    $("#password_confirm").val('');
                    $("#password").val('');
                    $("#oldPassword").val('');
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: response.message,
                        showConfirmButton: false,
                        iconColor: '#1463b8',
                        timerProgressBar: true,
                        timer: 1500,
                    });
                    if (response.profile.locale !== @js(app()->getLocale())) {
                      window.setTimeout(() => window.location.reload(), 500);
                    }
  
                  },
                  error: function(xhr) { 
                    $('#error-password_confirm').text('').hide();
                    $('#error-password').text('').hide();
                    $('#error-oldPassword').text('').hide();
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        if (xhr.responseJSON.errors.password_confirm) {
                            $('#error-password_confirm').text(xhr.responseJSON.errors.password_confirm[0]).show();
                        }
                        if (xhr.responseJSON.errors.password) {
                            $('#error-password').text(xhr.responseJSON.errors.password[0]).show();
                        }
                        if (xhr.responseJSON.errors.oldPassword) {
                            $('#error-oldPassword').text(xhr.responseJSON.errors.oldPassword[0]).show();
                        }
                    }
                    getErrors(xhr);
                 }
                });
                
              });
        });
      </script>
    @endpush
  </x-dashboard-layout>
