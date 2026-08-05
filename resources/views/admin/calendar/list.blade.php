@section('title', 'Записи')
<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">Записи</li>
      </ol>
    </nav>

    <div id="members" data-list='{"valueNames":["membername","client","price","role","joined"],"page":45,"pagination":true}'>
      <div class="row mb-4 gx-6 gy-3 align-items-center">
        <div class="col-auto">
          <h2 class="mb-0">{{ $title }}<span class="fw-normal text-body-tertiary ms-3">({{ count($appointments) }})</span></h2>
        </div>
      </div>

      <div class="row align-items-center justify-content-between g-3 mb-4">
        <div class="col col-auto">
          <div class="search-box">
            <form class="position-relative">
              <input class="form-control search-input search" type="search" placeholder="Поиск..." aria-label="Search" />
              <span class="fas fa-search search-box-icon"></span>
            </form>
          </div>
        </div>
      </div>

      <div class="mx-n4 mx-lg-n6 px-4 px-lg-6 mb-9 bg-body-emphasis border-y mt-2 position-relative top-1">
        <div class="table-responsive scrollbar ms-n1 ps-1">
          <table class="table table-sm fs-9 mb-0">
            <thead>
              <tr>
                <th class="fs-9 align-middle ps-0"></th>
                <th class="sort align-middle text-uppercase" scope="col" data-sort="membername" style="width:30%; min-width:200px;">Услуга / Мастер</th>
                <th class="sort align-middle text-uppercase" scope="col" data-sort="client" style="width:20%; min-width:200px;">Клиент</th>
                <th class="sort align-middle text-uppercase" scope="col" data-sort="role" style="width:5%;">Заметки</th>
                <th class="sort align-middle text-end pe-0 text-uppercase" scope="col" data-sort="joined" style="width:29%; min-width:200px;">Время записи</th>
                <th class="align-middle pe-0" scope="col" style="width:8%; min-width:80px;"></th>
              </tr>
            </thead>
            <tbody class="list" id="members-table-body">
              @foreach ($appointments as $appint)
              <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                <td class="fs-9 align-middle ps-0 py-3">
                  <div class="mb-0 fs-9">#{{ $appint['id'] }}</div>
                </td>
                <td class="membername align-middle">
                  <a class="d-flex align-items-center text-body text-hover-1000 no-underline" href="{{ route('calendar.show', ['appointment' => $appint['id']]) }}">
                    <div class="flex-col ms-3">
                      <div class="d-flex align-items-center w-auto">
                        <div class="bullet-item me-2" style="background-color: {{ $appint['textColor'] }};"></div>
                        <h6 class="mb-0 fw-semibold">{{ $appint['title'] }}</h6>
                      </div>
                      <div class="fs-9 fw-bold w-auto">Мастер: {{ $appint['master'] }}</div>
                      <div class="fs-9 fw-bold w-auto text-primary">{{ $appint['masterUsername'] }}</div>
                      <div class="fs-10 fw-bold w-auto">
                        @if ($appint['appointment_time_from'])
                          ( {{ $appint['appointment_time_from'] }} - {{ $appint['appointment_time_to'] }} мин. )
                        @else
                          ( {{ $appint['appointment_time_to'] }} мин. )
                        @endif
                      </div>
                    </div>
                  </a>
                </td>
                <td class="client align-middle">
                  <h6 class="mb-1 fw-bold">{{ $appint['client_name'] }} {{ $appint['client_lastname'] }}</h6>
                  <span class="badge badge-phoenix fs-10 badge-phoenix-primary">
                    <span class="uil fs-10 lh-1 uil-phone text-primary"></span>
                    <a class="fs-10 text-decoration-none" href="tel:{{ $appint['client_phone'] }}">{{ $appint['client_phone'] }}</a>
                  </span>
                </td>
                <td class="role align-middle text-body">
                  @if ($appint['description'])
                    <div>
                      <button class="badge badge-phoenix fs-10 badge-phoenix-primary collapsed"
                              type="button"
                              data-bs-toggle="collapse"
                              data-bs-target="#collapse_{{ $appint['id'] }}"
                              aria-expanded="false">
                        <span class="uil fs-8 lh-1 uil-comment-question text-primary"></span>
                      </button>
                      <div class="collapse mt-2" id="collapse_{{ $appint['id'] }}">
                        <div class="p-2 fs-10 text-body-secondary bg-light rounded">{{ $appint['description'] }}</div>
                      </div>
                    </div>
                  @endif
                </td>
                <td class="joined align-middle text-body-tertiary text-end white-space-nowrap">
                  <div class="d-flex gap-2 justify-content-end">
                    <div class="start">
                      <h6 class="mb-0 fw-semibold">Начало:</h6>
                      {{ \Carbon\Carbon::parse($appint['start'])->locale(App::getLocale())->translatedFormat('d M, Y') }}
                      {{ \Carbon\Carbon::parse($appint['start'])->format('H:i') }}
                    </div>
                    <div class="start">
                      <h6 class="mb-0 fw-semibold">Конец:</h6>
                      {{ \Carbon\Carbon::parse($appint['end'])->locale(App::getLocale())->translatedFormat('d M, Y') }}
                      {{ \Carbon\Carbon::parse($appint['end'])->format('H:i') }}
                    </div>
                  </div>
                </td>
                <td class="text-end">@include('admin.calendar._status-actions',['appointment'=>$appint])</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="row align-items-center justify-content-between py-2 pe-0 fs-9">
          <div class="col-auto d-flex">
            <p class="mb-0 d-none d-sm-block me-3 fw-semibold text-body" data-list-info="data-list-info"></p>
            <a class="fw-semibold" href="#!" data-list-view="*">View all <span class="fas fa-angle-right ms-1"></span></a>
            <a class="fw-semibold d-none" href="#!" data-list-view="less">View Less <span class="fas fa-angle-right ms-1"></span></a>
          </div>
          <div class="col-auto d-flex">
            <button class="page-link" data-list-pagination="prev"><span class="fas fa-chevron-left"></span></button>
            <ul class="mb-0 pagination"></ul>
            <button class="page-link pe-0" data-list-pagination="next"><span class="fas fa-chevron-right"></span></button>
          </div>
        </div>
      </div>
    </div>

    <x-dashboard-footer />
  </div>
  @push('scripts')@include('admin.calendar._status-script')@endpush
</x-dashboard-layout>
