@section('title', __('admin_appointments.all'))

<x-dashboard-layout>
    <div class="content">
        <nav class="mb-3" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">{{ __('admin_appointments.all') }}</li>
            </ol>
        </nav>

        <div class="row mb-4 align-items-center">
            <div class="col">
                <h2 class="mb-0">{{ __('admin_appointments.all') }}
                    <span class="fw-normal text-body-tertiary ms-2 fs-5">({{ $appointments->total() }})</span>
                </h2>
            </div>
        </div>

        {{-- Фильтры --}}
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.appointments.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" name="search"
                            value="{{ request('search') }}" placeholder="{{ __('admin_appointments.search') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="master_id">
                            <option value="">{{ __('admin_appointments.all_employees') }}</option>
                            @foreach ($masters as $master)
                                <option value="{{ $master->id }}"
                                    {{ request('master_id') == $master->id ? 'selected' : '' }}>
                                    {{ $master->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="service_id">
                            <option value="">{{ __('admin_appointments.all_services') }}</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}"
                                    {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="status">
                            <option value="">{{ __('appointment_statuses.all') }}</option>
                            @foreach(\App\Models\Appointments::STATUSES as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('appointment_statuses.'.$status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" name="date_from"
                            value="{{ request('date_from') }}" placeholder="{{ __('admin_appointments.from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" name="date_to"
                            value="{{ request('date_to') }}" placeholder="{{ __('admin_appointments.to') }}">
                    </div>
                    <div class="col-md-1 d-flex gap-1">
                        <button class="btn btn-sm btn-primary" type="submit">
                            <span class="fas fa-search"></span>
                        </button>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.appointments.index') }}">
                            <span class="fas fa-times"></span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm fs-9 mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>{{ __('admin_appointments.client') }}</th>
                                <th>{{ __('admin_appointments.service') }}</th>
                                <th>{{ __('admin_appointments.employee') }}</th>
                                <th>{{ __('admin_appointments.date_time') }}</th>
                                <th>{{ __('admin_appointments.price') }}</th>
                                <th>{{ __('appointment_statuses.status') }}</th>
                                <th>{{ __('admin_appointments.notes') }}</th>
                                <th class="text-end pe-3">{{ __('admin_appointments.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $apt)
                                <tr class="border-bottom">
                                    <td class="ps-3 text-muted fs-10">{{ $apt->id }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $apt->client_name }} {{ $apt->client_lastname }}
                                        </div>
                                        <a href="tel:{{ $apt->client_phone }}"
                                            class="text-muted fs-10">{{ $apt->client_phone }}</a>
                                        @if ($apt->client_email)
                                            <div class="text-muted fs-10">{{ $apt->client_email }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="rounded-circle"
                                                style="width:8px;height:8px;background:{{ $apt->service->eventColor ?? '#007bff' }};display:inline-block;flex-shrink:0"></span>
                                            <span>{{ $apt->service->name }}</span>
                                        </div>
                                        <div class="text-muted fs-10">{{ __('admin_appointments.minutes',['count'=>$apt->service->duration_minutes]) }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $apt->user->name }}</div>
                                        <div class="text-muted fs-10">{{ $apt->user->username }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ \Carbon\Carbon::parse($apt->appointment_start)->format('d.m.Y') }}</div>
                                        <div class="text-muted fs-10">
                                            {{ \Carbon\Carbon::parse($apt->appointment_start)->format('H:i') }}
                                            – {{ \Carbon\Carbon::parse($apt->appointment_end)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($apt->service->price_can_change)
                                            <span class="text-muted fs-10">{{ __('admin_appointments.from_price') }} </span>
                                        @endif
                                        <span class="fw-semibold">{{ $apt->price }} €</span>
                                    </td>
                                    <td>@include('admin.calendar._status-actions',['appointment'=>$apt])</td>
                                    <td>
                                        @if ($apt->description)
                                            <span class="text-muted fs-10"
                                                style="max-width:150px;display:block;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"
                                                title="{{ $apt->description }}">
                                                {{ $apt->description }}
                                            </span>
                                        @else
                                            <span class="text-muted fs-10">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('calendar.show', $apt->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <span class="fas fa-eye"></span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">{{ __('admin_appointments.not_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Пагинация --}}
                @if ($appointments->hasPages())
                    <div class="d-flex justify-content-between align-items-center px-3 py-3">
                        <div class="text-muted fs-9">
                            {{ __('admin_appointments.shown', ['from' => $appointments->firstItem(), 'to' => $appointments->lastItem(), 'total' => $appointments->total()]) }}
                        </div>
                        {{ $appointments->links() }}
                    </div>
                @endif
            </div>
        </div>

        <x-dashboard-footer />
    </div>
    @push('scripts')@include('admin.calendar._status-script')@endpush
</x-dashboard-layout>
