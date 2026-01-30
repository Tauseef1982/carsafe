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
         Schema::create('qrcodeapis', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('account_id');
             $table->string('api_key', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qrcodeapis');
    }
};
