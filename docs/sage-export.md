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
| Payment mode | `payment_mode` | `credit` (the default) or `cash` |
| **SAGE Export** button | — | Submits the form |

Pressing the button submits to `GET /reservations/sage-export`.

## End-to-end flow

```
┌──────────────┐    GET     ┌──────────────────────┐    validate    ┌────────────────────┐
│ /reservations│ ─────────▶ │ SageExportController │ ─────────────▶ │ SageExportRequest  │
│  toolbar     │            │     __invoke()       │                │  authorize + rules │
└──────────────┘            └──────────┬───────────┘                └────────────────────┘
                                       │
                       ┌───────────────┴───────────────┐
                       ▼                               ▼
            (cash) flash redirect              (credit) build CSV
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
'payment_mode' => ['required', 'in:cash,credit'],
```

## Controller

`App\Http\Controllers\SageExportController` is invokable. It has two paths:

### Cash mode
```php
return redirect()
    ->route('reservations.index')
    ->with('error', 'Cash SAGE export is not yet available — the format is pending from accounting.');
```
The Cash CSV format has not yet been finalised by the accounting team. The export simply flashes a notice and returns.

### Credit mode
1. Loads all `Confirmed` reservations with `client`, `placement`, `salesperson`, and `representedClient` eager-loaded.
2. Hands them to `App\Services\SageCsvBuilder`.
3. Streams the rows as a CSV download.

```php
$filename = sprintf('sage-export-%s-%s.csv', $start->format('Ymd'), $end->format('Ymd'));

return response()->streamDownload(function () use ($rows) {
    $handle = fopen('php://output', 'w');
    foreach ($rows as $row) {
        fputcsv($handle, $row, ';', '"', '\\');
    }
    fclose($handle);
}, $filename, ['Content-Type' => 'text/csv']);
```

## CSV builder

`App\Services\SageCsvBuilder` is the heart of the export. It is a pure, testable service: input is `Carbon $start`, `Carbon $end`, and a `Collection<Reservation>`; output is `array<array<string>>`.

### Filtering rules (applied in order)

1. **Status** — only reservations whose `status === ReservationStatus::Confirmed` are considered.
2. **Bill at end of campaign** — if `bill_at_end_of_campaign` is `true` *and* the reservation's last booked date falls **after** `end`, the reservation is excluded entirely (the agency hasn't been billed yet).
3. **Dates in range** — for each remaining reservation, the booked-dates array is intersected with the inclusive `[start, end]` window. Any reservation with zero overlapping dates is dropped.

### Sort order

Reservations are sorted by:
1. `client.sage_client_code` (ascending; missing codes sort to the end via the `'zzz'` sentinel)
2. `client.company_name` (tiebreaker, ascending)
3. `reservation.reference` (final tiebreaker for stable ordering)

Multiple reservations belonging to the same client therefore appear consecutively in the output, but each is treated as its own voucher (see below).

### Row structure (one block per reservation)

Each confirmed reservation that survives filtering produces:

- **One V row** — voucher header
- **One D + one LC pair per booked day** that falls inside the date range

For a reservation with 5 in-range booked dates, that's 1 V + 5 D + 5 LC = **11 rows**.

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

#### D row — line item (one per booked day in range)

```
D;MUTLTIM;1;{daily_gross};{commissionPct};{discountPct};{salesperson.sage_salesperson_code};{description}
```

| Field | Value |
|---|---|
| 1 | `D` (literal) |
| 2 | `MUTLTIM` (literal — accounting analytics axis) |
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
| `{date_DD-MM-YYYY}` | The specific booked day this D row covers, in `DD-MM-YYYY` format |
| `{placement}` | `reservation.placement.name` (e.g. "Run of site", "Facebook Boost") |
| `Ref. No {reference}` | `reservation.reference` (the autogenerated `{timestamp}-{Ymd}` ID) |

A multi-day reservation produces multiple D rows, each with the same product / platform / placement / reference but a **different date** in the third segment — making each row self-describing in SAGE.

Empty fields render as empty segments; the `||` separators are preserved so column count stays consistent.

### Daily gross calculation

For **standard** reservations (`type === ReservationType::Standard`):
```
daily_gross = placement.price
```
The same `placement.price` is emitted for every per-day D row. Total revenue per reservation is therefore `placement.price × number_of_in_range_days` distributed across N D rows.

For **Cost of Artwork** reservations (`type === ReservationType::CostOfArtwork`):
```
gross = reservation.gross_amount   // user-entered flat amount
```
These are billed as a **single line item** — exactly **one V + one D + one LC** per Cost of Artwork reservation, regardless of how many dates are in range. The single D row uses the first in-range date for the description's date segment. The user-entered `gross_amount` is emitted as-is (no daily-rate maths).

### Percentage normalisation

Both `commission` and `discount` on a Client can be stored as either a **percentage** (`%`) or a **flat MUR amount**. The CSV always emits a percentage, computed once per reservation against the **total in-range gross** (so multi-day decomposition does not affect the per-day percentage):

