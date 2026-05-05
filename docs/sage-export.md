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
| Payment mode | `payment_mode` | `credit` (default — exports `is_cash = false` reservations) or `cash` (exports `is_cash = true`) |
| **SAGE Export** button | — | Submits the form |

Pressing the button submits to `GET /reservations/sage-export`.

## Billing model

Each reservation is billed in **exactly one** export — the one whose date window covers the reservation's *billing date*:

| Reservation kind | Billing date |
|---|---|
| Standard (default) | First booked date |
| `bill_at_end_of_campaign` enabled | Last booked date |

When a reservation is included, **all** its booked dates produce a `D` + `LC` pair, including dates outside the export window. So a campaign that starts in April but extends into May is billed in full in April's export, and won't reappear in May's.

A reservation whose billing date falls **before** the export's start is therefore **excluded** — it was billed in a previous export. A reservation whose billing date falls **after** the export's end is also excluded — it'll be billed in a future export.

### Worked scenarios

| Reservation dates | bill_at_end | Export range | Included? | D rows |
|---|---|---|---|---|
| Apr 5–7 | no | Apr 1–30 | ✅ | 3 |
| Apr 28 – May 2 | no | Apr 1–30 | ✅ | 5 (incl. May dates) |
| Mar 30 – Apr 3 | no | Apr 1–30 | ❌ (already billed in March) | 0 |
| Apr 5–7 | yes | Apr 1–30 | ✅ (last date inside range) | 3 |
| Apr 20 – May 10 | yes | Apr 1–30 | ❌ (campaign not yet ended) | 0 |
| Mar 15 – Apr 15 | yes | Apr 1–30 | ✅ (last date inside range) | 32 (incl. all March dates) |

## Cash vs Credit

- **Credit mode** (`payment_mode=credit`): exports reservations where `is_cash = false` (the default for any reservation). A "credit" reservation is one billed against a client account.
- **Cash mode** (`payment_mode=cash`): exports reservations where `is_cash = true`. A "cash" reservation is one paid up front.

The CSV row format is identical between the two modes — only the underlying reservation set differs. The downloaded filename embeds the mode for clarity:

```
sage-export-credit-20260401-20260430.csv
sage-export-cash-20260401-20260430.csv
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
                          where is_cash matches mode
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

`App\Http\Controllers\SageExportController` is invokable.

```php
$isCash = $paymentMode === 'cash';

$reservations = Reservation::query()
    ->with(['client', 'placement.platform', 'salesperson', 'representedClient', 'platform'])
    ->where('status', ReservationStatus::Confirmed)
    ->where('is_cash', $isCash)
    ->get();

$builder = new SageCsvBuilder($start, $end);
$rows = $builder->build($reservations);

return response()->streamDownload(/* writes ;-delimited CSV with fputcsv */);
```

The status filter (`Confirmed`) and the cash filter (`is_cash`) are applied at the query level; everything downstream is the responsibility of the builder.

## CSV builder

`App\Services\SageCsvBuilder` is a pure, testable service: input is `Carbon $start`, `Carbon $end`, and a `Collection<Reservation>`; output is `array<array<string>>`.

### Eligibility

```php
private function isBillableInRange(Reservation $reservation): bool
{
    $dates = $this->allDatesSorted($reservation);
    if ($dates->isEmpty()) return false;

    $billingDate = $reservation->bill_at_end_of_campaign
        ? $dates->last()
        : $dates->first();

    return $billingDate->betweenIncluded($this->start, $this->end);
}
```

### Sort order

Reservations are sorted by:
1. `client.sage_client_code` (ascending; missing codes sort to the end via the `'zzz'` sentinel)
2. `client.company_name` (tiebreaker, ascending)
3. `reservation.id` (final tiebreaker for stable ordering)

Multiple reservations belonging to the same client therefore appear consecutively, but each is treated as its own voucher.

### Row structure (one block per reservation)

For each eligible reservation:

- **One V row** — voucher header
- **One D + one LC pair per booked date** — every date of the reservation (sorted chronologically), regardless of whether it falls inside the export window

For a reservation with 5 booked dates, that's 1 V + 5 D + 5 LC = **11 rows**.

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
{product}|| {platform}|| {date_DD-MM-YYYY}|| {placement}|| Ref. No {reservation_id}
```

| Segment | Source |
|---|---|
| `{product}` | `reservation.product` |
| `{platform}` | `reservation.placement.platform.name` (falls back to `reservation.platform.name`) |
| `{date_DD-MM-YYYY}` | The specific booked date this D row covers, in `DD-MM-YYYY` format |
| `{placement}` | `reservation.placement.name` (e.g. "Run of site", "Facebook Boost") |
| `Ref. No {reservation_id}` | `reservation.id` (the integer primary key — the canonical reservation reference) |

A multi-day reservation produces multiple D rows, each with the same product / platform / placement / id but a **different date** in the third segment — making each row self-describing in SAGE.

### Daily gross calculation

