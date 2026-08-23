@props([
    'label',
    'value',
    'icon' => 'bar-chart-2',
    'tone' => 'primary',
    'hint' => null,
])
<div {{ $attributes->class(['card h-100 border-0 shadow-sm']) }}>
  <div class="card-body p-3 p-lg-4">
    <div class="d-flex align-items-start justify-content-between gap-3">
      <div class="min-w-0">
        <div class="text-body-tertiary fs-9 text-uppercase fw-semibold mb-2">{{ $label }}</div>
        <div class="fs-5 fw-bold text-body-emphasis text-break">{{ $value }}</div>
      </div>
      <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $tone }}-subtle text-{{ $tone }} flex-shrink-0" style="width:2.5rem;height:2.5rem">
        <span data-feather="{{ $icon }}" style="width:1.15rem;height:1.15rem"></span>
      </span>
    </div>
    @if($hint)<div class="text-body-tertiary fs-10 mt-2">{{ $hint }}</div>@endif
    {{ $slot }}
  </div>
</div>
