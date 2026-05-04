@props([
    'variant' => 'neutral', // success | warning | danger | info | neutral
    'tag' => false,         // false = .ls-pill (rounded), true = .ls-tag (uppercase tag)
])

@php
    $base = $tag ? 'ls-tag' : 'ls-pill';
    $variantClass = match ($variant) {
        'success' => $tag ? 'ls-tag-success' : 'ls-pill-success',
        'warning' => $tag ? 'ls-tag-warning' : 'ls-pill-warning',
        'danger' => $tag ? 'ls-tag-danger' : 'ls-pill-danger',
        'info' => $tag ? 'ls-tag-info' : 'ls-pill-info',
        default => $tag ? 'ls-tag-neutral' : 'ls-pill-neutral',
    };
@endphp

<span {{ $attributes->class([$base, $variantClass]) }}>
    {{ $slot }}
</span>
