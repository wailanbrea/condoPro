<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraChargeApartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'extra_charge_id',
        'apartment_id',
        'assigned_amount',
        'monthly_amount',
        'percentage',
    ];

    protected function casts(): array
    {
        return [
            'assigned_amount' => 'decimal:2',
            'monthly_amount' => 'decimal:2',
            'percentage' => 'decimal:2',
        ];
    }

    public function extraCharge()
    {
        return $this->belongsTo(ExtraCharge::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}