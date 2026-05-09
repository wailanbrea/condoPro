<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'title',
        'description',
        'total_amount',
        'distribution_type',
        'start_month',
        'start_year',
        'installments_count',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'installments_count' => 'integer',
            'start_month' => 'integer',
            'start_year' => 'integer',
        ];
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function extraChargeApartments()
    {
        return $this->hasMany(ExtraChargeApartment::class);
    }

    public function extraChargeInstallments()
    {
        return $this->hasMany(ExtraChargeInstallment::class);
    }
}