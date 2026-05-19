@section('title', 'Galerii')
@push('styles')
<link href="{{ asset('public/vendors/glightbox/glightbox.min.css') }}" rel="stylesheet">
  <script src="
  https://cdn.jsdelivr.net/npm/before-after-slider@1.0.1/dist/slider.bundle.min.js
  "></script>
@endpush
<x-app-layout>
    
    <section class="pt-6 pb-9">
        <div class="container-medium">
          <div class="row g-2 g-sm-3">
            <div class="col-md-12">
              <div class="row g-2 g-sm-3">
                <div class="col-6 col-md-4 col-lg-3"><a href="{{ asset('assets/img/gallery/1.jpg') }}" class="img-fluid rounded-2 d-block h-100 overflow-hidden" data-gallery="default-gallery"><img class="h-100 w-100 object-fit-cover" src="{{ asset('assets/img/gallery/1.jpg') }}" alt="{{ asset('assets/img/gallery/1.jpg') }}"></a></div>
                <div class="col-6 col-md-4 col-lg-3"><a href="{{ asset('assets/img/gallery/1-1.jpg') }}" class="img-fluid rounded-2 d-block h-100 overflow-hidden" data-gallery="default-gallery"><img class="h-100 w-100 object-fit-cover" src="{{ asset('assets/img/gallery/1-1.jpg') }}" alt="{{ asset('assets/img/gallery/1-1.jpg') }}"></a></div>
                <div class="col-6 col-md-4 col-lg-3"><a href="{{ asset('assets/img/gallery/2.jpg') }}" class="img-fluid rounded-2 d-block h-100 overflow-hidden" data-gallery="default-gallery"><img class="h-100 w-100 object-fit-cover" src="{{ asset('assets/img/gallery/2.jpg') }}" alt="{{ asset('assets/img/gallery/2.jpg') }}"></a></div>
                <div class="col-6 col-md-4 col-lg-3"><a href="{{ asset('assets/img/gallery/2-1.jpg') }}" class="img-fluid rounded-2 d-block h-100 overflow-hidden" data-gallery="default-gallery"><img class="h-100 w-100 object-fit-cover" src="{{ asset('assets/img/gallery/2-1.jpg') }}" alt="{{ asset('assets/img/gallery/2-1.jpg') }}"></a></div>
                <div class="col-6 col-md-4 col-lg-3"><a href="{{ asset('assets/img/gallery/3.jpg') }}" class="img-fluid rounded-2 d-block h-100 overflow-hidden" data-gallery="default-gallery"><img class="h-100 w-100 object-fit-cover" src="{{ asset('assets/img/gallery/3.jpg') }}" alt="{{ asset('assets/img/gallery/3.jpg') }}"></a></div>
              </div>
            </div>
            
          </div>
        </div>
    </section>
      
    @push('scripts')
    <script src="{{ asset('public/vendors/glightbox/glightbox.min.js') }}"></script>
    @endpush
</x-app-layout>
