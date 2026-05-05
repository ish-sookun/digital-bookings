# SAGE Export

The **SAGE Export** produces a `;`-delimited CSV that can be imported directly into the SAGE accounting system to record revenue from confirmed advertising reservations over a chosen date range.

## Where to find it

- **Page:** `/reservations`
- **Visible to:** Super Admin, Admin, Finance (gate: `sage-export`)
- **Toolbar location:** top-right of the Reservations index, next to the **Add Reservation** button

The toolbar is a small inline `GET` form with three controls:

| Control | Field name | Notes |
|---|---|---|
| Start date | `start_date` | Native `<input type="date">` |
| End date | `end_date` | Must be `>= start_date` |
| Export type | `export_type` | `sage` (default — exports the SAGE accounting CSV) or `sales` (placeholder — not yet implemented) |
| **Export** button | — | Submits the form |

Pressing the button submits to `GET /reservations/sage-export`.

## Billing model

Eligibility and the dates that produce `D + LC` pairs depend on the reservation type and the `bill_at_end_of_campaign` flag:

| Reservation kind | Eligibility | Dates emitted (D + LC pairs) |
|---|---|---|
| **Standard, `bill_at_end = false`** | At least one booked date inside the export window | Only the booked dates **inside** the window |
| **Standard, `bill_at_end = true`** | The **last** booked date is inside the export window | **All** booked dates of the reservation, including any that fall outside the window |
| **Cost of Artwork** | The billing date is inside the window — first booked date by default, last booked date if `bill_at_end = true` | A single `D + LC` pair (artwork is a flat charge, not a daily rate) |

The two rules together mean:

- A normal multi-day campaign that crosses month boundaries is **split across exports** — May's export emits the May dates, June's export emits the June dates. The same reservation appears (with different dates) in both runs, never double-counted.
- A `bill_at_end_of_campaign` reservation is invoiced **exactly once**, in the export that contains its last booked date — including any earlier dates from previous months.
- A Cost of Artwork is invoiced **exactly once**, in the export that contains its billing date.

### Worked scenarios

