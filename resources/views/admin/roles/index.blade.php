<x-dashboard-layout>
  <div class="content">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
      <div><h2 class="mb-1">{{ __('admin_roles.title') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_roles.subtitle') }}</p></div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>{{ __('admin_roles.access_denied') }}</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="card mb-4">
      <div class="card-body p-3 p-lg-4">
        <h4 class="mb-3">{{ __('admin_roles.new_role') }}</h4>
        <form method="POST" action="{{ route('admin.roles.store') }}" class="row g-3 align-items-end">@csrf
          <div class="col-12 col-lg-5"><label class="form-label" for="new-role-name">{{ __('admin_roles.role_name') }}</label><input class="form-control" id="new-role-name" name="name" maxlength="80" placeholder="{{ __('admin_roles.role_example') }}" required></div>
          <div class="col-12 col-lg-5"><label class="form-label" for="new-role-scope">{{ __('admin_roles.data_scope') }}</label><select class="form-select" id="new-role-scope" name="appointment_scope"><option value="own">{{ __('admin_roles.scope_own') }}</option><option value="all">{{ __('admin_roles.scope_all') }}</option></select></div>
          <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_service_provider" value="1" id="new-role-provider"><label class="form-check-label" for="new-role-provider">{{ __('admin_roles.service_provider') }}</label></div></div>
          <div class="col-12 col-lg-2 ms-lg-auto"><button class="btn btn-primary w-100" type="submit"><span data-feather="plus" class="me-1"></span>{{ __('admin_roles.create') }}</button></div>
        </form>
      </div>
    </div>

    <div class="alert alert-subtle-info mb-4"><span data-feather="shield" class="me-2"></span>{{ __('admin_roles.permission_hint') }}</div>

    <div class="accordion" id="rolePermissionsAccordion">
      @foreach($roles as $role)
        @php($isCustomerRole = $role->resolvedSlug() === 'customer')
        <div class="accordion-item mb-3 border rounded-3 overflow-hidden">
          <h2 class="accordion-header" id="role-heading-{{ $role->id }}">
            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#role-body-{{ $role->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
              <span class="fw-bold me-2">{{ $role->displayName() }}</span>
              @if($role->is_system)<span class="badge badge-phoenix badge-phoenix-secondary me-2">{{ __('admin_roles.system') }}</span>@endif
              <span class="text-body-tertiary fs-9">{{ __('admin_roles.employees',['count'=>$role->users_count]) }}</span>
            </button>
          </h2>
          <div id="role-body-{{ $role->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#rolePermissionsAccordion">
            <div class="accordion-body p-3 p-lg-4">
              @if($isCustomerRole)
                <div class="alert alert-subtle-secondary mb-0">{{ __('admin_roles.customer_locked') }}</div>
              @else
                <form method="POST" action="{{ route('admin.roles.update',$role) }}">@csrf @method('PUT')
                  <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-5"><label class="form-label" for="role-name-{{ $role->id }}">{{ __('admin_roles.role_name') }}</label><input class="form-control" id="role-name-{{ $role->id }}" name="name" value="{{ $role->name }}" required></div>
                    <div class="col-12 col-lg-7"><label class="form-label" for="role-scope-{{ $role->id }}">{{ __('admin_roles.data_scope') }}</label><select class="form-select" id="role-scope-{{ $role->id }}" name="appointment_scope" @disabled($role->resolvedSlug()==='super-admin')><option value="own" @selected($role->appointment_scope==='own')>{{ __('admin_roles.scope_own') }}</option><option value="all" @selected($role->appointment_scope==='all')>{{ __('admin_roles.scope_all') }}</option></select>@if($role->resolvedSlug()==='super-admin')<input type="hidden" name="appointment_scope" value="all">@endif</div>
                  </div>
                  <div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="is_service_provider" value="1" id="role-provider-{{ $role->id }}" @checked($role->is_service_provider)><label class="form-check-label" for="role-provider-{{ $role->id }}">{{ __('admin_roles.service_provider') }}</label></div>
                  <div class="row g-3">
                    @foreach($permissions as $group => $items)
                      <div class="col-12 col-md-6 col-xl-4">
                        <fieldset class="border rounded-3 p-3 h-100"><legend class="float-none w-auto px-2 fs-8 fw-bold mb-1">{{ __('admin_roles.groups.'.$group) }}</legend>
                          @foreach($items as $permission)
                            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->key }}" id="role-{{ $role->id }}-permission-{{ $permission->id }}" @checked($role->resolvedSlug()==='super-admin' || $role->permissions->contains('id',$permission->id)) @disabled($role->resolvedSlug()==='super-admin')><label class="form-check-label" for="role-{{ $role->id }}-permission-{{ $permission->id }}">{{ __('admin_roles.actions.'.$permission->action) }}</label></div>
                          @endforeach
                        </fieldset>
                      </div>
                    @endforeach
                  </div>
                  <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-4"><button class="btn btn-primary" type="submit"><span data-feather="save" class="me-1"></span>{{ __('admin_roles.save') }}</button></div>
                </form>
                @if(!$role->is_system && $role->users_count===0 && auth()->user()->role_id!==$role->id)
                  <form method="POST" action="{{ route('admin.roles.destroy',$role) }}" class="mt-2 text-end" onsubmit="return confirm('{{ __('admin_roles.delete') }}?')">@csrf @method('DELETE')<button class="btn btn-link text-danger" type="submit">{{ __('admin_roles.delete') }}</button></form>
                @endif
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</x-dashboard-layout>
