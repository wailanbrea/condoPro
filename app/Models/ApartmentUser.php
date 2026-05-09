<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ApartmentUser extends Pivot
{
    public $incrementing = true;
    public $timestamps = true;

    protected $table = 'apartment_user';

    protected $fillable = [
        'apartment_id',
        'user_id',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}