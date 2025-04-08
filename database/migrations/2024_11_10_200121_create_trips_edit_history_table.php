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
        Schema::create('trips_edit_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('trip_id');
            $table->date('date');
            $table->time('time');
            $table->enum('type',['debit','credit']);
            $table->float('amount');
            $table->longText('old_data');
            $table->longText('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips_edit_history');
    }
};
