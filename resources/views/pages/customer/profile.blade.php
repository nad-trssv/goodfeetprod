@section('title', __('customer.my_data'))
<x-app-layout>
  <section class="py-7 py-lg-9"><div class="container" style="max-width: 900px">
    <div class="mb-4"><h1 class="fs-4 mb-1">{{ __('customer.my_data') }}</h1><p class="text-body-secondary mb-0">{{ __('customer.profile_note') }}</p></div>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('customer.profile.update') }}" class="card card-body mb-5">
      @csrf @method('PUT')
      <div class="row g-3">
        <div class="col-12 col-md-6"><label class="form-label" for="profile-first-name">Имя</label><input id="profile-first-name" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required>@error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 col-md-6"><label class="form-label" for="profile-last-name">Фамилия</label><input id="profile-last-name" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name', $customer->last_name) }}">@error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 col-md-6"><label class="form-label" for="profile-email">Email</label><input id="profile-email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $customer->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 col-md-6"><label class="form-label" for="profile-phone">Телефон</label><input id="profile-phone" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $customer->phone) }}" required>@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><label class="form-label" for="profile-password">Текущий пароль для подтверждения</label><input id="profile-password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" autocomplete="current-password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">Пароль обязателен при любом сохранении изменений.</div></div>
        <div class="col-12"><button class="btn btn-primary" type="submit">Сохранить данные</button></div>
      </div>
    </form>

    <section class="card border-danger" aria-labelledby="delete-account-title"><div class="card-body">
      <h2 id="delete-account-title" class="fs-6 text-danger">Удаление аккаунта</h2>
      <p class="mb-2">Контактные данные, клиентские заметки и загруженные файлы будут удалены. Записи об услугах могут сохраниться только в обезличенном виде для учёта или выполнения требований закона.</p>
      <p class="small text-body-secondary">Операция необратима. Подробнее — в <a href="{{ route('policy') }}">политике конфиденциальности</a>.</p>
      <form method="POST" action="{{ route('customer.destroy') }}" class="row g-3" onsubmit="return confirm('Удалить аккаунт без возможности восстановления?')">
        @csrf @method('DELETE')
        <div class="col-12 col-md-6"><label class="form-label" for="delete-password">Текущий пароль</label><input id="delete-password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" autocomplete="current-password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><div class="form-check"><input id="confirm-delete" class="form-check-input @error('confirm_delete') is-invalid @enderror" type="checkbox" name="confirm_delete" value="1" required><label class="form-check-label" for="confirm-delete">Я понимаю, что аккаунт нельзя будет восстановить.</label></div></div>
        <div class="col-12"><button class="btn btn-danger" type="submit">Удалить мой аккаунт</button></div>
      </form>
    </div></section>
  </div></section>
</x-app-layout>
