{{-- Renders any session flash message as a brand alert. --}}
@if (session('success'))
    <x-ls.alert variant="success">{{ session('success') }}</x-ls.alert>
@endif

@if (session('error'))
    <x-ls.alert variant="danger">{{ session('error') }}</x-ls.alert>
@endif

@if (session('warning'))
    <x-ls.alert variant="warning">{{ session('warning') }}</x-ls.alert>
@endif

@if (session('info'))
    <x-ls.alert variant="info">{{ session('info') }}</x-ls.alert>
@endif
