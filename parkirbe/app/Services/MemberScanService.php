<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EntryResult;
use App\Models\Member;
use App\Models\MemberEntry;

/**
 * MemberScanService memproses scan kartu member di pintu masuk.
 *
 * Alur verifikasi:
 * 1. Cek apakah kode ada dan member aktif
 * 2. Cek apakah ada tagihan belum lunas
 * 3. Catat hasil scan ke tabel member_entries
 */
class MemberScanService
{
    public function __construct(
        private readonly MemberBillService $billService,
    ) {}

    /**
     * Proses scan kartu member.
     * Selalu mencatat hasil scan ke member_entries (untuk audit trail).
     *
     * @param string $code Kode member dari QR (mis. MBR-XXXXXXXX)
     * @return array{allowed: bool, reason: string, member: Member|null}
     */
    public function scan(string $code): array
    {
        $code   = strtoupper(trim($code));
        $member = Member::where('member_code', $code)->first();

        // Kode tidak ditemukan
        if (!$member) {
            return [
                'allowed' => false,
                'reason'  => 'Kartu tidak valid.',
                'member'  => null,
            ];
        }

        // Member tidak aktif
        if (!$member->is_active) {
            $this->recordEntry($member, EntryResult::Denied, 'Member tidak aktif.');
            return [
                'allowed' => false,
                'reason'  => 'Kartu tidak valid. Member tidak aktif.',
                'member'  => $member,
            ];
        }

        // Ada tagihan belum lunas
        if ($this->billService->hasUnpaid($member)) {
            $reason = 'Tagihan belum lunas, silakan bayar ke petugas.';
            $this->recordEntry($member, EntryResult::Denied, $reason);
            return [
                'allowed' => false,
                'reason'  => $reason,
                'member'  => $member,
            ];
        }

        // Semua OK — diterima
        $this->recordEntry($member, EntryResult::Allowed, null);
        return [
            'allowed' => true,
            'reason'  => 'Silakan masuk.',
            'member'  => $member,
        ];
    }

    /**
     * Catat hasil scan ke tabel member_entries.
     *
     * @param Member       $member
     * @param EntryResult  $result
     * @param string|null  $reason
     */
    private function recordEntry(Member $member, EntryResult $result, ?string $reason): void
    {
        MemberEntry::create([
            'member_id'  => $member->id,
            'scanned_at' => now(),
            'result'     => $result,
            'reason'     => $reason,
        ]);
    }
}
