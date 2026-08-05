@section('title', 'Моё расписание')
@push('styles')
    <link href="{{ asset('vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
@endpush

<x-dashboard-layout>
    <div class="content">
        <nav class="mb-3" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Моё расписание</li>
            </ol>
        </nav>

        <div id="successAlert" class="alert alert-success d-none" role="alert">
            <span class="fas fa-check-circle me-2"></span>Данные успешно сохранены!
        </div>
        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
            <span class="fas fa-times-circle me-2"></span><span id="errorText"></span>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="sticky-top mb-4" style="margin-top:-72px;padding-top:72px">
                    <div class="nav flex-row gap-3">
                        <a class="btn btn-outline-secondary btn-sm" href="#section-workhours">Рабочее время</a>
                        <a class="btn btn-outline-secondary btn-sm" href="#section-lunch">Обед</a>
                        <a class="btn btn-outline-secondary btn-sm" href="#section-fixed">Фиксированное время</a>
                        <a class="btn btn-outline-secondary btn-sm" href="#section-limit">Лимит дней</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Рабочее время --}}
        <section id="section-workhours" class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><span class="fas fa-clock me-2"></span>Рабочее время</h5>
            </div>
            <div class="card-body">
                <form id="workHoursForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>День</th>
                                    <th>Начало</th>
                                    <th>Конец</th>
                                    <th>Выходной</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ([
        'monday' => 'Понедельник',
        'tuesday' => 'Вторник',
        'wednesday' => 'Среда',
        'thursday' => 'Четверг',
        'friday' => 'Пятница',
        'saturday' => 'Суббота',
        'sunday' => 'Воскресенье',
    ] as $day => $label)
                                    @php $isDayOff = !$schedule || !$schedule->{$day.'_start'} || !$schedule->{$day.'_end'}; @endphp
                                    <tr>
                                        <td><strong>{{ $label }}</strong></td>
                                        <td>
                                            <input type="time" class="form-control form-control-sm"
                                                id="wh_{{ $day }}_start"
                                                value="{{ $schedule ? $schedule->{$day . '_start'} : '' }}"
                                                style="width:120px" {{ $isDayOff ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <input type="time" class="form-control form-control-sm"
                                                id="wh_{{ $day }}_end"
                                                value="{{ $schedule ? $schedule->{$day . '_end'} : '' }}"
                                                style="width:120px" {{ $isDayOff ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input wh-dayoff" type="checkbox"
                                                    id="wh_{{ $day }}_off" data-day="{{ $day }}"
                                                    {{ $isDayOff ? 'checked' : '' }}>
                                                <label class="form-check-label text-muted fs-10"
                                                    for="wh_{{ $day }}_off">Выходной</label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <span class="fas fa-save me-1"></span>Сохранить расписание
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- Обед --}}
        <section id="section-lunch" class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><span class="fas fa-utensils me-2"></span>Обеденный перерыв</h5>
            </div>
            <div class="card-body">
                <form id="lunchForm">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-sm-4">
                            <label class="form-label">Начало обеда</label>
                            <input type="time" class="form-control" id="lunch_start"
                                value="{{ $schedule ? $schedule->lunch_start : '' }}">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Конец обеда</label>
                            <input type="time" class="form-control" id="lunch_end"
                                value="{{ $schedule ? $schedule->lunch_end : '' }}">
                        </div>
                        <div class="col-sm-4">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="lunch_off"
                                    {{ !$schedule || !$schedule->lunch_start ? 'checked' : '' }}>
                                <label class="form-check-label" for="lunch_off">Нет обеда</label>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <span class="fas fa-save me-1"></span>Сохранить обед
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- Модалка редактирования --}}
        <div class="modal fade" id="editRdModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Редактировать день</h5>
                        <button class="btn btn-close p-1" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_rd_id">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Название*</label>
                                <input type="text" class="form-control" id="edit_rd_name">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Дата*</label>
                                <input type="date" class="form-control" id="edit_rd_date">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Дата до</label>
                                <input type="date" class="form-control" id="edit_rd_date_to">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Причина</label>
                                <select class="form-select" id="edit_rd_type">
                                    @foreach (\App\Models\RedDay::TYPES as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_rd_fullday">
                                    <label class="form-check-label" for="edit_rd_fullday">Весь день</label>
                                </div>
                            </div>
                            <div class="col-6" id="edit_rd_time1">
                                <label class="form-label">Начало</label>
                                <input type="time" class="form-control" id="edit_rd_start">
                            </div>
                            <div class="col-6" id="edit_rd_time2">
                                <label class="form-label">Конец</label>
                                <input type="time" class="form-control" id="edit_rd_end">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_rd_repeat">
                                    <label class="form-check-label" for="edit_rd_repeat">Повторять ежегодно</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" type="button"
                            data-bs-dismiss="modal">Отмена</button>
                        <button class="btn btn-primary" type="button" id="saveEditRd">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Фиксированное время --}}
        <section id="section-fixed" class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><span class="fas fa-lock me-2"></span>Фиксированное время записи</h5>
            </div>
            <div class="card-body">
                <p class="text-muted fs-9 mb-3">Если включено — клиенты смогут записываться только на указанные слоты
                    времени.</p>
                <form id="fixedBookingForm">
                    @csrf
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="fixed_enabled"
                            {{ $schedule && $schedule->fixed_booking_enabled ? 'checked' : '' }}>
                        <label class="form-check-label" for="fixed_enabled">Включить фиксированное время</label>
                    </div>

                    <div id="fixedSlotsContainer"
                        class="{{ $schedule && $schedule->fixed_booking_enabled ? '' : 'd-none' }}">
                        <h6 class="mb-3">Временные слоты</h6>
                        <div id="fixedSlotsList">
                            @if ($schedule && $schedule->fixed_booking_slots)
                                @foreach ($schedule->fixed_booking_slots as $slot)
                                    <div class="row mb-2 fixed-slot-row">
                                        <div class="col-sm-3">
                                            <input type="time"
                                                class="form-control form-control-sm fixed-slot-input"
                                                value="{{ $slot }}">
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-slot">
                                                <span class="fas fa-minus"></span>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="addSlot">
                            <span class="fas fa-plus me-1"></span>Добавить слот
                        </button>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <span class="fas fa-save me-1"></span>Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- Лимит дней --}}
        <section id="section-limit" class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><span class="fas fa-calendar-alt me-2"></span>Лимит дней бронирования</h5>
            </div>
            <div class="card-body">
                <p class="text-muted fs-9 mb-3">Глобальный лимит устанавливается администратором.</p>
                <div class="alert alert-info fs-9">
                    <span class="fas fa-info-circle me-1"></span>
                    Клиенты могут бронировать на <strong>{{ $bookingLimit['days'] }} дней</strong> вперёд.
                </div>
            </div>
        </section>

        <x-dashboard-footer />
    </div>

    @push('scripts')
        <script src="{{ asset('vendors/flatpickr/flatpickr.min.js') }}"></script>
        <script>
            $(document).ready(function() {

                // Чекбокс выходного дня
                $('.wh-dayoff').on('change', function() {
                    const day = $(this).data('day');
                    const isOff = $(this).is(':checked');
                    if (isOff) {
                        $('#wh_' + day + '_start').val('').prop('disabled', true);
                        $('#wh_' + day + '_end').val('').prop('disabled', true);
                    } else {
                        $('#wh_' + day + '_start').prop('disabled', false);
                        $('#wh_' + day + '_end').prop('disabled', false);
                    }
                });

                // Чекбокс нет обеда
                $('#lunch_off').on('change', function() {
                    const isOff = $(this).is(':checked');
                    $('#lunch_start').val('').prop('disabled', isOff);
                    $('#lunch_end').val('').prop('disabled', isOff);
                });
                // Инициализация состояния обеда
                if ($('#lunch_off').is(':checked')) {
                    $('#lunch_start').prop('disabled', true);
                    $('#lunch_end').prop('disabled', true);
                }

                // Чекбокс весь день
                $('#rd_full_day').on('change', function() {
                    const isFullDay = $(this).is(':checked');
                    $('#rd_time_block, #rd_time_block2').toggle(!isFullDay);
                });

                // Сохранить рабочее время
                $('#workHoursForm').on('submit', function(e) {
                    e.preventDefault();
                    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    let data = {};
                    days.forEach(day => {
                        const isOff = $('#wh_' + day + '_off').is(':checked');
                        data[day + '_off'] = isOff ? '1' : '0';
                        data[day + '_start'] = isOff ? '' : $('#wh_' + day + '_start').val();
                        data[day + '_end'] = isOff ? '' : $('#wh_' + day + '_end').val();
                    });

                    $.ajax({
                        url: '{{ route('master.schedule.updateWorkHours') }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: data,
                        success: function() {
                            showSuccess();
                        },
                        error: function() {
                            showError('Ошибка сохранения расписания');
                        }
                    });
                });

                // Сохранить обед
                $('#lunchForm').on('submit', function(e) {
                    e.preventDefault();
                    const isOff = $('#lunch_off').is(':checked');
                    $.ajax({
                        url: '{{ route('master.schedule.updateLunchHours') }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            lunch_start: isOff ? '' : $('#lunch_start').val(),
                            lunch_end: isOff ? '' : $('#lunch_end').val(),
                        },
                        success: function() {
                            showSuccess();
                        },
                        error: function() {
                            showError('Ошибка сохранения обеда');
                        }
                    });
                });

                // Добавить нерабочий день
                $('#newRedDayForm').on('submit', function(e) {
                    e.preventDefault();
                    const isFullDay = $('#rd_full_day').is(':checked');
                    const repeat = $('#rd_repeat').is(':checked') ? 1 : 0;

                    $.ajax({
                        url: '{{ route('master.schedule.storeRedDay') }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            name: $('#rd_name').val(),
                            date: $('#rd_date').val(),
                            date_to: $('#rd_date_to').val() || $('#rd_date').val(),
                            type: $('#rd_type').val() || 'other',
                            start_time: isFullDay ? '' : $('#rd_start_time').val(),
                            end_time: isFullDay ? '' : $('#rd_end_time').val(),
                            repeat: repeat,
                        },
                        success: function(response) {
                            const rd = response.redDay;
                            const timeStr = rd.full_day ?
                                '<span class="badge bg-secondary">Весь день</span>' :
                                (rd.start_time + ' - ' + rd.end_time);
                            const date = new Date(rd.date);
                            const dateStr = date.toLocaleDateString('ru-RU');

                            $('#rd_empty').remove();
                            $('#redDaysBody').append(`
            <tr id="rd_row_${rd.id}" class="rd-row"
                data-name="${rd.name.toLowerCase()}"
                data-type="${rd.full_day ? 'full' : 'partial'}">
                <td>${rd.name}</td>
                <td>${dateStr}</td>
                <td>${timeStr}</td>
                <td>${rd.repeat == 1 ? '✅' : '❌'}</td>
                <td>
                  <button class="btn btn-sm btn-danger delete-rd" data-id="${rd.id}">
                    <span class="fas fa-trash"></span>
                  </button>
                </td>
              </tr>
            `);

                            $('#rd_name, #rd_date, #rd_start_time, #rd_end_time').val('');
                            $('#rd_full_day, #rd_repeat').prop('checked', false);
                            $('#addRedDayForm').collapse('hide');
                            // Обновляем массив всех строк после добавления новой
                            rdAllRows = $('#redDaysBody tr.rd-row').toArray();
                            applyRdFilters();
                            showSuccess();
                        },
                        error: function(xhr) {
                            showError(xhr.responseJSON?.message || 'Ошибка сохранения');
                        }
                    });
                });

                // Удалить нерабочий день
                $(document).on('click', '.delete-rd', function() {
                    const id = $(this).data('id');
                    if (!confirm('Удалить этот день?')) return;

                    $.ajax({
                        url: '/master/schedule/redDay/' + id,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            $('#rd_row_' + id).remove();
                            rdAllRows = rdAllRows.filter(r => $(r).attr('id') !== 'rd_row_' + id);
                            applyRdFilters();
                            if ($('#redDaysBody tr').length === 0) {
                                $('#redDaysBody').append(
                                    '<tr id="rd_empty"><td colspan="5" class="text-center text-muted">Нет нерабочих дней</td></tr>'
                                );
                            }
                            showSuccess();
                        },
                        error: function() {
                            showError('Ошибка удаления');
                        }
                    });
                });

                function showSuccess() {
                    $('#successAlert').removeClass('d-none');
                    setTimeout(() => $('#successAlert').addClass('d-none'), 3000);
                }

                function showError(msg) {
                    $('#errorText').text(msg);
                    $('#errorAlert').removeClass('d-none');
                    setTimeout(() => $('#errorAlert').addClass('d-none'), 4000);
                }
                // Переключение фиксированного времени
                $('#fixed_enabled').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#fixedSlotsContainer').removeClass('d-none');
                    } else {
                        $('#fixedSlotsContainer').addClass('d-none');
                    }
                });

                // Добавить слот
                $('#addSlot').on('click', function() {
                    $('#fixedSlotsList').append(`
                <div class="row mb-2 fixed-slot-row">
                    <div class="col-sm-3">
                        <input type="time" class="form-control form-control-sm fixed-slot-input">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-slot">
                            <span class="fas fa-minus"></span>
                        </button>
                    </div>
                </div>
            `);
                });

                // Удалить слот
                $(document).on('click', '.remove-slot', function() {
                    $(this).closest('.fixed-slot-row').remove();
                });

                // Сохранить фиксированное время
                $('#fixedBookingForm').on('submit', function(e) {
                    e.preventDefault();
                    const enabled = $('#fixed_enabled').is(':checked') ? 1 : 0;
                    let slots = [];
                    $('.fixed-slot-input').each(function() {
                        const val = $(this).val().trim();
                        if (val) slots.push(val);
                    });

                    $.ajax({
                        url: '{{ route('master.schedule.updateFixedBooking') }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            fixed_booking_enabled: enabled,
                            'fixed_booking_slots[]': slots,
                        },
                        success: function() {
                            showSuccess();
                        },
                        error: function() {
                            showError('Ошибка сохранения');
                        }
                    });
                });
                // Пагинация нерабочих дней
                const rdPerPage = 10;
                let rdCurrentPage = 1;
                let rdAllRows = [];
                let rdFilteredRows = [];

                function initRdRows() {
                    rdAllRows = $('#redDaysBody tr.rd-row').toArray();
                    rdFilteredRows = rdAllRows;
                    renderRdPage();
                }

                function applyRdFilters() {
                    const search = $('#rdSearch').val().toLowerCase();
                    const type = $('#rdFilterType').val();

                    rdFilteredRows = rdAllRows.filter(row => {
                        const name = $(row).data('name');
                        const rowType = $(row).data('type');
                        const matchSearch = !search || name.includes(search);
                        const matchType = !type || rowType === type;
                        return matchSearch && matchType;
                    });

                    rdCurrentPage = 1;
                    renderRdPage();
                }

                function renderRdPage() {
                    const total = rdFilteredRows.length;
                    const totalPages = Math.ceil(total / rdPerPage) || 1;
                    const start = (rdCurrentPage - 1) * rdPerPage;
                    const end = start + rdPerPage;

                    rdAllRows.forEach(row => $(row).hide());
                    rdFilteredRows.slice(start, end).forEach(row => $(row).show());

                    const showing = Math.min(end, total);
                    $('#rdPaginationInfo').text(total > 0 ? `Показано ${start + 1}–${showing} из ${total} записей` :
                        'Нет записей');

                    let buttons = '';
                    if (totalPages > 1) {
                        buttons +=
                            `<button class="btn btn-sm btn-outline-secondary rd-page-btn" data-page="${rdCurrentPage - 1}" ${rdCurrentPage === 1 ? 'disabled' : ''}><span class="fas fa-chevron-left"></span></button>`;
                        for (let i = 1; i <= totalPages; i++) {
                            if (i === 1 || i === totalPages || (i >= rdCurrentPage - 1 && i <= rdCurrentPage + 1)) {
                                buttons +=
                                    `<button class="btn btn-sm ${i === rdCurrentPage ? 'btn-primary' : 'btn-outline-secondary'} rd-page-btn" data-page="${i}">${i}</button>`;
                            } else if (i === rdCurrentPage - 2 || i === rdCurrentPage + 2) {
                                buttons += `<span class="btn btn-sm disabled">...</span>`;
                            }
                        }
                        buttons +=
                            `<button class="btn btn-sm btn-outline-secondary rd-page-btn" data-page="${rdCurrentPage + 1}" ${rdCurrentPage === totalPages ? 'disabled' : ''}><span class="fas fa-chevron-right"></span></button>`;
                    }
                    $('#rdPaginationButtons').html(buttons);
                }

                $(document).on('click', '.rd-page-btn', function() {
                    if (!$(this).prop('disabled')) {
                        rdCurrentPage = parseInt($(this).data('page'));
                        renderRdPage();
                    }
                });

                $('#rdSearch').on('input', applyRdFilters);
                $('#rdFilterType').on('change', applyRdFilters);
                $('#rdResetFilter').on('click', function() {
                    $('#rdSearch').val('');
                    $('#rdFilterType').val('');
                    applyRdFilters();
                });

                // Открыть модалку редактирования
                $(document).on('click', '.edit-rd', function() {
                    const fullDay = $(this).data('fullday') == 1;
                    $('#edit_rd_id').val($(this).data('id'));
                    $('#edit_rd_name').val($(this).data('name'));
                    $('#edit_rd_date').val($(this).data('date'));
                    $('#edit_rd_date_to').val($(this).data('date-to') || $(this).data('date'));
                    $('#edit_rd_type').val($(this).data('type') || 'other');
                    $('#edit_rd_start').val($(this).data('start'));
                    $('#edit_rd_end').val($(this).data('end'));
                    $('#edit_rd_repeat').prop('checked', $(this).data('repeat') == 1);
                    $('#edit_rd_fullday').prop('checked', fullDay);
                    $('#edit_rd_time1, #edit_rd_time2').toggle(!fullDay);
                    $('#editRdModal').modal('show');
                });

                $('#edit_rd_fullday').on('change', function() {
                    $('#edit_rd_time1, #edit_rd_time2').toggle(!$(this).is(':checked'));
                });

                // Сохранить редактирование
                $('#saveEditRd').on('click', function() {
                    const id = $('#edit_rd_id').val();
                    const isFullDay = $('#edit_rd_fullday').is(':checked');

                    $.ajax({
                        url: '/master/schedule/redDay/' + id,
                        type: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            name: $('#edit_rd_name').val(),
                            date: $('#edit_rd_date').val(),
                            date_to: $('#edit_rd_date_to').val() || $('#edit_rd_date').val(),
                            type: $('#edit_rd_type').val() || 'other',
                            start_time: isFullDay ? '' : $('#edit_rd_start').val(),
                            end_time: isFullDay ? '' : $('#edit_rd_end').val(),
                            repeat: $('#edit_rd_repeat').is(':checked') ? 1 : 0,
                        },
                        success: function(response) {
                            const rd = response.redDay;
                            const row = $(`#rd_row_${rd.id}`);
                            const date = new Date(rd.date);
                            const dateStr = date.toLocaleDateString('ru-RU');
                            const timeStr = rd.full_day ?
                                '<span class="badge bg-secondary fs-10">Весь день</span>' :
                                `${rd.start_time} – ${rd.end_time}`;

                            row.find('.rd-name').text(rd.name);
                            row.find('.rd-date').text(dateStr);
                            row.find('.rd-time').html(timeStr);
                            row.data('name', rd.name.toLowerCase());
                            row.data('type', rd.full_day ? 'full' : 'partial');

                            row.find('.edit-rd')
                                .data('name', rd.name)
                                .data('date', rd.date)
                                .data('start', rd.start_time || '')
                                .data('end', rd.end_time || '')
                                .data('repeat', rd.repeat)
                                .data('fullday', rd.full_day);

                            $('#editRdModal').modal('hide');
                            showSuccess('Нерабочий день обновлён!');
                            applyRdFilters();
                        },
                        error: function() {
                            showError('Ошибка сохранения');
                        }
                    });
                });

                // Инициализация пагинации
                initRdRows();
            });
        </script>
    @endpush
</x-dashboard-layout>ashboard-sidebar.blade