| Reservation dates | bill_at_end | Export range | Included? | D rows |
|---|---|---|---|---|
| Apr 5–7 | no | Apr 1–30 | ✅ | 3 |
| Apr 28 – May 2 | no | Apr 1–30 | ✅ — only the April dates | 3 (May 1–2 will appear in May's export) |
| Apr 28 – May 2 | no | May 1–31 | ✅ — only the May dates | 2 (April dates were already in April's export) |
| Mar 30 – Apr 3 | no | Apr 1–30 | ✅ — only the April dates | 3 (March dates were in March's export) |
| All dates outside the range | no | — | ❌ | 0 |
| Apr 5–7 | yes | Apr 1–30 | ✅ (last date inside range) | 3 |
| Apr 20 – May 10 | yes | Apr 1–30 | ❌ (campaign not yet ended) | 0 |
| Mar 28 – Apr 1 | yes | Apr 1–30 | ✅ (last date inside range) | 4 (incl. the three March dates) |

## Sage vs Sales

- **Sage** (`export_type=sage`, the default): exports Confirmed reservations where `is_cash = false`. This is the SAGE accounting CSV — the format described throughout this document.
- **Sales** (`export_type=sales`): **not yet implemented**. The controller redirects back to `/reservations` with an error flash so the user knows the format is pending. The Sales spec will be defined separately.

The Sage download filename is:

```
sage-export-20260401-20260430.csv
```

## End-to-end flow

```
┌──────────────┐    GET     ┌──────────────────────┐    validate    ┌────────────────────┐
│ /reservations│ ─────────▶ │ SageExportController │ ─────────────▶ │ SageExportRequest  │
│  toolbar     │            │     __invoke()       │                │  authorize + rules │
└──────────────┘            └──────────┬───────────┘                └────────────────────┘
                                       │
                                       ▼
                          query Confirmed reservations
                            where is_cash = false
                                       │
                                       ▼
                            ┌──────────────────────┐
                            │   SageCsvBuilder     │
                            │   build($reservations)│
                            └──────────┬───────────┘
                                       │
                                       ▼
                            response()->streamDownload()
                                  text/csv attachment
```

## Routing & authorization

Defined **before** `Route::resource('reservations', ...)` so the literal `sage-export` segment doesn't collide with the `{reservation}` route binding.

```php
// routes/web.php
Route::middleware('role:super_admin,admin,finance')->group(function () {
    Route::get('reservations/sage-export', SageExportController::class)
        ->name('reservations.sage-export');
});
```

`SageExportRequest::authorize()` additionally enforces `$user->can('sage-export')`.

Validation rules:

```php
'start_date'   => ['required', 'date'],
'end_date'     => ['required', 'date', 'after_or_equal:start_date'],
'export_type' => ['required', 'in:sage,sales'],
```

## Controller

`App\Http\Controllers\SageExportController` is invokable.

```php
if ($exportType === 'sales') {
    return redirect()
        ->route('reservations.index')
        ->with('error', 'Sales export is not yet available — the format is pending.');
}

$reservations = Reservation::query()
    ->with(['client', 'placement.platform', 'salesperson', 'representedClient', 'platform'])
    ->where('status', ReservationStatus::Confirmed)
    ->where('is_cash', false)
    ->get();

$builder = new SageCsvBuilder($start, $end);
$rows = $builder->build($reservations);

return response()->streamDownload(/* writes ;-delimited CSV with fputcsv */);
```

The Confirmed-status filter and the `is_cash = false` filter are both applied at the query level; everything downstream is the responsibility of the builder.

## CSV builder

`App\Services\SageCsvBuilder` is a pure, testable service: input is `Carbon $start`, `Carbon $end`, and a `Collection<Reservation>`; output is `array<array<string>>`.

### Eligibility

A single helper returns the set of booked dates to emit `D + LC` pairs for. An empty result means the reservation is excluded from this export entirely.

```php
private function datesToEmit(Reservation $reservation): Collection
{
    $allDates = $this->allDatesSorted($reservation);
    if ($allDates->isEmpty()) return collect();

    if ($reservation->type === ReservationType::CostOfArtwork) {
        $billingDate = $reservation->bill_at_end_of_campaign
            ? $allDates->last()
            : $allDates->first();

        return $billingDate->betweenIncluded($this->start, $this->end)
            ? collect([$billingDate])
            : collect();
    }

    if ($reservation->bill_at_end_of_campaign) {
        return $allDates->last()->betweenIncluded($this->start, $this->end)
            ? $allDates                         // bill the whole campaign at the end
            : collect();
    }

    return $allDates                            // standard: only in-range dates
        ->filter(fn (Carbon $date) => $date->betweenIncluded($this->start, $this->end))
        ->values();
}
```

### Sort order

Reservations are sorted by:
1. `client.sage_client_code` (ascending; missing codes sort to the end via the `'zzz'` sentinel)
2. `client.company_name` (tiebreaker, ascending)
3. `reservation.reference` (final tiebreaker for stable ordering)

Multiple reservations belonging to the same client therefore appear consecutively, but each is treated as its own voucher.

### Row structure (one block per reservation)

For each eligible reservation:

- **One V row** — voucher header
- **One D + one LC pair per emitted date** — see *Billing model* above for which dates are emitted

So for a Standard reservation (no `bill_at_end_of_campaign`) with 5 booked dates of which 3 fall inside the export window, that's 1 V + 3 D + 3 LC = **7 rows**. A `bill_at_end_of_campaign` reservation with the same 5 booked dates whose last date is in range emits 1 V + 5 D + 5 LC = **11 rows**.

#### V row — voucher header

```
V;LG01;INV;1;{client.sage_client_code};{today YYYYMMDD};{startDate} To {endDate}
```

| Field | Value |
|---|---|
| 1 | `V` (literal) |
| 2 | `LG01` (literal — accounting ledger code) |
| 3 | `INV` (literal — voucher type "invoice") |
| 4 | `1` (literal — line counter seed) |
| 5 | `client.sage_client_code` (empty string if missing) |
| 6 | Today's date in `YYYYMMDD` format |
| 7 | `{start.d-M-Y} To {end.d-M-Y}` (human-readable export window) |

#### D row — line item (one per booked date)

```
D;MULTIM-LSL;1;{daily_gross};{commissionPct};{discountPct};{salesperson.sage_salesperson_code};{description}
```

| Field | Value |
|---|---|
| 1 | `D` (literal) |
| 2 | `MULTIM-LSL` (literal — accounting analytics axis) |
| 3 | `1` (literal — line quantity) |
| 4 | Daily gross — `placement.price` (or `gross_amount` for Cost of Artwork — see below) |
| 5 | Commission percentage (see "Percentage normalisation") |
| 6 | Discount percentage (see "Percentage normalisation") |
| 7 | `salesperson.sage_salesperson_code` (empty string if reservation has no salesperson) |
| 8 | `||`-separated description (see "Description format" below) |

#### LC row — analytics tag (one after each D row)

```
LC;DPT;PRD;SNM;MUL
```

All literals — currently fixed for every line item.

### Description format (D row, column 8)

The 8th column of every D row carries a human-readable description with **`||` as the segment separator** so it remains readable when imported into SAGE. The format is:

```
{product}|| {platform}|| {date_DD-MM-YYYY}|| {placement}|| Ref. No {reference}
```

| Segment | Source |
|---|---|
| `{product}` | `reservation.product` |
| `{platform}` | `reservation.placement.platform.name` (falls back to `reservation.platform.name`) |
| `{date_DD-MM-YYYY}` | The specific booked date this D row covers, in `DD-MM-YYYY` format |
| `{placement}` | `reservation.placement.name` (e.g. "Run of site", "Facebook Boost") |
| `Ref. No {reference}` | `reservation.reference` (the autogenerated `{timestamp}-{Ymd}` ID) |

A multi-day reservation produces multiple D rows, each with the same product / platform / placement / reference but a **different date** in the third segment — making each row self-describing in SAGE.

### Daily gross calculation

For **standard** reservations (`type === ReservationType::Standard`):
```
daily_gross = placement.price
```
The same `placement.price` is emitted for every per-day D row. The total revenue **for this export** is `placement.price × number_of_emitted_dates`, where the number of emitted dates depends on the billing rule (in-range count for `bill_at_end = false`; full booked-date count for `bill_at_end = true`).

For **Cost of Artwork** reservations (`type === ReservationType::CostOfArtwork`):
```
gross = reservation.gross_amount   // user-entered flat amount
```
These are billed as a **single line item** — exactly **one V + one D + one LC** per Cost of Artwork reservation, regardless of how many dates the reservation has. The single D row uses the **billing date** for the description's date segment (first booked date by default, or last booked date when `bill_at_end_of_campaign` is set). The user-entered `gross_amount` is emitted as-is (no daily-rate maths).

### Percentage normalisation

Both `commission` and `discount` on a Client can be stored as either a **percentage** (`%`) or a **flat MUR amount**. The CSV always emits a percentage, computed once per reservation against the **total reservation gross** (`placement.price × number_of_booked_dates`):

- If `client.commission_type === Percentage`: use the value as-is, rounded to 2 dp.
- If `client.commission_type === MUR`: convert via `(amount / total_gross) × 100`, rounded to 2 dp. If `total_gross <= 0` the result is `0` (no division-by-zero).

Discount works identically.

For Cost of Artwork reservations, both `commissionPct` and `discountPct` are forced to `0` regardless of the client's commission/discount settings.

### Number formatting

`SageCsvBuilder::formatNumber()` strips trailing zeros and the trailing decimal point — `1000.00` becomes `1000`, `12.50` becomes `12.5`, `12.34` stays `12.34`. Whole numbers print as integers; decimals as 2-dp without trailing zeros.

## Worked example

A confirmed credit reservation, billed in April:

```
client.sage_client_code            = "ART-0009"
client.commission_amount           = 10
client.commission_type             = Percentage
client.discount_amount             = 0
salesperson.sage_salesperson_code  = "GINO"
placement.name                     = "Run of site"
placement.platform.name            = "lexpress.mu"
placement.price                    = 5000
product                            = "Spring promo"
reference                          = "1745234567-20260015"
type                               = Standard
is_cash                            = false
bill_at_end_of_campaign            = false
dates_booked                       = ["2026-04-15", "2026-04-16", "2026-04-17"]
```

Run with `start=2026-04-01`, `end=2026-04-30`, `export_type=sage`. The CSV emits **7 rows** — 1 V + 3 (D + LC):

```
V;LG01;INV;1;ART-0009;20260504;01-Apr-2026 To 30-Apr-2026
D;MULTIM-LSL;1;5000;10;0;GINO;Spring promo|| lexpress.mu|| 15-04-2026|| Run of site|| Ref. No 1745234567-20260015
LC;DPT;PRD;SNM;MUL
D;MULTIM-LSL;1;5000;10;0;GINO;Spring promo|| lexpress.mu|| 16-04-2026|| Run of site|| Ref. No 1745234567-20260015
LC;DPT;PRD;SNM;MUL
D;MULTIM-LSL;1;5000;10;0;GINO;Spring promo|| lexpress.mu|| 17-04-2026|| Run of site|| Ref. No 1745234567-20260015
LC;DPT;PRD;SNM;MUL
```

If the same reservation had `dates_booked = ["2026-04-28", "2026-04-29", "2026-04-30", "2026-05-01", "2026-05-02"]`, the April export would emit only **7 rows** — D + LC pairs for the three April dates only. A separate May export over `2026-05-01 to 2026-05-31` would emit the remaining 2 D + LC pairs for the May dates of the same reservation.

## Edge-case matrix

| Scenario | Behaviour |
|---|---|
| Client missing `sage_client_code` | V row 5th column is empty (`""`) — the row is still emitted. |
| Reservation missing salesperson | D row 7th column is empty. |
| Standard reservation crossing the previous-month boundary (first date < `start_date`) | **Included**, but only the in-range dates emit D rows. Earlier dates were emitted by the previous export. |
| Standard reservation crossing the next-month boundary (first date in range, last date > `end_date`) | **Included**, but only the in-range dates emit D rows. Later dates will be emitted by the next export. |
| Standard reservation with no booked date inside the range | **Excluded** entirely. |
| `bill_at_end_of_campaign = true`, last date inside range | **Included**, with D rows for every booked date (including past ones). |
| `bill_at_end_of_campaign = true`, last date after range | **Excluded** — campaign not yet finished. |
| `bill_at_end_of_campaign = true`, last date before range | **Excluded** — already billed when the campaign ended. |
| `Option` or `Canceled` status | Excluded. Only `Confirmed` is exported. |
| `is_cash = true` reservation and `export_type = sage` | Excluded. The Sage CSV is for `is_cash = false` Confirmed reservations only. |
| `export_type = sales` | No CSV; redirects to `/reservations` with a "not yet available" flash. |
| Client acting as agency (has `represented_client_id`) | V row uses the **billing** client's SAGE code (`client_id`). The represented brand is informational only and does not appear in the CSV. |
| `Cost of Artwork` reservation | Eligible exactly **once**, in the export whose window contains its billing date (first booked date, or last when `bill_at_end_of_campaign` is set). Emits 1 V + 1 D + 1 LC. The D row uses the full `gross_amount` and the billing date in the description. |
| Empty result (no reservations match) | An empty CSV is streamed (no V/D/LC rows). |
| Multiple reservations from the same client | They appear consecutively in the output (sorted by reference) but each gets its **own** V header. |

## Test coverage

`tests/Feature/SageExportTest.php` covers ~21 scenarios including:

- Single Standard reservation with all dates in range → `1×V + N×(D+LC)`
- Two reservations same client → 2 separate `V` blocks
- Two clients → V blocks ordered by SAGE code
- Standard reservation crossing into the previous month → only the in-range dates emit D rows
- Standard reservation crossing into the next month → only the in-range dates emit D rows
- Same Standard reservation across two monthly exports → May dates only in May export, June dates only in June export
- `bill_at_end_of_campaign` true + last date in range, with earlier dates spanning the previous month → all dates emitted (including the previous month's)
- `bill_at_end_of_campaign` true + last date after range → excluded
- `bill_at_end_of_campaign` true + last date inside range with all dates in range → included
- `Option` / `Canceled` reservations → excluded
- Sage export excludes `is_cash = true` reservations
- Sales export type redirects with a "not yet available" notice (no CSV)
- Client acting as agency → V row uses the billing client's SAGE code
- Missing `sage_client_code` → row still emitted with empty 5th column
- D row description format → `||`-separated with the booked date in DD-MM-YYYY
- Multi-day reservation → consecutive D rows each with their own date
- MUR commission normalised against total reservation gross

## File map

| Path | Role |
|---|---|
| `app/Http/Controllers/SageExportController.php` | Invokable controller; routes `sales` to a flash redirect, otherwise queries Confirmed `is_cash = false` reservations and streams the Sage CSV |
| `app/Http/Requests/SageExportRequest.php` | Authorisation + validation |
| `app/Services/SageCsvBuilder.php` | Pure builder — eligibility, sort, format |
| `routes/web.php` | Route declared before `Route::resource('reservations', ...)` |
| `resources/views/reservations/index.blade.php` | Toolbar with date pickers + payment mode + button |
| `tests/Feature/SageExportTest.php` | Feature tests |
