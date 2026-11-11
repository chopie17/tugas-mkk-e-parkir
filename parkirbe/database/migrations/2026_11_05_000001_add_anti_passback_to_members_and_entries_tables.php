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
            $table->enum('parking_status', ['out', 'in'])->default('out')->after('is_active')->comment('Posisi kendaraan: out (di luar), in (di dalam)');
            $table->dateTime('last_entry_at')->nullable()->after('parking_status')->comment('Waktu terakhir scan masuk');
            $table->dateTime('last_exit_at')->nullable()->after('last_entry_at')->comment('Waktu terakhir scan keluar');
        });

        Schema::table('member_entries', function (Blueprint $table) {
            $table->enum('direction', ['in', 'out'])->default('in')->after('member_id')->comment('Arah scan: in (masuk), out (keluar)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['parking_status', 'last_entry_at', 'last_exit_at']);
        });

        Schema::table('member_entries', function (Blueprint $table) {
            $table->dropColumn('direction');
        });
    }
};

