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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('make');
            $table->string('model');
            $table->year('year');
            $table->string('license_plate')->unique();
            $table->enum('status', ['pending', 'active', 'inactive', 'suspended'])->default('pending');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->text('description')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
