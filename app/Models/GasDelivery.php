<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GasDelivery extends Model
{
    protected $fillable = [
        'condominium_id',
        'tank_reading_before',
        'tank_reading_after',
        'truck_meter_reading',
        'gallons_delivered',
        'invoice_amount',
        'tank_photo_before_path',
        'tank_photo_after_path',
        'truck_photo_path',
        'invoice_photo_path',
        'delivery_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'tank_reading_before' => 'decimal:3',
        'tank_reading_after' => 'decimal:3',
        'truck_meter_reading' => 'decimal:3',
        'gallons_delivered' => 'decimal:2',
        'invoice_amount' => 'decimal:2',
        'delivery_date' => 'date',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}