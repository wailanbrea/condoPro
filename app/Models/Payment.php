<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'apartment_id',
        'user_id',
        'bill_id',
        'bank_account_id',
        'amount',
        'payment_date',
        'reference_number',
        'voucher_path',
        'status',
        'admin_observation',
        'confirmed_by',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'confirmed_at' => 'datetime',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bill()
    {
        return $this->belongsTo(MonthlyBill::class, 'bill_id');
    }

    public function bills()
    {
        return $this->belongsToMany(MonthlyBill::class, 'payment_bill')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}