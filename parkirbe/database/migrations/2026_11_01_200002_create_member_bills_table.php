<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel tagihan bulanan member.
     * period selalu tanggal 1 bulan yang bersangkutan.
     * UNIQUE(member_id, period) mencegah duplikat tagihan per bulan.
     */
    public function up(): void
    {
        Schema::create('member_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->date('period')->comment('Selalu tanggal 1 bulan bersangkutan, mis. 2026-11-01');
            $table->unsignedBigInteger('amount')->comment('Nominal tagihan dalam Rupiah');
            $table->enum('status', ['belum_lunas', 'lunas'])->default('belum_lunas');
            $table->unsignedBigInteger('cash_received')->nullable()->comment('Uang tunai yang diterima saat bayar');
            $table->unsignedBigInteger('change_amount')->nullable()->comment('Kembalian yang diberikan');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->comment('Petugas yang memproses pembayaran');
            $table->string('invoice_number')->nullable()->unique()->comment('Nomor invoice, format: INV-M-YYYYMM-0001');
            $table->timestamps();

            // Satu member hanya boleh punya satu tagihan per periode (idempotent)
            $table->unique(['member_id', 'period']);

            // Index untuk query laporan dan status
            $table->index('status');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_bills');
    }
};
