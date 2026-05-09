<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condominium extends Model
{
    use HasFactory;

    protected $table = 'condominiums';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'logo',
        'currency',
        'language_default',
        'gas_price_per_gallon',
        'gas_conversion_factor',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'logo' => 'string',
            'currency' => 'string',
            'language_default' => 'string',
            'gas_price_per_gallon' => 'decimal:2',
            'gas_conversion_factor' => 'decimal:4',
            'status' => 'string',
        ];
    }

    protected static function booted()
    {
        static::creating(function (Condominium $condominium) {
            $condominium->currency = $condominium->currency ?? 'DOP';
            $condominium->language_default = $condominium->language_default ?? 'es';
            $condominium->status = $condominium->status ?? 'active';
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }

    public function monthlyBills()
    {
        return $this->hasMany(MonthlyBill::class);
    }

    public function extraCharges()
    {
        return $this->hasMany(ExtraCharge::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function expenseCategories()
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}