<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'condominium_id',
        'phone',
        'avatar',
        'language',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
            'language' => 'string',
            'status' => 'string',
        ];
    }

    protected static function booted()
    {
        static::creating(function (User $user) {
            $user->role = $user->role ?? 'resident';
            $user->language = $user->language ?? 'es';
            $user->status = $user->status ?? 'active';
        });
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function apartments()
    {
        return $this->belongsToMany(Apartment::class, 'apartment_user')
            ->withPivot('is_primary')
            ->withTimestamps()
            ->using(ApartmentUser::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    public function extraCharges()
    {
        return $this->hasMany(ExtraCharge::class, 'created_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}