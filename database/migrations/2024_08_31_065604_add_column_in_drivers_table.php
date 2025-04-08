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
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->mediumText('role')->nullable();
            $table->mediumText('username')->nullable();
            $table->mediumText('password')->nullable();
            $table->mediumText('avatar')->nullable();
            $table->bigInteger('cid')->nullable();
            // $table->boolean('status');
            $table->decimal('weekly_fee', 10, 2)->default('80.00');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            //
        });
    }
};
