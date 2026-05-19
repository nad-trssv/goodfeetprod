@section('title', 'GoodFeet - Kontakt andmed')
@push('styles')
<script src="
https://cdn.jsdelivr.net/npm/before-after-slider@1.0.1/dist/slider.bundle.min.js
"></script>
@endpush
<x-app-layout>
    
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
