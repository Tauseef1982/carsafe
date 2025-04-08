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
        Schema::create('account_payments', function (Blueprint $table) {
            $table->id();
            $table->string('account_id')->nullable();
            $table->string('account_type')->nullable();
            $table->string('trip_id')->nullable();
            $table->unsignedInteger('batch_id')->nullable();
            $table->float('amount', 8 ,2 )->nullable();
            $table->date('payment_date');
            $table->string('transaction_id')->nullable();
            $table->string('payment_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_payments');
    }
};
