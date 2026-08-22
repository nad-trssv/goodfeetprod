@section('title', 'Services index')
  @push('styles')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  @endpush
  <x-dashboard-layout>
    <div class="content">
      <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item active">{{ __('admin_services.title') }}</li>
        </ol>
      </nav>
      <div class="mb-9">
        <div id="projectSummary" data-list='{"valueNames":["servicename","assignees","price","duration","status"],"page":24,"pagination":true}'>
          <div class="row mb-4 gx-6 gy-3 align-items-center">
            <div class="col-auto">
              <h2 class="mb-0">{{ __('admin_services.title') }}<span class="fw-normal text-body-tertiary ms-3">({{ $services->count() }})</span></h2>
            </div>
            <div class="col-auto"><a class="btn btn-primary px-5" href="{{ route('service.create') }}"><i class="fa-solid fa-plus me-2"></i>{{ __('admin_services.new') }}</a></div>
          </div>
          <div class="row g-3 justify-content-between align-items-end mb-4">
            <div class="col-12 col-sm-auto"></div>
            <div class="col-12 col-sm-auto">
              <div class="d-flex align-items-center">
                <div class="search-box me-3">
                  <form class="position-relative">
                    <input class="form-control search-input search" type="search" placeholder="{{ __('admin_services.search') }}" aria-label="{{ __('admin_services.search') }}" />
                    <span class="fas fa-search search-box-icon"></span>
                  </form>
                </div>
              </div>
            </div>
          </div>
          
          @if (session('success'))
          <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: @json(__('admin_service_extra.success')),
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });
          </script>
          @endif
          <div class="table-responsive scrollbar col-12">
            <table class="table fs-9 mb-0 border-top border-translucent">
              <thead>
                <tr>
                  <th class="sort align-middle ps-0 text-uppercase" scope="col" data-sort="servicename" style="width:30%;">{{ __('admin_services.service_name') }}</th>
                  <th class="sort align-middle ps-3 text-uppercase" scope="col" data-sort="assignees" style="width:10%;">{{ __('admin_services.employees') }}</th>
                  <th class="sort align-middle ps-3 text-uppercase" scope="col" data-sort="price" style="width:10%;">{{ __('admin_services.price') }}</th>
                  <th class="sort align-middle ps-3 text-uppercase" scope="col" data-sort="duration" style="width:15%;">{{ __('admin_services.duration') }}</th>
                  <th class="sort align-middle ps-3 text-end text-uppercase" scope="col" data-sort="statuses" style="width:12%;">{{ __('admin_services.status') }}</th>
                  <th class="sort align-middle text-end text-uppercase" scope="col" style="width:10%;"></th>
                </tr>
              </thead>
              <tbody class="list" id="project-list-table-body">
                @foreach ($services as $service)
                  <tr class="position-static">
                    <td class="align-middle time ps-0 servicename py-4">
                        <a class="fw-bold fs-8" href="{{ route('service.show', $service['id']) }}">
                        <span class="table_serviceColor" style="background-color:{{ $service['eventColor'] }}"></span>
                          {{ $service['name'] }}
                        </a>
                        @if ($service['has_fixed_time'])
                          <br />
                          <span class="badge badge-phoenix fs-10 badge-phoenix-primary">{{ \Carbon\Carbon::parse($service['time_from'])->format('H:i') }} - {{ \Carbon\Carbon::parse($service['time_to'])->format('H:i') }}</span>
                        @endif
                        @if ($service->futureRules && $service->futureRules->isNotEmpty())
                          <br />
                          <span class="badge badge-phoenix fs-10 badge-phoenix-warning mt-1">
                            {{ __('admin_service_extra.future_changes', ['date' => \Carbon\Carbon::parse($service->futureRules->first()->valid_from)->format('d.m.Y')]) }}
                          </span>
                        @endif
                        <p class="mb-0 fs-9 text-body">
                          {{ Str::words($service['short_description'], 5, '...') }}
                        </p>
                    </td>
                    <td class="align-middle white-space-nowrap assignees ps-3 py-4">
                      <div class="avatar-group avatar-group-dense">
                        @php
                          $userCount = count($service['users']);
                          $shownUsers = 3;
                          $more = $userCount > $shownUsers ? $userCount - $shownUsers : 0;
                        @endphp
                        @foreach ($service['users'] as $user)
                          @if ($loop->index < $shownUsers)
                            <a class="dropdown-toggle dropdown-caret-none d-inline-block" href="#!" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                              <x-ui.avatar :user="$user" :size="40" class="border border-dark rounded-circle" />
                            </a>
                            <div class="dropdown-menu avatar-dropdown-menu p-0 overflow-hidden" style="width: 320px;">
                              <div class="position-relative">
                                <div class="bg-holder z-n1 bg-secondary" style="background-image:url(../../assets/img/bg/bg-8.png);background-size: contain; background-position: bottom;">
                                </div>
                                <!--/.bg-holder-->
                                <div class="p-3">
                                  <div class="text-center">
                                    <x-ui.avatar :user="$user" :size="64" online class="me-2 me-sm-0 me-xl-2 mb-2 border border-light-subtle rounded-circle" />
                                    <h6 class="text-white">{{ $user->name }}</h6>
                                    <p class="text-light text-opacity-50 fw-semibold fs-10 mb-2">{{ $user->username }}</p>
                                    {{-- <div class="d-flex flex-center mb-3">
                                      <h6 class="text-white mb-0">224 <span class="fw-normal text-light text-opacity-75">connections</span></h6><span class="fa-solid fa-circle text-body-tertiary mx-1" data-fa-transform="shrink-10 up-2"></span>
                                      <h6 class="text-white mb-0">23 <span class="fw-normal text-light text-opacity-75">mutual</span></h6>
                                    </div> --}}
                                  </div>
                                </div>
                              </div>
                              <div class="bg-body-emphasis">
                                <div class="p-3 border-bottom border-translucent">
                                  <div class="d-flex justify-content-between">
                                    <div class="d-flex">
                                      @if ($user->phone)
                                        <a href="tel:{{ $user->phone }}"><button class="btn btn-phoenix-secondary btn-icon btn-icon-lg me-2"><span class="fa-solid fa-phone"></span></button></a>
                                      @endif
                                      {{-- <button class="btn btn-phoenix-secondary btn-icon btn-icon-lg me-2"><span class="fa-solid fa-message"></span></button>
                                      <button class="btn btn-phoenix-secondary btn-icon btn-icon-lg"><span class="fa-solid fa-video"></span></button> --}}
                                    </div>
                                    @if ($user->email)
                                      <a href="mailto:{{ $user->email }}"><button class="btn btn-phoenix-primary"><span class="fa-solid fa-envelope me-2"></span>{{ __('admin_services.send_email') }}</button></a>
                                    @endif
                                  </div>
                                </div>
                                {{-- <ul class="nav d-flex flex-column py-3 border-bottom">
                                  <li class="nav-item"><a class="nav-link px-3 d-flex flex-between-center" href="#!"> <span class="me-2 text-body d-inline-block" data-feather="clipboard"></span><span class="text-body-highlight flex-1">{{ __('admin_services.assigned_projects') }}</span><span class="fa-solid fa-chevron-right fs-11"></span></a></li>
                                  <li class="nav-item"><a class="nav-link px-3 d-flex flex-between-center" href="#!"> <span class="me-2 text-body" data-feather="pie-chart"></span><span class="text-body-highlight flex-1">{{ __('admin_services.view_activity') }}</span><span class="fa-solid fa-chevron-right fs-11"></span></a></li>
                                </ul> --}}
                              </div>
                              {{-- <div class="p-3 d-flex justify-content-between"><a class="btn btn-link p-0 text-decoration-none" href="#!">Details </a><a class="btn btn-link p-0 text-decoration-none text-danger" href="#!">Unassign </a></div> --}}
                            </div>
                          @endif
                        @endforeach
                        @if ($more > 0)
                          <div class="avatar avatar-s rounded-circle">
                            <div class="avatar-name rounded-circle"><span>+{{ $more }}</span></div>
                          </div>
                        @endif
                      </div>
                    </td>
                    <td class="align-middle white-space-nowrap price ps-3 py-4">
                      <p class="mb-0 fs-9 text-body">
                        @if ($service['price_can_change'])
                          <span>{{ __('admin_services.from') }} </span>
                        @endif
                        {{ $service['effective_price'] }}
                        <span>&euro;</span>
                      </p>
                    </td>
                    <td class="align-middle white-space-nowrap duration ps-3 py-4">
                      <p class="mb-0 fs-9 text-body">{{ __('admin_service_extra.minutes', ['count' => $service['duration_minutes']]) }}</p>
                    </td>
                    <td class="align-middle white-space-nowrap statuses text-end">
                      @if ($service['status'] === 1)
                          <span class="badge badge-phoenix fs-10 badge-phoenix-success">{{ __('admin_services.active') }}</span>
                      @elseif($service['status'] === 0)
                        <span class="badge badge-phoenix fs-10 badge-phoenix-secondary">{{ __('admin_services.hidden') }}</span>
                      @endif
                    </td>
                    <td class="align-middle text-end white-space-nowrap pe-0 action">
                      <div class="btn-reveal-trigger position-static">
                        <button class="btn btn-sm dropdown-toggle dropdown-caret-none transition-none btn-reveal fs-10" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                        <div class="dropdown-menu dropdown-menu-end py-2">
                          <a class="dropdown-item" href="{{ route('service.show', $service['id']) }}">{{ __('admin_services.edit') }}</a>
                          <a class="dropdown-item" href="{{ route('languages.edit', $service['id']) }}">{{ __('admin_services.translation') }}</a>
                          <a class="dropdown-item" href="{{ route('service.rules.edit', $service['id']) }}">{{ __('admin_services.date_rules') }}</a>
                          <form method="POST" action="/service/{{ $service['id'] }}/toggle-status" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="dropdown-item">{{ __('admin_services.change_status') }}</button>
                          </form>
                          <div class="dropdown-divider"></div>
                          <button class="dropdown-item text-danger service_destroy" data-item="{{ $service['name'] }}" data-id="{{ $service['id'] }}" type="button" data-bs-toggle="modal" data-bs-target="#verticallyCentered">{{ __('admin_services.delete') }}</button>
                        </div>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="d-flex flex-wrap align-items-center justify-content-between py-3 pe-0 fs-9 border-bottom border-translucent">
            <div class="d-flex">
              <p class="mb-0 d-none d-sm-block me-3 fw-semibold text-body" data-list-info="data-list-info"></p><a class="fw-semibold" href="#!" data-list-view="*">{{ __('admin_services.view_all') }}<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a><a class="fw-semibold d-none" href="#!" data-list-view="less">{{ __('admin_services.view_less') }}<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
            <div class="d-flex">
              <button class="page-link" data-list-pagination="prev"><span class="fas fa-chevron-left"></span></button>
              <ul class="mb-0 pagination"></ul>
              <button class="page-link pe-0" data-list-pagination="next"><span class="fas fa-chevron-right"></span></button>
            </div>
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
              <form method="POST" id="deleteForm">
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
    <script>
      $(document).ready(function() {
        const removeItemSpan = document.getElementById('removeItem');

        $(document).on('click', '.service_destroy', function() {
            var dataItem = $(this).data('item'); 
            var dataId = $(this).data('id');

            $('#deleteForm').attr('action', '/service/' + dataId);

            removeItemSpan.textContent = dataItem;
        });
      });
    </script>
    @endpush
  </x-dashboard-layout>
