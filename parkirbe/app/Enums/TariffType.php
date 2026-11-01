<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum untuk jenis tarif parkir.
 */
enum TariffType: string
{
    case NonMemberHourly = 'non_member_hourly';
    case MemberMonthly   = 'member_monthly';

    /** Mendapatkan label tampilan bahasa Indonesia. */
    public function label(): string
    {
        return match($this) {
            self::NonMemberHourly => 'Non Member (Per Jam)',
            self::MemberMonthly   => 'Member (Per Bulan)',
        };
    }
}
