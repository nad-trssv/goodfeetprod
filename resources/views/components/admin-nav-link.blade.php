@props(['route', 'icon', 'label'])
<div class="nav-item-wrapper">
  <a class="nav-link {{ request()->routeIs($route) ? 'active' : '' }} label-1" href="{{ route($route) }}">
    <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="{{ $icon }}"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">{{ $label }}</span></span></div>
  </a>
</div>
