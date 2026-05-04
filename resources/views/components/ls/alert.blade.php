@props([
    'variant' => 'info', // info | success | warning | danger
])

@php
    $variantClass = match ($variant) {
        'success' => 'ls-alert-success',
        'warning' => 'ls-alert-warning',
        'danger' => 'ls-alert-danger',
        default => 'ls-alert-info',
    };
@endphp

<div {{ $attributes->class(['ls-alert', $variantClass]) }} role="alert">
    {{ $slot }}
</div>
