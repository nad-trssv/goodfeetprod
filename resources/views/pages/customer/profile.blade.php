@section('title', __('customer.my_data'))
<x-app-layout>
  <section class="py-7 py-lg-9"><div class="container" style="max-width: 900px">
    <div class="mb-4"><h1 class="fs-4 mb-1">{{ __('customer.my_data') }}</h1><p class="text-body-secondary mb-0">{{ __('customer.profile_note') }}</p></div>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('customer.profile.update') }}" class="card card-body mb-5">
      @csrf @method('PUT')
      <div class="row g-3">
        <div class="col-12 col-md-6"><label class="form-label" for="profile-first-name">{{ __('customer.first_name') }}</label><input id="profile-first-name" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required>@error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 col-md-6"><label class="form-label" for="profile-last-name">{{ __('customer.last_name') }}</label><input id="profile-last-name" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name', $customer->last_name) }}">@error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 col-md-6"><label class="form-label" for="profile-email">Email</label><input id="profile-email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $customer->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 col-md-6"><label class="form-label" for="profile-phone">{{ __('customer.phone') }}</label><input id="profile-phone" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $customer->phone) }}" required>@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><label class="form-label" for="profile-password">{{ __('customer.current_password') }}</label><input id="profile-password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" autocomplete="current-password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">{{ __('customer.password_required') }}</div></div>
        <div class="col-12"><button class="btn btn-primary" type="submit">{{ __('customer.save') }}</button></div>
      </div>
    </form>

    <section class="card border-danger" aria-labelledby="delete-account-title"><div class="card-body">
      <h2 id="delete-account-title" class="fs-6 text-danger">{{ __('customer.delete_account') }}</h2>
      <p class="mb-2">{{ __('customer.delete_explanation') }}</p>
      <p class="small text-body-secondary">{{ __('customer.irreversible') }} <a href="{{ route('policy') }}">{{ __('customer.privacy_policy') }}</a>.</p>
      <form method="POST" action="{{ route('customer.destroy') }}" class="row g-3" onsubmit='return confirm(@json(__("customer.delete_confirm")))'>
        @csrf @method('DELETE')
        <div class="col-12 col-md-6"><label class="form-label" for="delete-password">{{ __('customer.current_password') }}</label><input id="delete-password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" autocomplete="current-password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><div class="form-check"><input id="confirm-delete" class="form-check-input @error('confirm_delete') is-invalid @enderror" type="checkbox" name="confirm_delete" value="1" required><label class="form-check-label" for="confirm-delete">{{ __('customer.understand_delete') }}</label></div></div>
        <div class="col-12"><button class="btn btn-danger" type="submit">{{ __('customer.delete_button') }}</button></div>
      </form>
    </div></section>
  </div></section>
</x-app-layout>
