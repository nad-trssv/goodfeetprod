@section('title', __('admin_service_extra.date_rules'))
<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('service.index') }}">{{ __('admin_services.title') }}</a></li>
        <li class="breadcrumb-item">
          <a href="{{ route('service.show', $service->id) }}">{{ __('admin_services.edit_service') }}</a>
        </li>
        <li class="breadcrumb-item active">{{ __('admin_services.date_rules') }}</li>
      </ol>
    </nav>

    @if (session('success'))
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          Swal.fire({
            title: @json(__('admin_service_extra.success')),
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
            title: @json(__('admin_service_extra.error')),
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonText: 'OK'
          });
        });
      </script>
    @endif

    <h2 class="mb-4">{{ __('admin_services.date_rules_title',['name'=>$service->name]) }}</h2>

    <div class="row g-4">
      {{-- Левая основная колонка --}}
      <div class="col-12 col-xl-10 order-1 order-xl-0">

        {{-- Текущие правила --}}
        <div class="card shadow-none mb-4 mt-6">
          <div class="card-body p-4">
            <h5 class="mb-3 text-body-highlight">{{ __('admin_services.current_rules') }}</h5>

            @if($rules->isEmpty())
              <p class="text-body-tertiary mb-0">
                {{ __('admin_service_extra.no_rules') }}
              </p>
            @else
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>{{ __('admin_services.valid_from') }}</th>
                      <th>{{ __('admin_services.valid_to') }}</th>
                      <th>{{ __('admin_services.price') }} (€)</th>
                      <th>{{ __('admin_services.duration') }} (min)</th>
                      <th>{{ __('admin_services.minimum_duration') }}</th>
                      <th>{{ __('admin_services.fixed_time') }}</th>
                      <th class="text-end">{{ __('admin_services.actions') }}</th>
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
                                onsubmit='return confirm(@js(__('admin_services.delete_rule')));'>
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('admin_services.delete') }}</button>
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
            <h5 class="mb-3 text-body-highlight">{{ __('admin_services.add_rule') }}</h5>

            <form class="row g-3 mb-3 needs-validation" novalidate
                  action="{{ route('service.rules.store', $service->id) }}"
                  method="POST">
              @csrf

              <div class="col-md-4">
                <label class="form-label" for="valid_from">{{ __('admin_services.valid_from_date') }}</label>
                <input type="date"
                       class="form-control @error('valid_from') is-invalid @enderror"
                       id="valid_from"
                       name="valid_from"
                       value="{{ old('valid_from') }}"
                       required>
                <div class="invalid-feedback">{{ __('admin_services.valid_from_required') }}</div>
                @error('valid_from')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label" for="valid_to">{{ __('admin_services.valid_to_optional') }}</label>
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
                <label class="form-label" for="price">{{ __('admin_services.new_price') }}</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       class="form-control @error('price') is-invalid @enderror"
                       id="price"
                       name="price"
                       value="{{ old('price') }}"
                       placeholder="{{ __('admin_services.unchanged_placeholder') }}">
                @error('price')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label" for="duration_minutes">{{ __('admin_services.new_duration') }}</label>
                <input type="number"
                       min="1"
                       class="form-control @error('duration_minutes') is-invalid @enderror"
                       id="duration_minutes"
                       name="duration_minutes"
                       value="{{ old('duration_minutes') }}"
                       placeholder="{{ __('admin_services.unchanged_placeholder') }}">
                @error('duration_minutes')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label" for="duration_minutes_min">{{ __('admin_services.minimum_duration_minutes') }}</label>
                <input type="number"
                       min="1"
                       class="form-control @error('duration_minutes_min') is-invalid @enderror"
                       id="duration_minutes_min"
                       name="duration_minutes_min"
                       value="{{ old('duration_minutes_min') }}"
                       placeholder="{{ __('admin_services.optional') }}">
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
                    {{ __('admin_service_extra.use_fixed_for_rule') }}
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <label class="form-label" for="time_from">{{ __('admin_services.time_from') }}</label>
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
                <label class="form-label" for="time_to">{{ __('admin_services.time_to') }}</label>
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
                      {{ __('admin_service_extra.save_rule') }}
                    </button>
                  </div>
                </div>
              </div>
            </form>

            <p class="mt-3 text-body-tertiary small">
              {{ __('admin_service_extra.rule_hint') }}
            </p>
          </div>
        </div>
      </div>

      {{-- Правая колонка с действиями, как в main.blade --}}
      <div class="col-12 col-xl-2">
        <div class="position-sticky mt-xl-4 h-[100vh]" style="top: 80px;">
          <h5 class="lh-1">{{ __('admin_services.actions') }}</h5>
          <hr>
          <ul class="nav nav-vertical flex-column doc-nav" data-doc-nav="data-doc-nav">
            <li class="nav-item"> 
              <a class="btn btn-primary w-100 my-2 py-1" href="{{ route('languages.edit', $service->id) }}">
                {{ __('admin_service_extra.translation') }}
              </a>
            </li>
            <li class="nav-item"> 
              <a class="btn border border-warning-dark text-warning-dark w-100 my-2 py-1"
                 href="{{ route('fixedtime.edit', $service->id) }}">
                {{ __('admin_service_extra.fixed_time') }}
              </a>
            </li>
            <li class="nav-item"> 
              <a class="btn border border-info text-info w-100 my-2 py-1"
                 href="{{ route('service.rules.edit', $service->id) }}">
                {{ __('admin_service_extra.date_rules') }}
              </a>
            </li>
            <li class="nav-item"> 
              <form method="POST" action="/service/{{ $service->id }}/toggle-status" style="display:inline;">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn border border-success text-success w-100 my-2 py-1">
                  {{ __('admin_service_extra.change_status') }}
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
