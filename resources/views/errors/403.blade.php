<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>403</title>
  <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
  <main class="container py-5"><div class="card border-0 shadow-sm mx-auto" style="max-width:620px"><div class="card-body p-4 p-lg-5 text-center">
    <div class="fs-1 fw-bold text-danger mb-3">403</div><h1 class="h3 mb-3">{{ __('admin_roles.access_denied') }}</h1>
    <p class="text-body-tertiary">{{ $exception->getMessage() ?: __('admin_roles.access_denied') }}</p>
    <a href="{{ url()->previous() === url()->current() ? route('home') : url()->previous() }}" class="btn btn-primary mt-2">← {{ __('Back') }}</a>
  </div></div></main>
</body>
</html>
