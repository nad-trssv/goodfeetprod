@section('title', 'Мастера')
<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">Мастера</li>
      </ol>
    </nav>

    <div id="members" data-list='{"valueNames":["membername","email","mobile_number","role","last_active","joined"],"page":10,"pagination":true}'>
      <div class="row mb-4 gx-6 gy-3 align-items-center">
        <div class="col-auto">
          <h2 class="mb-0">Мастера <span class="fw-normal text-body-tertiary ms-3">({{ $members->count() }})</span></h2>
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
        <div class="col-auto">
          <a href="{{ route('member.create') }}" class="btn btn-primary">
            <span class="fas fa-plus me-2"></span>Новый мастер
          </a>
        </div>
      </div>

      <div class="mx-n4 mx-lg-n6 px-4 px-lg-6 mb-9 bg-body-emphasis border-y mt-2 position-relative top-1">
        <div class="table-responsive scrollbar ms-n1 ps-1">
          <table class="table table-sm fs-9 mb-0">
            <thead>
              <tr>
                <th class="align-middle text-uppercase" style="width:20%; min-width:200px;">Имя</th>
                <th class="align-middle text-uppercase" style="width:20%; min-width:200px;">Email</th>
                <th class="align-middle text-uppercase" style="width:15%; min-width:150px;">Телефон</th>
                <th class="align-middle text-uppercase" style="width:10%;">Роль</th>
                <th class="align-middle text-uppercase" style="width:15%;">Услуги</th>
                <th class="align-middle text-end text-uppercase" style="width:15%; min-width:150px;">Последняя активность</th>
                <th class="align-middle text-end pe-0" style="width:5%; min-width:80px;"></th>
              </tr>
            </thead>
            <tbody class="list" id="members-table-body">
              @foreach ($members as $user)
                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                  <td class="membername align-middle white-space-nowrap">
                    <a class="d-flex align-items-center text-body text-hover-1000 no-underline" href="{{ route('member.edit', $user['id']) }}">
                      <div class="avatar avatar-m">
                        <img class="rounded-circle" src="{{ $user['profile_photo_url'] }}" alt="{{ $user['name'] }}" />
                      </div>
                      <div class="ms-3">
                        <h6 class="mb-0 fw-semibold">{{ $user['name'] }}</h6>
                        <p class="mb-0 text-muted fs-10">{{ $user['username'] }}</p>
                      </div>
                    </a>
                  </td>
                  <td class="email align-middle white-space-nowrap">
                    <a class="fw-semibold" href="mailto:{{ $user['email'] }}">{{ $user['email'] }}</a>
                  </td>
                  <td class="mobile_number align-middle white-space-nowrap">
                    <a class="fw-bold text-body-emphasis" href="tel:{{ $user['phone'] }}">{{ $user['phone'] }}</a>
                  </td>
                  <td class="role align-middle white-space-nowrap">
                    @if ($user['role_id'] === 1)
                      <span class="badge badge-phoenix fs-10 badge-phoenix-success">Admin</span>
                    @else
                      <span class="badge badge-phoenix fs-10 badge-phoenix-primary">Master</span>
                    @endif
                  </td>
                  <td class="align-middle">
                    @php
                      $member = \App\Models\User::with('services')->find($user['id']);
                    @endphp
                    @if($member && $member->services->count())
                      @foreach($member->services as $service)
                        <span class="badge bg-secondary fs-10 me-1">{{ $service->name }}</span>
                      @endforeach
                    @else
                      <span class="text-muted fs-10">—</span>
                    @endif
                  </td>
                  <td class="last_active align-middle text-end white-space-nowrap text-body-tertiary">
                    {{ $member->lastSeen() }}
                  </td>
                  <td class="align-middle text-end pe-0 white-space-nowrap">
                    <div class="btn-reveal-trigger position-static">
                      <button class="btn btn-sm dropdown-toggle dropdown-caret-none transition-none btn-reveal" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="fas fa-ellipsis-h fs-10"></span>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end py-2">
                        <a class="dropdown-item" href="{{ route('member.edit', $user['id']) }}">
                          <span class="fas fa-edit me-2"></span>Редактировать
                        </a>
                        <a class="dropdown-item" href="{{ route('master.calendar', $user['id']) }}">
                          <span class="fas fa-calendar me-2"></span>Календарь
                        </a>
                        <a class="dropdown-item" href="{{ route('master.calendar.list', $user['id']) }}">
                          <span class="fas fa-list me-2"></span>Список записей
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger member-destroy" href="#"
                           data-id="{{ $user['id'] }}"
                           data-item="{{ $user['name'] }}"
                           data-bs-toggle="modal"
                           data-bs-target="#deleteModal">
                          <span class="fas fa-trash me-2"></span>Удалить
                        </a>
                      </div>
                    </div>
                  </td>
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

    {{-- Модалка удаления --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Вы точно хотите удалить?</h5>
            <button class="btn btn-close p-1" type="button" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p class="text-body-tertiary lh-lg mb-0">Вы удаляете мастера "<span id="deleteItemName"></span>". Это действие нельзя отменить.</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-primary" type="button" data-bs-dismiss="modal">Отмена</button>
            <button class="btn btn-danger" type="button" id="confirmDelete">Удалить</button>
          </div>
        </div>
      </div>
    </div>

    <x-dashboard-footer />
  </div>

  @push('scripts')
  <script>
    $(document).ready(function () {
      let deleteId = null;

      $(document).on('click', '.member-destroy', function () {
        deleteId = $(this).data('id');
        $('#deleteItemName').text($(this).data('item'));
      });

      $('#confirmDelete').on('click', function () {
        if (!deleteId) return;
        $.ajax({
          url: '/member/' + deleteId,
          type: 'DELETE',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success: function () {
            $('#deleteModal').modal('hide');
            location.reload();
          },
          error: function (err) {
            console.log('Ошибка удаления:', err);
          }
        });
      });
    });
  </script>
  @endpush
</x-dashboard-layout>