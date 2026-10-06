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

    /**
     * Relaciones que impiden borrar al usuario.
     *
     * Las claves foraneas de `sales.user_id` y `cash_registers.opened_by` son
     * ON DELETE RESTRICT, asi que borrar un usuario con historial lanza una
     * QueryException (error 500). Estas consultas permiten avisar al usuario
     * con un mensaje claro en lugar de una pantalla en blanco.
     *
     * @return array<int, string>
     */
    public function deletionBlockers(): array
    {
        $blockers = [];

        if ($this->sales()->exists()) {
            $blockers[] = 'ventas registradas';
        }

        if ($this->openedCashRegisters()->exists()) {
            $blockers[] = 'cajas abiertas';
        }

        return $blockers;
    }

    /**
     * Indica si este usuario es el ultimo administrador activo. Borrarlo dejaria
     * el sistema sin ningun usuario capaz de administrar productos, categorias
     * ni usuarios.
     */
    public function isLastAdmin(): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        return static::query()
            ->where('role', self::ROLE_ADMIN)
            ->whereKeyNot($this->getKey())
            ->doesntExist();
    }
}
