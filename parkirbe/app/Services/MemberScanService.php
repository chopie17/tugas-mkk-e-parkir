<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EntryResult;
use App\Models\Member;
use App\Models\MemberEntry;

/**
 * MemberScanService memproses scan kartu member di pintu masuk dan pintu keluar.
 *
 * Alur verifikasi:
 * 1. Cek apakah kode/plat ada dan member aktif
 * 2. Cek apakah ada tagihan belum lunas
 * 3. Logika Anti-Passback (arah 'in' vs 'out'):
 *    - Masuk ('in'): hanya boleh jika status member 'out'. Jika 'in', ditolak karena sudah di dalam.
 *    - Keluar ('out'): hanya boleh jika status member 'in'. Jika 'out', ditolak karena belum masuk.
 * 4. Update status posisi member (parking_status)
 * 5. Catat riwayat scan ke tabel member_entries
 */
class MemberScanService
{
    public function __construct(
        private readonly MemberBillService $billService,
    ) {}

    /**
     * Proses scan kartu member (masuk atau keluar).
     *
     * @param string $code       Kode member dari QR (mis. MBR-XXXXXXXX) atau Plat Nomor
     * @param string $direction  Arah: 'in' (pintu masuk) atau 'out' (pintu keluar)
     * @return array{allowed: bool, reason: string, direction: string, member: Member|null}
     */
    public function scan(string $code, string $direction = 'in'): array
    {
        $direction = strtolower($direction) === 'out' ? 'out' : 'in';
        $normalized = strtoupper(trim($code));

        // Cari berdasarkan member_code ATAU plate_number (untuk kemudahan input manual)
        $member = Member::where('member_code', $normalized)
            ->orWhere('plate_number', $normalized)
            ->first();

        // 1. Kode / plat tidak ditemukan
        if (!$member) {
            return [
                'allowed'   => false,
                'reason'    => 'Kartu atau data member tidak ditemukan.',
                'direction' => $direction,
                'member'    => null,
            ];
        }

        // 2. Member tidak aktif
        if (!$member->is_active) {
            $this->recordEntry($member, $direction, EntryResult::Denied, 'Member non-aktif.');
            return [
                'allowed'   => false,
                'reason'    => 'Kartu tidak valid. Status member non-aktif.',
                'direction' => $direction,
                'member'    => $member,
            ];
        }

        // 3. Ada tagihan belum lunas
        if ($this->billService->hasUnpaid($member)) {
            $reason = 'Tagihan iuran bulanan belum lunas. Silakan lakukan pembayaran ke kasir.';
            $this->recordEntry($member, $direction, EntryResult::Denied, $reason);
            return [
                'allowed'   => false,
                'reason'    => $reason,
                'direction' => $direction,
                'member'    => $member,
            ];
        }

        // 4. Logika Anti-Passback
        $currentStatus = $member->parking_status ?? 'out';

        if ($direction === 'in') {
            // Jika scan masuk, tapi statusnya sudah di dalam (IN)
            if ($currentStatus === 'in') {
                $reason = 'Akses Masuk Ditolak: Kendaraan sudah tercatat berada di dalam area parkir (Anti-Passback).';
                $this->recordEntry($member, $direction, EntryResult::Denied, $reason);
                return [
                    'allowed'   => false,
                    'reason'    => $reason,
                    'direction' => $direction,
                    'member'    => $member,
                ];
            }

            // Valid: Berhasil masuk
            $member->update([
                'parking_status' => 'in',
                'last_entry_at'  => now(),
            ]);

            $this->recordEntry($member, $direction, EntryResult::Allowed, null);

            return [
                'allowed'   => true,
                'reason'    => 'Akses Masuk Berhasil. Gerbang dibuka.',
                'direction' => $direction,
                'member'    => $member->fresh(),
            ];
        } else {
            // Direction = 'out'
            // Jika scan keluar, tapi statusnya belum masuk (OUT)
            if ($currentStatus !== 'in') {
                $reason = 'Akses Keluar Ditolak: Kendaraan belum tercatat masuk ke area parkir.';
                $this->recordEntry($member, $direction, EntryResult::Denied, $reason);
                return [
                    'allowed'   => false,
                    'reason'    => $reason,
                    'direction' => $direction,
                    'member'    => $member,
                ];
            }

            // Valid: Berhasil keluar
            $member->update([
                'parking_status' => 'out',
                'last_exit_at'   => now(),
            ]);

            $this->recordEntry($member, $direction, EntryResult::Allowed, null);

            return [
                'allowed'   => true,
                'reason'    => 'Akses Keluar Berhasil. Gerbang dibuka. Terima kasih.',
                'direction' => $direction,
                'member'    => $member->fresh(),
            ];
        }
    }

    /**
     * Catat hasil scan ke tabel member_entries.
     */
    private function recordEntry(Member $member, string $direction, EntryResult $result, ?string $reason): void
    {
        MemberEntry::create([
            'member_id'  => $member->id,
            'direction'  => $direction,
            'scanned_at' => now(),
            'result'     => $result,
            'reason'     => $reason,
        ]);
    }
}
