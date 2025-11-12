<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->enum('status', ['open','locked'])->default('open');
            $table->foreignId('created_by_player_id')
                  ->nullable()
                  ->constrained('players')
                  ->nullOnDelete(); // keep round if creator leaves
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
