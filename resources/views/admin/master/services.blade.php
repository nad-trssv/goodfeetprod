@section('title', __('admin_staff.my_services'))
<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item active">{{ __('admin_staff.my_services') }}</li></ol></nav>
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
      <div><h2 class="mb-1">{{ __('admin_staff.my_services') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_staff.master_services_hint') }}</p></div>
      @if(auth()->user()->hasAllAppointmentsScope())<a class="btn btn-outline-primary align-self-start" href="{{ route('admin.master-services.index') }}"><span class="fas fa-users-cog me-2"></span>{{ __('admin_staff.master_services') }}</a>@endif
    </div>
    @include('admin.master-services._service-list', [
      'master' => auth()->user(),
      'catalogUrl' => route('master.services.index'),
      'catalogMasterParameter' => false,
      'selfServiceManagement' => true,
    ])
    <x-dashboard-footer />
  </div>
</x-dashboard-layout>
