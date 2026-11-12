<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('name')->comment('Nama perusahaan / instansi / tenant');
            $table->string('plate_number')->nullable()->change();
        });

        // Drop unique constraint on plate_number if it exists so plate is not tied uniquely to one member card
        try {
            Schema::table('members', function (Blueprint $table) {
                $table->dropUnique(['plate_number']);
            });
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('company_name');
        });
    }
};

