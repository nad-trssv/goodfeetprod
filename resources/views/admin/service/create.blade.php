@section('title', 'Services')
  @push('styles')
    <link href="{{ asset('vendors/choices/choices.min.css') }}" rel="stylesheet" />
  @endpush
  <x-dashboard-layout>
    <div class="content">
      <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('service.index') }}">Услуги</a></li>
          <li class="breadcrumb-item active">Добавить новую услугу</li>
        </ol>
      </nav>
      @if (session('success'))
        <div class="alert alert-outline-success d-flex align-items-center" role="alert">
          <span class="fas fa-check-circle text-success fs-5 me-3"></span>
          <p class="mb-0 flex-1">Новая услуга успешно добавлена!</p>
          <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      <h2 class="mb-4">Создание новой услуги</h2>
      <div class="row">
        <div class="col-xl-9">
          <form class="row g-3 mb-6 needs-validation" novalidate="" action="{{ route('service.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="col-12">
              <div class="form-floating">
                <input class="form-control" id="name" type="text" name="name" placeholder="Название проекта" value="{{ old('name') }}"  required=""/>
                <label for="name">Название проекта</label>
                <div class="invalid-feedback">Это поле обязательное</div>
              </div>
            </div>

            <div class="col-sm-6 col-md-4">
              <div class="form-floating">
                <h5 class="mb-3 text-body-highlight">Цена (&euro;)</h5>
                <div class="col-12">
                  <input class="form-control" id="price" name="price" type="number" placeholder="Цена" value="{{ old('price') }}" required />
                  <div class="invalid-feedback">Это поле обязательное</div>
                  <div class="mt-2 form-switch">
                    <input class="form-check-input" id="price_can_change" name="price_can_change" type="checkbox" value="{{ old('duration_minutes') }}" />
                    <label class="form-check-label" for="price_can_change" style="margin-left: 50px;">Цена может варьироваться</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="form-floating">
                <h5 class="mb-3 text-body-highlight">Продолжительность (в минутах)</h5>
                <div class="col-12">
                  <input class="form-control" id="duration_minutes" name="duration_minutes" type="number" placeholder="Время (в минутах)" value="{{ old('duration_minutes') }}" required />
                  <div class="invalid-feedback">Это поле обязательное</div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="form-floating">
                <h5 class="mb-3 text-body-highlight">Выберите цвет услуги</h5>
                <div class="col-12">
                  <input type="color" name="eventColor" id="eventColor" class="form-control" value="{{ old('eventColor', '#7398E2') }}" required>
                </div>
              </div>
            </div>

            <div class="col-12 gy-6">
              <div class="form-floating form-floating-advance-select">
                <label>Мастера</label>
                <select class="form-select" id="masters" name="masters[]" data-choices="data-choices" multiple="multiple" data-options='{"removeItemButton":true,"placeholder":true}'  multiple required>
                  @if ($masters)
                      @foreach ($masters as $master)
                        <option value="{{ $master['id'] }}">{{ $master['name'] }}</option>
                      @endforeach
                  @endif
                </select>
                <div class="invalid-feedback">Выберите минимум одного мастера</div>
              </div>
            </div>
            <div class="col-12 gy-6">
              <label class="form-label fw-semibold" for="image">Изображение услуги</label>
              <input class="form-control @error('image') is-invalid @enderror" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
              @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <div class="form-text">Рекомендуется WebP или JPG, 1200×800 px, до 300 КБ. Можно загрузить изображение любых пропорций и разрешения в JPG, PNG или WebP, максимум 10 МБ. Оно будет автоматически оптимизировано и обрезано по области показа.</div>
            </div>
            <div class="col-12 gy-6">
              <div class="form-floating">
                <textarea class="form-control" id="short_description" name="short_description" placeholder="Краткое Описание" style="height: 100px"></textarea>
                <label for="short_description">Краткое Описание</label>
              </div>
            </div>
            <div class="col-12 gy-6">
              <label for="full_description">Описание</label>
              <div class="form-floating">
                <textarea id="full_description" class="tinyarea" name="full_description"></textarea>
              </div>
            </div>
            
            <div class="col-12 gy-6">
              <div class="row g-3 justify-content-end">
                <div class="col-auto">
                  <a href="{{ route('service.index') }}" class="btn btn-phoenix-primary px-5">Отмена</a>
                </div>
                <div class="col-auto">
                  <button type="submit" class="btn btn-primary px-5 px-sm-15">Создать</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <x-dashboard-footer />
    </div>
    @push('scripts')
    <script src="{{ asset('vendors/choices/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/tiny.js') }}"></script>
    @endpush
  </x-dashboard-layout>
