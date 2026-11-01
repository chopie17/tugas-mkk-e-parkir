<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BillStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model MemberBill untuk tagihan bulanan member.
 * period selalu tanggal 1 bulan yang bersangkutan.
 *
 * @property int        $id
 * @property int        $member_id
 * @property string     $period      Format: Y-m-d (selalu tgl 1)
 * @property int        $amount
 * @property BillStatus $status
 * @property int|null   $cash_received
 * @property int|null   $change_amount
 * @property string|null $paid_at
 * @property int|null   $processed_by
 * @property string|null $invoice_number
 */
class MemberBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'period',
        'amount',
        'status',
        'cash_received',
        'change_amount',
        'paid_at',
        'processed_by',
        'invoice_number',
    ];

    protected function casts(): array
    {
        return [
            'status'        => BillStatus::class,
            'amount'        => 'integer',
            'cash_received' => 'integer',
            'change_amount' => 'integer',
            'paid_at'       => 'datetime',
            'period'        => 'date',
        ];
    }

    /** Member pemilik tagihan ini */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /** Petugas yang memproses pembayaran */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /** Apakah tagihan ini sudah lunas */
    public function isPaid(): bool
    {
        return $this->status === BillStatus::Lunas;
    }
}
