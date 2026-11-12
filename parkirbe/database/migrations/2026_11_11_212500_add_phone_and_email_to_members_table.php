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
            $table->string('phone_number', 25)->nullable()->after('plate_number')->comment('Nomor HP / WhatsApp member');
            $table->string('email', 100)->nullable()->after('phone_number')->comment('Email member untuk tagihan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'email']);
        });
    }
};

