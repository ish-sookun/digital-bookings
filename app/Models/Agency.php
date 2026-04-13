<?php

namespace App\Models;

use App\CommissionType;
use App\DiscountType;
use Database\Factories\AgencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    /** @use HasFactory<AgencyFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_name',
        'company_logo',
        'brn',
        'vat_number',
        'vat_exempt',
        'phone',
        'address',
        'commission_amount',
        'commission_type',
        'discount',
        'discount_type',
        'contact_person_name',
        'contact_person_email',
        'contact_person_phone',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vat_exempt' => 'boolean',
            'commission_type' => CommissionType::class,
            'discount_type' => DiscountType::class,
        ];
    }

    /**
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
