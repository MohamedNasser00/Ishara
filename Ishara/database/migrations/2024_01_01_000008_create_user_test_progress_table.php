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
        Schema::create('user_test_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('word_id')->constrained('words')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->decimal('accuracy', 5, 2)->nullable();
            $table->integer('attempts_count')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'word_id']);
            $table->index(['user_id', 'is_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_test_progress');
    }
};

