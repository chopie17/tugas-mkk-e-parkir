<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketStatus;
use App\Enums\VehicleCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ParkingTicket untuk karcis parkir non-member.
 * token adalah kode 6 karakter yang tercetak di karcis.
 * Status: 'active' saat masuk, 'paid' setelah bayar.
 *
 * @property int              $id
 * @property string           $token           6 karakter unik
 * @property \Carbon\Carbon   $entry_at
 * @property \Carbon\Carbon|null $exit_at
 * @property TicketStatus     $status
 * @property VehicleCategory|null $vehicle_category
 * @property string|null      $plate_number
 * @property int|null         $duration_hours
 * @property int|null         $fee
 * @property int|null         $cash_received
 * @property int|null         $change_amount
 * @property int|null         $processed_by
 * @property string|null      $invoice_number
 */
class ParkingTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'entry_at',
        'exit_at',
        'status',
        'vehicle_category',
        'plate_number',
        'duration_hours',
        'fee',
        'cash_received',
        'change_amount',
        'processed_by',
        'invoice_number',
    ];

    protected function casts(): array
    {
        return [
            'status'            => TicketStatus::class,
            'vehicle_category'  => VehicleCategory::class,
            'entry_at'          => 'datetime',
            'exit_at'           => 'datetime',
            'fee'               => 'integer',
            'cash_received'     => 'integer',
            'change_amount'     => 'integer',
            'duration_hours'    => 'integer',
        ];
    }

    /** Petugas yang memproses pembayaran tiket ini */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /** Apakah tiket sudah dibayar */
    public function isPaid(): bool
    {
        return $this->status === TicketStatus::Paid;
    }

    /** Apakah tiket masih aktif (belum bayar) */
    public function isActive(): bool
    {
        return $this->status === TicketStatus::Active;
    }
}
