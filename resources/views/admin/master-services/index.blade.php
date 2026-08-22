@section('title', __('admin_staff.master_services'))
<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('member.index') }}">{{ __('admin_staff.employees') }}</a></li><li class="breadcrumb-item active">{{ __('admin_staff.master_services') }}</li></ol></nav>
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4"><div><h2 class="mb-1">{{ __('admin_staff.master_services') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_staff.master_services_hint') }}</p></div></div>
    <div class="card mb-4"><div class="card-body"><form method="GET" action="{{ route('admin.master-services.index') }}" class="row g-3 align-items-end"><div class="col-12 col-md"><label class="form-label" for="master_id">{{ __('admin_staff.master') }}</label><select class="form-select" id="master_id" name="master_id" required><option value="">{{ __('admin_staff.select_master') }}</option>@foreach($masters as $option)<option value="{{ $option->id }}" @selected($selectedMasterId === $option->id)>{{ $option->name }}{{ $option->username ? ' — '.$option->username : '' }}</option>@endforeach</select></div><div class="col-12 col-md-auto"><button class="btn btn-primary w-100" type="submit">{{ __('admin_staff.open_services') }}</button></div></form></div></div>
    @if($master)
      @include('admin.master-services._service-list', ['catalogUrl' => route('admin.master-services.index'), 'catalogMasterParameter' => true])
    @else
      <div class="alert alert-light border text-center py-5">{{ __('admin_staff.select_master_hint') }}</div>
    @endif
    <x-dashboard-footer />
  </div>
</x-dashboard-layout>
