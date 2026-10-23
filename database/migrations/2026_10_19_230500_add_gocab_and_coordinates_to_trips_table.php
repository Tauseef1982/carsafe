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
            // Geolocation coordinates required by GoCab SCN for approval checking
            if (!Schema::hasColumn('trips', 'pickup_lat')) {
                $table->decimal('pickup_lat', 10, 7)->nullable()->after('location_from');
            }
            if (!Schema::hasColumn('trips', 'pickup_lng')) {
                $table->decimal('pickup_lng', 10, 7)->nullable()->after('pickup_lat');
            }
            if (!Schema::hasColumn('trips', 'dropoff_lat')) {
                $table->decimal('dropoff_lat', 10, 7)->nullable()->after('location_to');
            }
            if (!Schema::hasColumn('trips', 'dropoff_lng')) {
                $table->decimal('dropoff_lng', 10, 7)->nullable()->after('dropoff_lat');
            }

            // GoCab SCN transaction & auditing fields
            if (!Schema::hasColumn('trips', 'gocab_card_number')) {
                $table->string('gocab_card_number', 50)->nullable()->after('gocab_paid');
            }
            if (!Schema::hasColumn('trips', 'gocab_status')) {
                $table->string('gocab_status', 30)->nullable()->after('gocab_card_number'); // COVERED, UNCOVERED, VOID
            }
            if (!Schema::hasColumn('trips', 'gocab_reason')) {
                $table->string('gocab_reason', 255)->nullable()->after('gocab_status');
            }
            if (!Schema::hasColumn('trips', 'gocab_response')) {
                $table->json('gocab_response')->nullable()->after('gocab_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('trips', 'pickup_lat')) {
                $columnsToDrop[] = 'pickup_lat';
            }
            if (Schema::hasColumn('trips', 'pickup_lng')) {
                $columnsToDrop[] = 'pickup_lng';
            }
            if (Schema::hasColumn('trips', 'dropoff_lat')) {
                $columnsToDrop[] = 'dropoff_lat';
            }
            if (Schema::hasColumn('trips', 'dropoff_lng')) {
                $columnsToDrop[] = 'dropoff_lng';
            }
            if (Schema::hasColumn('trips', 'gocab_card_number')) {
                $columnsToDrop[] = 'gocab_card_number';
            }
            if (Schema::hasColumn('trips', 'gocab_status')) {
                $columnsToDrop[] = 'gocab_status';
            }
            if (Schema::hasColumn('trips', 'gocab_reason')) {
                $columnsToDrop[] = 'gocab_reason';
            }
            if (Schema::hasColumn('trips', 'gocab_response')) {
                $columnsToDrop[] = 'gocab_response';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
