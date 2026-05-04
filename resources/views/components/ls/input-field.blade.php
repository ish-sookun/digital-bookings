@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'hint' => null,
    'id' => null,
])

@php
    $fieldId = $id ?? $name;
    $hasError = $errors->has($name);
@endphp

<div class="ls-field">
    @if ($label)
        <label for="{{ $fieldId }}">
            {{ $label }}
            @if ($required)
                <span class="text-ls-danger">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $fieldId }}"
        @if (! is_null($value)) value="{{ $value }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
        {{ $attributes->class(['ls-input', 'error' => $hasError]) }}
    />

    @if ($hint && ! $hasError)
        <span class="hint">{{ $hint }}</span>
    @endif

    @error($name)
        <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
    @enderror
</div>
