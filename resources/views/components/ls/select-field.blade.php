@props([
    'name',
    'label' => null,
    'options' => [],     // [value => label]
    'selected' => null,
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

    <select
        name="{{ $name }}"
        id="{{ $fieldId }}"
        @if ($required) required @endif
        {{ $attributes->class(['ls-select', 'error' => $hasError]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $optionLabel)
            <option value="{{ $value }}" @selected((string) $selected === (string) $value)>
                {{ $optionLabel }}
            </option>
        @endforeach
        {{ $slot }}
    </select>

    @if ($hint && ! $hasError)
        <span class="hint">{{ $hint }}</span>
    @endif

    @error($name)
        <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
    @enderror
</div>
