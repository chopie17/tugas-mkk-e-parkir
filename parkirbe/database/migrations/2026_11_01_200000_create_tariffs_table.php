<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel tariff menyimpan harga parkir per kategori dan tipe.
     * Constraint UNIQUE memastikan tidak ada duplikat tarif.
     */
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['non_member_hourly', 'member_monthly']);
            $table->enum('vehicle_category', ['motor', 'mobil']);
            $table->unsignedBigInteger('amount')->comment('Tarif dalam Rupiah (integer)');
            $table->timestamps();

            // Satu kombinasi type+vehicle_category hanya boleh ada satu record
            $table->unique(['type', 'vehicle_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
