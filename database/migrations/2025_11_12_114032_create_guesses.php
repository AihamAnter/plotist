<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('rounds')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->string('text', 200);
            $table->boolean('is_correct')->nullable();
            $table->decimal('avg_rating', 4, 2)->nullable(); // cached, recomputed on rating changes
            $table->timestamps();

            $table->index(['round_id', 'player_id']); //add
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guesses');
    }
};
