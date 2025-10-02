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
        Schema::table('accounts', function (Blueprint $table) {
            $table->tinyInteger('is_trip_restricted_by_phone')->default(0);
            $table->mediumText('restricted_phones')->nullable();
            $table->string('invoice_email_day')->default(1);
            $table->dateTime('invoice_email_time')->default('2025-10-01 02:00:00');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            //
        });
    }
};
