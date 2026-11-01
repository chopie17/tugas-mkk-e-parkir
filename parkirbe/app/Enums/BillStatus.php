<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum untuk status tagihan member.
 */
enum BillStatus: string
{
    case BelumLunas = 'belum_lunas';
    case Lunas      = 'lunas';

    /** Mendapatkan label tampilan bahasa Indonesia. */
    public function label(): string
    {
        return match($this) {
            self::BelumLunas => 'Belum Lunas',
            self::Lunas      => 'Lunas',
        };
    }
}
