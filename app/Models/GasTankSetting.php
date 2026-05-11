<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GasTankSetting extends Model
{
    protected $fillable = [
        'condominium_id',
        'tank_name',
        'capacity_gallons',
        'alert_min_gallons',
        'alert_min_percentage',
        'average_consumption_method',
        'status',
    ];

    protected $casts = [
        'capacity_gallons' => 'decimal:2',
        'alert_min_gallons' => 'decimal:2',
        'alert_min_percentage' => 'decimal:2',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public static function getForCondominium(int $condominiumId): self
    {
        return static::firstOrCreate(
            ['condominium_id' => $condominiumId],
            [
                'tank_name' => 'Tanque Principal',
                'capacity_gallons' => 100,
                'alert_min_gallons' => 20,
                'alert_min_percentage' => 20,
                'average_consumption_method' => 'last_3_months',
                'status' => 'active',
            ]
        );
    }

    public static function getDefaultCapacity(): float
    {
        return 100;
    }
}