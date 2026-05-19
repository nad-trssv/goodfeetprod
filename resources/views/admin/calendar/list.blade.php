@section('title', 'Services index')
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
            <h2 class="mb-0">{{  $title  }}<span class="fw-normal text-body-tertiary ms-3">({{ count($appointments) }})</span></h2>
          </div>
        </div>
        <div class="row align-items-center justify-content-between g-3 mb-4">
          <div class="col col-auto">
            <div class="search-box">
              <form class="position-relative">
                <input class="form-control search-input search" type="search" placeholder="Search members" aria-label="Search" />
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
                  <th class="sort align-middle text-uppercase" scope="col" data-sort="membername" style="width:30%; min-width:200px;">Имя</th>
                  <th class="sort align-middle text-uppercase" scope="col" data-sort="client" style="width:15%; min-width:200px;">Клиент</th>
                  <th class="sort align-middle text-uppercase" scope="col" data-sort="role" style="width:5%;">Заметки</th>
                  <th class="sort align-middle text-end pe-0 text-uppercase" scope="col" data-sort="joined" style="width:29%;  min-width:200px;">Время записи</th>
                  <th class="align-middle pe-0" scope="col" style="width:8%;  min-width:80px;"></th>
                </tr>
              </thead>
              <tbody class="list" id="members-table-body">
                @foreach ($appointments as $appint)
                  <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                    <td class="fs-9 align-middle ps-0 py-3">
                      <div class="mb-0 fs-9">
                        #{{ $appint['id'] }}
                      </div>
                    </td>
                    <td class="membername align-middle">
                      <a class="d-flex align-items-center text-body text-hover-1000 no-underline" href="{{ route('calendar.show', ['appointment' => $appint['id']]) }}">
                        <div class="flex-col ms-3">
                            <div class="d-flex align-items-center w-auto">
                                <div class="bullet-item me-2" style="background-color: {{ $appint['textColor'] }};"></div>
                                <h6 class="mb-0 fw-semibold" style="white-space: pre-line;">{{ $appint['title'] }}</h6>
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
                        <h6 class="mb-1 fw-bold" style="white-space: pre-line;">{{ $appint['client_name'] }} {{ $appint['client_lastname'] }}</h6>
                        <span class="badge badge-phoenix fs-10 badge-phoenix-primary">
                            <span class="uil fs-10 lh-1 uil-phone text-primary"></span>
                            <a class="fs-10 text-decoration-none" href="tel:{{ $appint['client_phone'] }}">
                                {{ $appint['client_phone'] }}
                            </a>
                        </span>
                    </td>
                    <td class="role align-middle text-body">
                        @if ($appint['description'])
                                <button class="badge badge-phoenix fs-10 badge-phoenix-primary collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample{{ $appint['id'] }}" aria-expanded="false" aria-controls="collapseExample{{ $appint['id'] }}">
                                    <span class="uil fs-8 lh-1 uil-comment-question text-primary"></span>
                                </button>
                        @endif
                    </td>
                    <td class="joined align-middle text-body-tertiary text-end white-space-nowrap">
                        <div class="d-flex gap-2 justify-content-end">
                            <div class="start">
                                <h6 class="mb-0 fw-semibold" style="white-space: pre-line;">Начало Записи:</h6>
                                {{ \Carbon\Carbon::parse($appint['start'])->locale(App::getLocale())->translatedFormat('d M, Y') }}
                                {{ \Carbon\Carbon::parse($appint['start'])->format('H:i') }}
                            </div>
                            <div class="start">
                                <h6 class="mb-0 fw-semibold" style="white-space: pre-line;">Конец записи:</h6>
                                {{ \Carbon\Carbon::parse($appint['end'])->locale(App::getLocale())->translatedFormat('d M, Y') }}
                                {{ \Carbon\Carbon::parse($appint['end'])->format('H:i') }}
                            </div>
                        </div>
                    </td>
                    <td></td>
                  </tr>
                  @if ($appint['description'])
                    <tr class="border-none">
                      <td></td>
                      <td colspan="4">
                          <div class="collapse" id="collapseExample{{ $appint['id'] }}">
                              <div class="p-3">{{ $appint['description'] }}</div>
                          </div>
                      </td>
                      <td colspan="2"></td>
                    </tr>
                  @endif
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="row align-items-center justify-content-between py-2 pe-0 fs-9">
            <div class="col-auto d-flex">
              <p class="mb-0 d-none d-sm-block me-3 fw-semibold text-body" data-list-info="data-list-info"></p><a class="fw-semibold" href="#!" data-list-view="*">View all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a><a class="fw-semibold d-none" href="#!" data-list-view="less">View Less<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
            <div class="col-auto d-flex">
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
              <h5 class="modal-title" id="verticallyCenteredModalLabel">Вы точно хотите удалить?</h5>
              <button class="btn btn-close p-1" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p class="text-body-tertiary lh-lg mb-0">Вы сейчас удаляете запись "<span id="removeItem"></span>".</p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-primary" type="button" data-bs-dismiss="modal">Отмена</button>
              <button class="btn btn-primary" type="button" id="confirmButton" data-bs-dismiss="modal">Удалить</button>
            </div>
          </div>
        </div>
      </div>
      <x-dashboard-footer />
    </div>
    @push('scripts')
    <script>
      $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        
        const confirmButton = document.getElementById("confirmButton");
        const removeItemSpan = document.getElementById('removeItem');

        $(document).on('click', '.service_destroy', function() {
            var dataItem = $(this).data('item'); 
            var dataId = $(this).data('id');
            removeItemSpan.textContent = dataItem;
            confirmButton.addEventListener("click", () => {
              removeItemSpan.textContent = '';
              $.ajax({
                  url: "{{ route('service.destroy', '') }}"+'/'+dataId,
                  type: "DELETE",
                  dataType: "json",
                  success: function(response) {
                      Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: 'Событие успешно удалено!',
                        showConfirmButton: false,
                        iconColor: '#1463b8',
                        timerProgressBar: true,
                        timer: 1500,
                      });
                  },
                  error: function(err) {
                      console.log('Ошибка при удалении события:', err);
                  }
              });
          });
        });
      });
    </script>
    @endpush
  </x-dashboard-layout>
