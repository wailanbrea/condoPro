<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'apartment_id',
        'meter_number',
        'reading_date_start',
        'reading_date_end',
        'billing_month',
        'billing_year',
        'reading_initial',
        'reading_final',
        'consumption_m3',
        'conversion_factor',
        'gallons',
        'price_per_gallon',
        'gallon_price',
        'extra_cost_per_gallon',
        'total_gallon_price',
        'total_gas',
        'total_amount',
        'billed',
        'created_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'reading_date_start' => 'date',
            'reading_date_end' => 'date',
            'reading_initial' => 'decimal:3',
            'reading_final' => 'decimal:3',
            'consumption_m3' => 'decimal:3',
            'conversion_factor' => 'decimal:4',
            'gallons' => 'decimal:2',
            'price_per_gallon' => 'decimal:2',
            'gallon_price' => 'decimal:2',
            'extra_cost_per_gallon' => 'decimal:2',
            'total_gallon_price' => 'decimal:2',
            'total_gas' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'billed' => 'boolean',
        ];
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}