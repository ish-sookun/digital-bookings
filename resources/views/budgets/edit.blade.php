@extends('layouts.main')

@section('title', 'Set Budget • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header
      title="Set Budget — {{ $monthLabel }}"
      :back="route('budgets.index', ['fy' => $financialYearStart])"
      subtitle="Platform: {{ $platform->name }}"
    />

    <form action="{{ route('budgets.update', ['platform' => $platform, 'year' => $year, 'month' => $month]) }}" method="POST" class="mt-8 max-w-2xl space-y-6">
      @csrf
      @method('PUT')

      <x-ls.input-field
        name="amount"
        type="number"
        label="Monthly Budget (MUR)"
        :value="old('amount', $budget->amount)"
        step="0.01"
        min="0"
        required
      />

      <div>
        <h2 class="text-sm font-medium text-ls-text">Salesperson Targets</h2>
        <p class="mt-1 text-xs text-ls-text-muted">Set a target for each salesperson for this month. Leave blank to remove a target.</p>

        <div class="mt-4 space-y-4">
          @forelse($salespeople as $salesperson)
            @php
              $existing = $existingTargets->get($salesperson->id);
              $value = old('targets.'.$salesperson->id, $existing?->amount);
            @endphp
            <div class="flex items-center gap-4">
              <label for="target_{{ $salesperson->id }}" class="w-48 text-sm text-ls-text">
                {{ $salesperson->first_name }} {{ $salesperson->last_name }}
              </label>
              <div class="flex-1">
                <input type="number" step="0.01" min="0" name="targets[{{ $salesperson->id }}]" id="target_{{ $salesperson->id }}" value="{{ $value }}" placeholder="MUR"
                  @class(['ls-input', 'error' => $errors->has('targets.'.$salesperson->id)]) />
                @error('targets.'.$salesperson->id)
                  <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
                @enderror
              </div>
            </div>
          @empty
            <p class="text-sm text-ls-text-muted">No salespersons available. Add salespersons first to set targets.</p>
          @endforelse
        </div>
      </div>

      <div class="flex items-center gap-4">
        <x-ls.button type="submit" variant="primary">Save Budget</x-ls.button>
        <x-ls.button :href="route('budgets.index', ['fy' => $financialYearStart])" variant="ghost">Cancel</x-ls.button>
      </div>
    </form>
  </x-ls.page>
@endsection
