@extends('layouts.main')

@section('title', 'Edit Placement • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Edit Placement" :back="route('placements.index')" />

    <form action="{{ route('placements.update', $placement) }}" method="POST" class="mt-8 max-w-2xl space-y-6">
      @csrf
      @method('PUT')

      <x-ls.input-field name="name" label="Name" :value="old('name', $placement->name)" required />

      <x-ls.textarea-field name="description" label="Description" :value="old('description', $placement->description)" rows="4" />

      <x-ls.select-field
        name="platform_id"
        label="Platform"
        placeholder="— None —"
        :options="$platforms->pluck('name', 'id')->toArray()"
        :selected="old('platform_id', $placement->platform_id)"
      />

      <x-ls.select-field
        name="type"
        label="Type"
        :options="collect(\App\PlacementType::cases())->mapWithKeys(fn($t) => [$t->value => $t->label()])->toArray()"
        :selected="old('type', $placement->type?->value)"
        required
      />

      <x-ls.input-field name="price" label="Price (MUR)" :value="old('price', $placement->price)" min="0" required />

      <div class="flex items-center gap-4">
        <x-ls.button type="submit" variant="primary">Update Placement</x-ls.button>
        <x-ls.button :href="route('placements.index')" variant="ghost">Cancel</x-ls.button>
      </div>
    </form>
  </x-ls.page>
@endsection
