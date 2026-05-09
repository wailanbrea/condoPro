<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'movement_type',
        'category',
        'amount',
        'description',
        'reference_id',
        'reference_type',
        'movement_date',
        'month',
        'year',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'movement_date' => 'date',
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

    public function reference()
    {
        return $this->morphTo();
    }

    public static function getCategoryOptions(): array
    {
        return [
            'income' => [
                'maintenance' => 'Cuota de Mantenimiento',
                'gas' => 'Consumo de Gas',
                'extra_charge' => 'Cuota Extraordinaria',
                'arrears' => 'Atrasos / Mora',
                'other_income' => 'Otros Ingresos',
            ],
            'expense' => [
                'nomina' => 'Nómina',
                'caasd' => 'CAASD (Agua)',
                'edesur' => 'EDESUR (Electricidad)',
                'gas_pago' => 'Gas',
                'ayuntamiento' => 'Ayuntamiento',
                'transferencias' => 'Transferencias',
                'detergentes' => 'Detergentes',
                'botellones' => 'Botellones',
                'compensaciones' => 'Compensaciones',
                'reparaciones' => 'Reparaciones',
                'mantenimiento' => 'Mantenimiento',
                'seguridad' => 'Seguridad',
                'administracion' => 'Administración',
                'otros' => 'Otros Gastos',
            ],
            'adjustment' => [
                'bank_adjustment' => 'Ajuste Bancario',
                'correction' => 'Corrección Contable',
                'opening_balance' => 'Balance de Apertura',
            ],
        ];
    }
}