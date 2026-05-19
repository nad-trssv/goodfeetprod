@section('title', 'Services index')
  <x-dashboard-layout>
    <div class="content">
      <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item active">Персонал</li>
        </ol>
      </nav>
      <div id="members" data-list='{"valueNames":["membername","email","mobile_number","role","last_active","joined"],"page":10,"pagination":true}'>
        <div class="row mb-4 gx-6 gy-3 align-items-center">
          <div class="col-auto">
            <h2 class="mb-0">Персонал<span class="fw-normal text-body-tertiary ms-3">({{ $members->count() }})</span></h2>
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
          <div class="col-auto">
            <div class="d-flex align-items-center">
              {{-- <button class="btn btn-link text-body me-4 px-0"><span class="fa-solid fa-file-export fs-9 me-2"></span>Export</button> --}}
              <a href="{{ route('member.create') }}" class="btn btn-primary"><span class="fas fa-plus me-2"></span>Новый мастер</a>
            </div>
          </div>
        </div>
        <div class="mx-n4 mx-lg-n6 px-4 px-lg-6 mb-9 bg-body-emphasis border-y mt-2 position-relative top-1">
          <div class="table-responsive scrollbar ms-n1 ps-1">
            <table class="table table-sm fs-9 mb-0">
              <thead>
                <tr>
                  <th class="white-space-nowrap fs-9 align-middle ps-0">
                    <div class="form-check mb-0 fs-8">
                      <input class="form-check-input" id="checkbox-bulk-members-select" type="checkbox" data-bulk-select='{"body":"members-table-body"}' />
                    </div>
                  </th>
                  <th class="sort align-middle text-uppercase" scope="col" data-sort="membername" style="width:15%; min-width:200px;">Имя</th>
                  <th class="sort align-middle text-uppercase" scope="col" data-sort="email" style="width:15%; min-width:200px;">EMAIL</th>
                  <th class="sort align-middle pe-3 text-uppercase" scope="col" data-sort="mobile_number" style="width:20%; min-width:200px;">НОМЕР ТЕЛЕФОНА</th>
                  <th class="sort align-middle text-uppercase" scope="col" data-sort="role" style="width:10%;">РОЛЬ</th>
                  <th class="sort align-middle text-end text-uppercase" scope="col" data-sort="last_active" style="width:21%;  min-width:200px;">ПОСЛЕДНЯЯ АКТИВНОСТЬ</th>
                  <th class="sort align-middle text-end pe-0 text-uppercase" scope="col" data-sort="joined" style="width:19%;  min-width:200px;">ДАТА РЕГИСТРАЦИИ</th>
                  <th class="align-middle pe-0" scope="col" style="width:8%;  min-width:80px;"></th>
                </tr>
              </thead>
              <tbody class="list" id="members-table-body">
                @foreach ($members as $user)
                  <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                    <td class="fs-9 align-middle ps-0 py-3">
                      <div class="form-check mb-0 fs-8">
                        <input class="form-check-input" type="checkbox" data-bulk-select-row='{"membername":{"avatar":"/team/32.webp","name":"Carry Anna"},"email":"annac34@gmail.com","mobile":"+912346578","role":"Budapest","lastActive":"34 min ago","joined":"Dec 12, 12:56 PM"}' />
                      </div>
                    </td>
                    <td class="membername align-middle white-space-nowrap">
                      <a class="d-flex align-items-center text-body text-hover-1000 no-underline" href="#!">
                        <div class="avatar avatar-m">
                          <img class="rounded-circle" src="{{ $user->profile_photo_url }}" alt="{{ $user->profile_photo_path }}" />
                        </div>
                        <div class="row flex-col ">
                          <h6 class="mb-0 ms-3 fw-semibold">{{ $user->name }}</h6> 
                          <p class="mb-0 ms-3 fw-semibold link-color">{{ $user->username }}</p>
                        </div>
                      </a>
                    </td>
                    <td class="email align-middle white-space-nowrap"><a class="fw-semibold" href="mailto:annac34@gmail.com">{{ $user->email }}</a></td>
                    <td class="mobile_number align-middle white-space-nowrap"><a class="fw-bold text-body-emphasis" href="tel:{{ $user->phone }}">{{ $user->phone }}</a></td>
                    <td class="role align-middle white-space-nowrap text-body">
                      @if ($user['role_id'] === 1)
                        <span class="badge badge-phoenix fs-10 badge-phoenix-success">{{ $user->role->name }}</span>
                      @else
                        <span class="badge badge-phoenix fs-10 badge-phoenix-primary">{{ $user->role->name }}</span>
                      @endif
                    </td>
                    <td class="last_active align-middle text-end white-space-nowrap text-body-tertiary">
                      @if ($user->lastSeen() === 'Online')
                        <span class="badge badge-phoenix fs-10 badge-phoenix-success">{{ $user->lastSeen() }}</span>
                      @else
                        {{ $user->lastSeen() }}
                      @endif
                    </td>
                    <td class="joined align-middle white-space-nowrap text-body-tertiary text-end">
                      {{ \Carbon\Carbon::parse($user['created_at'])->locale(App::getLocale())->translatedFormat('d M, Y') }}
                      <br class="d-none d-md-block" /> 
                      {{ \Carbon\Carbon::parse($user['created_at'])->format('H:i') }}
                    </td>
                    <td></td>
                  </tr>
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
