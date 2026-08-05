@section('title', 'Нерабочее время')

<x-dashboard-layout>
    <div class="content">
        <nav class="mb-3" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Нерабочее время</li>
            </ol>
        </nav>

        @if (session('success'))
            <div class="alert alert-success">
                <span class="fas fa-check-circle me-2"></span>{{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                <span class="fas fa-times-circle me-2"></span>{{ session('error') }}
            </div>
        @endif

        <div class="row mb-4 align-items-center">
            <div class="col">
                <h2 class="mb-0">Нерабочее время</h2>
                <p class="text-muted fs-9 mt-1">Закрытые дни и часы всех мастеров</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse"
                    data-bs-target="#addForm">
                    <span class="fas fa-plus me-1"></span>Добавить
                </button>
            </div>
        </div>

        {{-- Форма добавления --}}
        <div class="collapse mb-4" id="addForm">
            <div class="card card-body bg-light">
                <h6 class="mb-3">Новая запись</h6>
                <form method="POST" action="{{ route('admin.red-days.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Мастер</label>
                            <select class="form-select form-select-sm" name="user_id">
                                <option value="">Общий (для всех)</option>
                                @foreach (\App\Models\User::whereIn('role_id', [1, 2])->orderBy('name')->get() as $master)
                                    <option value="{{ $master->id }}">{{ $master->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Название*</label>
                            <input type="text" class="form-control form-control-sm" name="name"
                                placeholder="Например: Отпуск" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Дата*</label>
                            <input type="date" class="form-control form-control-sm" name="date" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Дата до</label>
                            <input type="date" class="form-control form-control-sm" name="date_to">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Причина</label>
                            <select class="form-select form-select-sm" name="type">
                                @foreach (\App\Models\RedDay::TYPES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="full_day" id="new_full_day"
                                    value="1" checked>
                                <label class="form-check-label" for="new_full_day">Весь день</label>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="repeat" value="1">
                                <label class="form-check-label">Повтор</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-1" id="new_time_block" style="display:none">
                        <div class="col-md-2">
                            <label class="form-label">Начало*</label>
                            <input type="time" class="form-control form-control-sm" name="start_time">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Конец*</label>
                            <input type="time" class="form-control form-control-sm" name="end_time">
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button class="btn btn-sm btn-outline-secondary me-2" type="button" data-bs-toggle="collapse"
                            data-bs-target="#addForm">Отмена</button>
                        <button class="btn btn-sm btn-primary" type="submit">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                {{-- Фильтры --}}
                <form method="GET" action="{{ route('admin.red-days.index') }}" class="row g-3 mb-4">
                    @csrf
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" name="search"
                            value="{{ request('search') }}" placeholder="Поиск по названию...">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="master">
                            <option value="">Все мастера</option>
                            <option value="common" {{ request('master') == 'common' ? 'selected' : '' }}>Общие</option>
                            @foreach (\App\Models\User::whereIn('role_id', [1, 2])->orderBy('name')->get() as $master)
                                <option value="{{ $master->id }}"
                                    {{ request('master') == $master->id ? 'selected' : '' }}>{{ $master->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="type">
                            <option value="">Все типы</option>
                            <option value="full" {{ request('type') == 'full' ? 'selected' : '' }}>Весь день
                            </option>
                            <option value="partial" {{ request('type') == 'partial' ? 'selected' : '' }}>Частичные
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" name="date_from"
                            value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" name="date_to"
                            value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 d-flex gap-1">
                        <button class="btn btn-sm btn-primary" type="submit"><span
                                class="fas fa-search"></span></button>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.red-days.index') }}"><span
                                class="fas fa-times"></span></a>
                    </div>
                </form>

                @php
                    $filtered = $redDays;
                    if (request('search')) {
                        $filtered = $filtered->filter(
                            fn($d) => str_contains(
                                mb_strtolower($d['name'], 'UTF-8'),
                                mb_strtolower(request('search'), 'UTF-8'),
                            ),
                        );
                    }
                    if (request('type') == 'full') {
                        $filtered = $filtered->filter(fn($d) => $d['full_day']);
                    }
                    if (request('type') == 'partial') {
                        $filtered = $filtered->filter(fn($d) => !$d['full_day']);
                    }
                    if (request('master') == 'common') {
                        $filtered = $filtered->filter(fn($d) => !$d['user_id']);
                    } elseif (request('master')) {
                        $filtered = $filtered->filter(fn($d) => $d['user_id'] == request('master'));
                    }
                    if (request('date_from')) {
                        $filtered = $filtered->filter(fn($d) => $d['date'] >= request('date_from'));
                    }
                    if (request('date_to')) {
                        $filtered = $filtered->filter(fn($d) => $d['date'] <= request('date_to'));
                    }
                @endphp

                <div class="table-responsive">
                    <table class="table table-sm fs-9 mb-0">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Дата</th>
                                <th>Время</th>
                                <th>Мастер</th>
                                <th>Повтор</th>
                                <th class="text-end">Действие</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filtered as $day)
                                <tr>
                                    <td>{{ $day['name'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($day['date'])->format('d.m.Y') }}@if($day['date_to'] !== $day['date']) – {{ \Carbon\Carbon::parse($day['date_to'])->format('d.m.Y') }}@endif</td>
                                    <td>
                                        @if ($day['full_day'])
                                            <span class="badge bg-secondary fs-10">Весь день</span>
                                        @else
                                            {{ $day['start_time'] ? substr($day['start_time'], 0, 5) : '' }} –
                                            {{ $day['end_time'] ? substr($day['end_time'], 0, 5) : '' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($day['user_id'])
                                            <span
                                                class="badge badge-phoenix badge-phoenix-primary fs-10">{{ $day['master_name'] }}</span>
                                        @else
                                            <span
                                                class="badge badge-phoenix badge-phoenix-secondary fs-10">Общий</span>
                                        @endif
                                    </td>
                                    <td>{{ $day['repeat'] ? '✅' : '❌' }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary me-1" type="button"
                                            data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-id="{{ $day['id'] }}" data-name="{{ $day['name'] }}"
                                            data-date="{{ $day['date'] }}"
                                            data-date-to="{{ $day['date_to'] }}" data-type="{{ $day['type'] }}"
                                            data-start="{{ $day['start_time'] ? substr($day['start_time'], 0, 5) : '' }}"
                                            data-end="{{ $day['end_time'] ? substr($day['end_time'], 0, 5) : '' }}"
                                            data-repeat="{{ $day['repeat'] ? 1 : 0 }}"
                                            data-fullday="{{ $day['full_day'] ? 1 : 0 }}">
                                            <span class="fas fa-edit"></span>
                                        </button>
                                        <form method="POST"
                                            action="{{ route('admin.red-days.destroy', $day['id']) }}"
                                            style="display:inline" onsubmit="return confirm('Удалить запись?')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <span class="fas fa-trash"></span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Нет записей</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="text-muted fs-10 mt-3">Показано {{ $filtered->count() }} из {{ $redDays->count() }} записей
                </p>
            </div>
        </div>

        {{-- Модалка редактирования --}}
        <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Редактировать</h5>
                        <button class="btn btn-close p-1" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" id="editForm" action="">
                        @csrf
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Название*</label>
                                    <input type="text" class="form-control" name="name" id="edit_name"
                                        required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Дата*</label>
                                    <input type="date" class="form-control" name="date" id="edit_date"
                                        required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Дата до</label>
                                    <input type="date" class="form-control" name="date_to" id="edit_date_to">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Причина</label>
                                    <select class="form-select" name="type" id="edit_type">
                                        @foreach (\App\Models\RedDay::TYPES as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="full_day"
                                            id="edit_fullday" value="1">
                                        <label class="form-check-label" for="edit_fullday">Весь день</label>
                                    </div>
                                </div>
                                <div class="col-6" id="edit_time1">
                                    <label class="form-label">Начало*</label>
                                    <input type="time" class="form-control" name="start_time" id="edit_start">
                                </div>
                                <div class="col-6" id="edit_time2">
                                    <label class="form-label">Конец*</label>
                                    <input type="time" class="form-control" name="end_time" id="edit_end">
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="repeat"
                                            id="edit_repeat" value="1">
                                        <label class="form-check-label" for="edit_repeat">Повторять ежегодно</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-outline-secondary" type="button"
                                data-bs-dismiss="modal">Отмена</button>
                            <button class="btn btn-primary" type="submit">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <x-dashboard-footer />
    </div>

    @push('scripts')
        <script>
            // Чекбокс весь день — форма добавления
            document.getElementById('new_full_day').addEventListener('change', function() {
                document.getElementById('new_time_block').style.display = this.checked ? 'none' : 'flex';
            });

            // Модалка редактирования
            document.getElementById('editModal').addEventListener('show.bs.modal', function(event) {
                const btn = event.relatedTarget;
                const id = btn.getAttribute('data-id');
                const fullday = btn.getAttribute('data-fullday') == '1';

                document.getElementById('edit_name').value = btn.getAttribute('data-name');
                document.getElementById('edit_date').value = btn.getAttribute('data-date');
                document.getElementById('edit_date_to').value = btn.getAttribute('data-date-to');
                document.getElementById('edit_type').value = btn.getAttribute('data-type');
                document.getElementById('edit_start').value = btn.getAttribute('data-start');
                document.getElementById('edit_end').value = btn.getAttribute('data-end');
                document.getElementById('edit_repeat').checked = btn.getAttribute('data-repeat') == '1';
                document.getElementById('edit_fullday').checked = fullday;
                document.getElementById('edit_time1').style.display = fullday ? 'none' : 'block';
                document.getElementById('edit_time2').style.display = fullday ? 'none' : 'block';
                document.getElementById('editForm').action = '/admin/red-days/' + id + '/update';
            });

            // Чекбокс весь день — модалка
            document.getElementById('edit_fullday').addEventListener('change', function() {
                document.getElementById('edit_time1').style.display = this.checked ? 'none' : 'block';
                document.getElementById('edit_time2').style.display = this.checked ? 'none' : 'block';
            });
        </script>
    @endpush
</x-dashboard-layout>
