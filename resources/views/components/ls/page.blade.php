{{-- Standard page wrapper: occupies the main content slot in layouts.main. --}}
<main {{ $attributes->class('flex-1 bg-ls-surface') }}>
    <div class="px-12 py-10">
        {{ $slot }}
    </div>
</main>
