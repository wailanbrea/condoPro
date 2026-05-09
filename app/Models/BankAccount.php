<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'bank_name',
        'account_holder',
        'account_number',
        'account_type',
        'currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    protected static function booted()
    {
        static::creating(function (BankAccount $bankAccount) {
            $bankAccount->status = $bankAccount->status ?? 'active';
        });
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}