For **standard** reservations (`type === ReservationType::Standard`):
```
daily_gross = placement.price
```
The same `placement.price` is emitted for every per-day D row. Total revenue per reservation is therefore `placement.price × number_of_booked_dates` distributed across N D rows.

For **Cost of Artwork** reservations (`type === ReservationType::CostOfArtwork`):
```
gross = reservation.gross_amount   // user-entered flat amount
```
These are billed as a **single line item** — exactly **one V + one D + one LC** per Cost of Artwork reservation, regardless of how many dates the reservation has. The single D row uses the first booked date for the description's date segment. The user-entered `gross_amount` is emitted as-is (no daily-rate maths).

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
id                                 = 42
type                               = Standard
is_cash                            = false
bill_at_end_of_campaign            = false
dates_booked                       = ["2026-04-15", "2026-04-16", "2026-04-17"]
```

Run with `start=2026-04-01`, `end=2026-04-30`, `payment_mode=credit`. The CSV emits **7 rows** — 1 V + 3 (D + LC):

```
V;LG01;INV;1;ART-0009;20260504;01-Apr-2026 To 30-Apr-2026
D;MULTIM-LSL;1;5000;10;0;GINO;Spring promo|| lexpress.mu|| 15-04-2026|| Run of site|| Ref. No 42
LC;DPT;PRD;SNM;MUL
D;MULTIM-LSL;1;5000;10;0;GINO;Spring promo|| lexpress.mu|| 16-04-2026|| Run of site|| Ref. No 42
LC;DPT;PRD;SNM;MUL
D;MULTIM-LSL;1;5000;10;0;GINO;Spring promo|| lexpress.mu|| 17-04-2026|| Run of site|| Ref. No 42
LC;DPT;PRD;SNM;MUL
```

If the same reservation had `dates_booked = ["2026-04-28", "2026-04-29", "2026-04-30", "2026-05-01", "2026-05-02"]`, the same April export would emit **11 rows** — D + LC pairs for **all five** dates including the two May dates, since the reservation begins inside the export window.

## Edge-case matrix

| Scenario | Behaviour |
|---|---|
| Client missing `sage_client_code` | V row 5th column is empty (`""`) — the row is still emitted. |
| Reservation missing salesperson | D row 7th column is empty. |
| Reservation crossing the previous-month boundary (first date < `start_date`) | **Excluded** entirely — was billed in a previous export. |
| Reservation crossing the next-month boundary (first date in range, last date > `end_date`) | **Included**, with D rows for every booked date including the future ones. |
| `bill_at_end_of_campaign = true`, last date inside range | **Included**, with D rows for every booked date (including past ones). |
| `bill_at_end_of_campaign = true`, last date after range | **Excluded** — campaign not yet finished. |
| `bill_at_end_of_campaign = true`, last date before range | **Excluded** — already billed when the campaign ended. |
| `Option` or `Canceled` status | Excluded. Only `Confirmed` is exported. |
| `is_cash = true` and `payment_mode = credit` | Excluded. |
| `is_cash = false` and `payment_mode = cash` | Excluded. |
| Client acting as agency (has `represented_client_id`) | V row uses the **billing** client's SAGE code (`client_id`). The represented brand is informational only and does not appear in the CSV. |
| `Cost of Artwork` reservation | Always emits exactly 1 V + 1 D + 1 LC per reservation regardless of date count. The D row uses the full `gross_amount` and the first booked date in the description. |
| Empty result (no reservations match) | An empty CSV is streamed (no V/D/LC rows). |
| Multiple reservations from the same client | They appear consecutively in the output (sorted by `id`) but each gets its **own** V header. |

## Test coverage

`tests/Feature/SageExportTest.php` covers ~19 scenarios including:

- Single reservation with N booked dates → `1×V + N×(D+LC)`
- Two reservations same client → 2 separate `V` blocks
- Two clients → V blocks ordered by SAGE code
- Reservation that starts before the range → **excluded**
- Reservation that starts in the range and ends in the future → **included with all dates**
- `bill_at_end_of_campaign` true + ends outside range → excluded
- `bill_at_end_of_campaign` true + ends inside range → included
- `Option` / `Canceled` reservations → excluded
- Credit mode excludes `is_cash = true` reservations
- Cash mode includes `is_cash = true` reservations and excludes credit ones
- Client acting as agency → V row uses the billing client's SAGE code
- Missing `sage_client_code` → row still emitted with empty 5th column
- D row description format → `||`-separated with the booked date in DD-MM-YYYY
- Multi-day reservation → consecutive D rows each with their own date

## File map

| Path | Role |
|---|---|
| `app/Http/Controllers/SageExportController.php` | Invokable controller; queries Confirmed reservations filtered by `is_cash` and streams CSV |
| `app/Http/Requests/SageExportRequest.php` | Authorisation + validation |
| `app/Services/SageCsvBuilder.php` | Pure builder — eligibility, sort, format |
| `routes/web.php` | Route declared before `Route::resource('reservations', ...)` |
| `resources/views/reservations/index.blade.php` | Toolbar with date pickers + payment mode + button |
| `tests/Feature/SageExportTest.php` | Feature tests |