- If `client.commission_type === Percentage`: use the value as-is, rounded to 2 dp.
- If `client.commission_type === MUR`: convert via `(amount / total_in_range_gross) × 100`, rounded to 2 dp. If `total_in_range_gross <= 0` the result is `0` (no division-by-zero).

Discount works identically.

For Cost of Artwork reservations, both `commissionPct` and `discountPct` are forced to `0` regardless of the client's commission/discount settings.

### Number formatting

`SageCsvBuilder::formatNumber()` strips trailing zeros and the trailing decimal point — `1000.00` becomes `1000`, `12.50` becomes `12.5`, `12.34` stays `12.34`. Whole numbers print as integers; decimals as 2-dp without trailing zeros.

## Worked example

A confirmed reservation, fully inside `2026-04-01 to 2026-04-30`:

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
dates_booked                       = ["2026-04-15", "2026-04-16", "2026-04-17"]
```

The CSV emits **7 rows** — 1 V + 3 (D + LC):

```
V;LG01;INV;1;ART-0009;20260504;01-Apr-2026 To 30-Apr-2026
D;MUTLTIM;1;5000;10;0;GINO;Spring promo|| lexpress.mu|| 15-04-2026|| Run of site|| Ref. No 1745234567-20260015
LC;DPT;PRD;SNM;MUL
D;MUTLTIM;1;5000;10;0;GINO;Spring promo|| lexpress.mu|| 16-04-2026|| Run of site|| Ref. No 1745234567-20260015
LC;DPT;PRD;SNM;MUL
D;MUTLTIM;1;5000;10;0;GINO;Spring promo|| lexpress.mu|| 17-04-2026|| Run of site|| Ref. No 1745234567-20260015
LC;DPT;PRD;SNM;MUL
```

(Daily gross is `5000` per row, totaling `15 000` for the reservation. Commission is the flat 10 % from the client, applied identically to every D row.)

## Edge cases

| Scenario | Behaviour |
|---|---|
| Client missing `sage_client_code` | V row 5th column is empty (`""`) — the row is still emitted. |
| Reservation missing salesperson | D row 7th column is empty. |
| Reservation partially overlapping the date range | One D row per overlapping day (e.g. a 5-day campaign with 2 days inside the range produces 1 V + 2 D + 2 LC). |
| `bill_at_end_of_campaign = true` and last booked date is after `end` | Reservation **excluded** entirely. |
| `bill_at_end_of_campaign = true` and last booked date is inside `end` | Reservation **included** — D rows emitted for the in-range days only. |
| `Option` or `Canceled` status | Excluded. Only `Confirmed` is exported. |
| Client acting as agency (has `represented_client_id`) | V row uses the **billing** client's SAGE code (i.e. `client_id`). The represented brand is informational only and does not appear in the CSV. |
| `Cost of Artwork` reservation | Always emits exactly 1 V + 1 D + 1 LC per reservation regardless of date count. The D row uses the full `gross_amount` and the first in-range date in the description. |
| `Cost of Artwork` with no date in range | Excluded (same date-overlap rule applies). |
| `cash` payment mode | No CSV; redirect back to `/reservations` with a flash error. |
| Empty result (no reservations match) | An empty CSV is streamed (no V/D/LC rows). |
| Multiple reservations from the same client | They appear consecutively in the output (sorted by reference) but each gets its **own** V header. |

## Test coverage

`tests/Feature/SageExportTest.php` covers ~17 scenarios including:

- Single reservation with N in-range dates → `1×V + N×(D+LC)`
- Two reservations same client → 2 separate `V` blocks
- Two clients → V blocks ordered by SAGE code
- Reservation partially overlapping the range → only the overlapping days produce D rows
- `bill_at_end_of_campaign` true + ends outside range → excluded
- `bill_at_end_of_campaign` true + ends inside range → included
- `Option` / `Canceled` reservations → excluded
- Client acting as agency → V row uses the billing client's SAGE code
- Missing `sage_client_code` → row still emitted with empty 5th column
- `payment_mode=cash` → flash redirect, no download
- D row description format → `||`-separated with the booked date in DD-MM-YYYY
- Multi-day reservation → consecutive D rows each with their own day in the description

## Future work

The Cash CSV format is still being defined by the accounting team. Once the spec lands, the controller's `cash` branch will be replaced with its own builder (likely `SageCashCsvBuilder`) following the same pattern.

## File map

| Path | Role |
|---|---|
| `app/Http/Controllers/SageExportController.php` | Invokable controller; streams or redirects |
| `app/Http/Requests/SageExportRequest.php` | Authorisation + validation |
| `app/Services/SageCsvBuilder.php` | Pure builder — filters, sorts, formats rows |
| `routes/web.php` | Route declared before `Route::resource('reservations', ...)` |
| `resources/views/reservations/index.blade.php` | Toolbar with date pickers + payment mode + button |
| `tests/Feature/SageExportTest.php` | Feature tests |
