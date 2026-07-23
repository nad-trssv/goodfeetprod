@php($brand = app(\App\Services\BrandThemeService::class)->colors())
<style id="brand-theme">
:root {
  --brand-accent: {{ $brand['accent'] }};
  --brand-accent-rgb: {{ $brand['rgb'] }};
  --brand-accent-dark: {{ $brand['dark'] }};
  --brand-accent-light: {{ $brand['light'] }};
  --brand-accent-subtle: {{ $brand['subtle'] }};
  --brand-accent-contrast: {{ $brand['contrast'] }};
  --phoenix-primary: var(--brand-accent);
  --phoenix-primary-rgb: var(--brand-accent-rgb);
  --phoenix-primary-dark: var(--brand-accent-dark);
  --phoenix-primary-darker: var(--brand-accent-dark);
  --phoenix-primary-light: var(--brand-accent-light);
  --phoenix-primary-lighter: var(--brand-accent-subtle);
  --phoenix-primary-dark-rgb: var(--brand-accent-rgb);
  --phoenix-primary-light-rgb: var(--brand-accent-rgb);
  --phoenix-primary-bg-subtle: var(--brand-accent-subtle);
  --phoenix-primary-border-subtle: var(--brand-accent-light);
  --phoenix-link-color: var(--brand-accent);
  --phoenix-link-color-rgb: var(--brand-accent-rgb);
  --phoenix-focus-ring-color: rgba(var(--brand-accent-rgb), .25);
  --phoenix-navbar-vertical-link-active-color: var(--brand-accent);
  --phoenix-theme-wizard-complete-color: var(--brand-accent);
  --phoenix-theme-wizard-active-color: var(--brand-accent);
  --phoenix-flatpickr-calendar-day-selected-bg: rgba(var(--brand-accent-rgb), .2);
  --calendar-btn-color: var(--brand-accent);
  --secondary: var(--brand-accent);
  --secondary-dark: var(--brand-accent-dark);
  --text-primary: var(--brand-accent);
  --border: 1px solid var(--brand-accent);
}
[data-bs-theme=dark] {
  --phoenix-primary: var(--brand-accent);
  --phoenix-primary-rgb: var(--brand-accent-rgb);
  --phoenix-link-color: var(--brand-accent-light);
  --phoenix-link-color-rgb: var(--brand-accent-rgb);
  --phoenix-primary-bg-subtle: rgba(var(--brand-accent-rgb), .18);
  --phoenix-primary-border-subtle: var(--brand-accent-dark);
}
.btn-primary { --phoenix-btn-color:var(--brand-accent-contrast);--phoenix-btn-bg:var(--brand-accent);--phoenix-btn-border-color:var(--brand-accent);--phoenix-btn-hover-color:var(--brand-accent-contrast);--phoenix-btn-hover-bg:var(--brand-accent-dark);--phoenix-btn-hover-border-color:var(--brand-accent-dark);--phoenix-btn-active-bg:var(--brand-accent-dark);--phoenix-btn-active-border-color:var(--brand-accent-dark); }
.btn-outline-primary { --phoenix-btn-color:var(--brand-accent);--phoenix-btn-border-color:var(--brand-accent);--phoenix-btn-hover-color:var(--brand-accent-contrast);--phoenix-btn-hover-bg:var(--brand-accent);--phoenix-btn-hover-border-color:var(--brand-accent);--phoenix-btn-active-bg:var(--brand-accent); }
.btn-phoenix-primary { color:var(--brand-accent)!important;border-color:rgba(var(--brand-accent-rgb),.35)!important;background:var(--brand-accent-subtle)!important; }
.btn-phoenix-primary:hover { color:var(--brand-accent-contrast)!important;background:var(--brand-accent)!important; }
.text-primary,.fc-primary { color:var(--brand-accent)!important }.bg-primary { background-color:var(--brand-accent)!important }.border-primary { border-color:var(--brand-accent)!important }
.form-check-input:checked { background-color:var(--brand-accent)!important;border-color:var(--brand-accent)!important }
.form-control:focus,.form-select:focus { border-color:var(--brand-accent-light)!important;box-shadow:0 0 0 .25rem rgba(var(--brand-accent-rgb),.2)!important }
.page-item.active .page-link,.pagination .active>.page-link { color:var(--brand-accent-contrast)!important;background-color:var(--brand-accent)!important;border-color:var(--brand-accent)!important }
.page-link,.nav-link.active,.dropdown-item.active { color:var(--brand-accent) }.dropdown-item.active { color:var(--brand-accent-contrast);background-color:var(--brand-accent) }
.badge-phoenix-primary { color:var(--brand-accent-dark)!important;background:var(--brand-accent-subtle)!important }
.flatpickr-day.selected,.flatpickr-day.startRange,.flatpickr-day.endRange { color:var(--brand-accent-contrast)!important;background:var(--brand-accent)!important;border-color:var(--brand-accent)!important }
.fc-state-active,.fc-button-active,.fc-event-dot { background-color:var(--brand-accent)!important;border-color:var(--brand-accent)!important;color:var(--brand-accent-contrast)!important }
.btn_primary { color:var(--brand-accent-contrast)!important;background-color:var(--brand-accent)!important }.btn_primary:hover { background-color:var(--brand-accent-dark)!important }
.btn_primary__opacity:hover { color:var(--brand-accent-contrast)!important;background-color:var(--brand-accent)!important }
</style>
