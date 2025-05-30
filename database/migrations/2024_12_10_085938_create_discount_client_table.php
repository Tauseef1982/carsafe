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
        Schema::create('discount_client', function (Blueprint $table) {
            $table->id();
        $table->unsignedBigInteger('discount_id');
        $table->unsignedBigInteger('account_id');
        $table->timestamps();

        $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('cascade');
        $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_client');
    }
};
