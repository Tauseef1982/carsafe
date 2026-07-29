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
        Schema::create('driver_balance_histories', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->date('week_start');
            $table->decimal('balance_before', 10, 2)->default(0);
            $table->decimal('transaction_amount', 10, 2)->default(0);
            $table->decimal('balance_after', 10, 2)->default(0);
            $table->boolean('is_dispatcher')->default(0);
             $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_balance_histories');
    }
};
