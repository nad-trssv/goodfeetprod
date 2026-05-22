@section('title', 'Все нерабочие дни')

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">Все нерабочие дни</li>
      </ol>
    </nav>

    <div class="row mb-4 align-items-center">
      <div class="col">
        <h2 class="mb-0">Все нерабочие дни</h2>
        <p class="text-muted fs-9 mt-1">Нерабочие дни и часы всех мастеров</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        {{-- Фильтры --}}
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Поиск по названию...">
          </div>
          <div class="col-md-3">
            <select class="form-select form-select-sm" id="filterMaster">
              <option value="">Все мастера</option>
              <option value="Общий">Общие</option>
              @foreach(\App\Models\User::whereIn('role_id', [1, 2])->orderBy('name')->get() as $master)
                <option value="{{ $master->name }}">{{ $master->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select form-select-sm" id="filterType">
              <option value="">Все типы</option>
              <option value="full">Весь день</option>
              <option value="partial">Частичные</option>
            </select>
          </div>
          <div class="col-md-2">
            <button class="btn btn-sm btn-outline-secondary w-100" id="resetFilters">
              <span class="fas fa-times me-1"></span>Сбросить
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-sm fs-9 mb-0" id="allRedDaysTable">
            <thead>
              <tr>
                <th>Название</th>
                <th>Дата</th>
                <th>Время</th>
                <th>Мастер</th>
                <th>Повтор</th>
                <th class="text-end">Действие</th>
              </tr>
            </thead>
            <tbody id="allRedDaysBody">
              @foreach($redDays as $day)
              <tr class="rd-row"
                  data-name="{{ strtolower($day['name']) }}"
                  data-master="{{ $day['master_name'] }}"
                  data-type="{{ $day['full_day'] ? 'full' : 'partial' }}">
                <td>{{ $day['name'] }}</td>
                <td>{{ \Carbon\Carbon::parse($day['date'])->format('d.m.Y') }}</td>
                <td>
                  @if($day['full_day'])
                    <span class="badge bg-secondary fs-10">Весь день</span>
                  @else
                    <span class="text-muted">{{ $day['start_time'] }} – {{ $day['end_time'] }}</span>
                  @endif
                </td>
                <td>
                  @if($day['user_id'])
                    <span class="badge badge-phoenix badge-phoenix-primary fs-10">{{ $day['master_name'] }}</span>
                  @else
                    <span class="badge badge-phoenix badge-phoenix-secondary fs-10">Общий</span>
                  @endif
                </td>
                <td>{{ $day['repeat'] ? '✅' : '❌' }}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-danger delete-all-rd" data-id="{{ $day['id'] }}">
                    <span class="fas fa-trash"></span>
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Пагинация --}}
        <div class="row align-items-center justify-content-between py-3 fs-9" id="paginationBlock">
          <div class="col-auto">
            <p class="mb-0 text-muted" id="paginationInfo"></p>
          </div>
          <div class="col-auto d-flex gap-1" id="paginationButtons"></div>
        </div>
      </div>
    </div>

    <x-dashboard-footer />
  </div>

  @push('scripts')
  <script>
        $(document).ready(function () {
            const perPage = 15;
            let currentPage = 1;
            let allRows = [];
            let filteredRows = [];

            // Собираем все данные из таблицы при загрузке
            function initRows() {
                allRows = $('#allRedDaysBody tr.rd-row').toArray();
                filteredRows = allRows;
                renderPage();
            }

            function applyFilters() {
                const search = $('#filterSearch').val().toLowerCase();
                const master = $('#filterMaster').val();
                const type = $('#filterType').val();

                filteredRows = allRows.filter(row => {
                    const name = $(row).data('name');
                    const rowMaster = $(row).data('master');
                    const rowType = $(row).data('type');

                    const matchSearch = !search || name.includes(search);
                    const matchMaster = !master || rowMaster === master;
                    const matchType = !type || rowType === type;

                    return matchSearch && matchMaster && matchType;
                });

                currentPage = 1;
                renderPage();
            }

            function renderPage() {
                const total = filteredRows.length;
                const totalPages = Math.ceil(total / perPage) || 1;
                const start = (currentPage - 1) * perPage;
                const end = start + perPage;

                // Скрыть все
                allRows.forEach(row => $(row).hide());

                // Показать текущую страницу из отфильтрованных
                filteredRows.slice(start, end).forEach(row => $(row).show());

                // Инфо
                const showing = Math.min(end, total);
                $('#paginationInfo').text(total > 0 ? `Показано ${start + 1}–${showing} из ${total} записей` : 'Нет записей');

                // Кнопки пагинации
                let buttons = '';
                if (totalPages > 1) {
                    buttons += `<button class="btn btn-sm btn-outline-secondary page-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>
                        <span class="fas fa-chevron-left"></span>
                    </button>`;

                    for (let i = 1; i <= totalPages; i++) {
                        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                            buttons += `<button class="btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline-secondary'} page-btn" data-page="${i}">${i}</button>`;
                        } else if (i === currentPage - 3 || i === currentPage + 3) {
                            buttons += `<span class="btn btn-sm btn-outline-secondary disabled">...</span>`;
                        }
                    }

                    buttons += `<button class="btn btn-sm btn-outline-secondary page-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>
                        <span class="fas fa-chevron-right"></span>
                    </button>`;
                }
                $('#paginationButtons').html(buttons);
            }

            $(document).on('click', '.page-btn', function () {
                if (!$(this).prop('disabled')) {
                    currentPage = parseInt($(this).data('page'));
                    renderPage();
                }
            });

            $('#filterSearch').on('input', applyFilters);
            $('#filterMaster, #filterType').on('change', applyFilters);
            $('#resetFilters').on('click', function () {
                $('#filterSearch').val('');
                $('#filterMaster').val('');
                $('#filterType').val('');
                applyFilters();
            });

            $(document).on('click', '.delete-all-rd', function () {
                const id = $(this).data('id');
                const row = $(this).closest('tr');

                Swal.fire({
                    title: 'Вы уверены?', icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#d33',
                    confirmButtonText: 'Удалить', cancelButtonText: 'Отмена'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/settings/deleteRedDay/${id}`, type: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function () {
                                allRows = allRows.filter(r => r !== row[0]);
                                row.remove();
                                applyFilters();
                                Swal.fire('Удалено!', '', 'success');
                            }
                        });
                    }
                });
            });

            // Инициализация
            initRows();
        });
  </script>
  @endpush
</x-dashboard-layout>