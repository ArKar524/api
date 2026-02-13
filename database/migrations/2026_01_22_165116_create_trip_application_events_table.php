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
        Schema::create('trip_application_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_application_id')->constrained('trip_applications')->cascadeOnDelete();
            $table->enum('type', ['created', 'started', 'paused', 'resumed', 'completed', 'dispute'])->default('created');
            $table->text('notes')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['trip_application_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_application_events');
    }
};
