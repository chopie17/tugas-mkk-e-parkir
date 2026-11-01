<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum untuk role pengguna sistem.
 */
enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Petugas    = 'petugas';

    /** Mendapatkan label tampilan bahasa Indonesia. */
    public function label(): string
    {
        return match($this) {
            self::SuperAdmin => 'Super Admin',
            self::Petugas    => 'Petugas',
        };
    }
}
