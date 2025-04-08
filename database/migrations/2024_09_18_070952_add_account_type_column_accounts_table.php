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
            $table->string('account_type');
            $table->float('recharge', 8, 2)->default(00.00)->nullable();
            $table->enum('autofill', ['off','on'])->default('off')->nullable();
            $table->enum('card_added', ['no','yess'])->default('no')->nullable();
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
