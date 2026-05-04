@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->class('space-y-6') }}>
    @if ($title || $description)
        <div>
            @if ($title)
                <h2 class="text-lg font-medium text-ls-text">{{ $title }}</h2>
            @endif
            @if ($description)
                <p class="mt-1 text-sm text-ls-text-muted">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</section>
