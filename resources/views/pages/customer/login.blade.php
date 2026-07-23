@section('title', __('customer.login_title'))
<x-app-layout><section class="py-8 py-lg-10"><div class="container" style="max-width:560px"><div class="card shadow-sm"><div class="card-body p-4 p-md-5">
<h1 class="fs-4 mb-2">{{ __('customer.login_title') }}</h1><p class="text-body-secondary mb-4">{{ __('customer.login_help') }}</p>
<form method="POST" action="{{ route('customer.login.store') }}">@csrf
<div class="mb-3"><label class="form-label" for="identity">{{ __('customer.email_or_phone') }}</label><input class="form-control @error('identity') is-invalid @enderror" id="identity" name="identity" value="{{ old('identity') }}" autocomplete="username" required>@error('identity')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label class="form-label" for="customer-password">{{ __('customer.password') }}</label><input class="form-control @error('password') is-invalid @enderror" id="customer-password" name="password" type="password" autocomplete="current-password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="form-check mb-4"><input class="form-check-input" id="remember" name="remember" type="checkbox" value="1"><label class="form-check-label" for="remember">{{ __('customer.remember') }}</label></div><button class="btn btn-primary w-100" type="submit">{{ __('customer.login') }}</button></form>
<p class="text-center mt-4 mb-0">{{ __('customer.no_account') }} <a href="{{ route('customer.register') }}">{{ __('customer.create_account') }}</a></p>
</div></div></div></section></x-app-layout>
