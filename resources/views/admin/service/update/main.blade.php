@section('title', 'Services')
  @push('styles')
    <link href="{{ asset('vendors/choices/choices.min.css') }}" rel="stylesheet" />
  @endpush
  <x-dashboard-layout>
    <div class="content">
      <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('service.index') }}">{{ __('admin_services.title') }}</a></li>
          <li class="breadcrumb-item active">{{ __('admin_services.edit_service') }}</li>
          <li class="breadcrumb-item"><a href="{{ route('languages.edit', $service['id']) }}">{{ __('admin_services.localization') }}</a></li>
        </ol>
      </nav>
      @if (session('success'))
      <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: @json(__('admin_service_extra.updated')),
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
      </script>
      @endif
      <h2 class="mb-4">{{ __('admin_services.edit_service') }}</h2>
      
      <div class="row g-4">
        <div class="col-12 col-xl-10 order-1 order-xl-0">
                    <div class="card shadow-none mb-4 mt-6">
                      <div class="card-body p-0">
                        <div class="p-4">
                          <div class="row">
                            <div class="col-12">
                              <form class="row g-3 mb-6 needs-validation" novalidate="" action="{{ route('service.update', $service['id']) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="col-12">
                                  <div class="form-floating">
                                    <input class="form-control" id="name" type="text" name="name" placeholder="{{ __('admin_services.name') }}" value="{{ $service['name'] }}"  required=""/>
                                    <label for="name">{{ __('admin_services.name') }}</label>
                                    <div class="invalid-feedback">{{ __('admin_services.required') }}</div>
                                  </div>
                                </div>
                                <div class="col-12">
                                  <div class="form-floating  form-switch">
                                    <div class="col-12">
                                      <input class="form-check-input" id="status" name="status" type="checkbox" value="1" @if ($service['status']) checked @endif />
                                      <label class="form-check-label me-6" for="status">{{ __('admin_services.publish') }}</label>
                                    </div>
                                  </div>
                                </div>
                    
                                <div class="col-sm-6 col-md-4">
                                  <div class="form-floating">
                                    <h5 class="mb-3 text-body-highlight">{{ __('admin_services.price') }} (&euro;)</h5>
                                    <div class="col-12">
                                      <input class="form-control" id="price" name="price" type="number" placeholder="{{ __('admin_services.price') }}" value="{{ $service['price'] }}" required step="0.01" />
                                      <div class="invalid-feedback">{{ __('admin_services.required') }}</div>
                                      <div class="mt-2 form-switch">
                                        <input class="form-check-input" id="price_can_change" name="price_can_change" type="checkbox" value="1" @if ($service['price_can_change']) checked @endif />
                                        <label class="form-check-label me-6" for="price_can_change">{{ __('admin_services.variable_price') }}</label>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                  <div class="form-floating">
                                    <h5 class="mb-3 text-body-highlight">{{ __('admin_services.duration_minutes') }}</h5>
                                    <div class="col-12">
                                      <input class="form-control" id="duration_minutes" name="duration_minutes" type="number" placeholder="{{ __('admin_services.time_minutes') }}" value="{{ $service['duration_minutes'] }}" required />
                                      <div class="invalid-feedback">{{ __('admin_services.required') }}</div>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                  <div class="form-floating">
                                    <h5 class="mb-3 text-body-highlight">{{ __('admin_services.select_color') }}</h5>
                                    <div class="col-12">
                                      <input type="color" name="eventColor" id="eventColor" class="form-control" value="{{ $service['eventColor'] }}" required>
                                    </div>
                                  </div>
                                </div>
                    
                                <div class="col-12 gy-6">
                                  @can('is-superadmin')
                                  {{-- Администратор видит всех мастеров --}}
                                  <div class="form-floating form-floating-advance-select">
                                    <label>{{ __('admin_services.employees') }}</label>
                                    <select class="form-select" id="masters" name="masters[]" data-choices="data-choices" multiple="multiple"
                                            data-options='{"removeItemButton":true,"placeholder":true}' multiple required>
                                      @foreach ($masters as $master)
                                        <option value="{{ $master['id'] }}" @if(in_array($master['id'], $choosedMasters)) selected @endif>
                                          {{ $master['name'] }}
                                        </option>
                                      @endforeach
                                    </select>
                                    <div class="invalid-feedback">{{ __('admin_services.select_employee') }}</div>
                                  </div>
                                  @else
                                  {{-- Мастер видит только себя --}}
                                  @php $myId = auth()->user()->id; @endphp
                                  <div class="card bg-light p-3">
                                    <h6 class="mb-2">{{ __('admin_services.my_participation') }}</h6>
                                    <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="masters[]"
                                            value="{{ $myId }}" id="my_master"
                                            {{ in_array($myId, $choosedMasters) ? 'checked' : '' }}>
                                      <label class="form-check-label" for="my_master">
                                        {{ __('admin_service_extra.i_provide') }}
                                      </label>
                                    </div>
                                    {{-- Сохраняем остальных мастеров скрытыми полями --}}
                                    @foreach($choosedMasters as $masterId)
                                      @if($masterId != $myId)
                                        <input type="hidden" name="masters[]" value="{{ $masterId }}">
                                      @endif
                                    @endforeach
                                  </div>
                                  @endcan
                                </div>
                                <div class="col-12 gy-6">
                                  <label class="form-label fw-semibold" for="image">{{ __('admin_services.image') }}</label>
                                  <div class="border rounded-3 p-2 mb-2 bg-body-tertiary" style="max-width:360px">
                                    <x-ui.service-image :service="$service" :alt="$service['name']" :width="1200" :height="800" class="img-fluid rounded-2" style="width:100%;aspect-ratio:3/2" />
                                  </div>
                                  <input class="form-control @error('image') is-invalid @enderror" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                                  @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                  <div class="form-text">{{ __('admin_services.replace_image_hint',['hint'=>__('admin_services.image_hint')]) }}</div>
                                </div>
                                <div class="col-12 gy-6">
                                  <div class="form-floating">
                                    <textarea class="form-control" id="short_description" name="short_description" placeholder="{{ __('admin_services.short_description') }}" style="height: 100px">{{ $service['short_description'] }}</textarea>
                                    <label for="short_description">{{ __('admin_services.short_description') }}</label>
                                  </div>
                                </div>
                                <div class="col-12 gy-6">
                                  <label for="full_description">{{ __('admin_services.description') }}</label>
                                  <div class="form-floating">
                                    <textarea class="tinyarea" name="full_description">{{ $service['full_description'] }}</textarea>
                                  </div>
                                </div>
                                
                                <div class="col-12 gy-6">
                                  <div class="card shadow-none mb-4 mt-6">
                                    <div class="col-auto">
                                      <button type="submit" class="btn btn-primary w-100">{{ __('admin_services.save') }}</button>
                                    </div>
                                  </div>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
        </div>
        <div class="col-12 col-xl-2">
          <div class="position-sticky mt-xl-4 h-[100vh]" style="top: 80px;">
            <h5 class="lh-1">{{ __('admin_services.actions') }}</h5>
            <hr>
            <ul class="nav nav-vertical flex-column doc-nav" data-doc-nav="data-doc-nav">
              <li class="nav-item"> 
                <a class="btn btn-primary w-100 my-2 py-1" href="{{ route('languages.edit', $service['id']) }}">{{ __('admin_services.translation') }}</a>
              </li>
              <li class="nav-item"> 
                <a class="btn border border-warning-dark text-warning-dark w-100 my-2 py-1" href="{{ route('fixedtime.edit', $service['id']) }}">{{ __('admin_services.fixed_time') }}</a>
              </li>
              <li class="nav-item"> 
                <a class="btn border border-info text-info w-100 my-2 py-1" href="{{ route('service.rules.edit', $service['id']) }}">
                  {{ __('admin_service_extra.date_rules') }}
                </a>
              </li>
              
              <li class="nav-item"> 
                <form method="POST" action="/service/{{ $service['id'] }}/toggle-status" style="display:inline;">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="btn border border-success text-success w-100 my-2 py-1">{{ __('admin_services.change_status') }}</button>
                </form>
              </li>
              <li class="nav-item"> 
                <button class="btn border border-danger text-danger w-100 my-2 py-1" data-item="{{ $service['name'] }}" data-id="{{ $service['id'] }}" type="button" data-bs-toggle="modal" data-bs-target="#verticallyCentered">{{ __('admin_services.delete') }}</button>
              </li>
            </ul>
            <hr>
          </div>
        </div>
      </div>
      <div class="modal fade" id="verticallyCentered" tabindex="-1" aria-labelledby="verticallyCenteredModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="verticallyCenteredModalLabel">{{ __('admin_services.delete_confirm') }}</h5>
              <button class="btn btn-close p-1" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p class="text-body-tertiary lh-lg mb-0">{!! str_replace('__SERVICE__','<span id="removeItem"></span>',e(__('admin_services.deleting',['name'=>'__SERVICE__']))) !!}</p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-primary" type="button" data-bs-dismiss="modal">{{ __('admin_services.cancel') }}</button>
              <form method="POST" action="/service/{{ $service['id'] }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-primary">{{ __('admin_services.delete') }}</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <x-dashboard-footer />
    </div>
    @push('scripts')
      <script src="{{ asset('vendors/choices/choices.min.js') }}"></script>
      <script src="{{ asset('assets/js/tiny.js') }}"></script>
      <script>
        $(document).ready(function() {
          
          const confirmButton = document.getElementById("confirmButton");
          const removeItemSpan = document.getElementById('removeItem');
  
          $(document).on('click', '.service_destroy', function() {
              var dataItem = $(this).data('item'); 
              var dataId = $(this).data('id');
              removeItemSpan.textContent = dataItem;
              confirmButton.addEventListener("click", () => {
                removeItemSpan.textContent = '';
            });
          });
        });
      </script>
    @endpush
  </x-dashboard-layout>
