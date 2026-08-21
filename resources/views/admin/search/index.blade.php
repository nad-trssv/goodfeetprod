@section('title', __('admin_search.title'))
<x-dashboard-layout>
  <div class="content">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
      <div><h2 class="mb-1">{{ __('admin_search.title') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_search.subtitle') }}</p></div>
    </div>
    <form class="card card-body mb-4" method="GET" action="{{ route('admin.search') }}">
      <div class="input-group input-group-lg"><span class="input-group-text"><span data-feather="search"></span></span><input class="form-control" type="search" name="q" value="{{ $term }}" maxlength="100" placeholder="{{ __('admin_nav.search_placeholder') }}" autofocus><button class="btn btn-primary" type="submit">{{ __('admin_nav.search') }}</button></div>
    </form>
    <div class="card overflow-hidden">@include('admin.search._groups', ['compact' => false])</div>
    <x-dashboard-footer />
  </div>
</x-dashboard-layout>
