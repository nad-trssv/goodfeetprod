@section('title', __('admin_rooms.title'))

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">{{ __('admin_rooms.title') }}</li>
      </ol>
    </nav>

    @if(session('success'))
      <div class="alert alert-success"><span class="fas fa-check-circle me-2"></span>{{ session('success') }}</div>
    @endif

    <div class="row mb-4 align-items-center">
      <div class="col">
        <h2 class="mb-0">{{ __('admin_rooms.title') }}</h2>
        <p class="text-muted fs-9 mt-1">{{ __('admin_rooms.hint') }}</p>
      </div>
      <div class="col-auto">
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#addForm">
          <span class="fas fa-plus me-1"></span>{{ __('admin_rooms.add_room') }}
        </button>
      </div>
    </div>

    {{-- Форма добавления --}}
    <div class="collapse mb-4" id="addForm">
      <div class="card card-body bg-light">
        <h6 class="mb-3">{{ __('admin_rooms.new') }}</h6>
        <form method="POST" action="{{ route('admin.rooms.store') }}">
          @csrf
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">{{ __('admin_rooms.name') }}*</label>
              <input type="text" class="form-control form-control-sm" name="name" placeholder="{{ __('admin_rooms.name_example') }}" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">{{ __('admin_rooms.capacity') }}*</label>
              <input type="number" class="form-control form-control-sm" name="capacity" min="1" value="1" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">{{ __('admin_rooms.description') }}</label>
              <input type="text" class="form-control form-control-sm" name="description" placeholder="{{ __('admin_rooms.optional') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">{{ __('admin_rooms.employees') }}</label>
              <select class="form-select form-select-sm" name="user_ids[]" multiple>
                @foreach($masters as $master)
                  <option value="{{ $master->id }}">{{ $master->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="text-end mt-3">
            <button class="btn btn-sm btn-outline-secondary me-2" type="button" data-bs-toggle="collapse" data-bs-target="#addForm">{{ __('admin_rooms.cancel') }}</button>
            <button class="btn btn-sm btn-primary" type="submit">{{ __('admin_rooms.save') }}</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm fs-9 mb-0">
            <thead class="bg-light">
              <tr>
                <th class="ps-3">{{ __('admin_rooms.name') }}</th>
                <th>{{ __('admin_rooms.capacity') }}</th>
                <th>{{ __('admin_rooms.description') }}</th>
                <th>{{ __('admin_rooms.employees') }}</th>
                <th>{{ __('admin_rooms.status') }}</th>
                <th class="text-end pe-3">{{ __('admin_rooms.action') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rooms as $room)
              <tr>
                <td class="ps-3 fw-semibold">{{ $room->name }}</td>
                <td>{{ $room->capacity }}</td>
                <td class="text-muted">{{ $room->description ?? '—' }}</td>
                <td>
                  @forelse($room->users as $user)
                    <span class="badge badge-phoenix badge-phoenix-primary fs-10 me-1">{{ $user->name }}</span>
                  @empty
                    <span class="text-muted">—</span>
                  @endforelse
                </td>
                <td>
                  <form method="POST" action="{{ route('admin.rooms.toggle', $room->id) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $room->is_active ? 'btn-success' : 'btn-outline-secondary' }} fs-10">
                      {{ $room->is_active ? __('admin_rooms.active') : __('admin_rooms.inactive') }}
                    </button>
                  </form>
                </td>
                <td class="text-end pe-3">
                  <button class="btn btn-sm btn-outline-primary me-1" type="button"
                    data-bs-toggle="modal" data-bs-target="#editModal"
                    data-id="{{ $room->id }}"
                    data-name="{{ $room->name }}"
                    data-capacity="{{ $room->capacity }}"
                    data-description="{{ $room->description }}"
                    data-users="{{ $room->users->pluck('id')->join(',') }}">
                    <span class="fas fa-edit"></span>
                  </button>
                  <form method="POST" action="{{ route('admin.rooms.destroy', $room->id) }}" style="display:inline" onsubmit='return confirm(@js(__('admin_rooms.delete_confirm')))'>
                    @csrf
                    <button class="btn btn-sm btn-outline-danger" type="submit">
                      <span class="fas fa-trash"></span>
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr><td colspan="6" class="text-center text-muted py-5">{{ __('admin_rooms.empty') }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Модалка редактирования --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ __('admin_rooms.edit') }}</h5>
            <button class="btn btn-close p-1" type="button" data-bs-dismiss="modal"></button>
          </div>
          <form method="POST" id="editForm" action="">
            @csrf
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">{{ __('admin_rooms.name') }}*</label>
                  <input type="text" class="form-control" name="name" id="edit_name" required>
                </div>
                <div class="col-6">
                  <label class="form-label">{{ __('admin_rooms.capacity') }}*</label>
                  <input type="number" class="form-control" name="capacity" id="edit_capacity" min="1" required>
                </div>
                <div class="col-12">
                  <label class="form-label">{{ __('admin_rooms.description') }}</label>
                  <input type="text" class="form-control" name="description" id="edit_description">
                </div>
                <div class="col-12">
                  <label class="form-label">{{ __('admin_rooms.employees') }}</label>
                  <select class="form-select" name="user_ids[]" id="edit_users" multiple>
                    @foreach($masters as $master)
                      <option value="{{ $master->id }}">{{ $master->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">{{ __('admin_rooms.cancel') }}</button>
              <button class="btn btn-primary" type="submit">{{ __('admin_rooms.save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <x-dashboard-footer />
  </div>

  @push('scripts')
  <script>
    document.getElementById('editModal').addEventListener('show.bs.modal', function(event) {
      const btn = event.relatedTarget;
      document.getElementById('edit_name').value = btn.getAttribute('data-name');
      document.getElementById('edit_capacity').value = btn.getAttribute('data-capacity');
      document.getElementById('edit_description').value = btn.getAttribute('data-description') || '';
      document.getElementById('editForm').action = '/admin/rooms/' + btn.getAttribute('data-id') + '/update';

      // Отмечаем нужных мастеров
      const userIds = btn.getAttribute('data-users').split(',').filter(Boolean);
      const select = document.getElementById('edit_users');
      for (let option of select.options) {
        option.selected = userIds.includes(option.value);
      }
    });
  </script>
  @endpush
</x-dashboard-layout>
