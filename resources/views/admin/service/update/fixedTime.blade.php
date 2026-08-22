@section('title', 'Services')
  @push('styles')
    <link href="vendors/flatpickr/flatpickr.min.css" rel="stylesheet" />
  @endpush
  <x-dashboard-layout>
    <div class="content">
      <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('service.index') }}">{{ __('admin_services.title') }}</a></li>
          <li class="breadcrumb-item"><a href="{{ route('service.show', $service['id']) }}">{{ __('admin_services.edit') }}</a></li>
          <li class="breadcrumb-item active">{{ __('admin_services.fixed_time') }}</li>
        </ol>
      </nav>
      
      @if (session('success'))
        <script>
          document.addEventListener("DOMContentLoaded", function() {
              Swal.fire({
                  title: @json(__('admin_common.updated')),
                  text: "{{ session('success') }}",
                  icon: 'success',
                  confirmButtonText: 'OK'
              });
          });
        </script>
      @endif

      <form action="{{ route('fixedtime.update', $service['id']) }}" method="POST">
        @csrf
        @method('PUT') 
      <div class="row g-4">
        <div class="col-12 col-xl-10 order-1 order-xl-0">
                    <div class="card shadow-none border mb-4 mt-6" data-component-card="data-component-card">
                      <div class="card-header p-4 border-bottom bg-body">
                        <div class="row g-3 justify-content-between align-items-center">
                          <div class="col-12 col-md">
                            <h4 class="text-body mb-0">{{ __('admin_services.fixed_time') }}</h4>
                          </div>
                        </div>
                      </div>
                      <div class="card-body p-0">
                        <div class="p-4">
                          <div class="row">
                            <div class="col-12 mb-4">
                              <div class="form-floating  form-switch">
                                <div class="col-12">
                                  <input class="form-check-input" id="has_fixed_time" name="has_fixed_time" type="checkbox" value="1" @if ($service['has_fixed_time']) checked @endif />
                                  <label class="form-check-label me-6" for="has_fixed_time">{{ __('admin_services.publish') }}</label>
                                </div>
                              </div>
                            </div>
                            <div id="showTime" class="row col-12">
                              <div class="col-6">
                                <label class="form-label" for="start">{{ __('admin_services.time_from') }}</label>
                                <input class="form-control datetimepicker flatpickr-input" type="text" name="time_from" placeholder="hour : minute"  value="{{ $service['time_from'] }}"
                                  data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true }' readonly>
                              </div>
                              <div class="col-6">
                                <label class="form-label" for="start">{{ __('admin_services.time_to') }}</label>
                                <input class="form-control datetimepicker flatpickr-input" type="text" name="time_to" placeholder="hour : minute"  value="{{  $service['time_to'] }}"
                                  data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true,"time_24hr":true }' readonly>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
            <div class="card shadow-none mb-4 mt-6">
                <div class="col-auto">
                  <button type="submit" class="btn btn-primary w-100 mt-2">{{ __('admin_services.save') }}</button>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-2">
          <div class="position-sticky mt-xl-4 h-[100vh]" style="top: 80px;">
            <ul class="nav nav-vertical flex-column doc-nav" data-doc-nav="data-doc-nav">
              <li class="nav-item">
                <button type="submit" class="btn btn-primary w-100 my-2 py-1">{{ __('admin_services.save_changes') }}</button>
              </li>
              <li class="nav-item"> 
                <a class="btn border border-secondary text-secondary w-100 my-2 py-1" href="{{ route('service.show', $service['id']) }}">{{ __('admin_services.back_edit') }}</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </form>
      <x-dashboard-footer />
    </div>
    @push('scripts')
      <script src="vendors/flatpickr/flatpickr.min.js"></script>
      <script>
        document.addEventListener("DOMContentLoaded", function () {
            let statusCheckbox = document.getElementById("has_fixed_time");
            let showTimeBlock = document.getElementById("showTime");
        
            function toggleShowTime() {
                if (statusCheckbox.checked) {
                    showTimeBlock.style.display = "flex";
                } else {
                    showTimeBlock.style.display = "none";
                }
            }
        
            toggleShowTime();
            statusCheckbox.addEventListener("change", toggleShowTime);
        });
      </script>
    @endpush
  </x-dashboard-layout>
