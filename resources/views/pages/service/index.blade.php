@section('title', 'Teenused')
@push('styles')
@endpush
<x-app-layout>
    

    <section class="pt-4 pb-9 ff_secondary">
        <div class="container">
          <div class="mb-5 w-100">
            <div class="col-md-8 col-lg-9 w-100">
              <h1 class="mb-2 fw-semibold text-center ff_primary uppercase">{{ __('msg.services') }}</h1>
            </div>
          </div>
          <div class="row col-12 gy-4">
            @foreach ($services as $item)
              <div class="serviceDetailCard col-12 col-lg-6" data-scroll="{{ $loop->index }}">
                <div class="tab-content h-100">
                    <div class="row g-3 h-100">
                        @if(($settings['show_service_images'] ?? true))
                          <div class="col-12">
                            <img src="{{ $item->image_url }}" alt="{{ $item->translation['name'] ?? $item['name'] }}" width="1200" height="800" loading="lazy" decoding="async" class="w-100 rounded-3" style="aspect-ratio:3/2;object-fit:cover;max-height:320px">
                          </div>
                        @endif
                        <div class="col-lg-8 col-xxl-7">
                            <div class="row flex-lg-nowrap g-3 mb-2">
                                <div class="col-md-auto">
                                    <h4 class="mb-0 fw-semibold">
                                        @if (isset($item->translation['name']))
                                            {{ $item->translation['name'] }}
                                        @else
                                            {{ $item['name'] }}
                                        @endif
                                    </h4>
                                </div>
                            </div>

                        @if (!empty($item['next_rule']))
                        <p class="mb-0 mt-1 fs-9 text-danger fw-semibold">
                          <i class="fa-solid fa-triangle-exclamation me-1"></i>
                          {{ __('msg.service_changes_from') }}
                          {{ \Carbon\Carbon::parse($item['next_rule']['valid_from'])->format('d.m.Y') }}:
                          {{ $item['next_rule']['duration_minutes'] }} {{ __('msg.min') }},
                          {{ $item['next_rule']['price'] }} €
                        </p>
                        
                        @endif  
                            <p class="mb-0">
                                @if (isset($item->translation['short_description']))
                                    {{ Str::words($item->translation['short_description'], 20, '...') }}
                                @else
                                    {{ Str::words($item['short_description'], 20, '...') }}
                                @endif
                            </p>
                        </div>
                        <div class="col-lg-4 col-xxl-5 d-flex flex-row flex-lg-column align-items-end justify-content-between">
                            <h3 class="col-6 col-lg-12 d-flex align-items-center justify-content-lg-end gap-2 fw-semibold">
                                @if ($item['price_can_change'])
                                    <span>{{ __('msg.price_from') }} </span>
                                @endif
                                    {{ $item['effective_price'] }}
                                <span>&euro;</span>
                            </h3>
                            <div data-loop="{{ $loop->index }}" class="toggle-detail col-6 col-lg-12 btn_primary__opacity border_rounded text-center ff_secondary">
                                {{ __('msg.read_more') }}
                            </div>
                        </div>
                        
                        <div id="detail-{{ $loop->index }}" class="tab-content-detail col-12 mt-8">
                            @if (isset($item->translation['full_description']))
                                {!! $item->translation['full_description'] !!}
                            @else
                                {!! $item['full_description'] !!}
                            @endif
                            <div class="d-flex justify-content-between align-items-center col-12 mt-4">
                                <div class="col-6 text-start">
                                    <a href="{{ route('serviceBooking', ['service' => $item['id']]) }}" class="btn_primary border_rounded text-center ff_secondary">
                                        {{ __('msg.book') }}
                                    </a>

                                </div>
                                <div class="col-6 d-flex justify-content-end ">
                                    <div data-loop="{{ $loop->index }}" class="cursor-pointer close-detail btn_primary__opacity border_rounded text-center ff_secondary">
                                        {{ __('msg.close') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </section>
      
    @push('scripts')
      <script>
        $(document).ready(function () {
            $(".toggle-detail").click(function () {
                let index = $(this).data("loop"); 
                let scrollTarget = $("[data-scroll='" + index + "']");
                
                $(".tab-content-detail").removeClass("show");

                let target = $("#detail-" + index);
                target.addClass("show");

                let scrollPosition = scrollTarget.offset().top - (window.innerHeight * 0.2);
                $("html, body").animate({
                    scrollTop: scrollPosition
                }, 500); 
            });
            $(".close-detail").click(function () {
                let index = $(this).data("loop"); 
                let target = $("#detail-" + index);
                let scrollTarget = $("[data-scroll='" + index + "']");

                target.removeClass("show");
                let scrollPosition = scrollTarget.offset().top - (window.innerHeight * 0.2);
                $("html, body").animate({
                    scrollTop: scrollPosition
                }, 500); 
            });
        });
      </script>
    @endpush
</x-app-layout>
