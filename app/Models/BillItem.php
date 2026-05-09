<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'concept_type',
        'description',
        'amount',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function bill()
    {
        return $this->belongsTo(MonthlyBill::class, 'bill_id');
    }

    public function gasReading()
    {
        return $this->belongsTo(GasReading::class, 'reference_id');
    }

    public function extraCharge()
    {
        return $this->belongsTo(ExtraCharge::class, 'reference_id');
    }
}