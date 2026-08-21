<!DOCTYPE html>
<html lang="en-US" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>GoodFeet - Login to Admin Panel</title>


    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <link rel="icon" type="image/png') }}" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
    <meta name="theme-color" content="#ffffff">
    <script src="{{ asset('public/vendors/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>


    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
    <link href="{{ asset('public/vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link href="{{ asset('assets/css/theme.min.css') }}" type="text/css" rel="stylesheet" id="style-default">
    <link href="{{ asset('assets/css/user.min.css') }}" type="text/css" rel="stylesheet" id="user-style-default">
  </head>


  <body>

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
      <div class="container-fluid bg-body-tertiary dark__bg-gray-1200">
        <div class="bg-holder bg-auth-card-overlay" style="background-image:url({{ asset('assets/img/bg/37.png') }});">
        </div>
        <!--/.bg-holder-->

        <div class="row flex-center position-relative min-vh-100 g-0 py-5">
          <div class="col-11 col-sm-10 col-xl-8">
            <div class="card border border-translucent auth-card">
              <div class="card-body pe-md-0">
                <div class="row align-items-center gx-0 gy-7">
                  <div class="col-auto bg-body-highlight dark__bg-gray-1100 rounded-3 position-relative overflow-hidden auth-title-box">
                    <div class="bg-holder" style="background-image:url({{ asset('assets/img/bg/38.png') }});">
                    </div>
                    <!--/.bg-holder-->

                    <div class="position-relative px-4 px-lg-7 pt-7 pb-7 pb-sm-5 text-center text-md-start pb-lg-7 pb-md-7">
                      <h3 class="mb-3 text-body-emphasis fs-7">QuickCode Authentication</h3>
                      <a class="d-flex flex-center text-decoration-none mb-4" href="https://quickcode.ee" target="_blank">
                        <div class="d-flex align-items-center fw-bolder fs-3 d-inline-block">
                          <img src="{{ asset('assets/img/icons/logo.png') }}" alt="phoenix" width="120" />
                        </div>
                      </a>
                      <p class="text-body-tertiary">Give yourself some hassle-free development process with the uniqueness of QuickCode!</p>
                      <ul class="list-unstyled mb-0 w-max-content w-md-auto">
                        <li class="d-flex align-items-center"><span class="uil uil-check-circle text-success me-2"></span><span class="text-body-tertiary fw-semibold">Fast</span></li>
                        <li class="d-flex align-items-center"><span class="uil uil-check-circle text-success me-2"></span><span class="text-body-tertiary fw-semibold">Simple</span></li>
                        <li class="d-flex align-items-center"><span class="uil uil-check-circle text-success me-2"></span><span class="text-body-tertiary fw-semibold">Responsive</span></li>
                      </ul>
                    </div>
                    <div class="position-relative z-n1 mb-6 d-none d-md-block text-center mt-md-15"><img class="auth-title-box-img d-dark-none" src="{{ asset('assets/img/spot-illustrations/auth.png') }}" alt="" /><img class="auth-title-box-img d-light-none" src="{{ asset('assets/img/spot-illustrations/auth-dark.png') }}" alt="" /></div>
                  </div>
                  <div class="col mx-auto">
                    <div class="auth-form-box">
                      <div class="text-center mb-7">
                        <h3 class="text-body-highlight">Sign In</h3>
                        <p class="text-body-tertiary">Get access to your account</p>
                      </div>
                      <div class="position-relative">
                        <hr class="bg-body-secondary mt-5 mb-4" />
                        <div class="divider-content-center bg-body-emphasis">Please use email</div>
                      </div>
                      
                      <form method="POST" action="{{ route('login.store') }}">
                        @csrf
                        <div class="mb-3 text-start">
                          <label class="form-label" for="email" value="{{ __('Email') }}">Email</label>
                            <div class="form-icon-container">
                            <input class="form-control form-icon-input @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
                            <span class="fas fa-user text-body fs-9 form-icon"></span>
                              @error('email')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                                </span>
                              @enderror
                            </div>
                        </div>
                        <div class="mb-3 text-start">
                          <label class="form-label" for="password" value="{{ __('Password') }}">Password</label>
                          <div class="form-icon-container" data-password="data-password">
                            <input class="form-control form-icon-input pe-6" id="password" type="password"  name="password" required autocomplete="current-password"  data-password-input="data-password-input" /><span class="fas fa-key text-body fs-9 form-icon"></span>
                          </div>
                        </div>
                        <button class="btn btn-primary w-100 mb-3">{{ __('Log in') }}</button>
                      </form>
                      @if((app()->isLocal() && config('app.debug')) || app()->runningUnitTests())
                        @php($developmentAccounts = [
                          ['label' => 'Администратор', 'email' => 'admin@localhost.test', 'password' => 'ChangeMe123!'],
                          ['label' => 'Ресепшионист', 'email' => 'reception@localhost.test', 'password' => 'ChangeMe123!'],
                          ['label' => 'Мастер', 'email' => 'anna.master@example.test', 'password' => 'Master123!'],
                        ])
                        <section class="mt-4 pt-3 border-top" aria-labelledby="development-login-title">
                          <h6 id="development-login-title" class="text-body-highlight mb-1">Быстрый вход</h6>
                          <p class="text-body-tertiary fs-9 mb-3">Только для локальной разработки. Кнопка заполняет поля, но не отправляет форму.</p>
                          <div class="d-grid gap-2">
                            @foreach($developmentAccounts as $account)
                              <button class="btn btn-phoenix-secondary development-login-account text-start" type="button" data-email="{{ $account['email'] }}" data-password="{{ $account['password'] }}">
                                <span class="fw-semibold d-block">{{ $account['label'] }}</span>
                                <small class="text-body-tertiary">{{ $account['email'] }}</small>
                              </button>
                            @endforeach
                          </div>
                        </section>
                        <script>
                          document.querySelectorAll('.development-login-account').forEach(button => {
                            button.addEventListener('click', () => {
                              document.getElementById('email').value = button.dataset.email;
                              document.getElementById('password').value = button.dataset.password;
                              document.getElementById('password').focus();
                            });
                          });
                        </script>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script>
        var navbarTopStyle = window.config.config.phoenixNavbarTopStyle;
        var navbarTop = document.querySelector('.navbar-top');
        if (navbarTopStyle === 'darker') {
          navbarTop.setAttribute('data-navbar-appearance', 'darker');
        }

        var navbarVerticalStyle = window.config.config.phoenixNavbarVerticalStyle;
        var navbarVertical = document.querySelector('.navbar-vertical');
        if (navbarVertical && navbarVerticalStyle === 'darker') {
          navbarVertical.setAttribute('data-navbar-appearance', 'darker');
        }
      </script>
      <div class="support-chat-container">
        <div class="container-fluid support-chat">
          <div class="card bg-body-emphasis">
            <div class="card-header d-flex flex-between-center px-4 py-3 border-bottom border-translucent">
              <h5 class="mb-0 d-flex align-items-center gap-2">Demo widget<span class="fa-solid fa-circle text-success fs-11"></span></h5>
              <div class="btn-reveal-trigger">
                <button class="btn btn-link p-0 dropdown-toggle dropdown-caret-none transition-none d-flex" type="button" id="support-chat-dropdown" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h text-body"></span></button>
                <div class="dropdown-menu dropdown-menu-end py-2" aria-labelledby="support-chat-dropdown"><a class="dropdown-item" href="#!">Request a callback</a><a class="dropdown-item" href="#!">Search in chat</a><a class="dropdown-item" href="#!">Show history</a><a class="dropdown-item" href="#!">Report to Admin</a><a class="dropdown-item btn-support-chat" href="#!">Close Support</a></div>
              </div>
            </div>
            <div class="card-body chat p-0">
              <div class="d-flex flex-column-reverse scrollbar h-100 p-3">
                <div class="text-end mt-6"><a class="mb-2 d-inline-flex align-items-center text-decoration-none text-body-emphasis bg-body-hover rounded-pill border border-primary py-2 ps-4 pe-3" href="#!">
                    <p class="mb-0 fw-semibold fs-9">I need help with something</p><span class="fa-solid fa-paper-plane text-primary fs-9 ms-3"></span>
                  </a><a class="mb-2 d-inline-flex align-items-center text-decoration-none text-body-emphasis bg-body-hover rounded-pill border border-primary py-2 ps-4 pe-3" href="#!">
                    <p class="mb-0 fw-semibold fs-9">I can’t reorder a product I previously ordered</p><span class="fa-solid fa-paper-plane text-primary fs-9 ms-3"></span>
                  </a><a class="mb-2 d-inline-flex align-items-center text-decoration-none text-body-emphasis bg-body-hover rounded-pill border border-primary py-2 ps-4 pe-3" href="#!">
                    <p class="mb-0 fw-semibold fs-9">How do I place an order?</p><span class="fa-solid fa-paper-plane text-primary fs-9 ms-3"></span>
                  </a><a class="false d-inline-flex align-items-center text-decoration-none text-body-emphasis bg-body-hover rounded-pill border border-primary py-2 ps-4 pe-3" href="#!">
                    <p class="mb-0 fw-semibold fs-9">My payment method not working</p><span class="fa-solid fa-paper-plane text-primary fs-9 ms-3"></span>
                  </a>
                </div>
                <div class="text-center mt-auto">
                  <div class="avatar avatar-3xl status-online"><img class="rounded-circle border border-3 border-light-subtle" src="{{ asset('assets/img/team/30.webp') }}" alt="" /></div>
                  <h5 class="mt-2 mb-3">Eric</h5>
                  <p class="text-center text-body-emphasis mb-0">Ask us anything – we’ll get back to you here or by email within 24 hours.</p>
                </div>
              </div>
            </div>
            <div class="card-footer d-flex align-items-center gap-2 border-top border-translucent ps-3 pe-4 py-3">
              <div class="d-flex align-items-center flex-1 gap-3 border border-translucent rounded-pill px-4">
                <input class="form-control outline-none border-0 flex-1 fs-9 px-0" type="text" placeholder="Write message" />
                <label class="btn btn-link d-flex p-0 text-body-quaternary fs-9 border-0" for="supportChatPhotos"><span class="fa-solid fa-image"></span></label>
                <input class="d-none" type="file" accept="image/*" id="supportChatPhotos" />
                <label class="btn btn-link d-flex p-0 text-body-quaternary fs-9 border-0" for="supportChatAttachment"> <span class="fa-solid fa-paperclip"></span></label>
                <input class="d-none" type="file" id="supportChatAttachment" />
              </div>
              <button class="btn p-0 border-0 send-btn"><span class="fa-solid fa-paper-plane fs-9"></span></button>
            </div>
          </div>
        </div>
        <button class="btn btn-support-chat p-0 border border-translucent"><span class="fs-8 btn-text text-primary text-nowrap">Chat demo</span><span class="ping-icon-wrapper mt-n4 ms-n6 mt-sm-0 ms-sm-2 position-absolute position-sm-relative"><span class="ping-icon-bg"></span><span class="fa-solid fa-circle ping-icon"></span></span><span class="fa-solid fa-headset text-primary fs-8 d-sm-none"></span><span class="fa-solid fa-chevron-down text-primary fs-7"></span></button>
      </div>
    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->

  </body>
</html>
