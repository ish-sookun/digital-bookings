@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center gap-4">
        <a href="{{ route('budgets.index', ['fy' => $financialYearStart]) }}" class="text-gray-400 hover:text-gray-600">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 0 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
          </svg>
        </a>
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Set Budget — {{ $monthLabel }}</h1>
          <p class="mt-1 text-sm text-gray-500">Platform: <span class="font-medium text-gray-900">{{ $platform->name }}</span></p>
        </div>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      <form action="{{ route('budgets.update', ['platform' => $platform, 'year' => $year, 'month' => $month]) }}" method="POST" class="mt-8 max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <div>
          <label for="amount" class="block text-sm font-medium text-gray-700">Monthly Budget (MUR)</label>
          <div class="mt-2">
            <input type="number" step="0.01" min="0" name="amount" id="amount" value="{{ old('amount', $budget->amount) }}" required
              class="block w-full rounded-lg border @error('amount') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
          </div>
          @error('amount')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <h2 class="text-sm font-semibold text-gray-900">Salesperson Targets</h2>
          <p class="mt-1 text-xs text-gray-500">Set a target for each salesperson for this month. Leave blank to remove a target.</p>

          <div class="mt-4 space-y-4">
            @forelse($salespeople as $salesperson)
              @php
                $existing = $existingTargets->get($salesperson->id);
                $value = old('targets.'.$salesperson->id, $existing?->amount);
              @endphp
              <div class="flex items-center gap-4">
                <label for="target_{{ $salesperson->id }}" class="w-48 text-sm text-gray-700">
                  {{ $salesperson->first_name }} {{ $salesperson->last_name }}
                </label>
                <div class="flex-1">
                  <input type="number" step="0.01" min="0" name="targets[{{ $salesperson->id }}]" id="target_{{ $salesperson->id }}" value="{{ $value }}" placeholder="MUR"
                    class="block w-full rounded-lg border @error('targets.'.$salesperson->id) border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
                  @error('targets.'.$salesperson->id)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            @empty
              <p class="text-sm text-gray-500">No salespersons available. Add salespersons first to set targets.</p>
            @endforelse
          </div>
        </div>

        <div class="flex items-center gap-4">
          <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
            Save Budget
          </button>
          <a href="{{ route('budgets.index', ['fy' => $financialYearStart]) }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </main>
@endsection
