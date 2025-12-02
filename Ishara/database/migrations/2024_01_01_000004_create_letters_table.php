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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade');
            $table->string('letter', 1);
            $table->string('name');
            $table->json('key_steps')->nullable();
            $table->json('common_mistakes')->nullable();
            $table->integer('order_in_level');
            $table->timestamps();

            // Indexes
            $table->unique(['level_id', 'letter']);
            $table->index(['level_id', 'order_in_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};

