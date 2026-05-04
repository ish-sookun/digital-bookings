@props([
    'name',
    'label' => null,
    'value' => '1',
    'checked' => false,
    'id' => null,
    'includeHidden' => true,
])

@php
    $fieldId = $id ?? $name;
@endphp

<label class="ls-check cursor-pointer">
    @if ($includeHidden)
        <input type="hidden" name="{{ $name }}" value="0" />
    @endif
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $fieldId }}"
        value="{{ $value }}"
        @checked($checked)
        {{ $attributes }}
    />
    @if ($label)
        <span>{{ $label }}</span>
    @endif
    {{ $slot }}
</label>
