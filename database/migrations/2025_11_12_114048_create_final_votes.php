<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('final_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guess_id')->constrained('guesses')->cascadeOnDelete();
            $table->foreignId('voter_player_id')->constrained('players')->cascadeOnDelete();
            $table->enum('decision', ['correct','incorrect']);
            $table->timestamps();

            $table->unique(['guess_id', 'voter_player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_votes');
    }
};
