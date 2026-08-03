@section('title', 'GoodFeet - Booking')
@push('styles')
@endpush
<x-app-layout>

    <section class="pt-9 pb-10">
        <div class="bg-holder d-none d-xl-block"
            style="background-image:url(../../public/assets/img/bg/bg-left-33.png);background-size:auto;background-position:-8% 38px;">
        </div>
        <!--/.bg-holder-->

        <div class="bg-holder d-none d-xl-block"
            style="background-image:url(../../public/assets/img/bg/bg-right-33.png);background-size:18%;background-position:right;">
        </div>
        <!--/.bg-holder-->

        <div class="bg-get-app"></div>
        <div class="container-medium position-relative">
            <div class="row g-0 justify-content-center">
                <div class="col-lg-10 col-xl-8 col-xxl-7">
                    <div class="d-md-flex align-items-center gap-5 text-center text-md-start">
                        <img class="img-fluid" src="{{ asset('assets/img/spot-illustrations/41.png') }}" alt=""
                            width="250">
                        <div class="mt-5 mt-md-0 ff_secondary">
                            <h3 class="fw-bolder mt-4">{{ __('msg.thank_you_for_booking') }}</h3>
                            <p class="text-body-tertiary">{{ __('msg.waiting_for_you') }}</p>
                            <div class="row d-flex">
                                <div class="row col-12">
                                    <div class="col-6 text-start"><i
                                            class="fa-solid fa-clipboard-check me-2"></i>{{ __('msg.service') }}:</div>
                                    <div class="col-6 text-end">
                                        {{ $translation ? $translation : $appointment->service->name }}</div>
                                </div>
                                <div class="row col-12">
                                    <div class="col-6 text-start"><i
                                            class="fa-solid fa-user-tie me-2"></i>{{ __('msg.master') }}:</div>
                                    <div class="col-6 text-end">{{ $appointment->user['name'] }}</div>
                                </div>
                                <div class="row col-12">
                                    <div class="col-6 text-start"><i
                                            class="fa-solid fa-calendar me-2"></i>{{ __('msg.date') }}:</div>
                                    <div class="col-6 text-end">{{ $appointment['booking_date'] }}</div>
                                </div>
                                <div class="row col-12">
                                    <div class="col-6 text-start"><i
                                            class="fa-solid fa-clock me-2"></i>{{ __('msg.time') }}:</div>
                                    <div class="col-6 text-end">{{ $appointment['booking_start'] }} -
                                        {{ $appointment['booking_end'] }}</div>
                                </div>
                                <div class="row col-12">
                                    <div class="col-4 text-start"><i class="fa-solid fa-location-dot me-2"></i>
                                        {{ __('msg.address') }}: </div>
                                    <div class="col-8 text-end">
                                        {{ $settings['company_address'] }}
                                        <p class=" fw-regular">{{ __('msg.room_label') }}: <span
                                                class="bg_primary border_radius px-4 fw-bold" py-2>D1053</span></p>
                                    </div>
                                </div>
                                <hr class="mt-7 mb-5">
                                <div class="row col-12">
                                    <div class="col-6 text-start fw-semibold">{{ $appointment->discount_amount > 0 ? __('promo.final_price') : __('msg.service_price') }}: </div>
                                    <div class="col-6 text-end fw-semibold">
                                        @if ($appointment->service['price_can_change'])
                                            <span>{{ __('msg.price_from') }} </span>
                                        @endif
                                        {{ $appointment->price }}
                                        <span>&euro;</span>
                                    </div>
                                </div>
                                @if ($appointment->discount_amount > 0)
                                    <div class="row col-12 mt-2"><div class="col-6 text-start">{{ __('promo.original_price') }}:</div><div class="col-6 text-end">{{ $appointment->original_price }} €</div></div>
                                    <div class="row col-12 mt-1 text-success"><div class="col-6 text-start">{{ __('promo.discount_amount') }} ({{ $appointment->promo_code }}):</div><div class="col-6 text-end">−{{ $appointment->discount_amount }} €</div></div>
                                @endif
                                @if ($appointment->service['price_can_change'])
                                    <div class="row col-12 mt-4">
                                        <h4 class="text-start">{{ __('msg.service_price_2') }}</h4>
                                        <p class="text-body-tertiary text-start fs-9">{{ __('msg.price_not_final') }}
                                        </p>
                                    </div>
                                @endif

                                <div class="row col-12 mt-4">
                                    <div class="col-12">
                                        <h4 class="text-start">{{ __('msg.cancel_or_change_booking') }}</h4>
                                        <p class="text-body-tertiary text-start">{{ __('msg.cancel_notice') }}</p>
                                    </div>
                                    <div class="col-12">
                                        <h6 class="text-start">{{ __('msg.cancel_deadline') }}</h6>
                                        <p class="text-body-tertiary text-start">{{ __('msg.cancel_deadline_notice') }}
                                        </p>
                                    </div>
                                    <div class="col-12">
                                        <h6 class="text-start">{{ __('msg.contact_us') }}</h6>
                                    </div>
                                    <div class="row col-12">
                                        <div class="col-6 text-start">
                                            <p class="text-body-tertiary mb-1"><span
                                                    class="fc_main">{{ __('msg.master') }}</span></p>
                                        </div>
                                        <div class="col-6 text-end fw-semibold">{{ $master->name }}</div>
                                    </div>
                                    @if ($master->phone)
                                        <div class="row col-12">
                                            <div class="col-6 text-start">
                                                <p class="text-body-tertiary mb-1"><span
                                                        class="fc_main">{{ __('msg.phone_label') }}</span></p>
                                            </div>
                                            <div class="col-6 text-end"><a class="fc_secondary fw-semibold"
                                                    href="tel:{{ $master->phone }}">{{ $master->phone }}</a></div>
                                        </div>
                                    @endif
                                    @if ($master->email)
                                        <div class="row col-12">
                                            <div class="col-6 text-start">
                                                <p class="text-body-tertiary mb-1"><span class="fc_main">Email:</span>
                                                </p>
                                            </div>
                                            <div class="col-6 text-end"><a class="fc_secondary fw-semibold"
                                                    href="mailto:{{ $master->email }}">{{ $master->email }}</a></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    @endpush
</x-app-layout>
