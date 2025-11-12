<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guess_id')->constrained('guesses')->cascadeOnDelete();
            $table->foreignId('rater_player_id')->constrained('players')->cascadeOnDelete();
            $table->unsignedTinyInteger('value'); // 1–10 (validate in requests)
            $table->timestamps();

            // Keep history; newest (by created_at/id) is the active one.
            $table->index(['guess_id', 'rater_player_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
