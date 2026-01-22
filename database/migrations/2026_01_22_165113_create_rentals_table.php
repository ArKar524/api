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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_id')->constrained()->restrictOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'dispute', 'cancelled'])->default('active');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('MMK');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('dropoff_location')->nullable();
            $table->text('contract_terms')->nullable();
            $table->timestamps();

            $table->unique('rental_request_id');
            $table->index(['car_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
