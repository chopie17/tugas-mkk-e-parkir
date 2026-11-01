<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel member menyimpan data anggota tetap parkir.
     * member_code adalah kode unik yang dicetak di kartu QR member.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code')->unique()->comment('Kode QR kartu member, format: MBR-XXXXXXXX');
            $table->string('name');
            $table->string('plate_number')->unique()->comment('Nomor plat kendaraan, dinormalisasi uppercase');
            $table->enum('vehicle_category', ['motor', 'mobil']);
            $table->unsignedBigInteger('monthly_fee')->comment('Iuran bulanan dalam Rupiah');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->comment('Petugas yang mendaftarkan');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
