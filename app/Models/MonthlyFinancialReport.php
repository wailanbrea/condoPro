<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyFinancialReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'month',
        'year',
        'initial_balance',
        'total_income',
        'total_expenses',
        'special_payments',
        'final_balance',
        'total_maintenance',
        'total_gas',
        'total_extra_charges',
        'total_pending',
        'status',
        'created_by',
        'closed_by',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'total_income' => 'decimal:2',
            'total_expenses' => 'decimal:2',
            'special_payments' => 'decimal:2',
            'final_balance' => 'decimal:2',
            'total_maintenance' => 'decimal:2',
            'total_gas' => 'decimal:2',
            'total_extra_charges' => 'decimal:2',
            'total_pending' => 'decimal:2',
            'closed_at' => 'datetime',
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

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function getPeriodNameAttribute(): string
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        return ($months[$this->month] ?? $this->month) . ' ' . $this->year;
    }

    public function calculateFromData(): self
    {
        $bills = MonthlyBill::where('condominium_id', $this->condominium_id)
            ->where('billing_month', $this->month)
            ->where('billing_year', $this->year)
            ->get();

        $this->total_maintenance = $bills->sum(function ($bill) {
            return $bill->billItems->where('concept_type', 'maintenance')->sum('amount');
        });

        $this->total_gas = $bills->sum(function ($bill) {
            return $bill->billItems->where('concept_type', 'gas')->sum('amount');
        });

        $this->total_extra_charges = $bills->sum(function ($bill) {
            return $bill->billItems->where('concept_type', 'extra_charge')->sum('amount');
        });

        $this->total_income = $this->total_maintenance + $this->total_gas + $this->total_extra_charges;

        $confirmedPayments = Payment::where('condominium_id', $this->condominium_id)
            ->where('status', 'confirmed')
            ->whereMonth('payment_date', $this->month)
            ->whereYear('payment_date', $this->year)
            ->sum('amount');

        $expenses = Expense::where('condominium_id', $this->condominium_id)
            ->whereMonth('date', $this->month)
            ->whereYear('date', $this->year)
            ->sum('amount');

        $this->total_expenses = $expenses;
        $this->total_pending = $bills->where('status', '!=', 'paid')->sum('total');

        $totalAdjustments = FinancialMovement::where('condominium_id', $this->condominium_id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->where('movement_type', 'adjustment')
            ->sum('amount');

        $this->final_balance = $this->initial_balance + $this->total_income - $this->total_expenses - $this->special_payments + $totalAdjustments;

        return $this;
    }
}