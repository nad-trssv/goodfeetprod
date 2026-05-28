@section('title', 'Записи мастеров')

<x-dashboard-layout>
    <div class="content">
        <nav class="mb-3" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Записи мастеров</li>
            </ol>
        </nav>

        <div class="row mb-4 align-items-center">
            <div class="col">
                <h2 class="mb-0">Нагрузка мастеров</h2>
                <p class="text-muted fs-9 mt-1">
                    {{ $today->locale('ru')->isoFormat('dddd, D MMMM YYYY') }}
                </p>
            </div>
        </div>

        {{-- Навигация по дням --}}
        <div class="card mb-4">
            <div class="card-body py-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.masters.today') }}"
                        class="btn btn-sm {{ $today->isToday() ? 'btn-primary' : 'btn-outline-primary' }}">
                        Сегодня
                    </a>
                    <a href="{{ route('admin.masters.day', \Carbon\Carbon::now()->addDay()->format('Y-m-d')) }}"
                        class="btn btn-sm {{ $today->isTomorrow() ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Завтра
                    </a>
                    <a href="{{ route('admin.masters.day', \Carbon\Carbon::now()->subDay()->format('Y-m-d')) }}"
                        class="btn btn-sm {{ $today->isYesterday() ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Вчера
                    </a>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <a href="{{ route('admin.masters.day', $today->copy()->subDay()->format('Y-m-d')) }}"
                            class="btn btn-sm btn-outline-secondary">
                            <span class="fas fa-chevron-left"></span>
                        </a>
                        <form method="GET" action="" id="dateForm">
                            <input type="date" class="form-control form-control-sm"
                                value="{{ $today->format('Y-m-d') }}"
                                onchange="window.location='/admin/masters/day/'+this.value" style="width:150px">
                        </form>
                        <a href="{{ route('admin.masters.day', $today->copy()->addDay()->format('Y-m-d')) }}"
                            class="btn btn-sm btn-outline-secondary">
                            <span class="fas fa-chevron-right"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @foreach ($masters as $master)
                <div class="col-12 col-md-6 col-xl-4">
                    <div
                        class="card h-100 {{ $master['status'] === 'closed' ? 'border-danger' : ($master['status'] === 'dayoff' ? 'border-secondary' : 'border-success') }} border">
                        <div class="card-header d-flex align-items-center justify-content-between py-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="position-relative">
                                    <img src="{{ $master['photo'] }}" class="rounded-circle"
                                        style="width:36px;height:36px;object-fit:cover;" alt="{{ $master['name'] }}">
                                    @php
                                        $isOnline =
                                            $master['last_active'] &&
                                            \Carbon\Carbon::parse($master['last_active'])->diffInMinutes(now()) < 5;
                                    @endphp
                                    <span class="position-absolute bottom-0 end-0 rounded-circle border border-white"
                                        style="width:10px;height:10px;background:{{ $isOnline ? '#28a745' : '#adb5bd' }}"></span>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold fs-9">{{ $master['name'] }}</h6>
                                    <span
                                        class="fs-10 text-muted">{{ $isOnline ? 'Online' : ($master['last_active'] ? \Carbon\Carbon::parse($master['last_active'])->diffForHumans() : 'Не активен') }}</span>
                                </div>
                            </div>
                            <div>
                                @if ($master['status'] === 'closed')
                                    <span class="badge bg-danger fs-10">Недоступен</span>
                                @elseif($master['status'] === 'dayoff')
                                    <span class="badge bg-secondary fs-10">Выходной</span>
                                @else
                                    <span class="badge bg-success fs-10">Работает</span>
                                    @if ($master['work_start'])
                                        <span
                                            class="badge bg-light text-dark fs-10">{{ $master['work_start'] }}–{{ $master['work_end'] }}</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="card-body p-0">
                            @if ($master['status'] === 'closed')
                                <div class="text-center text-danger py-4 fs-9">
                                    <span class="fas fa-ban me-1"></span>День недоступен
                                </div>
                            @elseif($master['status'] === 'dayoff')
                                <div class="text-center text-muted py-4 fs-9">
                                    <span class="fas fa-couch me-1"></span>Нерабочий день
                                </div>
                            @else
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $todayStr = $today->format('Y-m-d');
                                    $items = collect();

                                    foreach ($master['appointments'] as $apt) {
                                        $items->push([
                                            'type' => 'appointment',
                                            'start' => \Carbon\Carbon::parse($apt->appointment_start),
                                            'end' => \Carbon\Carbon::parse($apt->appointment_end),
                                            'data' => $apt,
                                        ]);
                                    }

                                    if ($master['lunch_start'] && $master['lunch_end']) {
                                        $items->push([
                                            'type' => 'lunch',
                                            'start' => \Carbon\Carbon::parse($todayStr . ' ' . $master['lunch_start']),
                                            'end' => \Carbon\Carbon::parse($todayStr . ' ' . $master['lunch_end']),
                                            'data' => null,
                                        ]);
                                    }

                                    foreach ($master['closed_windows'] as $cw) {
                                        $items->push([
                                            'type' => 'closed',
                                            'start' => \Carbon\Carbon::parse(
                                                $todayStr .
                                                    ' ' .
                                                    ($cw->start_time ? substr($cw->start_time, 0, 5) : '00:00'),
                                            ),
                                            'end' => \Carbon\Carbon::parse(
                                                $todayStr .
                                                    ' ' .
                                                    ($cw->end_time ? substr($cw->end_time, 0, 5) : '00:00'),
                                            ),
                                            'data' => $cw,
                                        ]);
                                    }

                                    $items = $items->sortBy('start')->values();
                                @endphp

                                @if ($items->isEmpty())
                                    <div class="text-center text-muted py-4 fs-9">
                                        <span class="fas fa-calendar-check me-1"></span>Записей нет
                                    </div>
                                @else
                                    <div class="timeline-list" style="max-height:320px;overflow-y:auto;">
                                        @foreach ($items as $item)
                                            @php
                                                $isActive =
                                                    $today->isToday() && $now->between($item['start'], $item['end']);
                                                $isPast = $today->isToday() && $now->gt($item['end']);
                                            @endphp
                                            <div
                                                class="d-flex align-items-start px-3 py-2 border-bottom {{ $isActive ? 'bg-warning bg-opacity-10' : ($isPast ? 'opacity-50' : '') }}">
                                                <div class="me-2 text-center" style="min-width:45px">
                                                    <span
                                                        class="fs-10 fw-semibold {{ $isActive ? 'text-warning' : 'text-muted' }}">{{ $item['start']->format('H:i') }}</span>
                                                    <div class="fs-10 text-muted">{{ $item['end']->format('H:i') }}
                                                    </div>
                                                </div>
                                                <div class="flex-1">
                                                    @if ($item['type'] === 'appointment')
                                                        @php $apt = $item['data']; @endphp
                                                        <div class="d-flex align-items-center gap-1 flex-wrap">
                                                            <span class="rounded-circle me-1"
                                                                style="width:8px;height:8px;background:{{ $apt->service->eventColor ?? '#007bff' }};display:inline-block;flex-shrink:0"></span>
                                                            <span
                                                                class="fs-10 fw-semibold">{{ $apt->service->name }}</span>
                                                            @if ($isActive)
                                                                <span
                                                                    class="badge bg-warning text-dark fs-10">Сейчас</span>
                                                            @endif
                                                        </div>
                                                        <div class="fs-10 text-muted">{{ $apt->client_name }}
                                                            {{ $apt->client_lastname }}</div>
                                                        <a href="{{ route('calendar.show', $apt->id) }}"
                                                            class="fs-10 text-primary">Подробнее →</a>
                                                    @elseif($item['type'] === 'lunch')
                                                        <div class="d-flex align-items-center gap-1">
                                                            <span
                                                                class="fas fa-utensils text-warning me-1 fs-10"></span>
                                                            <span class="fs-10 fw-semibold text-warning">Обед</span>
                                                            @if ($isActive)
                                                                <span
                                                                    class="badge bg-warning text-dark fs-10 ms-1">Сейчас</span>
                                                            @endif
                                                        </div>
                                                    @elseif($item['type'] === 'closed')
                                                        <div class="d-flex align-items-center gap-1">
                                                            <span class="fas fa-lock text-danger me-1 fs-10"></span>
                                                            <span
                                                                class="fs-10 fw-semibold text-danger">{{ $item['data']->name }}</span>
                                                            @if ($isActive)
                                                                <span class="badge bg-danger fs-10 ms-1">Сейчас</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="card-footer py-2 d-flex gap-2">
                            <a href="{{ route('master.calendar', $master['id']) }}"
                                class="btn btn-sm btn-outline-primary flex-1 fs-10">
                                <span class="fas fa-calendar me-1"></span>Календарь
                            </a>
                            <a href="{{ route('master.calendar.list', $master['id']) }}"
                                class="btn btn-sm btn-outline-secondary flex-1 fs-10">
                                <span class="fas fa-list me-1"></span>Записи
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <x-dashboard-footer />
    </div>
</x-dashboard-layout>
