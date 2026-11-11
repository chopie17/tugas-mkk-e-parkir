<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntryResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model MemberEntry untuk riwayat scan kartu member.
 * Mencatat setiap percobaan scan, baik berhasil maupun gagal.
 *
 * @property int          $id
 * @property int          $member_id
 * @property string       $scanned_at
 * @property EntryResult  $result
 * @property string|null  $reason
 */
class MemberEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'direction',
        'scanned_at',
        'result',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'result'     => EntryResult::class,
            'scanned_at' => 'datetime',
        ];
    }

    /** Member yang melakukan scan */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
