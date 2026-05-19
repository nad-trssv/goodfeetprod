@section('title', 'GoodFeet - кабинет медицинского педикюра')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant:ital,wght@0,300..700;1,300..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link href="{{ asset('public/vendors/swiper/swiper-bundle.min.css') }}" rel="stylesheet" />
@endpush
@section('preloader')
  <div id="loading">
    @include('components.preloader')
  </div>
@endsection
<x-app-layout>
    <div class="booking-hero-header d-flex align-items-center py-8">
      <div class="container-medium position-relative z-5">
        <h1 class="fw-lighter mb-6 overflow-hidden w-80 mx-auto text-center fs_page_title">{{ __('msg.title') }}<span class="fc_secondary">{{ __('msg.title_span') }}</span><br/> {{ __('msg.title_2') }}</h1>
        <h3 class="text-secondary fs-7 fs-md-6 fs-xl-5 fw-medium mb-3 w-70 mx-auto text-center fs_page_subtitle">{{ __('msg.specialized_treatment') }}</h3>
      </div>
    </div>
    <div class="booking-hero-header d-flex align-items-center">
      <div class="bg-holder" style="background-image:url({{ asset('assets/img/homepage/banner.webp') }});">
      </div>
      <div class="certified_bl position-absolute bottom-100">
        <img src="{{ asset('assets/img/homepage/svg/certified.svg') }}" alt="certified">
      </div>
    </div>  
    <!-- ============================================-->
    <!-- <section> about ============================-->
    <section class="pt-6 pt-md-10 pb-10 of_hidden">
      <div class="container-medium">
        <div class="container_title">
            <h2 class="fw-semibold fc_primary fs-4 fs-xl-2">{{ __('msg.medical_pedicure_health_and_aesthetics') }}</h2>
            <h3 class="fw-semibold w-80 fs-7 fs-md-6 fs-xl-5">{{ __('msg.goodfeet_specialized') }}</h3>
        </div>
      </div>
      <div class="about_images d-flex justify-content-between gap-10 pb-10">
        <div class="bl w-30">
          <img class="about_image_left" src="{{ asset('assets/img/homepage/goodfeet-item-2.webp') }}" alt="GoodFeet">
        </div>
        <div class="bl w-30 d-flex justify-content-center">
            {{-- <img class="about_image_center" src="{{ asset('assets/img/homepage/svg/foot.svg') }}" alt="" height="300px"> --}}
          <div class="">
            <h2 class="about_image_center_title fs-7 fs-sm-5 fs-md-4 fs-xl-3">
              <span>10+</span>
              {{ __('msg.years_of_experience') }}
            </h2>
          </div>
        </div>
        <div class="bl w-30">
          <img class="about_image_right" src="{{ asset('assets/img/homepage/goodfeet-item.webp') }}" alt="">
        </div>
      </div>
    </section>
    <!-- <section> about ============================-->
    <!-- ============================================-->

    <!-- ============================================-->
    <!-- <section> benefits ============================-->
    <section class="py-10 overflow-hidden benefits_section bg_primary">
      <div class="container-medium">
        <div class="position-relative">
          <div id="benefits" class="benefits d-flex flex-row no-wrap w-100">
            <div class="benefit white-space-nowrap mx-3 text-center bg_primary d-flex align-items-center">
              <i class="fa-solid fa-hand-sparkles pe-2 "></i> {{ __('msg.quality') }}
              <div class="gleam"><img src="{{ asset('assets/img/homepage/svg/gleam-light.svg') }}" alt=""></div>
            </div>
            <div class="fs-6 fs-md-4 col-xl-2 benefit white-space-nowrap mx-3 text-center bg_primary d-flex align-items-center">
              <i class="fa-solid fa-shield-heart pe-2 "></i> {{ __('msg.safety') }}
              <div class="gleam"><img src="{{ asset('assets/img/homepage/svg/gleam-light.svg') }}" alt=""></div>
            </div>
            <div class="fs-6 fs-md-4 col-xl-2 benefit white-space-nowrap mx-3 text-center bg_primary d-flex align-items-center">
              <i class="fa-solid fa-leaf pe-2 "></i>{{ __('msg.sterility') }}
            </div>
            <div class="fs-6 fs-md-4 col-xl-2 benefit white-space-nowrap mx-3 text-center bg_primary d-flex align-items-center">
              <i class="fa-solid fa-handshake-simple pe-2 "></i>{{ __('msg.individual_approach') }}
              <div class="gleam"><img src="{{ asset('assets/img/homepage/svg/gleam-light.svg') }}" alt=""></div>
            </div>
            <div class="fs-6 fs-md-4 col-xl-2 benefit white-space-nowrap mx-3 text-center bg_primary d-flex align-items-center">
              <i class="fa-solid fa-graduation-cap pe-2 "></i>{{ __('msg.experienced_specialists') }}
              <div class="gleam"><img src="{{ asset('assets/img/homepage/svg/gleam-light.svg') }}" alt=""></div>
            </div>
            <div class="fs-6 fs-md-4 col-xl-2 benefit white-space-nowrap mx-3 text-center bg_primary d-flex align-items-center">
              <i class="fa-solid fa-brain pe-2 "></i>{{ __('msg.solving_complex_problems') }}
            </div>
            <div class="fs-6 fs-md-4 col-xl-2 benefit white-space-nowrap mx-3 text-center bg_primary d-flex align-items-center">
              <i class="fa-solid fa-face-smile-beam pe-2 "></i>{{ __('msg.comfort_and_painlessness') }}
              <div class="gleam"><img src="{{ asset('assets/img/homepage/svg/gleam-light.svg') }}" alt=""></div>
            </div>
            <div class="fs-6 fs-md-4 col-xl-2 benefit white-space-nowrap mx-3 text-center bg_primary d-flex align-items-center">
              <i class="fa-solid fa-people-arrows pe-2 "></i>{{ __('msg.individual_approach') }}
              <div class="gleam"><img src="{{ asset('assets/img/homepage/svg/gleam-light.svg') }}" alt=""></div>
            </div>
            <div class="fs-6 fs-md-4 col-xl-2 benefit white-space-nowrap mx-3 text-center bg_primary d-flex align-items-center opacity-0" style="width: 500px"></div>
          </div>
        </div>
      </div>
    </section>
    <!-- <section> benefits =========================-->
    <!-- ============================================-->
    
    <!-- ============================================-->
    <!-- <section> discounts ============================
      <section class="py-10 overflow-hidden">
        <div class="container">
          <div class="container_title">
              <div class="extra mx-auto">
                <h1 class="fw_regular fc_primary w-min-content fs-1"><span>10%</span> OFF</h1>
              </div>
              <h2 class="fw-semibold fc_main fs-3 fs-md-2 fs-xl-2">{{ __('msg.special_discounts') }}</h2>
              <h3 class="fw-semibold w-70 w-md-50 fs-7 fs-md-6 fs-xl-5">{{ __('msg.we_value_every_client') }}</h3>
          </div>
          <div class="discounts row col-12 justify-content-center gy-4 mt-4">
            <div class="col-12 col-lg-6 discount h-fit">
              <div class="icon mb-2"><i class="fa-solid fa-person-cane"></i></div>
              <div class="percent fw-bold fc_secondary mb-2">10% off</div>
              <div class="title fs-7 fs-md-6 fs-xl-5 fc_secondary ff_secondary fw-semibold mb-1">{{ __('msg.discount_for_seniors') }}</div>
              <div class="description ff_secondary fs-9 fs-md-8 fs-xl-7">{{ __('msg.senior_discount_description') }}</div>
            </div>
            <div class="col-12 col-lg-6 discount h-fit">
              <div class="icon mb-2"><i class="fa-solid fa-children"></i></div>
              <div class="percent fw-bold fc_secondary mb-2">10% off</div>
              <div class="title fs-7 fs-md-6 fs-xl-5 fc_secondary ff_secondary fw-semibold mb-1">{{ __('msg.discount_for_children') }}</div>
              <div class="description ff_secondary fs-9 fs-md-8 fs-xl-7">{{ __('msg.children_discount_description') }}</div>
            </div>
          </div>
        </div>
        <div class="text-center mt-8 fs-9 fs-md-8 ff_secondary"><span class="fw-bold">{{ __('msg.note') }}</span> {{ __('msg.sos_services_excluded') }}</div>
      </section>
     <section> discounts =========================-->
    <!-- ============================================-->

    
    <!-- ============================================-->
    <!-- <section> services  ============================-->
      <section class="py-10 overflow-hidden">
        <div class="container">
          <div class="container_title mb-4">
              <h2 class="fw-semibold fc_secondary fs-2 fs-md-2 fs-xl-2">{{ __('msg.services') }}</h2>
          </div>
            <div class="services row">
              @foreach ($services->take(6) as $item)
                <div class="col-12 {{ $loop->remaining == 0 && $loop->count % 2 != 0 ? '' : 'col-lg-6' }} mb-8">
                <div class="service d-flex flex-row justify-content-between">
                  <div class="service_name w-100">
                    <div class="service_name__title fs-6 fs-md-6 fs-lg-5 fc_main ff_primary fw-semibold mt-2">
                      @if (isset($item->translation['name']))
                        {{ $item->translation['name'] }}
                      @else
                        {{ $item['name'] }}
                      @endif
                    </div>
                    @if ($item['short_description'])
                      <div class="service_name__desc fs-9 fs-md-9 fs-lg-8 fs-xl-8 fc_secondary pe-8 pe-lg-0">
                        @if (isset($item->translation['short_description']))
                          {{ Str::words($item->translation['short_description'], 20, '...') }}
                        @else
                          {{ Str::words($item['short_description'], 20, '...') }}
                        @endif
                      </div>
                    @endif
                  </div>
                  <div class="service_price fs-7 text-nowrap pt-1">
                    <div class="price fc_main">
                      @if ($item['price_can_change'])
                        <span>{{ __('msg.price_from') }} </span>
                      @endif
                      {{ $item['effective_price'] }}
                      <span>&euro;</span>
                    </div>
                    <div class="d-flex">
                      <a class="btn_primary__opacity text-center fs-9 ff_secondary" href="{{ route('serviceBooking', ['service' => $item['id']]) }}">{{ __('msg.book') }}</a>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
            <div class="d-flex m-auto w-80 w-xl-30">
              <a class="btn_primary text-center w-100 fs-7 fs-xl-6 ff_secondary" href="{{  route('clientservice') }}">{{ __('msg.all_services') }}</a>
            </div>
        </div>
      </section>
    <!-- <section> services =========================-->
    <!-- ============================================-->

    <!-- ============================================-->
    <!-- <section> Booking Cards ============================-->
      <section class="py-10 overflow-hidden">
        <div class="container">
          <div class="bookingCards">
            <div class="bookingCards_img">
              <img src="{{ asset('assets/img/homepage/foot2.png') }}" alt="GoodFeet">
            </div>
            <div class="cards row col-12 h-100">
              <div class="cards_item col-12 col-lg-4">
                <div class="card h-100 w-100 bg_secondary px-4 py-4 border_0 border_radius" style="background: #6d5149e8;">
                  <div class="gleam"><img src="{{ asset('assets/img/homepage/svg/gleam-light.svg') }}" alt=""></div>
                  <div class="card_top mb-8">
                    <div class="title fs-5 text-center fw-bold fc_light f_variant__small mb-2 lh_normal">
                      {{ __('msg.trust_professionals') }}
                    </div>
                    <div class="subtitle fc_light text-center fs-7 f_variant__small text_opacity mb-2 lh_normal">{{ __('msg.book_today') }}</div>
                    <ul class="list fc_light mt-4">
                      <li class="fw_regular fs-7">{{ __('msg.card1_li1') }}</li>
                      <li class="fw_regular fs-7">{{ __('msg.card1_li2') }}</li>
                      <li class="fw_regular fs-7">{{ __('msg.card1_li3') }}</li>
                      <li class="fw_regular fs-7">{{ __('msg.card1_li4') }}</li>
                    </ul> 
                  </div>
                  <div class="card_bottom">
                    <div class="btn w-100 p-0 mb-2"><a  href="{{ route('serviceBooking') }}" target="_blank" class="card1_btn_1 fs-8 ff_secondary fw_regular fc_light"><i class="fa-solid fa-calendar me-2"></i> {{ __('msg.card1_btn1') }}</a></div>
                    <div class="btn w-100 p-0"><a  href="{{ route('clientservice') }}" class="card1_btn_2 fs-7 ff_secondary fw_regular">{{ __('msg.card1_btn2') }}</a></div>
                  </div>
                </div>
              </div>
              <div class="cards_item col-12 col-lg-4">
                <div class="card h-100 w-100 bg_primary px-4 py-4 border_0 border_radius">
                  <div class="gleam"><img src="{{ asset('assets/img/homepage/svg/gleam-dark.svg') }}" alt=""></div>
                  <div class="card_top mb-8">
                    <div class="title fs-5 text-center fw-bold fc_dark f_variant__small mb-2 lh_normal">{{ __('msg.not_sure') }}</div>
                    <div class="subtitle fc_dark text-center fs-7 f_variant__small text_opacity mb-2 lh_normal">{{ __('msg.consult_specialists') }}</div>
                    <ul class="list fc_dark mt-4">
                      <li class="fw_regular fs-7">{{ __('msg.card2_li1') }}</li>
                      <li class="fw_regular fs-7">{{ __('msg.card2_li2') }}</li>
                      <li class="fw_regular fs-7">{{ __('msg.card2_li3') }}</li>
                    </ul> 
                  </div>
                  <div class="card_bottom">
                    <div class="btn w-100 p-0"><a href="{{ route('serviceBooking') }}" target="_blank" class="card1_btn_black fs-7 ff_secondary fw_regular">{{ __('msg.card2_btn') }}</a></div>
                  </div>
                </div>
              </div>
              <div class="cards_item col-12 col-lg-4">
                <div class="card h-100 w-100 bg_bonus px-4 py-4 border_0 border_radius">
                  <div class="gleam"><img src="{{ asset('assets/img/homepage/svg/gleam-dark.svg') }}" alt=""></div>
                  <div class="card_top mb-8">
                    <div class="title fs-5 text-center fw-bold fc_dark f_variant__small mb-2 lh_normal">{{ __('msg.sos_service') }}</div>
                    <div class="subtitle fc_dark text-center fs-7 f_variant__small text_opacity mb-2 lh_normal">{{ __('msg.sos_service_description') }}</div>
                    <ul class="list fc_dark mt-4">
                      <li class="fw_regular fs-7">{{ __('msg.card3_li1') }}</li>
                      <li class="fw_regular fs-7">{{ __('msg.card3_li2') }}</li>
                      <li class="fw_regular fs-7">{{ __('msg.card3_li3') }}</li>
                    </ul>
                  </div>
                  <div class="card_bottom">
                    <div class="extra ff_secondary text-center d-flex justify-content-center fc_main fs-8">
                      <span class="me-2"><i class="fa-solid fa-bell fs-8"></i></span>
                      <p>{{ __('msg.available_only_at') }}</p>
                    </div>
                    <div class="btn w-100 p-0"><a href="{{ route('serviceBooking') }}" target="_blank" class="card1_btn_black fs-7 ff_secondary fw_regular">{{ __('msg.card3_btn') }}</a></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    <!-- <section> Booking Cards =========================-->
    <!-- ============================================-->

    <!-- ============================================-->
    <!-- <section> Contacts  ============================-->
      <section class="py-10 overflow-hidden">
        <div class="container">
          <div class="container_title contacts mb-4">
              <h2 class="fw-semibold fc_secondary fs-3 fs-md-2 fs-xl-2">{{ __('msg.visit_medical_pedicure_office1') }} <br />{{ __('msg.visit_medical_pedicure_office2') }}</h2>
              <h3 class="fw-semibold fc_main fs-7 fs-md-6 fs-xl-5">{{ __('msg.friendly_team_ready_to_help') }}</h3>
          </div>
          <div class="contacts_grid d-flex row justify-content-center gap-4">
            <div class="contacts row col-12 align-items-center gy-4">
              <div class="contact_content col-12 col-lg-6 mb-8">
                <h2 class="address  fs-7 fs-md-6 fs-xl-5 mb-8 ff_secondary fw-light">{{ $settings['company_address'] }}</h2>
                <h3 class="room fs-7 fs-md-6 fs-xl-5 text-center ff_secondary fw-light">{{ __('msg.room') }}: <span class="bg_primary fw-light border_radius mx-4">D1053</span></h3>
              </div>
              <div class="rounded_content col-12 col-lg-6 mb-8">
                <img src="{{ asset('assets/img/homepage/hospital.webp') }}" alt="GoodFeetAddress">
              </div>
            </div>
            <div class="contacts contact_map">
              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3415.2464287575613!2d27.294019073067258!3d59.40442262468937!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46947085ae596251%3A0xc5639019e576cbca!2z0J_QvtC70LjQutC70LjQvdC40LrQsCDQr9GA0LLQtdGB0LrQvtC5INGH0LDRgdGC0Lgg0LPQvtGA0L7QtNCwINCa0L7RhdGC0LvQsC3Qr9GA0LLQtQ!5e0!3m2!1sru!2see!4v1742733427402!5m2!1sru!2see" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <div class="contacts row col-12 align-items-center">
              <div class="rounded_content contact_rounded_content col-12 col-lg-7 bg_primary px-8 py-4">
                <h2 class="address fs-5 fs-md-4 mb-4 fc_primary">{{ __('msg.contact_information') }}</h2>
                <div class="contact_list">
                  <div class="contact">
                    <p class="title fc_primary fs-7 fs-md-6 fw-bold mb-1"><i class="fa-solid fa-location-dot me-2"></i>{{ __('msg.address') }}</p>
                    <p class="desc fc_light fs-9 fs-md-8 fs-xl-7 ff_secondary">{{ $settings['company_address'] }}</p>
                  </div>
                  <div class="contact">
                    <p class="title fc_primary fs-7 fs-md-6 fs-xl-6 fw-bold mb-1"><i class="fa-solid fa-phone me-2"></i>{{ __('msg.phone') }}</p>
                    <a href="tel:{{ $settings['company_phone'] }}" class="desc fc_light fs-9 fs-md-8 fs-xl-7 ff_secondary">{{ __('msg.phone_info') }}: {{ $settings['company_phone'] }}</a>
                  </div>
                  <div class="contact">
                    <p class="title fc_primary fs-7 fs-md-6 fw-bold mb-1"><i class="fa-solid fa-clock me-2"></i>{{ __('msg.working_hours') }}</p>
                    <p class="desc fc_light fs-9 fs-md-8 fs-xl-7 ff_secondary">
                      {{ __('msg.monday_to_friday') }} <br />
                      {{ __('msg.saturday_to_sunday') }}
                    </p>
                  </div>
                  <div class="contact">
                    <p class="title fc_primary fs-7 fs-md-6 fw-bold mb-1"><i class="fa-solid fa-envelope me-2"></i>Email</p>
                    <a href="mailto:{{ $settings['company_email'] }}" class="desc fc_light fs-9 fs-md-8 fs-xl-7 ff_secondary">{{ $settings['company_email'] }}</a>
                  </div>
                </div>
              </div>
              <div class="contact_content col-12 col-lg-5 mt-8 mt-lg-0">
                <div class="container_title mb-4">
                    <h2 class="fw-semibold fc_secondary">{{ __('msg.follow_us_on_social_media') }}</h2>
                    <h3 class="fw-semibold fc_main w-80">{{ __('msg.daily_inspiration_and_news') }}</h3>
                </div>
                <div class="d-flex col justify-content-center gap-4">
                  <a href="{{ $socials['social_media_facebook'] }}" target="_blank">
                    <img src="{{ asset('assets/img/homepage/svg/facebook.svg') }}" alt="GoodFeetFacebook" height="60">
                  </a>
                  <a href="{{ $socials['social_media_instagram'] }}" target="_blank">
                    <img src="{{ asset('assets/img/homepage/svg/instagram.svg') }}" alt="GoodFeetInstagram" height="60">
                  </a>
                </div>    
              </div>
            </div>
          </div>
        </div>
      </section>
    <!-- <section> Contacts =========================-->
    <!-- ============================================-->

    @push('scripts')
    @endpush
</x-app-layout>
