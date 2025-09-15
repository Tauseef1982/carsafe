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
        Schema::table('customer_bookings', function (Blueprint $table) {
            $table->string('type')->default('direct_webhook');
            $table->dateTime('schedule_date_time')->after('data')->nullable();
            $table->longText('booking_data')->after('schedule_date_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_bookings', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('booking_data');
        });
    }
};
