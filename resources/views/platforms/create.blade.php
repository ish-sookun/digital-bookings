@extends('layouts.main')

@section('title', 'Add Platform • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Add Platform" :back="route('platforms.index')" />

    <form action="{{ route('platforms.store') }}" method="POST" class="mt-8 max-w-2xl space-y-6">
      @csrf

      <x-ls.input-field name="name" label="Name" :value="old('name')" required />

      <x-ls.textarea-field name="description" label="Description" :value="old('description')" rows="4" />

      <div class="flex items-center gap-4">
        <x-ls.button type="submit" variant="primary">Save Platform</x-ls.button>
        <x-ls.button :href="route('platforms.index')" variant="ghost">Cancel</x-ls.button>
      </div>
    </form>
  </x-ls.page>
@endsection
