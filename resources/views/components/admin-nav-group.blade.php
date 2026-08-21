@props(['id', 'label', 'icon', 'open' => false, 'badgeId' => null, 'badgeCount' => 0])

<li class="nav-item">
  <div class="nav-item-wrapper">
    <button class="nav-link label-1 w-100 border-0 bg-transparent {{ $open ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $id }}" aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="{{ $id }}">
      <span class="d-flex align-items-center w-100">
        <span class="nav-link-icon"><span data-feather="{{ $icon }}"></span></span>
        <span class="nav-link-text-wrapper flex-grow-1 text-start"><span class="nav-link-text">{{ $label }}</span></span>
        @if($badgeId)
          <span id="{{ $badgeId }}" class="badge rounded-pill bg-danger me-2 {{ (int) $badgeCount > 0 ? '' : 'd-none' }}">{{ (int) $badgeCount > 99 ? '99+' : (int) $badgeCount }}</span>
        @endif
        <span class="nav-group-chevron ms-auto"><span data-feather="chevron-down"></span></span>
      </span>
    </button>
  </div>
  <div class="collapse {{ $open ? 'show' : '' }}" id="{{ $id }}">
    <div class="ps-3 border-start ms-3">{{ $slot }}</div>
  </div>
</li>
