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
        Schema::create('drivers_payments_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('driver_id');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state_code');
            $table->string('postal_code');
            $table->string('country_code');
            $table->string('bussiness_name')->nullable();
            $table->string('email')->nullable();
            $table->string('language')->default('en');
            $table->string('currency')->default('USD');
            $table->text('note')->nullable();
             $table->string('payout_id')->nullable();
            $table->boolean('send_notifications')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers_payments_infos');
    }
};
