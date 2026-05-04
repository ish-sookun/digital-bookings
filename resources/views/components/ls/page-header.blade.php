@props([
    'title',
    'back' => null,    // optional URL for back arrow
    'subtitle' => null,
])

<div class="flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        @if ($back)
            <a href="{{ $back }}" class="text-ls-text-muted hover:text-ls-text" aria-label="Back">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 0 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
                </svg>
            </a>
        @endif
        <div>
            <h1 class="text-2xl font-medium text-ls-text">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-1 text-sm text-ls-text-muted">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
<div class="mt-6 h-px w-full bg-ls-border"></div>
