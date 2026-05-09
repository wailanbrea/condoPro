<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'number',
        'owner_name',
        'area',
        'status',
        'balance',
        'maintenance_fee',
        'has_gas_meter',
    ];

    protected function casts(): array
    {
        return [
            'area' => 'decimal:2',
            'balance' => 'decimal:2',
            'maintenance_fee' => 'decimal:2',
            'has_gas_meter' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::creating(function (Apartment $apartment) {
            $apartment->status = $apartment->status ?? 'active';
            $apartment->balance = $apartment->balance ?? 0;
            $apartment->maintenance_fee = $apartment->maintenance_fee ?? 0;
        });
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'apartment_user')
            ->withPivot('is_primary')
            ->withTimestamps()
            ->using(ApartmentUser::class);
    }

    public function monthlyBills()
    {
        return $this->hasMany(MonthlyBill::class);
    }

    public function gasReadings()
    {
        return $this->hasMany(GasReading::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
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