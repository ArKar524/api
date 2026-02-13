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
        if (!Schema::hasTable('trip_applications')) {
            return;
        }

        Schema::table('trip_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('trip_applications', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('trip_applications', 'currency')) {
                $table->string('currency', 3)->default('USD');
            }
            if (!Schema::hasColumn('trip_applications', 'start_at')) {
                $table->timestamp('start_at')->nullable();
            }
            if (!Schema::hasColumn('trip_applications', 'end_at')) {
                $table->timestamp('end_at')->nullable();
            }
            if (!Schema::hasColumn('trip_applications', 'pickup_location')) {
                $table->string('pickup_location')->nullable();
            }
            if (!Schema::hasColumn('trip_applications', 'dropoff_location')) {
                $table->string('dropoff_location')->nullable();
            }
            if (!Schema::hasColumn('trip_applications', 'contract_terms')) {
                $table->text('contract_terms')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('trip_applications')) {
            return;
        }

        $columns = array_values(array_filter([
            'total_amount',
            'currency',
            'start_at',
            'end_at',
            'pickup_location',
            'dropoff_location',
            'contract_terms',
        ], fn (string $column) => Schema::hasColumn('trip_applications', $column)));

        if ($columns === []) {
            return;
        }

        Schema::table('trip_applications', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
