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
        Schema::table('account_payments', function (Blueprint $table) {

            $table->string('ref_no')->comment('invoice/ref number')->nullable();
            $table->longText('hash_id')->nullable();
            $table->date('invoice_from_date')->nullable();
            $table->date('invoice_to_date')->nullable();
            $table->enum('status',['paid','unpaid','partial'])->nullable();
            $table->integer('try')->nullable();
            $table->integer('email_sends')->default(0);
            $table->date('due_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
