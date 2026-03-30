<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // ← tambah ini
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids; // ← tambah HasUuids

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'business_name',
        'monthly_revenue',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'monthly_revenue'   => 'decimal:2',
        ];
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}