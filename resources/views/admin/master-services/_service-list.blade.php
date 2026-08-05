@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<form method="GET" action="{{ $catalogUrl }}" class="row g-2 align-items-end mb-3">
  @if(!empty($catalogMasterParameter))<input type="hidden" name="master_id" value="{{ $master->id }}">@endif
  <div class="col-12 col-md"><label class="form-label">Поиск услуги</label><input class="form-control" type="search" name="search" value="{{ $search }}"></div>
  <div class="col-8 col-md-3"><label class="form-label">Показывать</label><select class="form-select" name="filter"><option value="all" @selected($filter === 'all')>Все услуги</option><option value="active" @selected($filter === 'active')>Только активные</option></select></div>
  <div class="col-4 col-md-auto"><button class="btn btn-primary w-100" type="submit"><span class="fas fa-search me-1"></span>Найти</button></div>
</form>

<div class="d-flex justify-content-between align-items-center mb-3"><span class="text-body-tertiary">Найдено: <strong>{{ $allServices->count() }}</strong></span><span class="text-body-tertiary">Сотрудник: <strong>{{ $master->name }}</strong></span></div>

<div class="d-grid gap-3">
@forelse($allServices as $service)
  @php
    $masterService = $masterServices->get($service->id);
    $active = (bool)($masterService?->is_active ?? false);
    $customPrice = $masterService?->price_override !== null;
    $customDuration = $masterService?->duration_minutes_override !== null;
    $customMin = $masterService?->duration_minutes_min_override !== null;
  @endphp
  <article class="card border {{ $active ? 'border-primary' : '' }}">
    <div class="card-body">
      <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <img src="{{ $service->image_url }}" alt="{{ $service->name }}" width="96" height="64" loading="lazy" decoding="async" class="rounded object-fit-cover flex-shrink-0" style="width:96px;height:64px">
        <div class="min-w-0 flex-grow-1">
          <div class="d-flex flex-wrap align-items-center gap-2"><h5 class="mb-0 text-break">{{ $service->name }}</h5><span class="badge {{ $active ? 'bg-success' : 'bg-secondary' }}">{{ $active ? 'Активна' : 'Не активна' }}</span></div>
          <div class="text-body-tertiary mt-2">{{ $customPrice ? $masterService->price_override : $service->price }} € · {{ $customDuration ? $masterService->duration_minutes_override : $service->duration_minutes }} мин.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <form method="POST" action="{{ route('admin.master-services.toggle', [$master->id, $service->id]) }}">@csrf<button class="btn {{ $active ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">{{ $active ? 'Отключить' : 'Подключить' }}</button></form>
          <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#service-settings-{{ $service->id }}">Настроить</button>
        </div>
      </div>
      <div class="collapse mt-4" id="service-settings-{{ $service->id }}">
        <form method="POST" action="{{ route('admin.master-services.update', [$master->id, $service->id]) }}" class="row g-3">@csrf @method('PUT')
          <input type="hidden" name="service_form_id" value="{{ $service->id }}">
          <div class="col-12 col-md-4"><label class="form-label">Цена</label><input type="hidden" name="use_default_price" value="0"><div class="form-check mb-2"><input class="form-check-input default-toggle" type="checkbox" name="use_default_price" value="1" data-target="price-{{ $service->id }}" @checked(!$customPrice)><label class="form-check-label">Цена услуги по умолчанию</label></div><input id="price-{{ $service->id }}" class="form-control" name="price_override" type="number" min="0" step="0.01" value="{{ $masterService?->price_override ?? $service->price }}" @disabled(!$customPrice)></div>
          <div class="col-12 col-md-4"><label class="form-label">Продолжительность</label><input type="hidden" name="use_default_duration" value="0"><div class="form-check mb-2"><input class="form-check-input default-toggle" type="checkbox" name="use_default_duration" value="1" data-target="duration-{{ $service->id }}" @checked(!$customDuration)><label class="form-check-label">По умолчанию</label></div><input id="duration-{{ $service->id }}" class="form-control" name="duration_minutes_override" type="number" min="1" max="1440" value="{{ $masterService?->duration_minutes_override ?? $service->duration_minutes }}" @disabled(!$customDuration)></div>
          <div class="col-12 col-md-4"><label class="form-label">Минимальная продолжительность</label><input type="hidden" name="use_default_min_duration" value="0"><div class="form-check mb-2"><input class="form-check-input default-toggle" type="checkbox" name="use_default_min_duration" value="1" data-target="minimum-{{ $service->id }}" @checked(!$customMin)><label class="form-check-label">По умолчанию</label></div><input id="minimum-{{ $service->id }}" class="form-control" name="duration_minutes_min_override" type="number" min="1" max="1440" value="{{ $masterService?->duration_minutes_min_override ?? $service->duration_minutes_min }}" @disabled(!$customMin)></div>
          <div class="col-6 col-md-3"><label class="form-label">Буфер до, мин.</label><input class="form-control" name="buffer_before_minutes" type="number" min="0" max="1440" value="{{ $masterService?->buffer_before_minutes ?? 0 }}" required></div>
          <div class="col-6 col-md-3"><label class="form-label">Буфер после, мин.</label><input class="form-control" name="buffer_after_minutes" type="number" min="0" max="1440" value="{{ $masterService?->buffer_after_minutes ?? 0 }}" required></div>
          <div class="col-12"><button class="btn btn-primary" type="submit">Сохранить настройки</button></div>
        </form>
      </div>
    </div>
  </article>
@empty
  <div class="alert alert-light border text-center">Услуги не найдены.</div>
@endforelse
</div>

@once
@push('scripts')
<script>document.addEventListener('change',function(event){if(!event.target.classList.contains('default-toggle'))return;const input=document.getElementById(event.target.dataset.target);if(input)input.disabled=event.target.checked;});</script>
@endpush
@endonce
