@section('title', 'GoodFeet Booking')
@push('styles')
@endpush
<x-app-layout>
    <div class="container-medium ff_secondary">
        <div class="row justify-content-between">
            <div class="col-lg-7 col-xl-6">
                <form id="storeBooking" class="needs-validation" novalidate="">
                    @csrf
                    <input type="hidden" name="service_id" value="{{ $service['id'] }}">
                    <input type="hidden" name="user_id" value="{{ $bookingData['user_id'] }}">
                    <input type="hidden" name="choose_date" value="{{ $bookingData['choose_date'] }}">
                    <input type="hidden" name="appointment_start" value="{{ $bookingData['start'] }}">
                    <input type="hidden" name="appointment_end" value="{{ $bookingData['end'] }}">

                    <hr class="mt-0 mb-7">
                    <h3 class="fw-bold mb-5">{{ __('msg.booking_data') }}</h3>
                    <div class="row g-3 mb-5 mt-1">
                        <div class="col-sm-6">
                            <label class="fw-bold text-body-highlight mb-1 ff_secondary"
                                for="client_name">{{ __('msg.name') }}</label>
                            <input class="form-control ff_secondary border_rounded" type="text" id="client_name"
                                name="client_name" value="{{ old('client_name', Auth::guard('customer')->user()?->first_name) }}" placeholder="{{ __('msg.name') }}" pattern=".{2,}" required>
                            <div class="invalid-feedback ff_secondary">{{ __('msg.required_input') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="fw-bold text-body-highlight mb-1 ff_secondary"
                                for="client_lastname">{{ __('msg.surname') }}</label>
                            <input class="form-control ff_secondary border_rounded" type="text" id="client_lastname"
                                name="client_lastname" value="{{ old('client_lastname', Auth::guard('customer')->user()?->last_name) }}" placeholder="{{ __('msg.surname') }}" pattern=".{3,}" required>
                            <div class="invalid-feedback ff_secondary">{{ __('msg.required_input') }}</div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="fw-bold text-body-highlight mb-1 ff_secondary"
                                for="client_email">Email</label>
                            <input class="form-control ff_secondary border_rounded" type="email" id="client_email"
                                name="client_email" value="{{ old('client_email', Auth::guard('customer')->user()?->email) }}" placeholder="example@mail.ru" required="">
                            <div class="invalid-feedback ff_secondary">{{ __('msg.required_input') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="fw-bold text-body-highlight mb-1 ff_secondary"
                                for="client_phone">{{ __('msg.phone') }}</label>
                            <input class="form-control ff_secondary border_rounded" type="text" id="client_phone"
                                name="client_phone" value="{{ old('client_phone', Auth::guard('customer')->user()?->phone) }}" placeholder="+372 555 555 55" pattern="^\+[\d\s]+$" required>
                            <div class="invalid-feedback ff_secondary">{{ __('msg.required_input__tel') }}</div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="fw-bold text-body-highlight mb-1 ff_secondary" for="promo_code">{{ __('promo.label') }}</label>
                        <div class="input-group"><input class="form-control text-uppercase" id="promo_code" name="promo_code" maxlength="50" autocomplete="off"><button class="btn btn-outline-primary" id="applyPromoCode" type="button">{{ __('promo.apply') }}</button></div>
                        <div id="promoCodeFeedback" class="form-text"></div>
                        <div class="form-text">{{ __('promo.preview_note') }}</div>
                    </div>
                    <h5 class="mb-3 mt-6">{{ __('msg.booking_policy') }}</h5>
                    <div class="form-check mb-4">
                        <input class="form-check-input" id="policy" name="policy" type="checkbox" required="">
                        <label class="form-check-label fw-normal fs-8 text-body ff_secondary" for="policy">
                            {{ __('msg.agree_with_policy') }}
                            <span class="d-block fs-9 text-body-tertiary">
                                <p class="mb-2 mt-2 ff_secondary"><svg
                                        class="svg-inline--fa fa-circle text-body-quaternary fs-10 me-2"
                                        data-fa-transform="up-2" aria-hidden="true" focusable="false" data-prefix="fas"
                                        data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 512 512" data-fa-i2svg="" style="transform-origin: 0.5em 0.375em;">
                                        <g transform="translate(256 256)">
                                            <g transform="translate(0, -64)  scale(1, 1)  rotate(0 0 0)">
                                                <path fill="currentColor"
                                                    d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"
                                                    transform="translate(-256 -256)"></path>
                                            </g>
                                        </g>
                                    </svg>
                                    {{ __('msg.cancel_booking_notice') }}
                                </p>
                            </span>
                        </label>
                    </div>

                    <h5 class="mb-3">{{ __('msg.additional_info') }}</h5>
                    <textarea class="form-control ff_secondary border_rounded" name="description" rows="5" id="description"
                        placeholder="{{ __('msg.additional_info') }}" minlength="8"></textarea>
                    <div class="invalid-feedback ff_secondary">{{ __('msg.required_input_min_8') }}</div>
                    <input class="form-control ff_secondary mt-4 border_rounded" type="file" name="files[]"
                        id="fileInput" multiple>
                    <hr class="mt-7 mb-5">
                    <button type="submit" class="btn_primary border_rounded text-center ff_secondary"
                        href="">
                        {{ __('msg.book') }}
                    </button>
                </form>
            </div>
            <div class="col-lg-5 col-xl-4">
                <div class="card mt-5 mt-lg-0">
                    <div class="card-body">
                        <img class="rounded-2 mb-3" src="{{ asset('assets/img/logos/logo.svg') }}" alt="GoodFeet"
                            width="100%" class="logoLarge logoContainer" />
                        <p class="mb-5 text-body-tertiary"><i class="fa-solid fa-location-dot me-2"></i>
                            {{ $settings['company_address'] }}</p>
                        @if ($master)
                            <div class="mb-4">
                                <p class="mb-1 text-body-tertiary fs-9">
                                    <i class="fa-solid fa-user-tie me-2"></i>
                                    <span class="fw-semibold">{{ __('msg.master') }}:</span> {{ $master->name }}
                                </p>
                                @if ($master->phone)
                                    <p class="mb-1 text-body-tertiary fs-9">
                                        <i class="fa-solid fa-phone me-2"></i>
                                        <a href="tel:{{ $master->phone }}">{{ $master->phone }}</a>
                                    </p>
                                @endif
                                @if ($master->email)
                                    <p class="mb-1 text-body-tertiary fs-9">
                                        <i class="fa-solid fa-envelope me-2"></i>
                                        <a href="mailto:{{ $master->email }}">{{ $master->email }}</a>
                                    </p>
                                @endif
                            </div>
                        @endif
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between gap-3 mb-4">
                                    <div>
                                        <h5 class="text-body-highlight text-capitalize">{{ $choose_date }}:</h5>
                                        <p class="mb-0 text-body-tertiary">
                                            {{ $bookingData['start'] }} - {{ $bookingData['end'] }}
                                            ({{ $service['effective_duration_minutes'] }} мин.)
                                        </p>
                                    </div>
                                </div>
                                <div class="row align-items-center g-0 my-2">
                                    <div class="col-12">
                                        <h5 class="text-body text-nowrap mb-0">{{ __('msg.service') }}: </h5>
                                    </div>
                                    <div class="col-12">
                                        <span>
                                            @if (isset($service->translation['name']))
                                                {{ $service->translation['name'] }}
                                            @else
                                                {{ $service['name'] }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="row align-items-center g-0 my-2">
                                    <div class="col-3">
                                        <h5 class="text-body text-nowrap mb-0">{{ __('msg.time') }}: </h5>
                                    </div>
                                    <div class="col-auto">
                                        <span>
                                            {{ $bookingData['start'] }} - {{ $bookingData['end'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-body-highlight rounded-2">
                            <hr>
                            <div class="d-flex flex-between-center">
                                <h4 class="text-body">{{ __('promo.original_price') }}</h4>
                                <h4 class="text-body" id="bookingOriginalPrice">
                                    @if ($service['price_can_change'])
                                        <span>{{ __('msg.price_from') }} </span>
                                    @endif
                                    {{ $service['effective_price'] }}
                                    <span>&euro;</span>
                                </h4>
                            </div>
                            <div class="d-none flex-between-center text-success" id="bookingDiscountRow"><span>{{ __('promo.discount_amount') }}</span><strong id="bookingDiscountAmount"></strong></div>
                            <div class="d-none flex-between-center mt-2" id="bookingFinalRow"><h4 class="text-body mb-0">{{ __('promo.final_price') }}</h4><h4 class="text-body mb-0" id="bookingFinalPrice"></h4></div>
                            @if ($service['price_can_change'])
                                <p class="mb-0 text-body-tertiary">{{ __('msg.price_note') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-8">
            <div class="col-12">
                <div class="contacts contact_map">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3415.2464287575613!2d27.294019073067258!3d59.40442262468937!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46947085ae596251%3A0xc5639019e576cbca!2z0J_QvtC70LjQutC70LjQvdC40LrQsCDQr9GA0LLQtdGB0LrQvtC5INGH0LDRgdGC0Lgg0LPQvtGA0L7QtNCwINCa0L7RhdGC0LvQsC3Qr9GA0LLQtQ!5e0!3m2!1sru!2see!4v1742733427402!5m2!1sru!2see"
                        width="100%" height="280px" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#applyPromoCode').on('click', function() {
                    const code = $('#promo_code').val().trim();
                    const feedback = $('#promoCodeFeedback');
                    if (!code) return;
                    feedback.removeClass('text-danger text-success').text('{{ __('customer.loading') }}');
                    $.ajax({
                        url: @json(route('booking.promo.preview')),
                        type: 'POST',
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        data: {promo_code: code, service_id: @json($service['id']), choose_date: @json($bookingData['choose_date']), client_email: $('#client_email').val()},
                        success: function(data) {
                            $('#promo_code').val(data.code);
                            $('#bookingOriginalPrice').text(Number(data.original_price).toFixed(2) + ' €');
                            $('#bookingDiscountAmount').text('−' + Number(data.discount_amount).toFixed(2) + ' €');
                            $('#bookingFinalPrice').text(Number(data.final_price).toFixed(2) + ' €');
                            $('#bookingDiscountRow, #bookingFinalRow').removeClass('d-none').addClass('d-flex');
                            feedback.addClass('text-success').text('{{ __('promo.applied') }}');
                        },
                        error: function(xhr) {
                            $('#bookingDiscountRow, #bookingFinalRow').addClass('d-none').removeClass('d-flex');
                            const errors = xhr.responseJSON?.errors || {};
                            feedback.addClass('text-danger').text(errors.promo_code?.[0] || Object.values(errors)[0]?.[0] || '{{ __('promo.invalid') }}');
                        }
                    });
                });
                $('#storeBooking').on('submit', function(e) {
                    e.preventDefault();

                    let formData = new FormData(this);
                    let url = "{{ route('booking.store') }}";

                    $.ajax({
                        url: url,
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                        },
                        success: function(response) {
                            console.log('success')
                            if (response.redirectUrl) {
                                window.location.href = response.redirectUrl;
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr.responseJSON.error)
                            if (xhr.status === 400) {
                                console.log(xhr.responseJSON)
                            } else if (xhr.status === 404) {
                                Swal.fire({
                                    position: "top",
                                    icon: "warning",
                                    title: '{{ __('msg.validation_busyerror') }}',
                                    showConfirmButton: false,
                                    iconColor: '#d37d17',
                                    timerProgressBar: true,
                                    timer: 3000,
                                    customClass: {
                                        popup: 'ff_secondary'
                                    }
                                });
                            } else {
                                Swal.fire({
                                    position: "top",
                                    icon: "warning",
                                    title: '{{ __('msg.validation_error') }}',
                                    showConfirmButton: false,
                                    iconColor: '#d37d17',
                                    timerProgressBar: true,
                                    timer: 3000,
                                    customClass: {
                                        popup: 'ff_secondary'
                                    }
                                });
                            }
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
