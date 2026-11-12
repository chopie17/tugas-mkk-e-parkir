<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\BillStatus;
use App\Models\MemberBill;
use App\Services\MemberBillService;
use Illuminate\Console\Command;

/**
 * Artisan command untuk mengirimkan pesan pengingat tagihan/masa berlaku member
 * via WhatsApp (Fonnte) dan Email (Mailtrap).
 *
 * Cara pakai:
 *   php artisan members:send-expiry-reminder
 */
class SendMemberExpiryReminderCommand extends Command
{
    protected $signature = 'members:send-expiry-reminder';
    protected $description = 'Kirim pesan pengingat tagihan member belum lunas via WhatsApp (Fonnte) dan Email (Mailtrap)';

    public function __construct(
        private readonly MemberBillService $billService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info("Memeriksa tagihan member yang belum lunas...");

        $unpaidBills = MemberBill::with('member')
            ->where('status', BillStatus::BelumLunas->value)
            ->whereHas('member', fn($q) => $q->where('is_active', true))
            ->get();

        if ($unpaidBills->isEmpty()) {
            $this->info("✅ Tidak ada tagihan member yang belum lunas.");
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$unpaidBills->count()} tagihan belum lunas. Memulai pengiriman pengingat...");

        $waCount = 0;
        $emailCount = 0;

        foreach ($unpaidBills as $bill) {
            $res = $this->billService->sendReminder($bill);
            if ($res['whatsapp']) $waCount++;
            if ($res['email']) $emailCount++;

            $this->line("• Member [{$bill->member->name}] - WA: " . ($res['whatsapp'] ? '✅' : '❌') . " | Email: " . ($res['email'] ? '✅' : '❌'));
        }

        $this->info("Pengiriman selesai! WhatsApp terkirim: {$waCount}, Email terkirim: {$emailCount}.");

        return Command::SUCCESS;
    }
}

