<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum untuk kategori kendaraan parkir.
 * Digunakan di Member, ParkingTicket, dan Tariff.
 */
enum VehicleCategory: string
{
    case Motor = 'motor';
    case Mobil = 'mobil';

    /** Mendapatkan label tampilan bahasa Indonesia. */
    public function label(): string
    {
        return match($this) {
            self::Motor => 'Motor',
            self::Mobil => 'Mobil',
        };
    }
}
