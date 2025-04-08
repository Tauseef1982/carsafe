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
        Schema::create('driver_complaints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('admin_username')->nullable();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Pending', 'In Progress', 'Resolved'])->default('Pending');
            $table->date('date');
            $table->string('handled_by')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_complaints');
    }
};
