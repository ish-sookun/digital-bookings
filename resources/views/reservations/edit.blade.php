@extends('layouts.main')

@section('title', 'Edit Reservation • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Edit Reservation" :back="route('reservations.index')" />

    <form action="{{ route('reservations.update', $reservation) }}" method="POST" class="mt-8 max-w-2xl space-y-8"
      x-data="reservationForm()" x-init="init()"
      @dates-changed="datesCount = $event.detail.count; if (initialized) recalculateGrossAmount()"
      @client-selected.window="onClientSelected($event.detail.id)"
      @represented-client-selected.window="selectedRepresentedClientId = $event.detail.id">
      @csrf
      @method('PUT')

      {{-- Reference --}}
      <div>
        <p class="text-sm font-medium text-ls-text">Reference</p>
        <p class="mt-1 font-mono text-sm text-ls-text">{{ $reservation->reference }}</p>
      </div>

      {{-- Reservation Type --}}
      <x-ls.section title="Reservation Type">
        <div class="flex items-center gap-4">
          @foreach($reservationTypes as $rt)
            <label class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm cursor-pointer transition-colors"
              :class="type === '{{ $rt->value }}' ? 'border-ls-deep bg-ls-deep text-white hover:opacity-90' : 'border-ls-border-strong text-ls-text hover:bg-ls-surface-muted'">
              <input type="radio" name="type" value="{{ $rt->value }}"
                x-model="type"
                @change="onTypeChange()"
                class="sr-only" />
              {{ $rt->label() }}
            </label>
          @endforeach
        </div>
        @error('type')
          <p class="mt-1 text-sm text-ls-danger">{{ $message }}</p>
        @enderror
      </x-ls.section>

      {{-- Client --}}
      <x-ls.section title="Client">
        <div class="ls-field">
          <label>Client <span class="text-ls-danger">*</span></label>
          <x-client-combobox name="client_id" :clients="$clients" :selected="old('client_id', $reservation->client_id)" placeholder="Search for a client..." required dispatch-event="client-selected" />
          @error('client_id')
            <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label class="ls-check cursor-pointer">
            <input type="checkbox" :checked="isClientAgency"
              @change="isClientAgency = $event.target.checked; if (!isClientAgency) { selectedRepresentedClientId = null; }" />
            <span>Client is an agency acting on behalf of another company</span>
          </label>
        </div>

        <div x-show="isClientAgency" x-cloak class="ls-field">
          <label>Representing (end brand)</label>
          <x-client-combobox name="represented_client_id" :clients="$clients" :selected="old('represented_client_id', $reservation->represented_client_id)" placeholder="Search for the end brand..." dispatch-event="represented-client-selected" />
          @error('represented_client_id')
            <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
          @enderror
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div class="ls-field">
            <label for="salesperson_id">Salesperson</label>
            <select name="salesperson_id" id="salesperson_id" class="ls-select @error('salesperson_id') error @enderror">
              <option value="">Select salesperson</option>
              @foreach($salespeople as $salesperson)
                <option value="{{ $salesperson->id }}" {{ old('salesperson_id', $reservation->salesperson_id) == $salesperson->id ? 'selected' : '' }}>{{ $salesperson->first_name }} {{ $salesperson->last_name }}</option>
              @endforeach
            </select>
            @error('salesperson_id')
              <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
            @enderror
          </div>

          <div class="ls-field">
            <label for="status">Status <span class="text-ls-danger">*</span></label>
            <div class="flex items-center gap-3">
              <span class="inline-block h-3 w-3 shrink-0 rounded-full" :class="statusDotClass"></span>
              <select name="status" id="status" required x-model="status" class="ls-select @error('status') error @enderror">
                @foreach($statuses as $statusOption)
                  <option value="{{ $statusOption->value }}" {{ old('status', $reservation->status->value) === $statusOption->value ? 'selected' : '' }}>{{ $statusOption->label() }}</option>
                @endforeach
              </select>
            </div>
            @error('status')
              <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
            @enderror
          </div>
        </div>
      </x-ls.section>

      {{-- Product Details --}}
      <x-ls.section title="Product Details">
        <div class="ls-field">
          <label for="product">Product <span class="text-ls-danger">*</span></label>
          <input type="text" name="product" id="product" value="{{ old('product', $reservation->product) }}" required
            class="ls-input @error('product') error @enderror" />
          @error('product')
            <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
          @enderror
        </div>

        <div x-show="type !== 'cost_of_artwork'" x-cloak class="space-y-6">
          <div class="grid grid-cols-2 gap-6">
            <div class="ls-field">
              <label for="platform_id">Platform</label>
              <select name="platform_id" id="platform_id" x-model="selectedPlatformId" @change="filterPlacements()"
                class="ls-select @error('platform_id') error @enderror">
                <option value="">All platforms</option>
                @foreach($platforms as $platform)
                  <option value="{{ $platform->id }}" {{ old('platform_id', $reservation->platform_id) == $platform->id ? 'selected' : '' }}>{{ $platform->name }}</option>
                @endforeach
              </select>
              @error('platform_id')
                <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
              @enderror
            </div>

            <div class="ls-field">
              <label for="placement_id">Placement <span class="text-ls-danger">*</span></label>
              <select name="placement_id" id="placement_id" required x-model="selectedPlacementId" @change="prefillGrossAmount()"
                class="ls-select @error('placement_id') error @enderror">
                <option value="">Select placement</option>
                <template x-for="placement in filteredPlacements" :key="placement.id">
                  <option :value="placement.id" x-text="placement.name" :selected="placement.id == selectedPlacementId"></option>
                </template>
              </select>
              @error('placement_id')
                <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div class="ls-field">
              <label for="channel">Channel <span class="text-ls-danger">*</span></label>
              <select name="channel" id="channel" required class="ls-select @error('channel') error @enderror">
                <option value="">Select channel</option>
                @foreach($channels as $channel)
                  <option value="{{ $channel }}" {{ old('channel', $reservation->channel) === $channel ? 'selected' : '' }}>{{ $channel }}</option>
                @endforeach
              </select>
              @error('channel')
                <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
              @enderror
            </div>

            <div class="ls-field">
              <label for="scope">Scope <span class="text-ls-danger">*</span></label>
              <select name="scope" id="scope" required class="ls-select @error('scope') error @enderror">
                <option value="">Select scope</option>
                @foreach($scopes as $scope)
                  <option value="{{ $scope }}" {{ old('scope', $reservation->scope) === $scope ? 'selected' : '' }}>{{ $scope }}</option>
                @endforeach
              </select>
              @error('scope')
                <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
              @enderror
            </div>
          </div>
        </div>
      </x-ls.section>

      {{-- Dates --}}
      <div x-show="type !== 'cost_of_artwork'" x-cloak>
        <x-ls.section title="Reservation Dates">
          <div x-data="datePicker()" x-init="init()" class="ls-field">
            <label for="dates_display">Dates Published <span class="text-ls-danger">*</span></label>
            <input type="text" id="dates_display" x-ref="datepicker" readonly
              class="ls-input cursor-pointer @error('dates_booked') error @enderror"
              placeholder="Click to select dates" />
            <input type="hidden" name="dates_booked" x-model="datesJson" />
            <span class="hint">Click on individual dates to select them. You can select multiple non-consecutive dates.</span>
            @error('dates_booked')
              <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
            @enderror
          </div>
        </x-ls.section>
      </div>

      {{-- Financials --}}
      <x-ls.section title="Financials">
        <div class="grid grid-cols-2 gap-6">
          <div class="ls-field">
            <label for="gross_amount">Gross Amount (MUR) <span class="text-ls-danger">*</span></label>
            <input name="gross_amount" id="gross_amount" x-model="grossAmount" @input="calculateDiscount(); calculateCommission()" required
              class="ls-input @error('gross_amount') error @enderror" />
            @error('gross_amount')
              <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
            @enderror
          </div>

          <div x-show="type !== 'cost_of_artwork'" x-cloak class="ls-field">
            <label for="discount">Discount (MUR)</label>
            <input name="discount" id="discount" x-model="discount" @can('edit-financials') @input="calculateTotalAmountToPay()" @else readonly @endcan
              class="ls-input @error('discount') error @enderror @cannot('edit-financials') bg-ls-surface-muted text-ls-text-muted pointer-events-none @endcannot" />
            <p x-show="discountBreakdown" x-text="discountBreakdown" class="hint"></p>
            @error('discount')
              <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div x-show="type !== 'cost_of_artwork'" x-cloak class="grid grid-cols-2 gap-6">
          <div class="ls-field">
            <label for="commission">Commission (MUR)</label>
            <input name="commission" id="commission" x-model="commission" @can('edit-financials') @input="calculateTotalAmountToPay()" @else readonly @endcan
              class="ls-input @error('commission') error @enderror @cannot('edit-financials') bg-ls-surface-muted text-ls-text-muted pointer-events-none @endcannot" />
            <p x-show="commissionBreakdown" x-text="commissionBreakdown" class="hint"></p>
            @error('commission')
              <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div class="ls-field">
            <label for="vat">VAT (MUR)</label>
            <input name="vat" id="vat" x-model="vat" @input="calculateTotalAmountToPay()"
              class="ls-input @error('vat') error @enderror" />
            @error('vat')
              <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
            @enderror
          </div>

          <div class="ls-field">
            <label for="total_amount_to_pay">Total Amount to Pay (MUR) <span class="text-ls-danger">*</span></label>
            <input name="total_amount_to_pay" id="total_amount_to_pay" x-model="totalAmountToPay" readonly
              class="ls-input bg-ls-surface-muted" />
            @error('total_amount_to_pay')
              <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-6">
          <label class="ls-check cursor-pointer">
            <input type="hidden" name="vat_exempt" :value="vatExempt ? '1' : '0'" />
            <input type="checkbox" :checked="vatExempt" @change="vatExempt = $event.target.checked; calculateVat()" />
            <span>VAT Exempt</span>
          </label>

          <label class="ls-check cursor-pointer">
            <input type="hidden" name="is_cash" :value="isCash ? '1' : '0'" />
            <input type="checkbox" :checked="isCash" @change="isCash = $event.target.checked" />
            <span>Cash</span>
          </label>

          <label class="ls-check cursor-pointer">
            <input type="hidden" name="bill_at_end_of_campaign" :value="billAtEndOfCampaign ? '1' : '0'" />
            <input type="checkbox" :checked="billAtEndOfCampaign" @change="billAtEndOfCampaign = $event.target.checked" />
            <span>Bill at end of campaign</span>
          </label>

          <label class="ls-check cursor-pointer">
            <input type="hidden" name="is_foreign_currency" :value="isForeignCurrency ? '1' : '0'" />
            <input type="checkbox" :checked="isForeignCurrency"
              @change="isForeignCurrency = $event.target.checked; if (!isForeignCurrency) { foreignCurrencyAmount = ''; foreignCurrencyCode = ''; }" />
            <span>Foreign Currency</span>
          </label>
        </div>

        <div x-show="isForeignCurrency" x-cloak class="space-y-3">
          <div class="grid grid-cols-2 gap-6">
            <div class="ls-field">
              <label for="foreign_currency_amount">Amount</label>
              <input type="number" step="0.01" min="0" name="foreign_currency_amount" id="foreign_currency_amount"
                x-model="foreignCurrencyAmount"
                class="ls-input @error('foreign_currency_amount') error @enderror" />
              @error('foreign_currency_amount')
                <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
              @enderror
            </div>
            <div class="ls-field">
              <label for="foreign_currency_code">Currency</label>
              <select name="foreign_currency_code" id="foreign_currency_code" x-model="foreignCurrencyCode"
                class="ls-select @error('foreign_currency_code') error @enderror">
                <option value="">Select currency</option>
                @foreach($foreignCurrencies as $fc)
                  <option value="{{ $fc->value }}">{{ $fc->label() }}</option>
                @endforeach
              </select>
              @error('foreign_currency_code')
                <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
              @enderror
            </div>
          </div>
        </div>
      </x-ls.section>

      {{-- Parent Reservation --}}
      <x-ls.section title="Link to Parent Reservation">
        <p class="text-xs text-ls-text-muted">Optional. Use this to tie a Facebook Boost to its parent Facebook Post, for example.</p>
        <div x-data="linkableCombobox({
            items: @js($linkableReservationsJson),
            selectedId: @js(old('parent_reservation_id', $reservation->parent_reservation_id)),
          })"
          x-on:click.outside="open = false"
          class="relative">
          <input type="hidden" name="parent_reservation_id" :value="selectedId ?? ''" />
          <input type="text" x-model="query" @focus="open = true" @keydown.escape.prevent="open = false"
            placeholder="Search by reference or product..."
            autocomplete="off"
            class="ls-input" />
          <ul x-show="open && filtered.length" x-cloak class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-lg border border-ls-border-strong bg-ls-surface shadow-lg">
            <template x-for="item in filtered" :key="item.id">
              <li @mousedown.prevent="select(item)" class="cursor-pointer px-4 py-2 text-sm text-ls-text hover:bg-ls-surface-muted" x-text="item.label"></li>
            </template>
          </ul>
        </div>
        @error('parent_reservation_id')
          <p class="mt-1 text-sm text-ls-danger">{{ $message }}</p>
        @enderror
      </x-ls.section>

      {{-- Documents --}}
      <x-ls.section title="Documents">
        {{-- Reservation Order --}}
        <div>
          <label class="block text-sm font-medium text-ls-text mb-2">Reservation Order</label>
          <div class="flex items-center gap-3" x-data="documentUploader('signed_ro', '{{ route('reservations.upload-document', $reservation) }}', '{{ $reservation->signed_ro_path ? route('reservations.document', [$reservation, 'signed-ro']) : '' }}')">
            <a href="{{ route('reservations.pdf', $reservation) }}" class="ls-btn ls-btn-outline">
              <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
              </svg>
              Download RO
            </a>
            <button type="button" @click="$refs.fileInput.click()" class="ls-btn ls-btn-outline">
              <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
              </svg>
              Upload Signed RO
            </button>
            <input type="file" x-ref="fileInput" @change="upload($event)" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp" />
            <a x-show="downloadUrl" :href="downloadUrl" class="ls-btn ls-btn-outline ls-btn-sm" title="Download Signed RO">
              <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
              </svg>
            </a>
            <div x-show="uploading" class="flex items-center gap-2 text-sm text-ls-text-muted">
              <div class="h-2 w-32 overflow-hidden rounded-full bg-ls-surface-muted">
                <div class="h-full rounded-full bg-ls-deep transition-all" :style="'width: ' + progress + '%'"></div>
              </div>
              <span x-text="progress + '%'"></span>
            </div>
            <span x-show="success" class="text-sm text-ls-success" x-transition>Saved</span>
            <span x-show="error" class="text-sm text-ls-danger" x-text="error" x-transition></span>
          </div>
        </div>

        {{-- Reference Numbers with Upload --}}
        <div class="grid grid-cols-2 gap-6">
          <div class="ls-field">
            <label for="purchase_order_no">Purchase Order No.</label>
            <input type="text" name="purchase_order_no" id="purchase_order_no" value="{{ old('purchase_order_no', $reservation->purchase_order_no) }}"
              class="ls-input @error('purchase_order_no') error @enderror" />
            @error('purchase_order_no')
              <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
            @enderror
            <div class="mt-2 flex items-center gap-2" x-data="documentUploader('purchase_order', '{{ route('reservations.upload-document', $reservation) }}', '{{ $reservation->purchase_order_path ? route('reservations.document', [$reservation, 'purchase-order']) : '' }}')">
              <button type="button" @click="$refs.fileInput.click()" class="ls-btn ls-btn-outline ls-btn-sm">
                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
                Upload PO
              </button>
              <input type="file" x-ref="fileInput" @change="upload($event)" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp" />
              <a x-show="downloadUrl" :href="downloadUrl" class="ls-btn ls-btn-outline ls-btn-sm" title="Download PO">
                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
              </a>
              <div x-show="uploading" class="flex items-center gap-2 text-xs text-ls-text-muted">
                <div class="h-1.5 w-24 overflow-hidden rounded-full bg-ls-surface-muted">
                  <div class="h-full rounded-full bg-ls-deep transition-all" :style="'width: ' + progress + '%'"></div>
                </div>
                <span x-text="progress + '%'"></span>
              </div>
              <span x-show="success" class="text-xs text-ls-success" x-transition>Saved</span>
              <span x-show="error" class="text-xs text-ls-danger" x-text="error" x-transition></span>
            </div>
          </div>

          <div class="ls-field">
            <label for="invoice_no">Invoice No.</label>
            <input type="text" name="invoice_no" id="invoice_no" value="{{ old('invoice_no', $reservation->invoice_no) }}"
              class="ls-input @error('invoice_no') error @enderror" />
            @error('invoice_no')
              <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
            @enderror
            <div class="mt-2 flex items-center gap-2" x-data="documentUploader('invoice', '{{ route('reservations.upload-document', $reservation) }}', '{{ $reservation->invoice_path ? route('reservations.document', [$reservation, 'invoice']) : '' }}')">
              <button type="button" @click="$refs.fileInput.click()" class="ls-btn ls-btn-outline ls-btn-sm">
                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
                Upload Invoice
              </button>
              <input type="file" x-ref="fileInput" @change="upload($event)" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp" />
              <a x-show="downloadUrl" :href="downloadUrl" class="ls-btn ls-btn-outline ls-btn-sm" title="Download Invoice">
                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
              </a>
              <div x-show="uploading" class="flex items-center gap-2 text-xs text-ls-text-muted">
                <div class="h-1.5 w-24 overflow-hidden rounded-full bg-ls-surface-muted">
                  <div class="h-full rounded-full bg-ls-deep transition-all" :style="'width: ' + progress + '%'"></div>
                </div>
                <span x-text="progress + '%'"></span>
              </div>
              <span x-show="success" class="text-xs text-ls-success" x-transition>Saved</span>
              <span x-show="error" class="text-xs text-ls-danger" x-text="error" x-transition></span>
            </div>
          </div>
        </div>
      </x-ls.section>

      {{-- Remark --}}
      <x-ls.section title="Additional Information">
        <div class="ls-field">
          <label for="reservation_date">Reservation Date</label>
          <input type="date" name="reservation_date" id="reservation_date" value="{{ old('reservation_date', $reservation->created_at->format('Y-m-d')) }}"
            class="ls-input @error('reservation_date') error @enderror" />
          <span class="hint">Change this to backdate the reservation.</span>
          @error('reservation_date')
            <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
          @enderror
        </div>

        <div class="ls-field">
          <label for="remark">Remark</label>
          <textarea name="remark" id="remark" rows="4"
            class="ls-textarea @error('remark') error @enderror">{{ old('remark', $reservation->remark) }}</textarea>
          @error('remark')
            <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
          @enderror
        </div>
      </x-ls.section>

      <div class="flex items-center gap-4">
        <x-ls.button type="submit" variant="primary">Update Reservation</x-ls.button>
        <x-ls.button :href="route('reservations.index')" variant="ghost">Cancel</x-ls.button>
      </div>
    </form>
  </x-ls.page>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    function reservationForm() {
      return {
        allPlacements: @json($placementsJson),
        allClients: @json($clientsJson),
        filteredPlacements: [],
        selectedPlatformId: '{{ old('platform_id', $reservation->platform_id ?? '') }}',
        selectedPlacementId: '{{ old('placement_id', $reservation->placement_id ?? '') }}',
        selectedClientId: '{{ old('client_id', $reservation->client_id ?? '') }}',
        selectedRepresentedClientId: {{ old('represented_client_id', $reservation->represented_client_id) ? (int) old('represented_client_id', $reservation->represented_client_id) : 'null' }},
        isClientAgency: {{ old('represented_client_id', $reservation->represented_client_id) ? 'true' : 'false' }},
        type: '{{ old('type', $reservation->type->value) }}',
        isCash: {{ old('is_cash', $reservation->is_cash) ? 'true' : 'false' }},
        isForeignCurrency: {{ old('is_foreign_currency', $reservation->is_foreign_currency) ? 'true' : 'false' }},
        foreignCurrencyAmount: '{{ old('foreign_currency_amount', $reservation->foreign_currency_amount) }}',
        foreignCurrencyCode: '{{ old('foreign_currency_code', $reservation->foreign_currency_code?->value) }}',
        billAtEndOfCampaign: {{ old('bill_at_end_of_campaign', $reservation->bill_at_end_of_campaign) ? 'true' : 'false' }},
        grossAmount: '{{ old('gross_amount', $reservation->gross_amount) }}',
        discount: '{{ old('discount', $reservation->discount) }}',
        commission: '{{ old('commission', $reservation->commission) }}',
        totalAmountToPay: '{{ old('total_amount_to_pay', $reservation->total_amount_to_pay) }}',
        vat: '{{ old('vat', $reservation->vat) }}',
        vatExempt: {{ old('vat_exempt', $reservation->vat_exempt) ? 'true' : 'false' }},
        status: '{{ old('status', $reservation->status->value) }}',
        discountBreakdown: '',
        commissionBreakdown: '',
        datesCount: 0,
        initialized: false,
        get statusDotClass() {
          return {
            'option': 'bg-amber-500',
            'confirmed': 'bg-green-500',
            'canceled': 'bg-red-500',
          }[this.status] || 'bg-gray-300';
        },
        init() {
          this.filterPlacements();
          this.calculateTotalAmountToPay();
          this.$nextTick(() => { this.initialized = true; });
        },
        onClientSelected(id) {
          this.selectedClientId = id;
          this.calculateDiscount();
          this.calculateCommission();
          this.syncVatExemptFromClient();
        },
        onTypeChange() {
          if (this.type === 'cost_of_artwork') {
            this.discount = '0';
            this.commission = '0';
            this.discountBreakdown = '';
            this.commissionBreakdown = '';
            this.calculateVat();
          } else {
            this.calculateDiscount();
            this.calculateCommission();
          }
        },
        filterPlacements() {
          if (this.selectedPlatformId) {
            this.filteredPlacements = this.allPlacements.filter(p => p.platform_id == this.selectedPlatformId);
          } else {
            this.filteredPlacements = this.allPlacements;
          }
          if (!this.filteredPlacements.find(p => p.id == this.selectedPlacementId)) {
            this.selectedPlacementId = '';
          }
        },
        recalculateGrossAmount() {
          if (this.type === 'cost_of_artwork') {
            return;
          }
          const placement = this.allPlacements.find(p => p.id == this.selectedPlacementId);
          if (placement && placement.type === 'programmatic') {
            return;
          }
          if (placement && placement.price && this.datesCount > 0) {
            this.grossAmount = (parseFloat(placement.price) * this.datesCount).toFixed(2);
            this.calculateDiscount();
            this.calculateCommission();
          }
        },
        prefillGrossAmount() {
          this.recalculateGrossAmount();
        },
        calculateDiscount() {
          if (this.type === 'cost_of_artwork') {
            this.discount = '0';
            this.discountBreakdown = '';
            this.calculateVat();
            return;
          }

          const gross = parseFloat(this.grossAmount) || 0;
          const parts = [];
          let total = 0;

          const client = this.allClients.find(c => c.id == this.selectedClientId);
          if (client && client.discount && client.discount_type) {
            if (client.discount_type === '%') {
              const value = Math.round((client.discount * gross / 100) * 100) / 100;
              parts.push('Client: ' + client.discount + '% of MUR ' + this.formatNumber(gross) + ' = MUR ' + this.formatNumber(value));
              total += value;
            } else {
              const value = parseFloat(client.discount);
              parts.push('Client: MUR ' + this.formatNumber(value));
              total += value;
            }
          }

          if (parts.length > 0) {
            this.discount = total.toFixed(2);
            this.discountBreakdown = parts[0];
          } else {
            this.discount = '0.00';
            this.discountBreakdown = '';
          }

          this.calculateVat();
        },
        calculateCommission() {
          if (this.type === 'cost_of_artwork') {
            this.commission = '0';
            this.commissionBreakdown = '';
            this.calculateVat();
            return;
          }

          const gross = parseFloat(this.grossAmount) || 0;
          const client = this.allClients.find(c => c.id == this.selectedClientId);

          if (client && client.commission_amount && client.commission_type) {
            if (client.commission_type === '%') {
              const value = Math.round((client.commission_amount * gross / 100) * 100) / 100;
              this.commission = value.toFixed(2);
              this.commissionBreakdown = 'Client: ' + client.commission_amount + '% of MUR ' + this.formatNumber(gross) + ' = MUR ' + this.formatNumber(value);
            } else {
              const value = parseFloat(client.commission_amount);
              this.commission = value.toFixed(2);
              this.commissionBreakdown = 'Client: MUR ' + this.formatNumber(value);
            }
          } else {
            this.commission = '0.00';
            this.commissionBreakdown = '';
          }

          this.calculateVat();
        },
        syncVatExemptFromClient() {
          const client = this.allClients.find(c => c.id == this.selectedClientId);
          this.vatExempt = !(client && client.vat_number && !client.vat_exempt);
          this.calculateVat();
        },
        calculateVat() {
          if (this.vatExempt) {
            this.vat = '0.00';
          } else {
            const gross = parseFloat(this.grossAmount) || 0;
            const disc = parseFloat(this.discount) || 0;
            const comm = parseFloat(this.commission) || 0;
            const subtotal = Math.max(0, gross - disc - comm);
            this.vat = (subtotal * 0.15).toFixed(2);
          }
          this.calculateTotalAmountToPay();
        },
        calculateTotalAmountToPay() {
          const gross = parseFloat(this.grossAmount) || 0;
          const disc = parseFloat(this.discount) || 0;
          const comm = parseFloat(this.commission) || 0;
          const vat = parseFloat(this.vat) || 0;
          this.totalAmountToPay = Math.max(0, gross - disc - comm + vat).toFixed(2);
        },
        formatNumber(num) {
          return Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
      }
    }

    function documentUploader(type, uploadUrl, existingDownloadUrl) {
      return {
        uploading: false,
        progress: 0,
        success: false,
        error: '',
        downloadUrl: existingDownloadUrl || '',
        upload(event) {
          const file = event.target.files[0];
          if (!file) return;

          this.uploading = true;
          this.progress = 0;
          this.success = false;
          this.error = '';

          const formData = new FormData();
          formData.append('file', file);
          formData.append('type', type);
          formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]').value);

          const xhr = new XMLHttpRequest();

          xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
              this.progress = Math.round((e.loaded / e.total) * 100);
            }
          });

          xhr.addEventListener('load', () => {
            this.uploading = false;
            if (xhr.status === 200) {
              const response = JSON.parse(xhr.responseText);
              this.downloadUrl = response.download_url;
              this.success = true;
              setTimeout(() => { this.success = false; }, 3000);
            } else {
              this.error = 'Upload failed. Please try again.';
              setTimeout(() => { this.error = ''; }, 5000);
            }
          });

          xhr.addEventListener('error', () => {
            this.uploading = false;
            this.error = 'Upload failed. Please try again.';
            setTimeout(() => { this.error = ''; }, 5000);
          });

          xhr.open('POST', uploadUrl);
          xhr.send(formData);

          event.target.value = '';
        }
      }
    }

    function datePicker() {
      return {
        dates: [],
        datesJson: '[]',
        init() {
          const existingDates = @json($reservation->dates_booked);
          const oldDates = @json(old('dates_booked') ? json_decode(old('dates_booked')) : null);
          this.dates = oldDates || existingDates || [];
          this.datesJson = JSON.stringify(this.dates);
          this.$dispatch('dates-changed', { count: this.dates.length });

          flatpickr(this.$refs.datepicker, {
            mode: 'multiple',
            dateFormat: 'Y-m-d',
            minDate: null,
            defaultDate: this.dates,
            onChange: (selectedDates, dateStr) => {
              this.dates = selectedDates.map(date => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
              });
              this.datesJson = JSON.stringify(this.dates);
              this.$dispatch('dates-changed', { count: this.dates.length });
            }
          });
        }
      }
    }
  </script>
@endsection
