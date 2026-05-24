@section('title', 'Нерабочее время')

<x-dashboard-layout>
    <div class="content">
        <nav class="mb-3" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Нерабочее время</li>
            </ol>
        </nav>

        <div id="successAlert" class="alert alert-success d-none" role="alert">
            <span class="fas fa-check-circle me-2"></span><span id="successText">Сохранено!</span>
        </div>

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
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Мастер*</label>
                        <select class="form-select form-select-sm" id="new_master_id">
                            <option value="">Общий (для всех)</option>
                            @foreach (\App\Models\User::whereIn('role_id', [1, 2])->orderBy('name')->get() as $master)
                                <option value="{{ $master->id }}">{{ $master->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Название*</label>
                        <input type="text" class="form-control form-control-sm" id="new_name"
                            placeholder="Например: Отпуск">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Дата*</label>
                        <input type="date" class="form-control form-control-sm" id="new_date">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" id="new_full_day" checked>
                            <label class="form-check-label" for="new_full_day">Весь день</label>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="new_repeat">
                            <label class="form-check-label" for="new_repeat">Повтор</label>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-1" id="new_time_block" style="display:none !important">
                    <div class="col-md-2">
                        <label class="form-label">Начало</label>
                        <input type="time" class="form-control form-control-sm" id="new_start_time">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Конец</label>
                        <input type="time" class="form-control form-control-sm" id="new_end_time">
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button class="btn btn-sm btn-outline-secondary me-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#addForm">Отмена</button>
                    <button class="btn btn-sm btn-primary" id="saveNewRedDay">Сохранить</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                {{-- Фильтры --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" id="filterSearch"
                            placeholder="Поиск по названию...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filterMaster">
                            <option value="">Все мастера</option>
                            <option value="Общий">Общие</option>
                            @foreach (\App\Models\User::whereIn('role_id', [1, 2])->orderBy('name')->get() as $master)
                                <option value="{{ $master->name }}">{{ $master->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" id="filterType">
                            <option value="">Все типы</option>
                            <option value="full">Весь день</option>
                            <option value="partial">Частичные</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" id="filterDateFrom"
                            placeholder="От">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" id="filterDateTo"
                            placeholder="До">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" id="resetFilters">
                            <span class="fas fa-times me-1"></span>Сбросить
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm fs-9 mb-0" id="allRedDaysTable">
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
                        <tbody id="allRedDaysBody">
                            @foreach ($redDays as $day)
                                <tr class="rd-row" id="all_rd_row_{{ $day['id'] }}"
                                    data-name="{{ mb_strtolower($day['name'], 'UTF-8') }}"
                                    data-master="{{ $day['master_name'] }}"
                                    data-type="{{ $day['full_day'] ? 'full' : 'partial' }}"
                                    data-date="{{ $day['date'] }}">
                                    <td class="rd-name">{{ $day['name'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($day['date'])->format('d.m.Y') }}</td>
                                    <td>
                                        @if ($day['full_day'])
                                            <span class="badge bg-secondary fs-10">Весь день</span>
                                        @else
                                            <span class="text-muted">{{ $day['start_time'] }} –
                                                {{ $day['end_time'] }}</span>
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
                                        <button class="btn btn-sm btn-outline-danger delete-all-rd"
                                            data-id="{{ $day['id'] }}">
                                            <span class="fas fa-trash"></span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Пагинация --}}
                <div class="row align-items-center justify-content-between py-3 fs-9" id="paginationBlock">
                    <div class="col-auto">
                        <p class="mb-0 text-muted" id="paginationInfo"></p>
                    </div>
                    <div class="col-auto d-flex gap-1" id="paginationButtons"></div>
                </div>
            </div>
        </div>

        <x-dashboard-footer />
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                const perPage = 15;
                let currentPage = 1;
                let allRows = [];
                let filteredRows = [];

                function initRows() {
                    allRows = $('#allRedDaysBody tr.rd-row').toArray();
                    filteredRows = allRows;
                    renderPage();
                }

                function applyFilters() {
                    const search = $('#filterSearch').val().toLowerCase();
                    const master = $('#filterMaster').val();
                    const type = $('#filterType').val();
                    const dateFrom = $('#filterDateFrom').val();
                    const dateTo = $('#filterDateTo').val();

                    filteredRows = allRows.filter(row => {
                        const name = ($(row).find('.rd-name').text() || '').toLowerCase();
                        const rowMaster = $(row).attr('data-master') || '';
                        const rowType = $(row).attr('data-type') || '';
                        const rowDate = $(row).attr('data-date') || '';

                        const matchSearch = !search || name.includes(search);
                        const matchMaster = !master || rowMaster === master;
                        const matchType = !type || rowType === type;
                        const matchDateFrom = !dateFrom || rowDate >= dateFrom;
                        const matchDateTo = !dateTo || rowDate <= dateTo;

                        return matchSearch && matchMaster && matchType && matchDateFrom && matchDateTo;
                    });

                    currentPage = 1;
                    renderPage();
                }

                function renderPage() {
                    const total = filteredRows.length;
                    const totalPages = Math.ceil(total / perPage) || 1;
                    const start = (currentPage - 1) * perPage;
                    const end = start + perPage;

                    allRows.forEach(row => $(row).hide());
                    filteredRows.slice(start, end).forEach(row => $(row).show());

                    const showing = Math.min(end, total);
                    $('#paginationInfo').text(total > 0 ? `Показано ${start + 1}–${showing} из ${total} записей` :
                        'Нет записей');

                    let buttons = '';
                    if (totalPages > 1) {
                        buttons +=
                            `<button class="btn btn-sm btn-outline-secondary page-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}><span class="fas fa-chevron-left"></span></button>`;
                        for (let i = 1; i <= totalPages; i++) {
                            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                                buttons +=
                                    `<button class="btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline-secondary'} page-btn" data-page="${i}">${i}</button>`;
                            } else if (i === currentPage - 3 || i === currentPage + 3) {
                                buttons += `<span class="btn btn-sm btn-outline-secondary disabled">...</span>`;
                            }
                        }
                        buttons +=
                            `<button class="btn btn-sm btn-outline-secondary page-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}><span class="fas fa-chevron-right"></span></button>`;
                    }
                    $('#paginationButtons').html(buttons);
                }

                $(document).on('click', '.page-btn', function() {
                    if (!$(this).prop('disabled')) {
                        currentPage = parseInt($(this).data('page'));
                        renderPage();
                    }
                });

                $('#filterSearch').on('input', applyFilters);
                $('#filterMaster, #filterType').on('change', applyFilters);
                $('#filterDateFrom, #filterDateTo').on('change', applyFilters);
                $('#resetFilters').on('click', function() {
                    $('#filterSearch').val('');
                    $('#filterMaster').val('');
                    $('#filterType').val('');
                    $('#filterDateFrom').val('');
                    $('#filterDateTo').val('');
                    applyFilters();
                });

                // Чекбокс весь день
                $('#new_full_day').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#new_time_block').hide();
                    } else {
                        $('#new_time_block').show();
                    }
                });

                // Сохранить новую запись
                $('#saveNewRedDay').on('click', function() {
                    const name = $('#new_name').val();
                    const date = $('#new_date').val();
                    const masterId = $('#new_master_id').val();
                    const fullDay = $('#new_full_day').is(':checked');
                    const repeat = $('#new_repeat').is(':checked') ? 1 : 0;
                    const startTime = fullDay ? '' : $('#new_start_time').val();
                    const endTime = fullDay ? '' : $('#new_end_time').val();

                    if (!name || !date) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Заполните название и дату',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        return;
                    }

                    $.ajax({
                        url: '/admin/red-days/store',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            name,
                            date,
                            user_id: masterId,
                            full_day: fullDay ? 1 : 0,
                            start_time: startTime,
                            end_time: endTime,
                            repeat
                        },
                        success: function(response) {
                            const day = response.redDay;
                            const masterName = day.user_id ? day.master_name : 'Общий';
                            const masterBadge = day.user_id ?
                                `<span class="badge badge-phoenix badge-phoenix-primary fs-10">${masterName}</span>` :
                                `<span class="badge badge-phoenix badge-phoenix-secondary fs-10">Общий</span>`;
                            const timeCell = day.full_day ?
                                `<span class="badge bg-secondary fs-10">Весь день</span>` :
                                `<span class="text-muted">${day.start_time} – ${day.end_time}</span>`;
                            const month = day.date.substring(0, 7);

                            const newRow = `<tr class="rd-row" id="all_rd_row_${day.id}"
              data-name="${day.name.toLowerCase()}"
              data-master="${masterName}"
              data-type="${day.full_day ? 'full' : 'partial'}"
              data-date="${day.date}">
              <td class="rd-name">${day.name}</td>
              <td>${new Date(day.date).toLocaleDateString('ru-RU')}</td>
              <td>${timeCell}</td>
              <td>${masterBadge}</td>
              <td>${day.repeat ? '✅' : '❌'}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-danger delete-all-rd" data-id="${day.id}">
                  <span class="fas fa-trash"></span>
                </button>
              </td>
            </tr>`;

                            $('#allRedDaysBody').prepend(newRow);
                            allRows = $('#allRedDaysBody tr.rd-row').toArray();

                            $('#new_name').val('');
                            $('#new_date').val('');
                            $('#new_master_id').val('');
                            $('#new_full_day').prop('checked', true);
                            $('#new_repeat').prop('checked', false);
                            $('#new_time_block').hide();
                            $('#addForm').collapse('hide');

                            applyFilters();
                            showSuccess('Запись добавлена!');
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ошибка сохранения',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    });
                });

                // Удаление
                $(document).on('click', '.delete-all-rd', function() {
                    const id = $(this).data('id');
                    const row = $(this).closest('tr');

                    Swal.fire({
                        title: 'Вы уверены?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Удалить',
                        cancelButtonText: 'Отмена'
                    }).then(result => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/api/settings/deleteRedDay/${id}`,
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                                success: function() {
                                    allRows = allRows.filter(r => r !== row[0]);
                                    row.remove();
                                    applyFilters();
                                    Swal.fire('Удалено!', '', 'success');
                                }
                            });
                        }
                    });
                });

                function showSuccess(msg) {
                    $('#successText').text(msg);
                    $('#successAlert').removeClass('d-none');
                    setTimeout(() => $('#successAlert').addClass('d-none'), 3000);
                }

                initRows();
            });
        </script>
    @endpush
</x-dashboard-layout>
