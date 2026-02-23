<?php

namespace App\Models;

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
        'invoice_no',
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
            'cost_of_artwork' => 'decimal:2',
            'vat' => 'decimal:2',
            'vat_exempt' => 'boolean',
        ];
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
