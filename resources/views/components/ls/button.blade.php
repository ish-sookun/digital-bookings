@props([
    'variant' => 'primary', // primary | secondary | outline | ghost | danger
    'size' => 'md',         // sm | md | lg
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'ls-btn cursor-pointer';
    $variantClass = match ($variant) {
        'primary' => 'ls-btn-primary',
        'secondary' => 'ls-btn-secondary',
        'outline' => 'ls-btn-outline',
        'ghost' => 'ls-btn-ghost',
        'danger' => 'ls-btn-danger',
        default => 'ls-btn-primary',
    };
    $sizeClass = match ($size) {
        'sm' => 'ls-btn-sm',
        'lg' => 'ls-btn-lg',
        default => '',
    };
    $classes = trim("$base $variantClass $sizeClass");
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
