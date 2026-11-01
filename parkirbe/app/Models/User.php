<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model User untuk Petugas dan Super Admin.
 *
 * @property int      $id
 * @property string   $name
 * @property string   $username
 * @property string   $email
 * @property UserRole $role
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role'     => UserRole::class,
        ];
    }

    /** Member yang didaftarkan oleh user ini */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'created_by');
    }

    /** Tiket non-member yang diproses oleh user ini */
    public function parkingTickets(): HasMany
    {
        return $this->hasMany(ParkingTicket::class, 'processed_by');
    }

    /** Tagihan member yang dilunaskan oleh user ini */
    public function memberBills(): HasMany
    {
        return $this->hasMany(MemberBill::class, 'processed_by');
    }

    /** Mengecek apakah user adalah super admin */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /** Mengecek apakah user adalah petugas */
    public function isPetugas(): bool
    {
        return $this->role === UserRole::Petugas;
    }
}
