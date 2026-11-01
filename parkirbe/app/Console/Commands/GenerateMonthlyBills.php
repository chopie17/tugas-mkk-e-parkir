<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MemberBillService;
use Illuminate\Console\Command;

/**
 * Artisan command untuk generate tagihan bulanan member.
 *
 * Cara pakai:
 *   php artisan parking:generate-bills
 *   php artisan parking:generate-bills --period=2024-02-01
 *
 * Dijadwalkan otomatis di routes/console.php setiap tanggal 1 bulan.
 */
class GenerateMonthlyBills extends Command
{
    /**
     * Nama dan signature command.
     * --period: Tanggal awal bulan yang dituju (format Y-m-d).
     *           Default: bulan berjalan.
     */
    protected $signature = 'parking:generate-bills
                            {--period= : Periode tagihan (format Y-m-d, default: bulan ini)}';

    /**
     * Deskripsi singkat command.
     */
    protected $description = 'Generate tagihan bulanan untuk semua member aktif';

    public function __construct(
        private readonly MemberBillService $billService,
    ) {
        parent::__construct();
    }

    /**
     * Jalankan command.
     * Menggunakan MemberBillService::generateForMonth() yang bersifat idempotent.
     */
    public function handle(): int
    {
        $period = $this->option('period')
            ? now()->parse($this->option('period'))->startOfMonth()
            : now()->startOfMonth();

        $this->info("Generating tagihan untuk periode: {$period->format('F Y')} ...");

        $count = $this->billService->generateForMonth($period);

        if ($count === 0) {
            $this->warn("Tidak ada tagihan baru dibuat (sudah ada atau tidak ada member aktif).");
        } else {
            $this->info("✅ {$count} tagihan baru berhasil dibuat.");
        }

        return Command::SUCCESS;
    }
}
