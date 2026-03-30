<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'reference',
        'client_id',
        'agency_id',
        'salesperson_id',
        'product',
        'platform_id',
        'placement_id',
        'channel',
        'scope',
        'dates_booked',
        'gross_amount',
        'total_amount_to_pay',
        'discount',
        'commission',
        'cost_of_artwork',
        'vat',
        'vat_exempt',
        'purchase_order_no',
        'purchase_order_path',
        'invoice_no',
        'invoice_path',
        'signed_ro_path',
        'remark',
    ];

    protected static function booted(): void
    {
        static::creating(function (Reservation $reservation): void {
            if (empty($reservation->reference)) {
                $now = Carbon::now();
                $reservation->reference = $now->timestamp.'-'.$now->format('Ymd');
            }
        });
    }

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
            'cost_of_artwork' => 'decimal:2',
            'vat' => 'decimal:2',
            'vat_exempt' => 'boolean',
        ];
    }

    /**
     * Format booked dates into consecutive ranges.
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
     * @return BelongsTo<Agency, $this>
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
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
}
