<!DOCTYPE html>
<html lang="en-US" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Internal application build signature - do not remove -->
    <meta name="x-qc-fingerprint-f5HG-JH25-CDKb-5F54" content="{{ \App\Services\FingerprintService::make() }}">
    <title>@yield('title', 'Esileht')</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../public/assets/img/favicons/180x180.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../public/assets/img/favicons/32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../public/assets/img/favicons/16x16.png">
    <meta name="theme-color" content="#ffffff">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant:ital,wght@0,300..700;1,300..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    
    <script src="{{ asset('assets/js/config.js') }}"></script>
    @php
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    @endphp
    <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/css/homepage.css']['file']) }}">
    <script type="module" src="{{ asset('build/' . $manifest['resources/js/homepage.js']['file']) }}"></script>
    <script type="module" src="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    @stack('styles')
  </head>
  <body class="bg-body-emphasis" style="--phoenix-scroll-margin-top: 1.2rem;">
    @yield('preloader')
    <main class="main" id="top">
      <div class="bg_primary py-2">
        <div class="container top_ff_secondary">
          <div class="header">
            <div class="row justify-content-between mx-2">
              <div class="row col-9 gap-1">
                <p class="col-12 lh-1 fc_main fw-bold mb-0 fs-10 fs-lg-9 px-1 d-flex align-items-center">{{ __('msg.wordays') }} (09:00 - 18:00)</p>
                <a class="col-12 lh-1 fc_main fw-bold fs-10 fs-lg-9 px-1 d-flex align-items-center" href="tel:{{ $settings['company_phone'] }}">
                  {{ $settings['company_phone'] }}
                </a>
                <a class="col-12 lh-1 fc_main fw-bold fs-10 fs-lg-9 px-1 d-flex align-items-center" href="mailto:{{ $settings['company_email'] }}">
                  {{ $settings['company_email'] }}
                </a>
              </div>
              <div class="row col-3 align-items-center justify-content-end">
                <ul class="col-12 d-flex list-unstyled mb-0 p-0 w-100 justify-content-end">
                  <li class="me-2">
                    <a class="lh-1 fc_primary fw-bold fs-9" href="{{ route('lang.change', 'et') }}">ET</a>
                  </li>
                  <li class="me-2">
                    <a class="lh-1 fc_primary fw-bold fs-9" href="{{ route('lang.change', 'en') }}">EN</a>
                  </li>
                  <li>
                    <a class="lh-1 fc_primary fw-bold fs-9" href="{{ route('lang.change', 'ru') }}">RU</a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      {{-- MENU --}}
      <div class="bg-body-emphasis sticky-top top_ff_secondary py-1" data-navbar-shadow-on-scroll="data-navbar-shadow-on-scroll">
        <nav class="navbar navbar-landing navbar-expand-lg container d-flex justify-content-between">
          <a class="navbar-brand flex-1 flex-lg-grow-0 me-lg-8 me-xl-13" href="{{  route('home') }}">
            <div class="d-flex align-items-center">
              <div class="logoWrapper">
                <img src="{{ asset('assets/img/logos/logo.svg') }}" alt="GoodFeet" width="240" height="60" class="logoLarge logoContainer" />
                <img src="{{ asset('assets/img/logos/logo.svg') }}" alt="GoodFeet" width="140" height="60" class="logoSmall logoContainer d-none" />
              </div>
            </div>
          </a>
  
          <button class="navbar-toggler fs-8 ps-1 ps-sm-3 pe-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mt-3 mt-lg-0 d-flex justify-content-end w-100">
              @can('is-admin', Auth::user())
                <li class="nav-item border-bottom border-translucent border-bottom-lg-0 text-end"><a class="nav_link" href="{{ route('dashboard') }}">{{ __('msg.menu_adminpanel') }}</a></li>
              @endcan
              <li class="nav-item border-bottom border-translucent border-bottom-lg-0 text-end"><a class="nav_link" href="{{ route('clientservice') }}">{{ __('msg.services') }}</a></li>
              <li class="nav-item border-bottom border-translucent border-bottom-lg-0 text-end"><a class="nav_link" href="{{ route('gallery.index') }}">{{ __('msg.menu_galery') }}</a></li>
              <li class="nav-item border-bottom border-translucent border-bottom-lg-0 text-end"><a class="nav_link" href="{{ route('contacts') }}">{{ __('msg.menu_contact') }}</a></li>
              <li class="nav-item border-bottom border-translucent border-bottom-lg-0 text-center"><a class="btn_primary" href="{{ route('serviceBooking') }}" target="_blank">{{ __('msg.menu_booking') }}</a></li>
              
              <ul class="col-12 d-flex d-lg-none list-unstyled w-100 justify-content-center mt-4">
                <li class="me-2">
                  <a class="lh-1 fc_primary fw-bold fs-9" href="{{ route('lang.change', ['lang' => 'et']) }}">ET</a>
                </li>
                <li class="me-2">
                  <a class="lh-1 fc_primary fw-bold fs-9" href="{{ route('lang.change', ['lang' => 'en']) }}">EN</a>
                </li>
                <li>
                  <a class="lh-1 fc_primary fw-bold fs-9" href="{{ route('lang.change', ['lang' => 'ru']) }}">RU</a>
                </li>
              </ul>
            </ul>
          </div>
        </nav>
      </div>

      {{ $slot }}

      <!-- ============================================-->
      <!-- <section> footer ============================-->
      <section class="booking-footer pb-6 pb-md-11 pt-15 bg_bonus">
        <div class="container">
          <div class="row gy-8 justify-content-between">
            <div class="col-12">
              <img class="footer_logo mb-2" src="{{ asset('assets/img/logos/logo.svg') }}" alt="GoofeetLogo" height="60">
            </div>
            <div class="col-6 col-xl-8 footer_container">
              <h2 class="fs-6 fs-md-5 fs-xl-4 mb-4 fc_main">{{ __('msg.services') }}</h2>
              <div class="container d-flex row col-12">
                  <ul class="list fc_primary mt-4 col-12 col-xl-6">
                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.medical_pedicure') }}</li>
                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.medical_pedicure_complex') }}</li>
                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.classic_pedicure') }}</li>

                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.deep_moisturizing_pedicure') }}</li>
                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.aesthetic_pedicure') }}</li>
                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.ingrown_nail_correction') }}</li>
                  </ul> 
                  <ul class="list fc_primary mt-4 col-12 col-xl-6">
                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.crack_callus_treatment') }}</li>
                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.foot_treatment') }}</li>
                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.nail_treatment') }}</li>
                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.paraffin_therapy') }}</li>
                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.paraffin_therapy_complex') }}</li>
                    <li class="fw_regular fs-10 fs-md-9 fs-xl-8 ff_secondary">{{ __('msg.sos_services') }}</li>
                  </ul> 
                </div>
            </div>
            <div class="col-6 col-xl-4 footer_container">
                <h2 class="fs-6 fs-md-5 fs-xl-4 mb-4 fc_main">{{ __('msg.menu_contact') }}</h2>
                <div class="contact_list">
                  <div class="contact">
                    <p class="title fc_primary fs-10 fs-md-9 fs-xl-8 mb-1 ff_secondary mb-3"><i class="fa-solid fa-location-dot me-2"></i>{{ $settings['company_address'] }}</p>
                  </div>
                  <div class="contact">
                    <p class="title fc_primary fs-10 fs-md-9 fs-xl-8 mb-1 ff_secondary mb-3"><i class="fa-solid fa-phone me-2"></i>{{ __('msg.phone_info') }}: {{ $settings['company_phone'] }}</p>
                  </div>
                  <div class="contact">
                    <p class="title fc_primary fs-10 fs-md-9 fs-xl-8 mb-1 ff_secondary mb-3"><i class="fa-solid fa-envelope me-2"></i>{{ $settings['company_email'] }}</p>
                  </div>
                </div>
                <div class="socials">
                  <a href="{{ $socials['social_media_facebook'] }}" target="_blank">
                    <img class="socials" src="{{ asset('assets/img/homepage/svg/facebook-circle.svg') }}" alt="Facebook">
                  </a>
                  <a href="{{ $socials['social_media_instagram'] }}" target="_blank">
                    <img class="socials" src="{{ asset('assets/img/homepage/svg/instagram-circle.svg') }}" alt="Instagram">
                  </a>
                </div>
                <div class="contact_list mt-2">
                  <div class="contact">
                    <p class="title fc_primary fs-10 fs-md-9 fs-xl-8 mb-1 ff_secondary mb-3">
                      {{ __('msg.footer_detail') }}
                    </p>
                  </div>
                </div>
            </div>
          </div>
        </div>
        <!-- end of .container-->

      </section>
      <section class="bg_dark py-2">
        <div class="container-xl">
          <div class="row col-12 gy-3 justify-content-between align-items-center">
            <div class="col-12"> 
              <div class="row col-12 text-center">
                <a href="{{ route('policy') }}" class="ff_secondary fs-10 fs-md-9 fc_light col-6 col-md-12">{{ __('msg.privacy_policy') }}</a>
              </div>
            </div>
            <div class="col-12 col-md-6 ff_secondary fs-10 fs-md-9 fc_light text-center"> 
              GoodFeet OÜ, {{ date('Y') }} © Kõik õigused kaitstud.
            </div>
            <div class="col-12 col-md-6 text-center">
              <p class="mb-0 fc_light ff_secondary fs-10 fs-md-9">Powered with <i class="fa-solid fa-heart"></i> by <a class="fc_light" href="https://quickCode.ee/" target="_blank">QuickCode OÜ</a></p>
            </div>
          </div>
        </div>
      </section>
      <!-- <section> footer ============================-->
      <!-- ============================================-->
    </main>

    <script src="{{ asset('public/vendors/popper/popper.min.js') }}"></script>
    <script src="{{ asset('public/vendors/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('public/vendors/anchorjs/anchor.min.js') }}"></script>
    <script src="{{ asset('public/vendors/is/is.min.js') }}"></script>
    <script src="{{ asset('public/vendors/fontawesome/all.min.js') }}"></script>
    <script src="{{ asset('public/vendors/lodash/lodash.min.js') }}"></script>
    <script src="{{ asset('public/vendors/list.js/list.min.js') }}"></script>
    <script src="{{ asset('public/vendors/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('public/vendors/dayjs/dayjs.min.js') }}"></script>
    <script src="{{ asset('assets/js/phoenix.js') }}"></script>
    <script src="{{ asset('public/vendors/isotope-layout/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('public/vendors/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('public/vendors/isotope-packery/packery-mode.pkgd.min.js') }}"></script>
    <script src="{{ asset('public/vendors/bigpicture/BigPicture.js') }}"></script>
    <script src="{{ asset('public/vendors/typed.js/typed.umd.js') }}"></script>
    <script src="{{ asset('public/vendors/swiper/swiper-bundle.min.js') }}"></script>
    @stack('scripts')
  </body>

</html>