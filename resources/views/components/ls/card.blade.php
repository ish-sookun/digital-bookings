@props([
    'accent' => false,
    'flush' => false,
])

@php
    $classes = ['ls-card'];
    if ($accent) {
        $classes[] = 'ls-card-accent';
    }
    if ($flush) {
        $classes[] = 'p-0 overflow-hidden';
    }
@endphp

<div {{ $attributes->class($classes) }}>
    {{ $slot }}
</div>
