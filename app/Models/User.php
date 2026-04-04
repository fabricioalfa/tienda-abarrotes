<?php
// filepath: /home/fabri/Documentos/tienda/app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_CAJA = 'caja';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
        ];
    }

    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_CAJA => 'Caja / Ventas',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isCaja(): bool
    {
        return $this->role === self::ROLE_CAJA;
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function openedCashRegisters(): HasMany
    {
        return $this->hasMany(CashRegister::class, 'opened_by');
    }

    public function closedCashRegisters(): HasMany
    {
        return $this->hasMany(CashRegister::class, 'closed_by');
    }
}