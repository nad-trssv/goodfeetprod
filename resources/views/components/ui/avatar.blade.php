@props([
    'user' => null,
    'name' => null,
    'src' => null,
    'size' => 40,
    'online' => false,
    'eager' => false,
    'alt' => null,
])
@php
    $resolvedName = trim((string) ($name ?: data_get($user, 'name', '')));
    $photoPath = data_get($user, 'profile_photo_path');
    $resolvedSrc = $src ?: (is_array($user) ? data_get($user, 'profile_photo_url') : null);
    if (!$resolvedSrc && $photoPath) {
        $resolvedSrc = asset('storage/'.ltrim($photoPath, '/'));
    }
    $words = preg_split('/\s+/u', $resolvedName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($words)->take(2)->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->implode('') ?: '—';
    $pixels = max(24, min(160, (int) $size));
    $style = "width:{$pixels}px;height:{$pixels}px;".$attributes->get('style', '');
@endphp
<span {{ $attributes->except('style')->class(['position-relative','d-inline-flex','flex-shrink-0']) }} style="{{ $style }}">
  @if($resolvedSrc)
    <img src="{{ $resolvedSrc }}" alt="{{ $alt ?? $resolvedName }}" width="{{ $pixels }}" height="{{ $pixels }}" loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async" class="rounded-circle w-100 h-100 object-fit-cover">
  @else
    <span role="img" aria-label="{{ $alt ?? $resolvedName }}" class="rounded-circle w-100 h-100 d-inline-flex align-items-center justify-content-center fw-bold bg-primary-subtle text-primary" style="font-size:{{ max(11, round($pixels * .34)) }}px">{{ $initials }}</span>
  @endif
  @if($online)<span class="position-absolute rounded-circle bg-success border border-2 border-body" style="right:0;bottom:0;width:{{ max(8, round($pixels*.22)) }}px;height:{{ max(8, round($pixels*.22)) }}px" title="Онлайн" aria-label="Онлайн"></span>@endif
</span>
