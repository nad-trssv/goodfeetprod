@section('title', 'Мои услуги')

<x-dashboard-layout>
  <div class="content">

    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">
          Мои услуги
        </li>
      </ol>
    </nav>

    {{-- Успешное действие --}}
    @if (session('success'))
      <div class="alert alert-success" role="alert">
        <span class="fas fa-check-circle me-2"></span>
        {{ session('success') }}
      </div>
    @endif

    {{-- Ошибка действия --}}
    @if (session('error'))
      <div class="alert alert-danger" role="alert">
        <span class="fas fa-exclamation-circle me-2"></span>
        {{ session('error') }}
      </div>
    @endif

    {{-- Ошибка валидации --}}
    @if ($errors->any())
      <div class="alert alert-danger" role="alert">
        <span class="fas fa-exclamation-circle me-2"></span>
        Проверьте введённые настройки услуги.
      </div>
    @endif

    <div class="row mb-4 align-items-center justify-content-between">
      <div class="col">
        <h2 class="mb-1">
          Мои услуги
        </h2>

        <p class="text-muted mb-0">
          Подключайте услуги и при необходимости задавайте
          индивидуальную цену и продолжительность.
        </p>
      </div>
      @can('is-superadmin')
        <div class="col-auto">
            <a
                href="{{ route('admin.master-services.index') }}"
                class="btn btn-outline-primary"
            >
                <span class="fas fa-users-cog me-2"></span>
                Администрирование услуг других мастеров
            </a>
        </div>
      @endcan
    </div>

    @php
      $search = $search ?? request()->query('search', '');
      $filter = $filter ?? request()->query('filter', 'all');
    @endphp

    {{-- Поиск и фильтрация --}}
    <div class="service-filter-panel mb-3">

      <form
        method="GET"
        action="{{ route('master.services.index') }}"
        class="service-search-form"
      >
        <input
          type="hidden"
          name="filter"
          value="{{ $filter }}"
        >

        <div class="service-search">
          <span class="fas fa-search service-search__icon"></span>

          <input
            type="search"
            name="search"
            class="form-control"
            value="{{ $search }}"
            placeholder="Поиск услуги по названию"
            autocomplete="off"
            aria-label="Поиск услуги по названию"
          >
        </div>

        <button
          type="submit"
          class="btn btn-primary"
        >
          <span class="fas fa-search me-1"></span>
          Найти
        </button>

        @if ($search !== '')
          <a
            href="{{ route('master.services.index', [
                'filter' => $filter,
            ]) }}"
            class="btn btn-outline-secondary"
          >
            <span class="fas fa-times me-1"></span>
            Очистить
          </a>
        @endif
      </form>

      <div
        class="btn-group service-filter-buttons"
        role="group"
        aria-label="Фильтр услуг"
      >
        <a
          href="{{ route('master.services.index', [
              'search' => $search,
              'filter' => 'all',
          ]) }}"
          class="btn btn-sm
                 {{ $filter === 'all'
                    ? 'btn-primary'
                    : 'btn-outline-primary' }}"
        >
          Все услуги
        </a>

        <a
          href="{{ route('master.services.index', [
              'search' => $search,
              'filter' => 'active',
          ]) }}"
          class="btn btn-sm
                 {{ $filter === 'active'
                    ? 'btn-primary'
                    : 'btn-outline-primary' }}"
        >
          Только активные
        </a>
      </div>

      <div class="service-filter-counter text-muted">
        Найдено:
        <strong>{{ $allServices->count() }}</strong>
      </div>

    </div>

    {{-- Список услуг --}}
    <div class="service-list">

      @forelse ($allServices as $service)

        @php
          $masterService = $masterServices->get($service->id);

          $isActive = (bool) ($masterService?->is_active ?? false);

          $hasPriceOverride =
              $masterService !== null
              && $masterService->price_override !== null;

          $hasDurationOverride =
              $masterService !== null
              && $masterService->duration_minutes_override !== null;

          $hasMinDurationOverride =
              $masterService !== null
              && $masterService->duration_minutes_min_override !== null;

          $effectivePrice = $hasPriceOverride
              ? (float) $masterService->price_override
              : (float) $service->price;

          $effectiveDuration = $hasDurationOverride
              ? (int) $masterService->duration_minutes_override
              : (int) $service->duration_minutes;

          $bufferBefore =
              (int) ($masterService?->buffer_before_minutes ?? 0);

          $bufferAfter =
              (int) ($masterService?->buffer_after_minutes ?? 0);

          $hasCustomSettings =
              $hasPriceOverride
              || $hasDurationOverride
              || $hasMinDurationOverride
              || $bufferBefore > 0
              || $bufferAfter > 0;

          /*
           * Определяем, относится ли ошибка валидации
           * именно к этой услуге.
           */
          $isFailedService =
              $errors->any()
              && (string) old('service_form_id')
                  === (string) $service->id;

          /*
           * Если Laravel вернул форму после ошибки,
           * восстанавливаем введённые значения.
           */
          $useDefaultPrice = $isFailedService
              ? (string) old('use_default_price', '0') === '1'
              : !$hasPriceOverride;

          $useDefaultDuration = $isFailedService
              ? (string) old('use_default_duration', '0') === '1'
              : !$hasDurationOverride;

          $useDefaultMinDuration = $isFailedService
              ? (string) old('use_default_min_duration', '0') === '1'
              : !$hasMinDurationOverride;

          $priceInputValue = $isFailedService
              ? old('price_override', $effectivePrice)
              : $effectivePrice;

          $durationInputValue = $isFailedService
              ? old('duration_minutes_override', $effectiveDuration)
              : $effectiveDuration;

          $minimumDurationInputValue = $isFailedService
              ? old(
                  'duration_minutes_min_override',
                  $hasMinDurationOverride
                      ? $masterService->duration_minutes_min_override
                      : $service->duration_minutes_min
              )
              : (
                  $hasMinDurationOverride
                      ? $masterService->duration_minutes_min_override
                      : $service->duration_minutes_min
              );

          $bufferBeforeInputValue = $isFailedService
              ? old('buffer_before_minutes', $bufferBefore)
              : $bufferBefore;

          $bufferAfterInputValue = $isFailedService
              ? old('buffer_after_minutes', $bufferAfter)
              : $bufferAfter;
        @endphp

        <div
          class="card service-card {{ $isActive ? 'is-active' : '' }}"
          id="service_card_{{ $service->id }}"
        >

          {{-- Свёрнутая строка услуги --}}
          <div class="service-summary">

            <div class="service-summary__main">

              <div class="service-title-row">
                <h5 class="service-title">
                  {{ $service->name }}
                </h5>

                <span
                  class="service-status-badge
                         {{ $isActive
                            ? 'is-enabled'
                            : 'is-disabled' }}"
                >
                  {{ $isActive ? 'Активна' : 'Не активна' }}
                </span>
              </div>

              <div class="service-meta">

                <span
                  class="service-meta-item"
                  title="Продолжительность услуги"
                >
                  <span class="fas fa-clock"></span>

                  <span>
                    {{ $effectiveDuration }} мин
                  </span>
                </span>

                <span
                  class="service-meta-item"
                  title="Цена услуги"
                >
                  <span class="fas fa-euro-sign"></span>

                  <span>
                    {{ number_format(
                        $effectivePrice,
                        2,
                        ',',
                        ' '
                    ) }}
                    €
                  </span>
                </span>

                <span
                  class="service-settings-state
                         {{ $hasCustomSettings
                            ? 'has-custom-settings'
                            : '' }}"
                >
                  <span
                    class="fas
                           {{ $hasCustomSettings
                              ? 'fa-sliders-h'
                              : 'fa-link' }}"
                  ></span>

                  <span class="settings-state-text">
                    {{ $hasCustomSettings
                        ? 'Индивидуальные настройки'
                        : 'Общие настройки' }}
                  </span>
                </span>

              </div>
            </div>

            <div class="service-summary__actions">

              {{-- Подключение или отключение услуги --}}
              <form
                method="POST"
                action="{{ route(
                    'master.service.toggle',
                    $service
                ) }}"
                class="m-0"
              >
                @csrf

                <button
                  type="submit"
                  class="btn btn-sm
                         {{ $isActive
                            ? 'btn-outline-danger'
                            : 'btn-outline-success' }}"
                >
                  <span
                    class="fas
                           {{ $isActive
                              ? 'fa-toggle-on'
                              : 'fa-toggle-off' }}
                           me-1"
                  ></span>

                  {{ $isActive ? 'Отключить' : 'Подключить' }}
                </button>
              </form>

              {{-- Раскрытие настроек --}}
              <button
                type="button"
                class="service-settings-toggle"
                data-bs-toggle="collapse"
                data-bs-target="#service_settings_{{ $service->id }}"
                aria-expanded="{{ $isFailedService ? 'true' : 'false' }}"
                aria-controls="service_settings_{{ $service->id }}"
              >
                <span class="settings-toggle-text">
                  {{ $isFailedService ? 'Скрыть' : 'Настроить' }}
                </span>

                <span
                  class="service-chevron"
                  aria-hidden="true"
                ></span>
              </button>

            </div>
          </div>

          {{-- Раскрываемые настройки --}}
          <div
            id="service_settings_{{ $service->id }}"
            class="collapse {{ $isFailedService ? 'show' : '' }}"
          >
            <form
              method="POST"
              action="{{ route(
                  'master.service.update',
                  $service
              ) }}"
              class="service-settings"
            >
              @csrf
              @method('PUT')

              <input
                type="hidden"
                name="service_form_id"
                value="{{ $service->id }}"
              >

              {{-- Ошибки именно этой формы --}}
              @if ($isFailedService)
                <div
                  class="service-error alert alert-danger"
                  role="alert"
                >
                  <div class="fw-semibold mb-1">
                    Проверьте введённые значения:
                  </div>

                  @foreach ($errors->all() as $error)
                    <div>
                      {{ $error }}
                    </div>
                  @endforeach
                </div>
              @endif

              @if ($service->short_description)
                <div class="service-description">
                  {{ $service->short_description }}
                </div>
              @endif

              <div class="settings-grid">

                {{-- Цена --}}
                <div class="setting-block">

                  <div class="setting-block__header">
                    <span class="setting-block__icon">
                      <span class="fas fa-euro-sign"></span>
                    </span>

                    <div>
                      <h6 class="mb-0">
                        Цена
                      </h6>

                      <span class="setting-block__hint">
                        Стандартная цена:
                        {{ number_format(
                            (float) $service->price,
                            2,
                            ',',
                            ' '
                        ) }}
                        €
                      </span>
                    </div>
                  </div>

                  <input
                    type="hidden"
                    name="use_default_price"
                    value="0"
                  >

                  <div class="form-check form-switch mb-3">
                    <input
                      class="form-check-input use-default-price"
                      type="checkbox"
                      role="switch"
                      name="use_default_price"
                      value="1"
                      id="use_default_price_{{ $service->id }}"
                      {{ $useDefaultPrice ? 'checked' : '' }}
                    >

                    <label
                      class="form-check-label"
                      for="use_default_price_{{ $service->id }}"
                    >
                      Использовать общую цену
                    </label>
                  </div>

                  <label
                    class="form-label"
                    for="price_override_{{ $service->id }}"
                  >
                    Индивидуальная цена
                  </label>

                  <div class="input-group">
                    <input
                      type="number"
                      name="price_override"
                      class="form-control price-override"
                      id="price_override_{{ $service->id }}"
                      min="0"
                      max="99999999.99"
                      step="0.01"
                      value="{{ $priceInputValue }}"
                      {{ $useDefaultPrice ? 'disabled' : '' }}
                    >

                    <span class="input-group-text">
                      €
                    </span>
                  </div>

                </div>

                {{-- Основная продолжительность --}}
                <div class="setting-block">

                  <div class="setting-block__header">
                    <span class="setting-block__icon">
                      <span class="fas fa-clock"></span>
                    </span>

                    <div>
                      <h6 class="mb-0">
                        Продолжительность
                      </h6>

                      <span class="setting-block__hint">
                        Стандартное время:
                        {{ $service->duration_minutes }} мин
                      </span>
                    </div>
                  </div>

                  <input
                    type="hidden"
                    name="use_default_duration"
                    value="0"
                  >

                  <div class="form-check form-switch mb-3">
                    <input
                      class="form-check-input use-default-duration"
                      type="checkbox"
                      role="switch"
                      name="use_default_duration"
                      value="1"
                      id="use_default_duration_{{ $service->id }}"
                      {{ $useDefaultDuration ? 'checked' : '' }}
                    >

                    <label
                      class="form-check-label"
                      for="use_default_duration_{{ $service->id }}"
                    >
                      Использовать Стандартное время
                    </label>
                  </div>

                  <label
                    class="form-label"
                    for="duration_override_{{ $service->id }}"
                  >
                    Индивидуальная продолжительность
                  </label>

                  <div class="input-group">
                    <input
                      type="number"
                      name="duration_minutes_override"
                      class="form-control duration-override"
                      id="duration_override_{{ $service->id }}"
                      min="1"
                      max="1440"
                      step="1"
                      value="{{ $durationInputValue }}"
                      {{ $useDefaultDuration ? 'disabled' : '' }}
                    >

                    <span class="input-group-text">
                      мин
                    </span>
                  </div>

                </div>

                {{-- Минимальная продолжительность --}}
                <div class="setting-block">

                  <div class="setting-block__header">
                    <span class="setting-block__icon">
                      <span class="fas fa-hourglass-half"></span>
                    </span>

                    <div>
                      <h6 class="mb-0">
                        Минимальная продолжительность
                      </h6>

                      <span class="setting-block__hint">
                        Стандартное значение:
                        {{ $service->duration_minutes_min !== null
                            ? $service->duration_minutes_min . ' мин'
                            : 'не задано' }}
                      </span>
                    </div>
                  </div>

                  <input
                    type="hidden"
                    name="use_default_min_duration"
                    value="0"
                  >

                  <div class="form-check form-switch mb-3">
                    <input
                      class="form-check-input
                             use-default-min-duration"
                      type="checkbox"
                      role="switch"
                      name="use_default_min_duration"
                      value="1"
                      id="use_default_min_duration_{{ $service->id }}"
                      {{ $useDefaultMinDuration ? 'checked' : '' }}
                    >

                    <label
                      class="form-check-label"
                      for="use_default_min_duration_{{ $service->id }}"
                    >
                      Использовать Стандартное значение
                    </label>
                  </div>

                  <label
                    class="form-label"
                    for="min_duration_override_{{ $service->id }}"
                  >
                    Индивидуальное минимальное время
                  </label>

                  <div class="input-group">
                    <input
                      type="number"
                      name="duration_minutes_min_override"
                      class="form-control min-duration-override"
                      id="min_duration_override_{{ $service->id }}"
                      min="1"
                      max="1440"
                      step="1"
                      value="{{ $minimumDurationInputValue }}"
                      {{ $useDefaultMinDuration ? 'disabled' : '' }}
                    >

                    <span class="input-group-text">
                      мин
                    </span>
                  </div>

                </div>

                {{-- Техническое время --}}
                <div class="setting-block">

                  <div class="setting-block__header">
                    <span class="setting-block__icon">
                      <span class="fas fa-stopwatch"></span>
                    </span>

                    <div>
                      <h6 class="mb-0">
                        Техническое время
                      </h6>

                      <span class="setting-block__hint">
                        Дополнительно блокирует календарь
                      </span>
                    </div>
                  </div>

                  <div class="row g-3">

                    <div class="col-12 col-sm-6">
                      <label
                        class="form-label"
                        for="buffer_before_{{ $service->id }}"
                      >
                        До услуги
                      </label>

                      <div class="input-group">
                        <input
                          type="number"
                          name="buffer_before_minutes"
                          class="form-control buffer-before"
                          id="buffer_before_{{ $service->id }}"
                          min="0"
                          max="1440"
                          step="1"
                          value="{{ $bufferBeforeInputValue }}"
                          required
                        >

                        <span class="input-group-text">
                          мин
                        </span>
                      </div>
                    </div>

                    <div class="col-12 col-sm-6">
                      <label
                        class="form-label"
                        for="buffer_after_{{ $service->id }}"
                      >
                        После услуги
                      </label>

                      <div class="input-group">
                        <input
                          type="number"
                          name="buffer_after_minutes"
                          class="form-control buffer-after"
                          id="buffer_after_{{ $service->id }}"
                          min="0"
                          max="1440"
                          step="1"
                          value="{{ $bufferAfterInputValue }}"
                          required
                        >

                        <span class="input-group-text">
                          мин
                        </span>
                      </div>
                    </div>

                  </div>

                  <p class="setting-block__note">
                    Это время не показывается клиенту как
                    продолжительность услуги, но учитывается
                    при расчёте свободного времени.
                  </p>

                </div>

              </div>

              <div class="service-settings-footer">
                <button
                  type="submit"
                  class="btn btn-primary"
                >
                  <span class="fas fa-save me-1"></span>
                  Сохранить настройки
                </button>
              </div>

            </form>
          </div>

        </div>

      @empty

        <div class="alert alert-light border text-muted" role="status">
          <span class="fas fa-search me-2"></span>

          @if ($search !== '' && $filter === 'active')
            Среди активных услуг по запросу
            «{{ $search }}» ничего не найдено.
          @elseif ($search !== '')
            По запросу «{{ $search }}» услуги не найдены.
          @elseif ($filter === 'active')
            У вас пока нет активных услуг.
          @else
            Доступные услуги пока не созданы.
          @endif
        </div>

      @endforelse

    </div>

    <x-dashboard-footer />
  </div>

  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        function updateSettingsFields(container) {
          const useDefaultPrice = container.querySelector(
            '.use-default-price'
          );

          const useDefaultDuration = container.querySelector(
            '.use-default-duration'
          );

          const useDefaultMinDuration = container.querySelector(
            '.use-default-min-duration'
          );

          const priceInput = container.querySelector(
            '.price-override'
          );

          const durationInput = container.querySelector(
            '.duration-override'
          );

          const minDurationInput = container.querySelector(
            '.min-duration-override'
          );

          if (useDefaultPrice && priceInput) {
            priceInput.disabled = useDefaultPrice.checked;
          }

          if (useDefaultDuration && durationInput) {
            durationInput.disabled = useDefaultDuration.checked;
          }

          if (useDefaultMinDuration && minDurationInput) {
            minDurationInput.disabled =
              useDefaultMinDuration.checked;
          }
        }

        document
          .querySelectorAll('.service-settings')
          .forEach(function (container) {
            updateSettingsFields(container);

            container
              .querySelectorAll(
                '.use-default-price, ' +
                '.use-default-duration, ' +
                '.use-default-min-duration'
              )
              .forEach(function (input) {
                input.addEventListener('change', function () {
                  updateSettingsFields(container);
                });
              });
          });

        document
          .querySelectorAll('.service-card .collapse')
          .forEach(function (collapseElement) {
            const card = collapseElement.closest('.service-card');

            if (!card) {
              return;
            }

            const toggleText = card.querySelector(
              '.settings-toggle-text'
            );

            collapseElement.addEventListener(
              'show.bs.collapse',
              function () {
                if (toggleText) {
                  toggleText.textContent = 'Скрыть';
                }
              }
            );

            collapseElement.addEventListener(
              'hide.bs.collapse',
              function () {
                if (toggleText) {
                  toggleText.textContent = 'Настроить';
                }
              }
            );
          });
      });
    </script>
  @endpush

</x-dashboard-layout>