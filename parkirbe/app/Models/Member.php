<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VehicleCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Member untuk anggota parkir tetap.
 * Tiap member punya kartu QR berisi member_code.
 *
 * @property int             $id
 * @property string          $member_code   Kode unik untuk QR kartu
 * @property string          $name
 * @property string          $plate_number
 * @property string|null     $phone_number
 * @property string|null     $email
 * @property VehicleCategory $vehicle_category
 * @property int             $monthly_fee
 * @property bool            $is_active
 * @property int             $created_by
 */
class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_code',
        'name',
        'company_name',
        'plate_number',
        'phone_number',
        'email',
        'vehicle_category',
        'monthly_fee',
        'is_active',
        'parking_status',
        'last_entry_at',
        'last_exit_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_category' => VehicleCategory::class,
            'monthly_fee'      => 'integer',
            'is_active'        => 'boolean',
            'last_entry_at'    => 'datetime',
            'last_exit_at'     => 'datetime',
        ];
    }

    /** Petugas yang mendaftarkan member ini */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Semua tagihan bulanan member ini */
    public function bills(): HasMany
    {
        return $this->hasMany(MemberBill::class);
    }

    /** Riwayat scan kartu member ini */
    public function entries(): HasMany
    {
        return $this->hasMany(MemberEntry::class);
    }

    /**
     * Cek apakah member memiliki tagihan yang belum lunas.
     */
    public function hasUnpaidBill(): bool
    {
        return $this->bills()->where('status', 'belum_lunas')->exists();
    }
}
