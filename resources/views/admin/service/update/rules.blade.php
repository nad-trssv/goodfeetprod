@section('title', 'Изменения по датам')
<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('service.index') }}">Услуги</a></li>
        <li class="breadcrumb-item">
          <a href="{{ route('service.show', $service->id) }}">Редактирование услуги</a>
        </li>
        <li class="breadcrumb-item active">Изменения по датам</li>
      </ol>
    </nav>

    @if (session('success'))
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          Swal.fire({
            title: 'Успешно!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'OK'
          });
        });
      </script>
    @endif

    @if (session('error'))
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          Swal.fire({
            title: 'Ошибка',
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonText: 'OK'
          });
        });
      </script>
    @endif

    <h2 class="mb-4">Изменения по датам для услуги: {{ $service->name }}</h2>

    <div class="row g-4">
      {{-- Левая основная колонка --}}
      <div class="col-12 col-xl-10 order-1 order-xl-0">

        {{-- Текущие правила --}}
        <div class="card shadow-none mb-4 mt-6">
          <div class="card-body p-4">
            <h5 class="mb-3 text-body-highlight">Текущие правила</h5>

            @if($rules->isEmpty())
              <p class="text-body-tertiary mb-0">
                Пока нет ни одного правила. Для всех дат будет использоваться текущая цена и длительность из карточки услуги.
              </p>
            @else
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Действует с</th>
                      <th>По</th>
                      <th>Цена (€)</th>
                      <th>Длительность (мин)</th>
                      <th>Мин. длительность</th>
                      <th>Фиксированное время</th>
                      <th class="text-end">Действия</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($rules as $rule)
                      <tr>
                        <td>{{ $rule->id }}</td>
                        <td>{{ optional($rule->valid_from)->format('d.m.Y') }}</td>
                        <td>{{ $rule->valid_to ? $rule->valid_to->format('d.m.Y') : '—' }}</td>
                        <td>{{ $rule->price ?? '—' }}</td>
                        <td>{{ $rule->duration_minutes ?? '—' }}</td>
                        <td>{{ $rule->duration_minutes_min ?? '—' }}</td>
                        <td>
                          @if($rule->has_fixed_time)
                            {{ $rule->time_from }}–{{ $rule->time_to }}
                          @else
                            —
                          @endif
                        </td>
                        <td class="text-end">
                          <form method="POST"
                                action="{{ route('service.rules.destroy', $rule->id) }}"
                                onsubmit="return confirm('Удалить это правило?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Удалить</button>
                          </form>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>

        {{-- Форма добавления нового правила --}}
        <div class="card shadow-none mb-4 mt-6">
          <div class="card-body p-4">
            <h5 class="mb-3 text-body-highlight">Добавить новое правило</h5>

            <form class="row g-3 mb-3 needs-validation" novalidate
                  action="{{ route('service.rules.store', $service->id) }}"
                  method="POST">
              @csrf

              <div class="col-md-4">
                <label class="form-label" for="valid_from">Действует с даты</label>
                <input type="date"
                       class="form-control @error('valid_from') is-invalid @enderror"
                       id="valid_from"
                       name="valid_from"
                       value="{{ old('valid_from') }}"
                       required>
                <div class="invalid-feedback">Укажите дату начала действия правила</div>
                @error('valid_from')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label" for="valid_to">По дату (опционально)</label>
                <input type="date"
                       class="form-control @error('valid_to') is-invalid @enderror"
                       id="valid_to"
                       name="valid_to"
                       value="{{ old('valid_to') }}">
                @error('valid_to')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label" for="price">Новая цена (€)</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       class="form-control @error('price') is-invalid @enderror"
                       id="price"
                       name="price"
                       value="{{ old('price') }}"
                       placeholder="Оставьте пустым, если не меняется">
                @error('price')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label" for="duration_minutes">Новая длительность (мин)</label>
                <input type="number"
                       min="1"
                       class="form-control @error('duration_minutes') is-invalid @enderror"
                       id="duration_minutes"
                       name="duration_minutes"
                       value="{{ old('duration_minutes') }}"
                       placeholder="Оставьте пустым, если не меняется">
                @error('duration_minutes')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label" for="duration_minutes_min">Мин. длительность (мин)</label>
                <input type="number"
                       min="1"
                       class="form-control @error('duration_minutes_min') is-invalid @enderror"
                       id="duration_minutes_min"
                       name="duration_minutes_min"
                       value="{{ old('duration_minutes_min') }}"
                       placeholder="Опционально">
                @error('duration_minutes_min')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12 mt-2">
                <div class="form-check mb-2">
                  <input class="form-check-input"
                         type="checkbox"
                         value="1"
                         id="has_fixed_time"
                         name="has_fixed_time"
                         {{ old('has_fixed_time') ? 'checked' : '' }}>
                  <label class="form-check-label" for="has_fixed_time">
                    Использовать фиксированное время для этого правила
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <label class="form-label" for="time_from">Время от</label>
                <input type="time"
                       class="form-control @error('time_from') is-invalid @enderror"
                       id="time_from"
                       name="time_from"
                       value="{{ old('time_from') }}">
                @error('time_from')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-3">
                <label class="form-label" for="time_to">Время до</label>
                <input type="time"
                       class="form-control @error('time_to') is-invalid @enderror"
                       id="time_to"
                       name="time_to"
                       value="{{ old('time_to') }}">
                @error('time_to')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12 gy-6 mt-3">
                <div class="card shadow-none mb-0">
                  <div class="col-auto">
                    <button type="submit" class="btn btn-primary w-100">
                      Сохранить правило
                    </button>
                  </div>
                </div>
              </div>
            </form>

            <p class="mt-3 text-body-tertiary small">
              Подсказка: для твоего случая достаточно добавить одно правило с датой начала, например
              <strong>01.01.2026</strong> и указать там новую цену и длительность. Все записи на даты
              до этой даты будут использовать текущую цену и продолжительность из карточки услуги.
            </p>
          </div>
        </div>
      </div>

      {{-- Правая колонка с действиями, как в main.blade --}}
      <div class="col-12 col-xl-2">
        <div class="position-sticky mt-xl-4 h-[100vh]" style="top: 80px;">
          <h5 class="lh-1">Действия</h5>
          <hr>
          <ul class="nav nav-vertical flex-column doc-nav" data-doc-nav="data-doc-nav">
            <li class="nav-item"> 
              <a class="btn btn-primary w-100 my-2 py-1" href="{{ route('languages.edit', $service->id) }}">
                Перевод
              </a>
            </li>
            <li class="nav-item"> 
              <a class="btn border border-warning-dark text-warning-dark w-100 my-2 py-1"
                 href="{{ route('fixedtime.edit', $service->id) }}">
                Фиксированное время
              </a>
            </li>
            <li class="nav-item"> 
              <a class="btn border border-info text-info w-100 my-2 py-1"
                 href="{{ route('service.rules.edit', $service->id) }}">
                Изменения по датам
              </a>
            </li>
            <li class="nav-item"> 
              <form method="POST" action="/service/{{ $service->id }}/toggle-status" style="display:inline;">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn border border-success text-success w-100 my-2 py-1">
                  Поменять статус
                </button>
              </form>
            </li>
          </ul>
          <hr>
        </div>
      </div>
    </div>

    <x-dashboard-footer />
  </div>
</x-dashboard-layout>
