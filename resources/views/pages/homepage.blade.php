@section('title', ($settings['company_name'] ?? config('app.name')).' — '.__('msg.services'))

<x-app-layout>
  <section class="py-10 overflow-hidden">
    <div class="container">
      <div class="container_title mb-4">
        <h1 class="fw-semibold fc_secondary fs-2">{{ __('msg.services') }}</h1>
      </div>
      <div class="services row">
        @foreach ($services->take(6) as $item)
          <div class="col-12 {{ $loop->remaining == 0 && $loop->count % 2 != 0 ? '' : 'col-lg-6' }} mb-8">
            <div class="service d-flex flex-row justify-content-between gap-3">
              @if(($settings['show_service_images'] ?? true))
                <x-ui.service-image :service="$item" :alt="$item->translation['name'] ?? $item['name']" :width="360" :height="240" class="rounded-3 flex-shrink-0" style="width:clamp(96px,18vw,180px);aspect-ratio:3/2" />
              @endif
              <div class="service_name w-100">
                <div class="service_name__title fs-6 fs-lg-5 fc_main ff_primary fw-semibold mt-2">
                  {{ $item->translation['name'] ?? $item['name'] }}
                </div>
                @if ($item['short_description'])
                  <div class="service_name__desc fs-9 fs-lg-8 fc_secondary pe-8 pe-lg-0">
                    {{ Str::words($item->translation['short_description'] ?? $item['short_description'], 20, '...') }}
                  </div>
                @endif
              </div>
              <div class="service_price fs-7 text-nowrap pt-1">
                <div class="price fc_main">
                  @if ($item['price_can_change'])<span>{{ __('msg.price_from') }} </span>@endif
                  {{ $item['effective_price'] }} <span>&euro;</span>
                </div>
                <a class="btn_primary__opacity text-center fs-9 ff_secondary" href="{{ route('serviceBooking', ['service' => $item['id']]) }}">{{ __('msg.book') }}</a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
      <div class="d-flex m-auto w-80 w-xl-30">
        <a class="btn_primary text-center w-100 fs-7 fs-xl-6 ff_secondary" href="{{ route('clientservice') }}">{{ __('msg.all_services') }}</a>
      </div>
    </div>
  </section>

  <section class="py-10 overflow-hidden bg-body-highlight">
    <div class="container">
      <div class="container_title contacts mb-5">
        <h2 class="fw-semibold fc_secondary fs-3 fs-md-2">{{ __('msg.contact_information') }}</h2>
      </div>
      <div class="row g-4 justify-content-center">
        @if(!empty($settings['company_address']))
          <div class="col-12 col-md-4 text-center"><i class="fa-solid fa-location-dot fs-5 fc_primary mb-3"></i><p class="ff_secondary">{{ $settings['company_address'] }}</p></div>
        @endif
        @if(!empty($settings['company_phone']))
          <div class="col-12 col-md-4 text-center"><i class="fa-solid fa-phone fs-5 fc_primary mb-3"></i><p><a class="ff_secondary fc_main" href="tel:{{ $settings['company_phone'] }}">{{ $settings['company_phone'] }}</a></p></div>
        @endif
        @if(!empty($settings['company_email']))
          <div class="col-12 col-md-4 text-center"><i class="fa-solid fa-envelope fs-5 fc_primary mb-3"></i><p><a class="ff_secondary fc_main" href="mailto:{{ $settings['company_email'] }}">{{ $settings['company_email'] }}</a></p></div>
        @endif
      </div>
      <div class="text-center mt-5"><a class="btn_primary ff_secondary" href="{{ route('contacts') }}">{{ __('msg.menu_contact') }}</a></div>
    </div>
  </section>
</x-app-layout>
