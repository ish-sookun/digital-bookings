<?php

namespace App\Models;

use App\ForeignCurrency;
use App\ReservationStatus;
use App\ReservationType;
use Carbon\Carbon;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'represented_client_id',
        'salesperson_id',
        'product',
        'platform_id',
        'placement_id',
        'type',
        'channel',
        'scope',
        'dates_booked',
        'gross_amount',
        'total_amount_to_pay',
        'discount',
        'commission',
        'vat',
        'vat_exempt',
        'is_cash',
        'parent_reservation_id',
        'is_foreign_currency',
        'foreign_currency_amount',
        'foreign_currency_code',
        'bill_at_end_of_campaign',
        'status',
        'purchase_order_no',
        'purchase_order_path',
        'invoice_no',
        'invoice_path',
        'receipt_no',
        'receipt_path',
        'signed_ro_path',
        'remark',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dates_booked' => 'array',
            'gross_amount' => 'decimal:2',
            'total_amount_to_pay' => 'decimal:2',
            'discount' => 'decimal:2',
            'commission' => 'decimal:2',
            'vat' => 'decimal:2',
            'vat_exempt' => 'boolean',
            'is_cash' => 'boolean',
            'is_foreign_currency' => 'boolean',
            'foreign_currency_amount' => 'decimal:2',
            'foreign_currency_code' => ForeignCurrency::class,
            'bill_at_end_of_campaign' => 'boolean',
            'type' => ReservationType::class,
            'status' => ReservationStatus::class,
        ];
    }

    /**
     * Format published dates into consecutive ranges.
     *
     * @return list<string>
     */
    public function formattedDateRanges(): array
    {
        $dates = collect($this->dates_booked)
            ->map(fn (string $date) => Carbon::parse($date))
            ->sort()
            ->values();

        if ($dates->isEmpty()) {
            return [];
        }

        $ranges = [];
        $rangeStart = $dates->first();
        $rangeEnd = $dates->first();

        for ($i = 1; $i < $dates->count(); $i++) {
            $current = $dates[$i];

            if ((int) abs($current->diffInDays($rangeEnd)) === 1) {
                $rangeEnd = $current;
            } else {
                $ranges[] = $rangeStart->eq($rangeEnd)
                    ? $rangeStart->format('d F Y')
                    : $rangeStart->format('d F Y').' — '.$rangeEnd->format('d F Y');
                $rangeStart = $current;
                $rangeEnd = $current;
            }
        }

        $ranges[] = $rangeStart->eq($rangeEnd)
            ? $rangeStart->format('d F Y')
            : $rangeStart->format('d F Y').' — '.$rangeEnd->format('d F Y');

        return $ranges;
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The client that `client_id` is booking on behalf of, when acting as an agency.
     *
     * @return BelongsTo<Client, $this>
     */
    public function representedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'represented_client_id');
    }

    /**
     * @return BelongsTo<Salesperson, $this>
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Salesperson::class);
    }

    /**
     * @return BelongsTo<Platform, $this>
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * @return BelongsTo<Placement, $this>
     */
    public function placement(): BelongsTo
    {
        return $this->belongsTo(Placement::class);
    }

    /**
     * @return BelongsTo<Reservation, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'parent_reservation_id');
    }

    /**
     * @return HasMany<Reservation, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Reservation::class, 'parent_reservation_id');
    }
}
