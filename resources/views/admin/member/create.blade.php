@section('title', 'Member')
  @push('styles')
    <link href="{{ asset('public/vendors/choices/choices.min.css') }}" rel="stylesheet" />
  @endpush
  <x-dashboard-layout>
    <div class="content">
      <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('member.index') }}">Персонал</a></li>
          <li class="breadcrumb-item active">добавление пользователя</li>
        </ol>
      </nav>
      @if (session('success'))
        <div class="alert alert-outline-success d-flex align-items-center" role="alert">
          <span class="fas fa-check-circle text-success fs-5 me-3"></span>
          <p class="mb-0 flex-1">Новый пользователь успешно добавлен!</p>
          <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      <h2 class="mb-4">Создание нового пользователя</h2>
      
      <div class="card theme-wizard mb-5" data-theme-wizard="data-theme-wizard">
        <div class="card-body pt-4 pb-0">
          <div class="row justify-content-between">
            <div class="col-md-3 order-md-1">
              <div class="scrollbar mb-4">
                <ul class="nav justify-content-between flex-nowrap nav-wizard nav-wizard-vertical-md" role="tablist">
                  <li class="nav-item" role="presentation">
                    <a id="stepFirst" class="nav-link active py-0 py-md-3" href="#bootstrap-vertical-wizard-tab1" data-bs-toggle="tab" data-wizard-step="1" aria-selected="true" role="tab">
                      <div class="text-center d-inline-block d-md-flex align-items-center gap-3"><span class="nav-item-circle-parent"><span class="nav-item-circle"><svg class="svg-inline--fa fa-file nav-item-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="file" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" data-fa-i2svg=""><path fill="currentColor" d="M0 64C0 28.7 28.7 0 64 0H224V128c0 17.7 14.3 32 32 32H384V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V64zm384 64H256V0L384 128z"></path></svg><!-- <span class="fa-solid fa-file nav-item-icon"></span> Font Awesome fontawesome.com --><svg class="svg-inline--fa fa-check check-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"></path></svg><!-- <span class="fa-solid fa-check check-icon"></span> Font Awesome fontawesome.com --></span></span><span class="nav-item-title fs-9 fs-xl-8">Account</span></div>
                    </a>
                  </li>
                  <li class="nav-item" role="presentation">
                    <a id="stepSec" class="nav-link py-0 py-md-3" href="#bootstrap-vertical-wizard-tab2" data-bs-toggle="tab" data-wizard-step="2" aria-selected="false" tabindex="-1" role="tab">
                      <div class="text-center d-inline-block d-md-flex align-items-center gap-3"><span class="nav-item-circle-parent"><span class="nav-item-circle"><svg class="svg-inline--fa fa-location-dot nav-item-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="location-dot" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" data-fa-i2svg=""><path fill="currentColor" d="M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z"></path></svg><!-- <span class="fa-solid fa-location-dot nav-item-icon"></span> Font Awesome fontawesome.com --><svg class="svg-inline--fa fa-check check-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"></path></svg><!-- <span class="fa-solid fa-check check-icon"></span> Font Awesome fontawesome.com --></span></span><span class="nav-item-title fs-9 fs-xl-8">Personal</span></div>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#bootstrap-vertical-wizard-tab4" class="nav-link py-0 py-md-3 cursor-default" data-bs-toggle="tab" data-wizard-step="4" disabled>
                      <div class="text-center d-inline-block d-md-flex align-items-center gap-3"><span class="nav-item-circle-parent"><span class="nav-item-circle"><span class="fa-solid fa-images nav-item-icon"></span><span class="fa-solid fa-check check-icon"></span></span></span><span class="nav-item-title fs-9 fs-xl-8">Done</span></div>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
            <div class="col-md-8">
              <div class="tab-content">
                <div class="tab-pane active" role="tabpanel" aria-labelledby="bootstrap-vertical-wizard-tab1" id="bootstrap-vertical-wizard-tab1">
                  <form class="needs-validation" id="wizardValidationForm1" novalidate="novalidate" data-wizard-form="1">
                    <div class="row g-4 mb-4" data-dropzone="data-dropzone" data-options='{"maxFiles":1,"data":[{"name":"avatar.webp","size":"54kb","url":"../../assets/img/team"}]}'>
                      <div class="fallback">
                        <input type="file" name="file" id="file">
                      </div>
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
                            <h5 class="mb-2"><span class="fa-solid fa-upload me-2"></span>Upload Profile Picture</h5>
                            <p class="mb-0 fs-9 text-body-tertiary text-opacity-85 lh-sm">Upload a 300x300 jpg image with <br />a maximum size of 400KB</p>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="mb-2">
                      <label class="form-label text-body" for="bootstrap-vertical-wizard-wizard-name">Имя*</label>
                      <input class="form-control" type="text" name="name" id="name" placeholder="John Smith">
                      <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="mb-2">
                      <label class="form-label text-body" for="company_phone">Никнейм*</label>
                      <div class="input-group">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input class="form-control" type="text" name="username" id="username" placeholder="@john">
                        <div class="invalid-feedback" id="username-error"></div>
                      </div>
                    </div>
                    <div class="mb-2">
                      <label class="form-label text-body" for="bootstrap-vertical-wizard-wizard-name">Роль*</label>
                      <select class="fs-10" class="form-select" id="role_id" name="role_id" data-choices="data-choices">
                        @if ($roles)
                            @foreach ($roles as $role)
                              <option class="fs-10" value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                            @endforeach
                        @endif
                      </select>
                    </div>
                    <div class="mb-2">
                      <label class="form-label text-body" for="bootstrap-vertical-wizard-wizard-name">Номер телефона</label>
                      <input class="form-control" type="text" id="phone" name="phone" placeholder="+372 55 555 555">
                      <div class="invalid-feedback" id="phone-error"></div>
                    </div>
                    <div class="flatpickr-input-container mb-5">
                      <div class="form-floating">
                        <input class="form-control datetimepicker" id="date_birthday" type="text" name="date_birthday" placeholder="yyyy/mm/dd" data-options='{"disableMobile":true,"enableTime":"false","dateFormat":"Y-m-d}' />
                        <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                        <label class="ps-6" for="date_birthday">Birthday date</label>
                        <div class="invalid-feedback" id="date_birthday-error"></div>
                      </div>
                    </div>
                  </form>
                </div>    
                <div class="tab-pane" role="tabpanel" aria-labelledby="bootstrap-vertical-wizard-tab2" id="bootstrap-vertical-wizard-tab2">
                  <form id="wizardVerticalForm2" novalidate="novalidate" data-wizard-form="2">
                    <div class="mb-2">
                      <label class="form-label" for="bootstrap-vertical-wizard-wizard-email">Email*</label>
                      <input class="form-control" type="email" name="email" placeholder="Email address" id="email">
                      <div class="invalid-feedback" id="email-error"></div>
                    </div>
                    <div class="row g-3 mb-3">
                      <div class="col-sm-6">
                        <div class="mb-2 mb-sm-0">
                          <label class="form-label text-body" for="bootstrap-vertical-wizard-wizard-password">Пароль*</label>
                          <input class="form-control" type="password" name="password" placeholder="Password" id="password" data-wizard-password="true" >
                          <div class="invalid-feedback" id="password-error"></div>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="mb-2">
                            <label class="form-label text-body" for="bootstrap-vertical-wizard-wizard-confirm-password">Повторите пароль*</label>
                            <input class="form-control" type="password" name="password_confirmation" placeholder="Confirm Password" id="password_confirmation" data-wizard-confirm-password="true" >
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
                
                <div class="tab-pane" role="tabpanel" aria-labelledby="bootstrap-vertical-wizard-tab4" id="bootstrap-vertical-wizard-tab4">
                  <div class="row flex-center pb-8 pt-4 gx-3 gy-4">
                    <div class="col-12 col-sm-auto">
                      <div class="text-center text-sm-start"><img class="d-dark-none" src="../../assets/img/spot-illustrations/38.webp" alt="" width="220" /><img class="d-light-none" src="../../assets/img/spot-illustrations/dark_38.webp" alt="" width="220" /></div>
                    </div>
                    <div class="col-12 col-sm-auto">
                      <div class="text-center text-sm-start">
                        <h5 class="mb-3">You are all set!</h5>
                        <p class="text-body-emphasis fs-9">Now you can access your account<br />anytime anywhere</p><a class="btn btn-primary px-6" href="{{ route('member.index') }}">User list</a>
                      </div>
                    </div>
                  </div>
                </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer border-top-0 underweb" data-wizard-footer="data-wizard-footer">
          <div class="d-flex pager wizard list-inline mb-0">
            <button class="d-none btn btn-link ps-0" type="button" data-wizard-prev-btn="data-wizard-prev-btn"><svg class="svg-inline--fa fa-chevron-left me-1" data-fa-transform="shrink-3" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-left" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" data-fa-i2svg="" style="transform-origin: 0.3125em 0.5em;"><g transform="translate(160 256)"><g transform="translate(0, 0)  scale(0.8125, 0.8125)  rotate(0 0 0)"><path fill="currentColor" d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z" transform="translate(-160 -256)"></path></g></g></svg><!-- <span class="fas fa-chevron-left me-1" data-fa-transform="shrink-3"></span> Font Awesome fontawesome.com -->Previous</button>
            <div class="flex-1 text-end">
              <button class="btn btn-primary px-6 px-sm-6" type="submit" data-wizard-next-btn="data-wizard-next-btn">
                Next
                <span class="fas fa-chevron-right ms-1" data-fa-transform="shrink-3"> </span>
              </button>
            </div>
          </div>
        </div>
        <div id="nextBtnBlock" class="card-footer border-top-0" data-wizard-footer="data-wizard-footer">
          <div class="d-flex pager wizard list-inline mb-0">
            <button class="d-none btn btn-link ps-0" type="button" data-wizard-prev-btn="data-wizard-prev-btn"><svg class="svg-inline--fa fa-chevron-left me-1" data-fa-transform="shrink-3" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-left" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" data-fa-i2svg="" style="transform-origin: 0.3125em 0.5em;"><g transform="translate(160 256)"><g transform="translate(0, 0)  scale(0.8125, 0.8125)  rotate(0 0 0)"><path fill="currentColor" d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z" transform="translate(-160 -256)"></path></g></g></svg><!-- <span class="fas fa-chevron-left me-1" data-fa-transform="shrink-3"></span> Font Awesome fontawesome.com -->Previous</button>
            <div class="flex-1 text-end">
              <button id="nextStep" class="btn btn-primary px-6 px-sm-6" type="submit">
                Continue
                <span class="fas fa-chevron-right ms-1" data-fa-transform="shrink-3"> </span>
              </button>
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
        const nextStep = document.getElementById("nextStep");

        $("#stepFirst").on("click", () => currentStep = 1);
        $("#stepSec").on("click", () => currentStep = 2);

        let currentStep = 1;
        let photo = null;
        
        nextStep.addEventListener("click", (e) => {
            if(currentStep === 1){
              let formData = new FormData(); 
              let username = '@' + $("#username").val();
              formData.append("name", $("#name").val()); 
              formData.append("username", username); 
              formData.append("phone", $("#phone").val()); 
              formData.append("date_birthday", $("#date_birthday").val()); 
              formData.append("role_id", $("#role_id").val()); 

              
              let dropzoneInstance = Dropzone.instances[0];
              if (dropzoneInstance) {
                  if (dropzoneInstance.files.length > 0) {
                      formData.append("photo", dropzoneInstance.files[0]);
                  }
              }
              
              $.ajax({
                url: '/member/store/1',
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                  currentStep++;
                  photo = response.data.photo;
                  $('[data-wizard-next-btn="data-wizard-next-btn"]').click();

                },
                error: function(xhr) { getErrors(xhr); }
              });

            } else if (currentStep === 2) {
              let formData = new FormData(); 
              let username = '@' + $("#username").val();
              
              formData.append("name", $("#name").val()); 
              formData.append("username", username); 
              formData.append("phone", $("#phone").val()); 
              formData.append("date_birthday", $("#date_birthday").val()); 
              formData.append("role_id", $("#role_id").val()); 
              formData.append("profile_photo_path", photo);

              formData.append("email", $("#email").val()); 
              formData.append("password", $("#password").val()); 
              formData.append("password_confirmation", $("#password_confirmation").val()); 
              
              $.ajax({
                url: '/member/store/2',
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                  currentStep++;
                  $('[data-wizard-next-btn="data-wizard-next-btn"]').click();
                  $('#nextBtnBlock').addClass('underweb');
                },
                error: function(xhr) { getErrors(xhr); }
              });
            }
        });

        function getErrors(xhr){
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
        $("#stepFirst, #stepSec").on("click", function() {
          $('.is-invalid').removeClass('is-invalid');
          if($(this).is("#stepSec")){
            const name = $("#name").val();
            const username = $("#username").val();
            const phone = $("#phone").val();
            const roleId = $("#role_id").val();
            if (!name || !username || !phone || !roleId) {
              if (!name) {
                $("#name").addClass("is-invalid");
              } else {
                  $("#name").removeClass("is-invalid");
              }
            
              if (!username) {
                  $("#username").addClass("is-invalid");
              } else {
                  $("#username").removeClass("is-invalid");
              }
            
              if (!phone) {
                  $("#phone").addClass("is-invalid");
              } else {
                  $("#phone").removeClass("is-invalid");
              }
              if (!roleId) {
                  $("#role_id").addClass("is-invalid");
              } else {
                  $("#role_id").removeClass("is-invalid");
              }
                $('[data-wizard-prev-btn="data-wizard-prev-btn"]').click();
            }else{
              let formData = new FormData(); 
              let username = '@' + $("#username").val();
              formData.append("name", $("#name").val()); 
              formData.append("username", username); 
              formData.append("phone", $("#phone").val()); 
              formData.append("date_birthday", $("#date_birthday").val()); 
              formData.append("role_id", $("#role_id").val()); 
              let dropzoneInstance = Dropzone.instances[0];
              if (dropzoneInstance) {
                  if (dropzoneInstance.files.length > 0) {
                      formData.append("photo", dropzoneInstance.files[0]);
                  }
              }

              $.ajax({
                url: '/member/store/1',
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                  photo = response.data.photo;
                },
                error: function(xhr) { 
                  $('[data-wizard-prev-btn="data-wizard-prev-btn"]').click();
                  getErrors(xhr); 
                }
              });
            }
          }
          
          currentStep = $(this).is("#stepFirst") ? 1 : 2;
        });
      });
    </script>
    @endpush
  </x-dashboard-layout>
