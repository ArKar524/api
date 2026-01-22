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
        Schema::create('verification_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['identity', 'residence', 'vehicle', 'other'])->default('identity');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->index(['verification_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_files');
    }
};
