<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_host')->default(false);
            $table->decimal('score', 6, 2)->default(0);
            $table->timestamps();

            $table->unique(['game_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
