
@php
  $canAdminSearch = auth()->user()->hasPermission('appointments.view') || auth()->user()->hasPermission('customers.view') || auth()->user()->hasPermission('services.view');
  $canQuickAppointments = auth()->user()->hasPermission('appointments.view') || auth()->user()->hasPermission('appointments.create');
@endphp
<style>
  .admin-header-search{width:min(32rem,38vw)}
  .admin-header-search-results{top:calc(100% + .45rem);max-height:min(32rem,70vh);overflow:auto;z-index:1080}
  .admin-quick-link{white-space:nowrap}
  .admin-nav-group-chevron{transition:transform .2s ease}
  [aria-expanded="true"] .admin-nav-group-chevron{transform:rotate(180deg)}
  @media(max-width:1399.98px){.admin-header-search{width:min(25rem,42vw)}}
  @media(min-width:1400px) and (max-width:1599.98px){.admin-header-search{width:min(20rem,23vw)}}
</style>
<nav class="navbar navbar-top fixed-top navbar-expand" id="navbarDefault">
    <div class="collapse navbar-collapse justify-content-between">
      <div class="navbar-logo">

        <button class="btn navbar-toggler navbar-toggler-humburger-icon hover-bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse" aria-expanded="false" aria-label="Toggle Navigation"><span class="navbar-toggle-icon"><span class="toggle-line"></span></span></button>
        <a class="navbar-brand me-1 me-sm-3" href="{{ route('dashboard') }}">
          <div class="d-flex align-items-center">
            <div class="d-flex align-items-center">
                <img src="{{ asset('assets/img/logos/GoodFeet-svg.svg') }}" alt="QuickCode" width="110" class="d-dark-none" />
                <img src="{{ asset('assets/img/logos/white-GoodFeet-svg.svg') }}" alt="QuickCode" width="110" class="d-light-none" />
            </div>
          </div>
        </a>
      </div>
      <div class="d-none d-md-flex align-items-center justify-content-center gap-2 flex-grow-1 mx-3 min-w-0">
        <div class="d-none d-xxl-flex align-items-center gap-1">
          @can('appointments.view')
            <a class="btn btn-sm btn-phoenix-secondary admin-quick-link" href="{{ route('calendar.index') }}"><span data-feather="calendar" class="me-1"></span>{{ __('admin_nav.calendar') }}</a>
            <a class="btn btn-sm btn-phoenix-secondary admin-quick-link" href="{{ route('appointments.today') }}"><span data-feather="clock" class="me-1"></span>{{ __('admin_nav.today') }}</a>
          @endcan
          @can('appointments.create')<a class="btn btn-sm btn-primary admin-quick-link" href="{{ route('calendar.create') }}"><span data-feather="plus" class="me-1"></span>{{ __('admin_nav.new_appointment') }}</a>@endcan
        </div>
        @if($canAdminSearch)
          <div class="admin-header-search position-relative">
            <form method="GET" action="{{ route('admin.search') }}" role="search" autocomplete="off">
              <div class="input-group input-group-sm"><span class="input-group-text bg-body border-end-0"><span data-feather="search" style="width:15px;height:15px"></span></span><input id="adminHeaderSearch" class="form-control border-start-0 ps-0" type="search" name="q" minlength="2" maxlength="100" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}" placeholder="{{ __('admin_nav.search_placeholder') }}" aria-label="{{ __('admin_nav.search') }}"></div>
            </form>
            <div id="adminHeaderSearchResults" class="admin-header-search-results position-absolute start-0 end-0 bg-body rounded-3 shadow border d-none"></div>
          </div>
        @endif
      </div>
      <ul class="navbar-nav navbar-nav-icons flex-row align-items-center">
        @if($canAdminSearch)<li class="nav-item d-md-none"><a class="nav-link px-2" href="{{ route('admin.search') }}" aria-label="{{ __('admin_nav.search') }}"><span data-feather="search"></span></a></li>@endif
        @if($canQuickAppointments)<li class="nav-item dropdown d-xxl-none">
          <a class="nav-link px-2" href="#" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('admin_nav.quick_actions') }}"><span data-feather="zap"></span></a>
          <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret shadow border">
            <h6 class="dropdown-header">{{ __('admin_nav.quick_actions') }}</h6>
            @can('appointments.view')<a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('calendar.index') }}"><span data-feather="calendar"></span>{{ __('admin_nav.calendar') }}</a><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('appointments.today') }}"><span data-feather="clock"></span>{{ __('admin_nav.today') }}</a>@endcan
            @can('appointments.create')<a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('calendar.create') }}"><span data-feather="plus-circle"></span>{{ __('admin_nav.new_appointment') }}</a>@endcan
          </div>
        </li>@endif
        <li class="nav-item">
          <div class="theme-control-toggle fa-icon-wait px-2">
            <input class="form-check-input ms-0 theme-control-toggle-input" type="checkbox" data-theme-control="phoenixTheme" value="dark" id="themeControlToggle" />
            <label class="mb-0 theme-control-toggle-label theme-control-toggle-light" for="themeControlToggle" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="{{ __('admin_nav.switch_theme') }}" style="height:32px;width:32px;"><span class="icon" data-feather="moon"></span></label>
            <label class="mb-0 theme-control-toggle-label theme-control-toggle-dark" for="themeControlToggle" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="{{ __('admin_nav.switch_theme') }}" style="height:32px;width:32px;"><span class="icon" data-feather="sun"></span></label>
          </div>
        </li>
        @can('notifications.view')
        <li class="nav-item dropdown">
          <a class="nav-link px-2 position-relative d-flex align-items-center justify-content-center" id="navbarNotifications" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="{{ __('admin_notifications.title') }}" style="width:2.25rem;height:2.25rem">
            <span data-feather="bell"></span>
            <span id="header-notification-count" class="position-absolute badge rounded-pill bg-danger p-0 d-flex align-items-center justify-content-center {{ $unreadNotificationCount > 0 ? '' : 'd-none' }}" style="top:-.15rem;right:-.05rem;min-width:1rem;height:1rem;font-size:.62rem">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}<span class="visually-hidden">{{ __('admin_notifications.new') }}</span></span>
          </a>
          <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret shadow border p-0" aria-labelledby="navbarNotifications" style="width:min(24rem,calc(100vw - 1rem))">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom"><h6 class="mb-0">{{ __('admin_notifications.title') }}</h6>@can('notifications.update')<form id="header-notifications-read-all" class="{{ $unreadNotificationCount ? '' : 'd-none' }}" method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-link btn-sm p-0" type="submit">{{ __('admin_notifications.mark_all_read') }}</button></form>@endcan</div>
            <div id="header-notification-items" class="overflow-auto" style="max-height:24rem">
              @forelse($headerNotifications as $item)
                @php($data = $item->data)
                @can('notifications.update')<form method="POST" action="{{ route('notifications.read', $item->id) }}">@csrf<button class="dropdown-item text-wrap border-bottom py-3" type="submit"><span class="d-block fw-semibold">{{ __('admin_notifications.events.'.($data['event'] ?? 'booking_created')) }}</span><small class="d-block text-body-secondary">{{ $data['client_name'] ?? '' }} · {{ $data['service_name'] ?? '' }}</small><small class="text-body-tertiary">{{ $item->created_at->diffForHumans() }}</small></button></form>@else<div class="dropdown-item text-wrap border-bottom py-3"><span class="d-block fw-semibold">{{ __('admin_notifications.events.'.($data['event'] ?? 'booking_created')) }}</span><small class="d-block text-body-secondary">{{ $data['client_name'] ?? '' }} · {{ $data['service_name'] ?? '' }}</small></div>@endcan
              @empty<div class="p-4 text-center text-body-secondary">{{ __('admin_notifications.empty') }}</div>@endforelse
            </div>
            <a class="d-block text-center fw-semibold p-2" href="{{ route('notifications.index') }}">{{ __('admin_notifications.view_all') }}</a>
          </div>
        </li>
        @endcan
        @can('crm.chat.view')
        <li class="nav-item">
          <a class="nav-link px-2 position-relative d-flex align-items-center justify-content-center" href="{{ route('crm.chat.index') }}" aria-label="{{ __('crm.chat') }}" style="width:2.25rem;height:2.25rem">
            <span data-feather="message-square"></span>
            <span id="header-crm-chat-count" class="position-absolute badge rounded-pill bg-danger p-0 d-flex align-items-center justify-content-center {{ ($unreadCrmChatCount ?? 0) > 0 ? '' : 'd-none' }}" style="top:-.15rem;right:-.05rem;min-width:1rem;height:1rem;font-size:.62rem">{{ ($unreadCrmChatCount ?? 0) > 99 ? '99+' : ($unreadCrmChatCount ?? 0) }}</span>
          </a>
        </li>
        @endcan
        <li class="nav-item dropdown">
          <a class="nav-link lh-1 pe-0" id="navbarDropdownUser" href="#!" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
            <x-ui.avatar :user="Auth::user()" :size="40" eager />
          </a>
          <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret py-0 dropdown-profile shadow border" aria-labelledby="navbarDropdownUser">
            <div class="card position-relative border-0">
              <div class="card-body p-0">
                <div class="text-center pt-4 pb-3">
                  <x-ui.avatar :user="Auth::user()" :size="64" eager />
                  <h6 class="mt-2 text-body-emphasis">{{ Auth::user()->name }}</h6>
                  <p class="mt-2 text-body-emphasis">{{ Auth::user()->username }}</p>
                </div>
              </div>
              <div class="card-footer p-0 border-top border-translucent">
                <div class="px-3">
                  @can('profile.view')<a class="btn btn-phoenix-secondary d-flex flex-center w-100 mb-2" href="{{ route('profile.index') }}"><span class="me-2" data-feather="user"></span>{{ __('admin_nav.profile') }}</a>@endcan
                  <a class="btn btn-phoenix-secondary d-flex flex-center w-100" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <span class="me-2" data-feather="log-out"> </span>{{ __('admin_nav.sign_out') }}
                  </a>
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                      @csrf
                  </form>
                </div>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </nav>
