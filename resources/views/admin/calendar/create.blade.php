@section('title', 'Новая запись')
<x-dashboard-layout>
<div class="content">
    <div class="row g-0 mb-4 align-items-center">
        <div class="col">
            <h4 class="mb-0 fw-bold">Новая запись</h4>
            <p class="text-body-tertiary mb-0">Заполните форму или выберите слот из календаря</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('calendar.index') }}" class="btn btn-phoenix-secondary btn-sm">← Назад к календарю</a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Левая колонка: форма --}}
        <div class="col-12 col-xl-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Данные записи</h5>
                    <form id="createAppointmentForm" autocomplete="off">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Услуга</label>
                            <select class="form-select" id="formService">
                                <option value="">Выберите услугу</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}"
                                        data-duration="{{ $service->effective_duration_minutes }}"
                                        data-price="{{ $service->effective_price }}"
                                        data-loop="{{ $loop->index }}">
                                        {{ $service->name }} ({{ $service->effective_duration_minutes }} мин.)
                                    </option>
                                @endforeach
                            </select>
                            <span id="serviceError" class="text-danger small"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Мастер</label>
                            <select class="form-select" id="formMaster">
                                <option value="">Сначала выберите услугу</option>
                            </select>
                            <span id="masterError" class="text-danger small"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Цена (€)</label>
                            <input type="number" class="form-control" id="formPrice" step="0.01">
                            <span id="priceError" class="text-danger small"></span>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Дата</label>
                                <input type="date" class="form-control" id="formDate">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Время начала</label>
                                <input type="time" class="form-control" id="formStartTime">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Время окончания</label>
                            <input type="time" class="form-control" id="formEndTime">
                            <span id="dateError" class="text-danger small"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Имя</label>
                            <input type="text" class="form-control" id="formName">
                            <span id="nameError" class="text-danger small"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Фамилия</label>
                            <input type="text" class="form-control" id="formLastname">
                            <span id="lastnameError" class="text-danger small"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Телефон</label>
                            <input type="text" class="form-control" id="formPhone">
                            <span id="phoneError" class="text-danger small"></span>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Примечание</label>
                            <textarea class="form-control" id="formDescription" rows="3"></textarea>
                            <span id="descriptionError" class="text-danger small"></span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="saveBtn">Сохранить запись</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Правая колонка: слоты --}}
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-1">Доступные слоты</h5>
                    <p class="text-body-tertiary small mb-4">Выберите услугу, мастера и дату чтобы увидеть свободное время</p>

                    <div class="mb-3">
                        <label class="form-label">Выберите дату</label>
                        <input type="date" class="form-control" id="slotDate" value="{{ date('Y-m-d') }}">
                    </div>

                    <div id="slotsLoading" class="text-center py-4 d-none">
                        <i class="fas fa-spinner fa-spin fs-4 text-primary"></i>
                        <p class="text-body-tertiary mt-2">Загружаем слоты...</p>
                    </div>

                    <div id="slotsContainer" class="row g-2"></div>
                    <div id="slotsEmpty" class="text-center py-4 d-none">
                        <i class="fas fa-calendar-times fs-3 text-body-tertiary"></i>
                        <p class="text-body-tertiary mt-2">Нет свободных слотов на эту дату</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    var services = @json($services);
    var selectedSlot = null;
    // Подставляем дату из URL если передана
    const urlParams = new URLSearchParams(window.location.search);
    const urlDate = urlParams.get('date');
    if (urlDate) {
        $('#formDate').val(urlDate);
        $('#slotDate').val(urlDate);
    }
    // При выборе услуги
    $('#formService').on('change', function() {
        const loop = $(this).find('option:selected').data('loop');
        const price = $(this).find('option:selected').data('price');
        $('#formMaster').empty().append('<option value="">Выберите мастера</option>');
        $('#formPrice').val(price || '');

        if (loop !== undefined) {
            services[loop].users.forEach(user => {
                $('#formMaster').append(new Option(user.name, user.id));
            });
        }
        loadSlots();
    });

    $('#formMaster, #slotDate').on('change', function() { loadSlots(); });

    function loadSlots() {
        const service_id = $('#formService').val();
        const user_id = $('#formMaster').val();
        const date = $('#slotDate').val();

        $('#slotsContainer').empty();
        $('#slotsEmpty').addClass('d-none');

        if (!service_id || !user_id || !date) return;

        $('#slotsLoading').removeClass('d-none');

        $.ajax({
            url: "{{ route('api.booking.getFullyBooked') }}",
            type: 'POST',
            dataType: 'json',
            data: { service_id, user_id, choose_date: date },
            success: function(response) {
                $('#slotsLoading').addClass('d-none');
                if (!response || response.length === 0) {
                    $('#slotsEmpty').removeClass('d-none');
                    return;
                }
                response.forEach(slot => {
                    const btn = $(`
                        <div class="col-6 col-md-4 col-lg-3">
                            <button type="button" class="btn btn-phoenix-primary w-100 slot-btn"
                                data-start="${slot.start}" data-end="${slot.end}">
                                <span class="fw-semibold">${slot.start}</span>
                                <small class="d-block text-body-tertiary">до ${slot.end}</small>
                            </button>
                        </div>
                    `);
                    $('#slotsContainer').append(btn);
                });
            },
            error: function() {
                $('#slotsLoading').addClass('d-none');
                $('#slotsEmpty').removeClass('d-none');
            }
        });
    }

    // Клик по слоту
    $(document).on('click', '.slot-btn', function() {
        $('.slot-btn').removeClass('btn-primary').addClass('btn-phoenix-primary');
        $(this).removeClass('btn-phoenix-primary').addClass('btn-primary');

        const start = $(this).data('start');
        const end = $(this).data('end');
        const date = $('#slotDate').val();

        $('#formDate').val(date);
        $('#formStartTime').val(start);
        $('#formEndTime').val(end);
        selectedSlot = { date, start, end };
    });

    // Синхронизация даты
    $('#formDate').on('change', function() {
        $('#slotDate').val($(this).val());
        loadSlots();
    });

    // Сохранение
    $('#createAppointmentForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const date = $('#formDate').val();
        const startTime = $('#formStartTime').val();
        const endTime = $('#formEndTime').val();

        if (!date || !startTime || !endTime) {
            $('#dateError').html('Укажите дату и время');
            return;
        }

        const appointment_start = date + ' ' + startTime;
        const appointment_end = date + ' ' + endTime;

        $('#saveBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Сохраняем...');

        $.ajax({
            url: "{{ route('calendar.store') }}",
            type: 'POST',
            dataType: 'json',
            data: {
                appointment_start,
                appointment_end,
                service_id: $('#formService').val(),
                user_id: $('#formMaster').val(),
                price: $('#formPrice').val(),
                client_name: $('#formName').val(),
                client_lastname: $('#formLastname').val(),
                client_phone: $('#formPhone').val(),
                description: $('#formDescription').val(),
            },
            success: function(response) {
                Swal.fire({
                    position: "top",
                    icon: "success",
                    title: 'Запись успешно добавлена!',
                    showConfirmButton: false,
                    timerProgressBar: true,
                    timer: 1500,
                }).then(() => {
                    window.location.href = "{{ route('calendar.index') }}";
                });
            },
            error: function(err) {
                $('#saveBtn').prop('disabled', false).html('Сохранить запись');
                if (!err.responseJSON) return;
                const errors = err.responseJSON.errors;
                if (errors) {
                    $('#phoneError').html(errors.client_phone || "");
                    $('#serviceError').html(errors.service_id || "");
                    $('#nameError').html(errors.client_name || "");
                    $('#lastnameError').html(errors.client_lastname || "");
                    $('#dateError').html(errors.appointment_start || errors.appointment_end || "");
                    $('#descriptionError').html(errors.description || "");
                    if (errors.appointment_start) {
                        Swal.fire({
                            position: "top",
                            icon: "error",
                            title: errors.appointment_start[0],
                            showConfirmButton: false,
                            timerProgressBar: true,
                            timer: 3000,
                        });
                    }
                }
            }
        });
    });

    function clearErrors() {
        $('#phoneError, #serviceError, #nameError, #lastnameError, #dateError, #descriptionError, #masterError').html('');
    }
});
</script>
@endpush
</x-dashboard-layout>
