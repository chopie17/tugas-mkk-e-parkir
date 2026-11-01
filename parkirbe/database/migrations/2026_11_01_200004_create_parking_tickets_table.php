<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel tiket parkir untuk non-member.
     * token adalah kode 6 karakter yang tercetak di karcis (juga isi QR).
     * Status berubah dari 'active' ke 'paid' setelah pembayaran.
     */
    public function up(): void
    {
        Schema::create('parking_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('token', 6)->unique()->comment('Kode 6 karakter unik, ex: A7K2Q9');
            $table->dateTime('entry_at')->comment('Waktu masuk parkir');
            $table->dateTime('exit_at')->nullable()->comment('Waktu keluar saat proses pembayaran');
            $table->enum('status', ['active', 'paid'])->default('active');
            $table->enum('vehicle_category', ['motor', 'mobil'])->nullable()->comment('Diisi saat pembayaran');
            $table->string('plate_number')->nullable()->comment('No plat, diisi saat pembayaran, uppercase');
            $table->unsignedInteger('duration_hours')->nullable()->comment('Durasi parkir dalam jam (ceil)');
            $table->unsignedBigInteger('fee')->nullable()->comment('Total biaya parkir dalam Rupiah');
            $table->unsignedBigInteger('cash_received')->nullable()->comment('Uang tunai yang diterima');
            $table->unsignedBigInteger('change_amount')->nullable()->comment('Kembalian yang diberikan');
            $table->foreignId('processed_by')->nullable()->constrained('users')->comment('Petugas yang memproses');
            $table->string('invoice_number')->nullable()->unique()->comment('Nomor invoice, format: INV-NM-YYYYMMDD-0001');
            $table->timestamps();

            // Index untuk query scan + laporan
            $table->index('status');
            $table->index('exit_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_tickets');
    }
};