@if($canAdminSearch)
@push('scripts')
<script>
(() => {
  const input = document.getElementById('adminHeaderSearch');
  const results = document.getElementById('adminHeaderSearchResults');
  if (!input || !results) return;
  let timer;
  let requestController;
  const close = () => results.classList.add('d-none');
  input.addEventListener('input', () => {
    window.clearTimeout(timer);
    requestController?.abort();
    const query = input.value.trim();
    if (query.length < 2) { close(); results.innerHTML = ''; return; }
    timer = window.setTimeout(async () => {
      requestController = new AbortController();
      try {
        const response = await fetch(@js(route('admin.search')) + '?q=' + encodeURIComponent(query), {
          headers: {'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest'},
          signal: requestController.signal,
          cache: 'no-store'
        });
        if (!response.ok) return;
        const payload = await response.json();
        results.innerHTML = payload.html;
        results.classList.remove('d-none');
        window.feather?.replace();
      } catch (error) {
        if (error.name !== 'AbortError') close();
      }
    }, 250);
  });
  input.addEventListener('focus', () => { if (results.innerHTML) results.classList.remove('d-none'); });
  input.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
  document.addEventListener('click', event => { if (!event.target.closest('.admin-header-search')) close(); });
})();
</script>
@endpush
@endif
@can('notifications.view')
@push('scripts')
<script>
(() => {
  const endpoint = @js(route('notifications.status'));
  let loading = false;
  const renderCount = (element, count) => {
    if (!element) return;
    const textNode = Array.from(element.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
    if (textNode) textNode.nodeValue = count > 99 ? '99+' : String(count);
    element.classList.toggle('d-none', count < 1);
  };
  const refreshNotifications = async () => {
    if (loading || document.hidden) return;
    loading = true;
    try {
      const response = await fetch(endpoint, {
        headers: {'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest'},
        credentials: 'same-origin',
        cache: 'no-store'
      });
      if (!response.ok) return;
      const data = await response.json();
      renderCount(document.getElementById('header-notification-count'), data.count);
      renderCount(document.getElementById('sidebar-notification-count'), data.count);
      document.getElementById('header-notifications-read-all')?.classList.toggle('d-none', data.count < 1);
      const items = document.getElementById('header-notification-items');
      if (items) items.innerHTML = data.html;
    } catch (_) {
      // A temporary network failure must not interrupt work in the admin panel.
    } finally {
      loading = false;
    }
  };
  window.setInterval(refreshNotifications, 4000);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshNotifications(); });
  window.addEventListener('focus', refreshNotifications);
})();
</script>
@endpush
@endcan
@can('crm.chat.view')
@push('scripts')
<script>
(() => {
  const endpoint = @js(route('crm.chat.status'));
  let loading = false;
  const render = (element, count) => {
    if (!element) return;
    element.textContent = count > 99 ? '99+' : String(count);
    element.classList.toggle('d-none', count < 1);
  };
  const refresh = async () => {
    if (loading || document.hidden) return;
    loading = true;
    try {
      const response = await fetch(endpoint, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin',cache:'no-store'});
      if (!response.ok) return;
      const data = await response.json();
      render(document.getElementById('header-crm-chat-count'), data.count);
      render(document.getElementById('sidebar-crm-chat-count'), data.count);
      render(document.getElementById('sidebar-crm-group-count'), data.count);
    } catch (_) {
      // Polling resumes automatically after a temporary connection failure.
    } finally { loading = false; }
  };
  window.setInterval(refresh, 4000);
  window.addEventListener('focus', refresh);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
})();
</script>
@endpush
@endcan
