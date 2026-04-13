@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center gap-4">
        <a href="{{ route('reservations.index') }}" class="text-gray-400 hover:text-gray-600">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 0 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
          </svg>
        </a>
        <h1 class="text-2xl font-semibold text-gray-900">Add Reservation</h1>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      <form action="{{ route('reservations.store') }}" method="POST" enctype="multipart/form-data" class="mt-8 max-w-2xl space-y-8"
        x-data="reservationForm()" x-init="init()" @dates-changed="datesCount = $event.detail.count; recalculateGrossAmount()">
        @csrf

        {{-- Client & Agency --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Client & Agency</h2>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="client_id" class="block text-sm font-medium text-gray-700">Client <span class="text-red-500">*</span></label>
              <div class="mt-2">
                <select name="client_id" id="client_id" required x-model="selectedClientId" @change="calculateDiscount(); syncVatExemptFromClient()"
                  class="block w-full rounded-lg border @error('client_id') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
                  <option value="">Select client</option>
                  @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                  @endforeach
                </select>
              </div>
              @error('client_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="agency_id" class="block text-sm font-medium text-gray-700">Agency</label>
              <div class="mt-2">
                <select name="agency_id" id="agency_id" x-model="selectedAgencyId" @change="calculateDiscount(); calculateCommission()"
                  class="block w-full rounded-lg border @error('agency_id') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
                  <option value="">Select agency</option>
                  @foreach($agencies as $agency)
                    <option value="{{ $agency->id }}" {{ old('agency_id') == $agency->id ? 'selected' : '' }}>{{ $agency->company_name }}</option>
                  @endforeach
                </select>
              </div>
              @error('agency_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="salesperson_id" class="block text-sm font-medium text-gray-700">Salesperson</label>
              <div class="mt-2">
                <select name="salesperson_id" id="salesperson_id"
                  class="block w-full rounded-lg border @error('salesperson_id') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
                  <option value="">Select salesperson</option>
                  @foreach($salespeople as $salesperson)
                    <option value="{{ $salesperson->id }}" {{ old('salesperson_id') == $salesperson->id ? 'selected' : '' }}>{{ $salesperson->first_name }} {{ $salesperson->last_name }}</option>
                  @endforeach
                </select>
              </div>
              @error('salesperson_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="status" class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
              <div class="mt-2 flex items-center gap-3">
                <span class="inline-block h-3 w-3 shrink-0 rounded-full" :class="statusDotClass"></span>
                <select name="status" id="status" required x-model="status"
                  class="block w-full rounded-lg border @error('status') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
                  @foreach($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}" {{ old('status', \App\ReservationStatus::Option->value) === $statusOption->value ? 'selected' : '' }}>{{ $statusOption->label() }}</option>
                  @endforeach
                </select>
              </div>
              @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>

        {{-- Product Details --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Product Details</h2>

          <div>
            <label for="product" class="block text-sm font-medium text-gray-700">Product <span class="text-red-500">*</span></label>
            <div class="mt-2">
              <input type="text" name="product" id="product" value="{{ old('product') }}" required
                class="block w-full rounded-lg border @error('product') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
            </div>
            @error('product')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="platform_id" class="block text-sm font-medium text-gray-700">Platform</label>
              <div class="mt-2">
                <select name="platform_id" id="platform_id" x-model="selectedPlatformId" @change="filterPlacements()"
                  class="block w-full rounded-lg border @error('platform_id') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
                  <option value="">All platforms</option>
                  @foreach($platforms as $platform)
                    <option value="{{ $platform->id }}" {{ old('platform_id') == $platform->id ? 'selected' : '' }}>{{ $platform->name }}</option>
                  @endforeach
                </select>
              </div>
              @error('platform_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="placement_id" class="block text-sm font-medium text-gray-700">Placement <span class="text-red-500">*</span></label>
              <div class="mt-2">
                <select name="placement_id" id="placement_id" required x-model="selectedPlacementId" @change="prefillGrossAmount()"
                  class="block w-full rounded-lg border @error('placement_id') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
                  <option value="">Select placement</option>
                  <template x-for="placement in filteredPlacements" :key="placement.id">
                    <option :value="placement.id" x-text="placement.name" :selected="placement.id == selectedPlacementId"></option>
                  </template>
                </select>
              </div>
              @error('placement_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="channel" class="block text-sm font-medium text-gray-700">Channel <span class="text-red-500">*</span></label>
              <div class="mt-2">
                <select name="channel" id="channel" required
                  class="block w-full rounded-lg border @error('channel') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
                  <option value="">Select channel</option>
                  @foreach($channels as $channel)
                    <option value="{{ $channel }}" {{ old('channel') === $channel ? 'selected' : '' }}>{{ $channel }}</option>
                  @endforeach
                </select>
              </div>
              @error('channel')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="scope" class="block text-sm font-medium text-gray-700">Scope <span class="text-red-500">*</span></label>
              <div class="mt-2">
                <select name="scope" id="scope" required
                  class="block w-full rounded-lg border @error('scope') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
                  <option value="">Select scope</option>
                  @foreach($scopes as $scope)
                    <option value="{{ $scope }}" {{ old('scope') === $scope ? 'selected' : '' }}>{{ $scope }}</option>
                  @endforeach
                </select>
              </div>
              @error('scope')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>

        {{-- Dates --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Reservation Dates</h2>

          <div x-data="datePicker()" x-init="init()">
            <label for="dates_display" class="block text-sm font-medium text-gray-700">Dates Booked <span class="text-red-500">*</span></label>
            <div class="mt-2">
              <input type="text" id="dates_display" x-ref="datepicker" readonly
                class="block w-full rounded-lg border @error('dates_booked') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100 cursor-pointer"
                placeholder="Click to select dates" />
              <input type="hidden" name="dates_booked" x-model="datesJson" />
            </div>
            <p class="mt-1 text-xs text-gray-500">Click on individual dates to select them. You can select multiple non-consecutive dates.</p>
            @error('dates_booked')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>
        </div>

        {{-- Financials --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Financials</h2>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="gross_amount" class="block text-sm font-medium text-gray-700">Gross Amount (MUR) <span class="text-red-500">*</span></label>
              <div class="mt-2">
                <input name="gross_amount" id="gross_amount" x-model="grossAmount" @input="calculateDiscount(); calculateCommission()" required
                  class="block w-full rounded-lg border @error('gross_amount') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('gross_amount')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="discount" class="block text-sm font-medium text-gray-700">Discount (MUR)</label>
              <div class="mt-2">
                <input name="discount" id="discount" x-model="discount" @can('edit-financials') @input="calculateTotalAmountToPay()" @else readonly @endcan
                  class="block w-full rounded-lg border @error('discount') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100 @cannot('edit-financials') bg-gray-50 text-gray-500 pointer-events-none @endcannot" />
              </div>
              <p x-show="discountBreakdown" x-text="discountBreakdown" class="mt-1 text-xs text-gray-500"></p>
              @error('discount')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="commission" class="block text-sm font-medium text-gray-700">Commission (MUR)</label>
              <div class="mt-2">
                <input name="commission" id="commission" x-model="commission" @can('edit-financials') @input="calculateTotalAmountToPay()" @else readonly @endcan
                  class="block w-full rounded-lg border @error('commission') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100 @cannot('edit-financials') bg-gray-50 text-gray-500 pointer-events-none @endcannot" />
              </div>
              <p x-show="commissionBreakdown" x-text="commissionBreakdown" class="mt-1 text-xs text-gray-500"></p>
              @error('commission')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="cost_of_artwork" class="block text-sm font-medium text-gray-700">Cost of Artwork (MUR)</label>
              <div class="mt-2">
                <input name="cost_of_artwork" id="cost_of_artwork" x-model="costOfArtwork" @input="calculateVat()"
                  class="block w-full rounded-lg border @error('cost_of_artwork') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('cost_of_artwork')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div class="space-y-4">
              <div>
                <label for="vat" class="block text-sm font-medium text-gray-700">VAT (MUR)</label>
                <div class="mt-2">
                  <input name="vat" id="vat" x-model="vat" @input="calculateTotalAmountToPay()"
                    class="block w-full rounded-lg border @error('vat') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
                </div>
                @error('vat')
                  <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input type="hidden" name="vat_exempt" :value="vatExempt ? '1' : '0'" />
                  <input type="checkbox" :checked="vatExempt" @change="vatExempt = $event.target.checked; calculateVat()"
                    class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-200" />
                  VAT Exempt
                </label>
              </div>
            </div>

            <div>
              <label for="total_amount_to_pay" class="block text-sm font-medium text-gray-700">Total Amount to Pay (MUR) <span class="text-red-500">*</span></label>
              <div class="mt-2">
                <input name="total_amount_to_pay" id="total_amount_to_pay" x-model="totalAmountToPay" readonly
                  class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-gray-900 shadow-sm focus:outline-none" />
              </div>
              @error('total_amount_to_pay')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>

        {{-- Documents --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Documents</h2>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="purchase_order_no" class="block text-sm font-medium text-gray-700">Purchase Order No.</label>
              <div class="mt-2">
                <input type="text" name="purchase_order_no" id="purchase_order_no" value="{{ old('purchase_order_no') }}"
                  class="block w-full rounded-lg border @error('purchase_order_no') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('purchase_order_no')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
              <div class="mt-2">
                <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 cursor-pointer">
                  <svg class="h-3.5 w-3.5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                  </svg>
                  Upload PO
                  <input type="file" name="purchase_order_file" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp" />
                </label>
              </div>
            </div>

            <div>
              <label for="invoice_no" class="block text-sm font-medium text-gray-700">Invoice No.</label>
              <div class="mt-2">
                <input type="text" name="invoice_no" id="invoice_no" value="{{ old('invoice_no') }}"
                  class="block w-full rounded-lg border @error('invoice_no') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('invoice_no')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
              <div class="mt-2">
                <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 cursor-pointer">
                  <svg class="h-3.5 w-3.5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                  </svg>
                  Upload Invoice
                  <input type="file" name="invoice_file" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp" />
                </label>
              </div>
            </div>
          </div>
        </div>

        {{-- Remark --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Additional Information</h2>

          <div>
            <label for="reservation_date" class="block text-sm font-medium text-gray-700">Reservation Date</label>
            <div class="mt-2">
              <input type="date" name="reservation_date" id="reservation_date" value="{{ old('reservation_date', now()->format('Y-m-d')) }}"
                class="block w-full rounded-lg border @error('reservation_date') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
            </div>
            <p class="mt-1 text-xs text-gray-500">Change this to backdate the reservation.</p>
            @error('reservation_date')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="remark" class="block text-sm font-medium text-gray-700">Remark</label>
            <div class="mt-2">
              <textarea name="remark" id="remark" rows="4"
                class="block w-full rounded-lg border @error('remark') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">{{ old('remark') }}</textarea>
            </div>
            @error('remark')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <div class="flex items-center gap-4">
          <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
            Save Reservation
          </button>
          <a href="{{ route('reservations.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </main>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    function reservationForm() {
      return {
        allPlacements: @json($placementsJson),
        allClients: @json($clientsJson),
        allAgencies: @json($agenciesJson),
        filteredPlacements: [],
        selectedPlatformId: '{{ old('platform_id', '') }}',
        selectedPlacementId: '{{ old('placement_id', '') }}',
        selectedClientId: '{{ old('client_id', '') }}',
        selectedAgencyId: '{{ old('agency_id', '') }}',
        grossAmount: '{{ old('gross_amount', '') }}',
        discount: '{{ old('discount', '0.00') }}',
        commission: '{{ old('commission', '0.00') }}',
        costOfArtwork: '{{ old('cost_of_artwork', '0.00') }}',
        totalAmountToPay: '{{ old('total_amount_to_pay', '0.00') }}',
        vat: '{{ old('vat', '0.00') }}',
        vatExempt: {{ old('vat_exempt') ? 'true' : 'false' }},
        status: '{{ old('status', \App\ReservationStatus::Option->value) }}',
        discountBreakdown: '',
        commissionBreakdown: '',
        datesCount: 0,
        get statusDotClass() {
          return {
            'option': 'bg-amber-500',
            'confirmed': 'bg-green-500',
            'canceled': 'bg-red-500',
          }[this.status] || 'bg-gray-300';
        },
        init() {
          this.filterPlacements();
          this.calculateDiscount();
          this.calculateCommission();
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

          const agency = this.allAgencies.find(a => a.id == this.selectedAgencyId);
          if (agency && agency.discount && agency.discount_type) {
            if (agency.discount_type === '%') {
              const value = Math.round((agency.discount * gross / 100) * 100) / 100;
              parts.push('Agency: ' + agency.discount + '% of MUR ' + this.formatNumber(gross) + ' = MUR ' + this.formatNumber(value));
              total += value;
            } else {
              const value = parseFloat(agency.discount);
              parts.push('Agency: MUR ' + this.formatNumber(value));
              total += value;
            }
          }

          if (parts.length > 0) {
            this.discount = total.toFixed(2);
            if (parts.length > 1) {
              this.discountBreakdown = parts.join(' + ') + ' = Total: MUR ' + this.formatNumber(total);
            } else {
              this.discountBreakdown = parts[0];
            }
          } else {
            this.discountBreakdown = '';
          }

          this.calculateVat();
        },
        calculateCommission() {
          const gross = parseFloat(this.grossAmount) || 0;
          const agency = this.allAgencies.find(a => a.id == this.selectedAgencyId);

          if (agency && agency.commission_amount && agency.commission_type) {
            if (agency.commission_type === '%') {
              const value = Math.round((agency.commission_amount * gross / 100) * 100) / 100;
              this.commission = value.toFixed(2);
              this.commissionBreakdown = 'Agency: ' + agency.commission_amount + '% of MUR ' + this.formatNumber(gross) + ' = MUR ' + this.formatNumber(value);
            } else {
              const value = parseFloat(agency.commission_amount);
              this.commission = value.toFixed(2);
              this.commissionBreakdown = 'Agency: MUR ' + this.formatNumber(value);
            }
          } else {
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
            const artwork = parseFloat(this.costOfArtwork) || 0;
            const subtotal = Math.max(0, gross - disc - comm + artwork);
            this.vat = (subtotal * 0.15).toFixed(2);
          }
          this.calculateTotalAmountToPay();
        },
        calculateTotalAmountToPay() {
          const gross = parseFloat(this.grossAmount) || 0;
          const disc = parseFloat(this.discount) || 0;
          const comm = parseFloat(this.commission) || 0;
          const artwork = parseFloat(this.costOfArtwork) || 0;
          const vat = parseFloat(this.vat) || 0;
          this.totalAmountToPay = Math.max(0, gross - disc - comm + artwork + vat).toFixed(2);
        },
        formatNumber(num) {
          return Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
      }
    }

    function datePicker() {
      return {
        dates: [],
        datesJson: '[]',
        init() {
          const oldDates = @json(old('dates_booked') ? json_decode(old('dates_booked')) : []);
          this.dates = oldDates || [];
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
