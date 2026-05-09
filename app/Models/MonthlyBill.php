<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'apartment_id',
        'billing_month',
        'billing_year',
        'subtotal',
        'previous_balance',
        'payments_applied',
        'total',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'previous_balance' => 'decimal:2',
            'payments_applied' => 'decimal:2',
            'total' => 'decimal:2',
            'due_date' => 'date',
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

    public function billItems()
    {
        return $this->hasMany(BillItem::class, 'bill_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'bill_id');
    }

    public function paymentsViaPivot()
    {
        return $this->belongsToMany(Payment::class, 'payment_bill')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function getRemainingAttribute()
    {
        return max(0, $this->total - $this->payments_applied);
    }
}