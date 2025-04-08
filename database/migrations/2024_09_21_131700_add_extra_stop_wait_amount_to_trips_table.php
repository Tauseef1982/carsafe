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
        Schema::table('trips', function (Blueprint $table) {
            //
            $table->decimal('extra_stop_amount', 10, 2)->default(0.00)->after('extra_charges');
            $table->decimal('extra_wait_amount', 10, 2)->default(0.00)->after('extra_stop_amount');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            //
            $table->dropColumn(['extra_stop_amount', 'extra_wait_amount']);

        });
    }
};
