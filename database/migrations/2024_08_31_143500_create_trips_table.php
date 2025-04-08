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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('location_from')->nullable();
            $table->string('location_to')->nullable();
            $table->string('duration')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->decimal('trip_cost', 10, 2)->nullable();
            $table->bigInteger('gocab_payment_id')->nullable();
            $table->decimal('driver_paid', 10, 2)->nullable();
            $table->decimal('gocab_paid', 10, 2)->default(0);
            $table->string('payment_method')->default('cash');
            $table->bigInteger('driver_id')->nullable();
            $table->bigInteger('account_number')->nullable();
            $table->string('strip_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
