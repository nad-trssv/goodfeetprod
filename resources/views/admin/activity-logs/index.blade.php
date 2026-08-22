@section('title', __('admin_activity.title'))

<x-dashboard-layout>
  <div class="content">

    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">
          {{ __('admin_activity.title') }}
        </li>
      </ol>
    </nav>

    <div class="row mb-4">
      <div class="col">
        <h2 class="mb-1">{{ __('admin_activity.title') }}</h2>

        <p class="text-muted mb-0">
          {{ __('admin_activity.hint') }}
        </p>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">

        <form
          method="GET"
          action="{{ route('admin.activity-logs.index') }}"
          class="row g-3"
        >
          <div class="col-12 col-xl-3">
            <label class="form-label" for="log_search">
              {{ __('admin_activity.search') }}
            </label>

            <input
              type="search"
              name="search"
              id="log_search"
              class="form-control"
              value="{{ $search }}"
              placeholder="{{ __('admin_activity.search_placeholder') }}"
            >
          </div>

          <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label" for="log_actor">
              {{ __('admin_activity.employee') }}
            </label>

            <select
              name="actor_id"
              id="log_actor"
              class="form-select"
            >
              <option value="">{{ __('admin_activity.all_employees') }}</option>

              @foreach ($actors as $actor)
                <option
                  value="{{ $actor->id }}"
                  @selected($actorId === $actor->id)
                >
                  {{ $actor->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label" for="log_module">
              {{ __('admin_activity.module') }}
            </label>

            <select
              name="module"
              id="log_module"
              class="form-select"
            >
              <option value="">{{ __('admin_activity.all_modules') }}</option>

              @foreach ($modules as $moduleOption)
                <option
                  value="{{ $moduleOption }}"
                  @selected($module === $moduleOption)
                >
                  {{ $moduleOption }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-6 col-xl-2">
            <label class="form-label" for="date_from">
              {{ __('admin_activity.date_from') }}
            </label>

            <input
              type="date"
              name="date_from"
              id="date_from"
              class="form-control"
              value="{{ $dateFrom }}"
            >
          </div>

          <div class="col-6 col-xl-2">
            <label class="form-label" for="date_to">
              {{ __('admin_activity.date_to') }}
            </label>

            <input
              type="date"
              name="date_to"
              id="date_to"
              class="form-control"
              value="{{ $dateTo }}"
            >
          </div>

          <div class="col-12 col-xl-1 d-flex align-items-end">
            <button
              type="submit"
              class="btn btn-primary w-100"
            >
              {{ __('admin_activity.find') }}
            </button>
          </div>

          @if (
              $search !== ''
              || $module !== ''
              || $actorId > 0
              || $dateFrom
              || $dateTo
          )
            <div class="col-12">
              <a
                href="{{ route('admin.activity-logs.index') }}"
                class="btn btn-outline-secondary btn-sm"
              >
                {{ __('admin_activity.reset') }}
              </a>
            </div>
          @endif
        </form>

      </div>
    </div>

    <div class="card">
    <div class="card-body p-0">

        <div class="table-responsive">
        <table class="table table-hover activity-log-table mb-0">
            <thead>
            <tr>
                <th class="activity-log-id-column ps-3">
                ID
                </th>

                <th class="activity-log-type-column">
                {{ __('admin_activity.type') }}
                </th>

                <th>
                {{ __('admin_activity.employee') }}
                </th>

                <th>
                {{ __('admin_activity.action') }}
                </th>

                <th>
                {{ __('admin_activity.module') }}
                </th>

                <th>
                {{ __('admin_activity.object') }}
                </th>

                <th>
                {{ __('admin_activity.date') }}
                </th>
            </tr>
            </thead>

            <tbody>
            @forelse ($logs as $log)
                <tr
                class="activity-log-row
                        activity-log-row--type-{{ $log->type }}"
                >
                <td class="activity-log-id-column ps-3">
                    {{ $log->id }}
                </td>

                <td class="activity-log-type-column">
                    <span
                    class="activity-log-type {{ $log->typeCssClass() }}"
                    title="{{ __('admin_activity.type_title',['type'=>$log->typeLabel()]) }}"
                    >

                    <span class="activity-log-type__text">
                        {{ $log->typeLabel() }}
                    </span>
                    </span>
                </td>

                <td class="fw-semibold">
                    {{ $log->actor_name ?? __('admin_activity.system') }}
                </td>

                <td>
                    {{ $log->localizedMessage() }}
                </td>

                <td>
                    {{ $log->module }}
                </td>

                <td class="text-nowrap">
                    {{ $log->objectLabel() }}
                </td>

                <td class="text-nowrap">
                    {{ $log->created_at?->format('d.m.Y H:i:s') }}
                </td>
                </tr>
            @empty
                <tr>
                <td
                    colspan="7"
                    class="text-center text-muted py-5"
                >
                    {{ __('admin_activity.empty') }}
                </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>

    </div>

    @if ($logs->hasPages())
        <div class="card-footer">
        {{ $logs->links() }}
        </div>
    @endif
    </div>

    <x-dashboard-footer />
  </div>
</x-dashboard-layout>
