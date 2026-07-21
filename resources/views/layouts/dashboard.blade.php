<!DOCTYPE html>
<html lang="en-US" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'QuickCode OU')</title>

    <!-- {{ asset('assets') }} -->

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/favicon-16x16.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicons/favicon.png') }}">
    <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
    <meta name="msapplication-TileImage" content="{{ asset('assets/img/favicons/favicon.png') }}">
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
    {{-- <link href="{{ asset('assets/css/theme.min.css') }}" type="text/css" rel="stylesheet" id="style-default"> --}}
    <link href="{{ asset('assets/css/user-rtl.min.css') }}" type="text/css" rel="stylesheet" id="user-style-rtl">
    <link href="{{ asset('assets/css/user.min.css') }}" type="text/css" rel="stylesheet" id="user-style-default">
    <link href="{{ asset('public/vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendors/leaflet/leaflet.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendors/leaflet.markercluster/MarkerCluster.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendors/leaflet.markercluster/MarkerCluster.Default.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendors/nouislider/nouislider.min.css') }}" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Подключаем TinyMCE и его jQuery интеграцию -->
    <script src="https://cdn.tiny.cloud/1/s7ud0rfb9rhqny56ggfzbslnp2omlpkuonbdswz8xud5104j/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tinymce/tinymce-jquery@2/dist/tinymce-jquery.min.js"></script>

    @php
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    @endphp

    <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/css/app.css']['file']) }}">
    <script type="module" src="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}"></script>
    @stack('styles')
  </head>


  <body>

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
      <x-dashboard-sidebar />
      <x-dashboard-top-header />
      
      {{ $slot }}

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
    </main>


    <!-- ===============================================-->
    <!--    JavaScripts-->
    <!-- ===============================================-->
    <script src="{{ asset('public/vendors/popper/popper.min.js') }}"></script>
    <script src="{{ asset('public/vendors/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('public/vendors/anchorjs/anchor.min.js') }}"></script>
    <script src="{{ asset('public/vendors/is/is.min.js') }}"></script>
    <script src="{{ asset('public/vendors/fontawesome/all.min.js') }}"></script>
    <script src="{{ asset('public/vendors/lodash/lodash.min.js') }}"></script>
    <script src="{{ asset('public/vendors/list.js/list.min.js') }}"></script>
    <script src="{{ asset('public/vendors/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('public/vendors/dayjs/dayjs.min.js') }}"></script>
    <script src="{{ asset('public/vendors/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('public/vendors/leaflet.markercluster/leaflet.markercluster.js') }}"></script>
    <script src="{{ asset('public/vendors/leaflet.tilelayer.colorfilter/leaflet-tilelayer-colorfilter.min.js') }}"></script>
    <script src="{{ asset('assets/js/phoenix.js') }}"></script>
    <script src="{{ asset('public/vendors/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('assets/js/ecommerce-dashboard.js') }}"></script>
    <script src="{{ asset('public/vendors/flatpickr/flatpickr.min.js') }}"></script>
    @stack('scripts')
  </body>

</html>
