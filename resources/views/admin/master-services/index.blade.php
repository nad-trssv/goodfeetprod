@section('title', 'Услуги мастеров')
<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('member.index') }}">Сотрудники</a></li><li class="breadcrumb-item active">Услуги мастеров</li></ol></nav>
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4"><div><h2 class="mb-1">Услуги мастеров</h2><p class="text-body-tertiary mb-0">Подключение услуг и индивидуальные настройки цены, длительности и технического времени.</p></div></div>
    <div class="card mb-4"><div class="card-body"><form method="GET" action="{{ route('admin.master-services.index') }}" class="row g-3 align-items-end"><div class="col-12 col-md"><label class="form-label" for="master_id">Мастер</label><select class="form-select" id="master_id" name="master_id" required><option value="">Выберите мастера</option>@foreach($masters as $option)<option value="{{ $option->id }}" @selected($selectedMasterId === $option->id)>{{ $option->name }}{{ $option->username ? ' — '.$option->username : '' }}</option>@endforeach</select></div><div class="col-12 col-md-auto"><button class="btn btn-primary w-100" type="submit">Открыть услуги</button></div></form></div></div>
    @if($master)
      @include('admin.master-services._service-list', ['catalogUrl' => route('admin.master-services.index'), 'catalogMasterParameter' => true])
    @else
      <div class="alert alert-light border text-center py-5">Выберите мастера, чтобы открыть его услуги.</div>
    @endif
    <x-dashboard-footer />
  </div>
</x-dashboard-layout>
