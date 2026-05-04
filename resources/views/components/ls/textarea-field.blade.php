@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'rows' => 4,
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

    <textarea
        name="{{ $name }}"
        id="{{ $fieldId }}"
        rows="{{ $rows }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
        {{ $attributes->class(['ls-textarea', 'error' => $hasError]) }}
    >{{ $value }}</textarea>

    @if ($hint && ! $hasError)
        <span class="hint">{{ $hint }}</span>
    @endif

    @error($name)
        <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
    @enderror
</div>
