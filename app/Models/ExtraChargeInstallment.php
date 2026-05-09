<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraChargeInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'extra_charge_id',
        'apartment_id',
        'billing_month',
        'billing_year',
        'amount',
        'bill_item_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'billing_month' => 'integer',
            'billing_year' => 'integer',
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

    public function billItem()
    {
        return $this->belongsTo(BillItem::class);
    }
}