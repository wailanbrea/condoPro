<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'name',
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
        static::creating(function (ExpenseCategory $expenseCategory) {
            $expenseCategory->status = $expenseCategory->status ?? 'active';
        });
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}