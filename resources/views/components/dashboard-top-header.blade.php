
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
      <ul class="navbar-nav navbar-nav-icons flex-row">
        <li class="nav-item">
          <div class="theme-control-toggle fa-icon-wait px-2">
            <input class="form-check-input ms-0 theme-control-toggle-input" type="checkbox" data-theme-control="phoenixTheme" value="dark" id="themeControlToggle" />
            <label class="mb-0 theme-control-toggle-label theme-control-toggle-light" for="themeControlToggle" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Switch theme" style="height:32px;width:32px;"><span class="icon" data-feather="moon"></span></label>
            <label class="mb-0 theme-control-toggle-label theme-control-toggle-dark" for="themeControlToggle" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Switch theme" style="height:32px;width:32px;"><span class="icon" data-feather="sun"></span></label>
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link px-2 position-relative d-flex align-items-center justify-content-center" id="navbarNotifications" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="{{ __('admin_notifications.title') }}" style="width:2.25rem;height:2.25rem">
            <span data-feather="bell"></span>
            <span id="header-notification-count" class="position-absolute badge rounded-pill bg-danger p-0 d-flex align-items-center justify-content-center {{ $unreadNotificationCount > 0 ? '' : 'd-none' }}" style="top:-.15rem;right:-.05rem;min-width:1rem;height:1rem;font-size:.62rem">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}<span class="visually-hidden">{{ __('admin_notifications.new') }}</span></span>
          </a>
          <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret shadow border p-0" aria-labelledby="navbarNotifications" style="width:min(24rem,calc(100vw - 1rem))">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom"><h6 class="mb-0">{{ __('admin_notifications.title') }}</h6><form id="header-notifications-read-all" class="{{ $unreadNotificationCount ? '' : 'd-none' }}" method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-link btn-sm p-0" type="submit">{{ __('admin_notifications.mark_all_read') }}</button></form></div>
            <div id="header-notification-items" class="overflow-auto" style="max-height:24rem">
              @forelse($headerNotifications as $item)
                @php($data = $item->data)
                <form method="POST" action="{{ route('notifications.read', $item->id) }}">@csrf<button class="dropdown-item text-wrap border-bottom py-3" type="submit"><span class="d-block fw-semibold">{{ __('admin_notifications.events.'.($data['event'] ?? 'booking_created')) }}</span><small class="d-block text-body-secondary">{{ $data['client_name'] ?? '' }} · {{ $data['service_name'] ?? '' }}</small><small class="text-body-tertiary">{{ $item->created_at->diffForHumans() }}</small></button></form>
              @empty<div class="p-4 text-center text-body-secondary">{{ __('admin_notifications.empty') }}</div>@endforelse
            </div>
            <a class="d-block text-center fw-semibold p-2" href="{{ route('notifications.index') }}">{{ __('admin_notifications.view_all') }}</a>
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link lh-1 pe-0" id="navbarDropdownUser" href="#!" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
            <div class="avatar avatar-l ">
              <img class="rounded-circle" src="{{ Auth::user()->profile_photo_path ? asset('public/storage/' . Auth::user()->profile_photo_path) : "https://ui-avatars.com/api/?name=".strtoupper(substr(Auth::user()->name, 0, 1))."&color=7F9CF5&background=EBF4FF" }}" alt="User Avatar" />
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret py-0 dropdown-profile shadow border" aria-labelledby="navbarDropdownUser">
            <div class="card position-relative border-0">
              <div class="card-body p-0">
                <div class="text-center pt-4 pb-3">
                  <div class="avatar avatar-xl ">
                    <img class="rounded-circle" src="{{ Auth::user()->profile_photo_path ? asset('public/storage/' . Auth::user()->profile_photo_path) : "https://ui-avatars.com/api/?name=".strtoupper(substr(Auth::user()->name, 0, 1))."&color=7F9CF5&background=EBF4FF" }}" alt="User Avatar" />
                  </div>
                  <h6 class="mt-2 text-body-emphasis">{{ Auth::user()->name }}</h6>
                  <p class="mt-2 text-body-emphasis">{{ Auth::user()->username }}</p>
                </div>
              </div>
              <div class="card-footer p-0 border-top border-translucent">
                <div class="px-3">
                  <a class="btn btn-phoenix-secondary d-flex flex-center w-100" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <span class="me-2" data-feather="log-out"> </span>Sign out
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
