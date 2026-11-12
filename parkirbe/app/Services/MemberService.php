<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TariffType;
use App\Enums\VehicleCategory;
use App\Models\Member;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * MemberService mengelola pendaftaran dan pembaruan data member parkir.
 */
class MemberService
{
    public function __construct(
        private readonly MemberBillService $billService,
    ) {}

    /**
     * Mendaftarkan member baru dan otomatis membuat tagihan bulan berjalan.
     *
     * @param array{name: string, plate_number: string, vehicle_category: string} $data
     * @param int $createdBy ID petugas yang mendaftarkan
     * @return Member Member yang baru dibuat
     */
    public function create(array $data, int $createdBy): Member
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $category  = VehicleCategory::from($data['vehicle_category']);
            $monthlyFee = Tariff::getAmount(TariffType::MemberMonthly, $category) ?? 0;

            $member = Member::create([
                'member_code'      => $this->generateMemberCode(),
                'name'             => $data['name'],
                'company_name'     => isset($data['company_name']) ? trim((string)$data['company_name']) : null,
                'plate_number'     => !empty($data['plate_number']) ? strtoupper(trim((string)$data['plate_number'])) : null,
                'phone_number'     => isset($data['phone_number']) ? trim((string)$data['phone_number']) : null,
                'email'            => isset($data['email']) ? strtolower(trim((string)$data['email'])) : null,
                'vehicle_category' => $category,
                'monthly_fee'      => $monthlyFee,
                'is_active'        => true,
                'created_by'       => $createdBy,
            ]);

            // Buat tagihan bulan berjalan secara otomatis dengan status belum_lunas
            $this->billService->generateForPeriod($member, now()->startOfMonth());

            return $member;
        });
    }

    /**
     * Memperbarui data member (nama, plat, kategori, status aktif).
     * Jika kategori berubah, monthly_fee diupdate sesuai tarif terbaru.
     *
     * @param Member $member
     * @param array  $data
     * @return Member
     */
    public function update(Member $member, array $data): Member
    {
        return DB::transaction(function () use ($member, $data) {
            $updateData = array_filter([
                'name'         => $data['name'] ?? null,
                'company_name' => array_key_exists('company_name', $data) ? ($data['company_name'] ? trim((string)$data['company_name']) : null) : null,
                'phone_number' => array_key_exists('phone_number', $data) ? ($data['phone_number'] ? trim((string)$data['phone_number']) : null) : null,
                'email'        => array_key_exists('email', $data) ? ($data['email'] ? strtolower(trim((string)$data['email'])) : null) : null,
                'is_active'    => isset($data['is_active']) ? (bool) $data['is_active'] : null,
            ], fn($v) => $v !== null);

            if (isset($data['plate_number'])) {
                $updateData['plate_number'] = strtoupper(trim($data['plate_number']));
            }

            if (isset($data['vehicle_category'])) {
                $category = VehicleCategory::from($data['vehicle_category']);
                $updateData['vehicle_category'] = $category;
                $updateData['monthly_fee']      = Tariff::getAmount(TariffType::MemberMonthly, $category) ?? $member->monthly_fee;
            }

            $member->update($updateData);
            return $member->fresh();
        });
    }

    /**
     * Soft-delete member.
     *
     * @param Member $member
     */
    public function delete(Member $member): void
    {
        $member->delete();
    }

    /**
     * Generate kode member unik: MBR- + 8 karakter random huruf besar/angka.
     */
    private function generateMemberCode(): string
    {
        do {
            $code = 'MBR-' . strtoupper(Str::random(8));
        } while (Member::where('member_code', $code)->withTrashed()->exists());

        return $code;
    }
}
