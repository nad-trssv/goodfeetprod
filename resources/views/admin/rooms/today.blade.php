@section('title', 'Загрузка кабинетов')

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Кабинеты</a></li>
        <li class="breadcrumb-item active">Загрузка</li>
      </ol>
    </nav>

    <div class="row mb-4 align-items-center">
      <div class="col">
        <h2 class="mb-0">Загрузка кабинетов</h2>
        <p class="text-muted fs-9 mt-1">{{ $today->locale('ru')->isoFormat('dddd, D MMMM YYYY') }}</p>
      </div>
    </div>

    {{-- Навигация по дням --}}
    <div class="card mb-4">
      <div class="card-body py-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <a href="{{ route('admin.rooms.today') }}"
             class="btn btn-sm {{ $today->isToday() ? 'btn-primary' : 'btn-outline-primary' }}">
            Сегодня
          </a>
          <a href="{{ route('admin.rooms.day', \Carbon\Carbon::now()->addDay()->format('Y-m-d')) }}"
             class="btn btn-sm {{ $today->isTomorrow() ? 'btn-primary' : 'btn-outline-secondary' }}">
            Завтра
          </a>
          <a href="{{ route('admin.rooms.day', \Carbon\Carbon::now()->subDay()->format('Y-m-d')) }}"
             class="btn btn-sm {{ $today->isYesterday() ? 'btn-primary' : 'btn-outline-secondary' }}">
            Вчера
          </a>
          <div class="ms-auto d-flex align-items-center gap-2">
            <a href="{{ route('admin.rooms.day', $today->copy()->subDay()->format('Y-m-d')) }}"
               class="btn btn-sm btn-outline-secondary">
              <span class="fas fa-chevron-left"></span>
            </a>
            <input type="date" class="form-control form-control-sm"
                   value="{{ $today->format('Y-m-d') }}"
                   onchange="window.location='/admin/rooms/day/'+this.value"
                   style="width:150px">
            <a href="{{ route('admin.rooms.day', $today->copy()->addDay()->format('Y-m-d')) }}"
               class="btn btn-sm btn-outline-secondary">
              <span class="fas fa-chevron-right"></span>
            </a>
          </div>
        </div>
      </div>
    </div>

    @if($rooms->isEmpty())
      <div class="card">
        <div class="card-body text-center text-muted py-5">
          <span class="fas fa-door-open fs-3 mb-3 d-block"></span>
          Нет активных кабинетов. <a href="{{ route('admin.rooms.index') }}">Добавить кабинет</a>
        </div>
      </div>
    @else
    <div class="row g-4">
      @foreach($rooms as $room)
      <div class="col-12 col-md-6 col-xl-4">
        <div class="card h-100">
          <div class="card-header d-flex align-items-center justify-content-between py-2">
            <div>
              <h6 class="mb-0 fw-semibold">{{ $room['name'] }}</h6>
              @if($room['description'])
                <span class="text-muted fs-10">{{ $room['description'] }}</span>
              @endif
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-light text-dark fs-10">
                <span class="fas fa-users me-1"></span>{{ $room['capacity'] }} мест
              </span>
              @if($room['appointments_count'] > 0)
                <span class="badge bg-primary fs-10">{{ $room['appointments_count'] }} записей</span>
              @endif
            </div>
          </div>

          <div class="card-body p-0">
            @if(empty($room['slot_groups']))
              <div class="text-center text-muted py-4 fs-9">
                <span class="fas fa-calendar-check me-1"></span>Записей нет
              </div>
            @else
              @php $now = \Carbon\Carbon::now(); @endphp
              <div style="max-height:400px;overflow-y:auto;">
                @foreach($room['slot_groups'] as $slot)
                @php
                  $slotStart = \Carbon\Carbon::parse($today->format('Y-m-d') . ' ' . $slot['start']);
                  $slotEnd = \Carbon\Carbon::parse($today->format('Y-m-d') . ' ' . $slot['end']);
                  $isActive = $today->isToday() && $now->between($slotStart, $slotEnd);
                  $isPast = $today->isToday() && $now->gt($slotEnd);
                @endphp
                <div class="border-bottom {{ $isActive ? 'bg-warning bg-opacity-10' : ($isPast ? 'opacity-50' : '') }}">
                  {{-- Заголовок слота --}}
                  <div class="d-flex align-items-center px-3 py-2 {{ $slot['is_full'] ? 'bg-danger bg-opacity-10' : '' }}">
                    <div class="me-2 text-center" style="min-width:80px">
                      <span class="fs-10 fw-semibold {{ $isActive ? 'text-warning' : 'text-muted' }}">
                        {{ $slot['start'] }} – {{ $slot['end'] }}
                      </span>
                    </div>
                    <div class="flex-1">
                      <div class="d-flex align-items-center gap-1">
                        @if($slot['is_full'])
                          <span class="badge bg-danger fs-10">Занято {{ count($slot['appointments']) }}/{{ $room['capacity'] }}</span>
                        @else
                          <span class="badge bg-success fs-10">{{ count($slot['appointments']) }}/{{ $room['capacity'] }}</span>
                        @endif
                        @if($isActive)
                          <span class="badge bg-warning text-dark fs-10">Сейчас</span>
                        @endif
                      </div>
                    </div>
                  </div>

                  {{-- Записи в этом слоте --}}
                  @foreach($slot['appointments'] as $apt)
                  <div class="d-flex align-items-start px-3 py-1 ps-4 bg-light bg-opacity-50">
                    <div class="me-2">
                      <span class="rounded-circle d-inline-block"
                        style="width:8px;height:8px;background:{{ $apt->service->eventColor ?? '#007bff' }}"></span>
                    </div>
                    <div class="flex-1">
                      <div class="fs-10 fw-semibold">{{ $apt->client_name }} {{ $apt->client_lastname }}</div>
                      <div class="fs-10 text-muted">
                        {{ $apt->service->name }}
                        <span class="ms-1 text-primary">· {{ $apt->user->name }}</span>
                      </div>
                      <a href="{{ route('calendar.show', $apt->id) }}" class="fs-10 text-primary">Подробнее →</a>
                    </div>
                  </div>
                  @endforeach
                </div>
                @endforeach
              </div>
            @endif
          </div>

          <div class="card-footer py-2">
            <a href="{{ route('admin.rooms.index') }}" class="btn btn-sm btn-outline-secondary w-100 fs-10">
              <span class="fas fa-cog me-1"></span>Настройки кабинета
            </a>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @endif

    <x-dashboard-footer />
  </div>
</x-dashboard-layout>