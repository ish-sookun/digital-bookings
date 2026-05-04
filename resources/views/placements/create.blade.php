@extends('layouts.main')

@section('title', 'Add Placement • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Add Placement" :back="route('placements.index')" />

    <form action="{{ route('placements.store') }}" method="POST" class="mt-8 max-w-2xl space-y-6">
      @csrf

      <x-ls.input-field name="name" label="Name" :value="old('name')" required />

      <x-ls.textarea-field name="description" label="Description" :value="old('description')" rows="4" />

      <x-ls.select-field
        name="platform_id"
        label="Platform"
        placeholder="— None —"
        :options="$platforms->pluck('name', 'id')->toArray()"
        :selected="old('platform_id')"
      />

      <x-ls.select-field
        name="type"
        label="Type"
        :options="collect(\App\PlacementType::cases())->mapWithKeys(fn($t) => [$t->value => $t->label()])->toArray()"
        :selected="old('type')"
        required
      />

      <x-ls.input-field name="price" label="Price (MUR)" :value="old('price')" min="0" required />

      <div class="flex items-center gap-4">
        <x-ls.button type="submit" variant="primary">Save Placement</x-ls.button>
        <x-ls.button :href="route('placements.index')" variant="ghost">Cancel</x-ls.button>
      </div>
    </form>
  </x-ls.page>
@endsection
