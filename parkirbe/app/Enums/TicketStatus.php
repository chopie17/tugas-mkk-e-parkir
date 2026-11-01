<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum untuk status tiket parkir non-member.
 */
enum TicketStatus: string
{
    case Active = 'active';
    case Paid   = 'paid';

    /** Mendapatkan label tampilan bahasa Indonesia. */
    public function label(): string
    {
        return match($this) {
            self::Active => 'Aktif',
            self::Paid   => 'Sudah Dibayar',
        };
    }
}
