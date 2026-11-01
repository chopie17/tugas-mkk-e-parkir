<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel riwayat scan kartu member di pintu masuk.
     * Mencatat setiap percobaan scan, baik allowed maupun denied.
     */
    public function up(): void
    {
        Schema::create('member_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->dateTime('scanned_at')->comment('Waktu scan kartu member');
            $table->enum('result', ['allowed', 'denied'])->comment('Hasil verifikasi: diterima atau ditolak');
            $table->string('reason')->nullable()->comment('Alasan penolakan jika result = denied');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_entries');
    }
};
