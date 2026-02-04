<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rental_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            // $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'declined', 'cancelled'])->default('pending');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('pickup_location')->nullable();
            $table->string('dropoff_location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_requests');
    }
};
