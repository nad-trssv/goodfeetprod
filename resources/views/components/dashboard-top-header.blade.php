
@php
  $canAdminSearch = auth()->user()->hasPermission('appointments.view') || auth()->user()->hasPermission('customers.view') || auth()->user()->hasPermission('services.view');
  $canQuickAppointments = auth()->user()->hasPermission('appointments.view') || auth()->user()->hasPermission('appointments.create');
@endphp
<style>
  .admin-header-search{width:min(32rem,38vw)}
  .admin-header-search-results{top:calc(100% + .45rem);max-height:min(32rem,70vh);overflow:auto;z-index:1080}
  .admin-quick-link{white-space:nowrap}
  .work-shift-panel{width:18rem;max-width:calc(100vw - 2rem)}.work-shift-timer{font-variant-numeric:tabular-nums;font-size:1.45rem;font-weight:750}.work-shift-dot{width:.55rem;height:.55rem;border-radius:50%;background:var(--phoenix-secondary-color)}.work-shift-panel[data-status="running"] .work-shift-dot{background:#20c997;box-shadow:0 0 0 .2rem rgba(32,201,151,.13)}.work-shift-panel[data-status="paused"] .work-shift-dot{background:#f59f00}
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
        @if($canQuickAppointments)<li class="nav-item dropdown px-1">
          <button class="btn btn-sm btn-phoenix-primary admin-quick-link d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('admin_nav.quick_actions') }}"><span data-feather="zap" style="width:16px;height:16px"></span><span class="d-none d-lg-inline">{{ __('admin_nav.quick_actions') }}</span><span data-feather="chevron-down" style="width:14px;height:14px"></span></button>
          <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret shadow border mt-2">
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
                  <p class="mt-2 mb-0 text-body-emphasis">{{ Auth::user()->username }}</p>
                </div>
                <div id="workShiftPanel" class="work-shift-panel mx-3 mb-3 rounded-3 border p-3" data-status="{{ $workShiftState['status'] }}" data-state='@json($workShiftState)' data-status-url="{{ route('work-time.status') }}" data-start-url="{{ route('work-time.start') }}" data-pause-url="{{ route('work-time.pause') }}" data-resume-url="{{ route('work-time.resume') }}" data-stop-url="{{ route('work-time.stop') }}">
                  <div class="d-flex align-items-center gap-2 mb-1"><span class="work-shift-dot"></span><span class="fw-semibold">{{ __('admin_work_time.my_shift') }}</span></div>
                  <div id="workShiftStatus" class="fs-9 text-body-secondary mb-2"></div>
                  <div id="workShiftActiveDetails" class="d-none">
                    <div id="workShiftStarted" class="fs-10 text-body-tertiary"></div>
                    <div id="workShiftTimer" class="work-shift-timer my-2">00:00:00</div>
                    <div class="fs-10 text-body-tertiary mb-2">{{ __('admin_work_time.elapsed') }}</div>
                  </div>
                  <div id="workShiftError" class="alert alert-subtle-danger py-2 px-2 fs-10 d-none" role="alert"></div>
                  <div class="d-grid gap-2">
                    <button id="workShiftStart" class="btn btn-sm btn-primary" type="button" data-action="start"><span data-feather="play" class="me-1"></span>{{ __('admin_work_time.start_work') }}</button>
                    <div id="workShiftRunningActions" class="d-none gap-2"><button class="btn btn-sm btn-warning flex-grow-1" type="button" data-action="pause"><span data-feather="pause" class="me-1"></span>{{ __('admin_work_time.pause') }}</button><button class="btn btn-sm btn-outline-danger flex-grow-1" type="button" data-action="stop"><span data-feather="square" class="me-1"></span>{{ __('admin_work_time.stop') }}</button></div>
                    <div id="workShiftPausedActions" class="d-none gap-2"><button class="btn btn-sm btn-success flex-grow-1" type="button" data-action="resume"><span data-feather="play" class="me-1"></span>{{ __('admin_work_time.resume') }}</button><button class="btn btn-sm btn-outline-danger flex-grow-1" type="button" data-action="stop"><span data-feather="square" class="me-1"></span>{{ __('admin_work_time.stop') }}</button></div>
                  </div>
                  @can('work_time.view')<a class="btn btn-link btn-sm w-100 mt-2 mb-0" href="{{ route('admin.work-time.index') }}">{{ __('admin_work_time.open_report') }}</a>@endcan
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
@push('scripts')
<script>
(() => {
  const panel = document.getElementById('workShiftPanel');
  if (!panel) return;
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  const labels = @json(__('admin_work_time.status'));
  const genericError = @json(__('admin_work_time.action_failed'));
  let state = JSON.parse(panel.dataset.state || '{"status":"inactive"}');
  let stateReceivedAt = Date.now();
  let busy = false;
  const nodes = {
    status: document.getElementById('workShiftStatus'), details: document.getElementById('workShiftActiveDetails'),
    started: document.getElementById('workShiftStarted'), timer: document.getElementById('workShiftTimer'),
    start: document.getElementById('workShiftStart'), running: document.getElementById('workShiftRunningActions'),
    paused: document.getElementById('workShiftPausedActions'), error: document.getElementById('workShiftError')
  };
  const pad = value => String(value).padStart(2, '0');
  const renderTimer = () => {
    let seconds = Number(state.worked_seconds || 0);
    if (state.status === 'running') seconds += Math.max(0, Math.floor((Date.now() - stateReceivedAt) / 1000));
    const hours = Math.floor(seconds / 3600);
    nodes.timer.textContent = `${pad(hours)}:${pad(Math.floor(seconds % 3600 / 60))}:${pad(seconds % 60)}`;
  };
  const render = () => {
    const active = ['running','paused'].includes(state.status);
    panel.dataset.status = state.status;
    nodes.status.textContent = labels[state.status] || labels.inactive;
    nodes.details.classList.toggle('d-none', !active);
    nodes.start.classList.toggle('d-none', active);
    nodes.running.classList.toggle('d-none', state.status !== 'running');
    nodes.running.classList.toggle('d-flex', state.status === 'running');
    nodes.paused.classList.toggle('d-none', state.status !== 'paused');
    nodes.paused.classList.toggle('d-flex', state.status === 'paused');
    nodes.started.textContent = state.started_at ? @json(__('admin_work_time.started_at', ['time'=>'__TIME__'])).replace('__TIME__', new Date(state.started_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})) : '';
    renderTimer();
  };
  const act = async action => {
    if (busy) return;
    busy = true;
    nodes.error.classList.add('d-none');
    panel.querySelectorAll('button[data-action]').forEach(button => button.disabled = true);
    try {
      const response = await fetch(panel.dataset[`${action}Url`], {method:'POST', credentials:'same-origin', headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':token}});
      const data = await response.json();
      if (!response.ok) throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || genericError);
      state = data; stateReceivedAt = Date.now(); render();
    } catch (error) {
      nodes.error.textContent = error.message || genericError;
      nodes.error.classList.remove('d-none');
    } finally {
      busy = false;
      panel.querySelectorAll('button[data-action]').forEach(button => button.disabled = false);
    }
  };
  panel.addEventListener('click', event => { const button = event.target.closest('button[data-action]'); if (button) { event.stopPropagation(); act(button.dataset.action); } });
  const refresh = async () => {
    if (busy || document.hidden) return;
    try { const response = await fetch(panel.dataset.statusUrl, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin',cache:'no-store'}); if (response.ok) { state = await response.json(); stateReceivedAt = Date.now(); render(); } } catch (_) {}
  };
  window.setInterval(renderTimer, 1000);
  window.setInterval(refresh, 30000);
  window.addEventListener('focus', refresh);
  render();
})();
</script>
@endpush
@push('scripts')
<script>
(() => {
  if (window.adminNotificationSound) return;
  let context = null;
  let unlocked = false;
  let lastPlayedAt = 0;
  const unlock = async () => {
    try {
      context ??= new (window.AudioContext || window.webkitAudioContext)();
      if (context.state === 'suspended') await context.resume();
      unlocked = context.state === 'running';
    } catch (_) {
      unlocked = false;
    }
  };
  document.addEventListener('pointerdown', unlock, {once:true, passive:true});
  document.addEventListener('keydown', unlock, {once:true});
  window.adminNotificationSound = {
    play() {
      if (!unlocked || !context || Date.now() - lastPlayedAt < 900) return;
      lastPlayedAt = Date.now();
      const startedAt = context.currentTime;
      const gain = context.createGain();
      gain.gain.setValueAtTime(0.0001, startedAt);
      gain.gain.exponentialRampToValueAtTime(0.12, startedAt + 0.015);
      gain.gain.exponentialRampToValueAtTime(0.0001, startedAt + 0.42);
      gain.connect(context.destination);
      [740, 988].forEach((frequency, index) => {
        const oscillator = context.createOscillator();
        oscillator.type = 'sine';
        oscillator.frequency.value = frequency;
        oscillator.connect(gain);
        oscillator.start(startedAt + index * 0.12);
        oscillator.stop(startedAt + 0.28 + index * 0.12);
      });
    }
  };
})();
</script>
@endpush
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
  let previousCount = @json((int) $unreadNotificationCount);
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
      if (Number(data.count) > previousCount) window.adminNotificationSound?.play();
      previousCount = Number(data.count);
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
  let previousCount = @json((int) ($unreadCrmChatCount ?? 0));
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
      if (Number(data.count) > previousCount) window.adminNotificationSound?.play();
      previousCount = Number(data.count);
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
