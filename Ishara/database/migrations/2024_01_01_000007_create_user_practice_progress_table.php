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
        Schema::create('user_practice_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('letter_id')->constrained('letters')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->decimal('accuracy', 5, 2)->nullable();
            $table->integer('attempts_count')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_practiced_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'letter_id']);
            $table->index(['user_id', 'is_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_practice_progress');
    }
};

