<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TariffType;
use App\Enums\VehicleCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Tariff menyimpan harga parkir.
 * Tipe: non_member_hourly (per jam) atau member_monthly (per bulan).
 *
 * @property int             $id
 * @property TariffType      $type
 * @property VehicleCategory $vehicle_category
 * @property int             $amount
 */
class Tariff extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'vehicle_category', 'amount'];

    protected function casts(): array
    {
        return [
            'type'             => TariffType::class,
            'vehicle_category' => VehicleCategory::class,
            'amount'           => 'integer',
        ];
    }

    /**
     * Ambil tarif berdasarkan tipe dan kategori kendaraan.
     *
     * @param TariffType      $type
     * @param VehicleCategory $category
     * @return static|null
     */
    public static function getAmount(TariffType $type, VehicleCategory $category): ?int
    {
        return static::where('type', $type->value)
            ->where('vehicle_category', $category->value)
            ->value('amount');
    }
}
