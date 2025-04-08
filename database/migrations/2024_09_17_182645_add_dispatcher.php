<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin');
        });

        \App\Models\User::create([
            'username' => 'dispatcher',
            'name' => 'Dispatcher',
            'email' => 'dispatcher@gmail.com',
            'password' => Hash::make(12345678),
            'role' => 'dispatcher',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
