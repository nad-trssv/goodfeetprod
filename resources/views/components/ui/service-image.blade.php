@props(['service' => null, 'src' => null, 'alt' => null, 'width' => 360, 'height' => 240, 'eager' => false])
@php
    $resolvedSrc = $src ?: data_get($service, 'image_url') ?: asset('assets/img/generic/default.png');
    $resolvedAlt = $alt ?: data_get($service, 'translation.name') ?: data_get($service, 'name', '');
    $style = 'object-fit:cover;'.$attributes->get('style', '');
@endphp
<img src="{{ $resolvedSrc }}" alt="{{ $resolvedAlt }}" width="{{ (int)$width }}" height="{{ (int)$height }}" loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async" {{ $attributes->except('style')->class(['object-fit-cover']) }} style="{{ $style }}">
