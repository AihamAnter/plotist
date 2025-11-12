<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('status', ['draft','active','finalizing','finished'])->default('draft');
            $table->string('host_name')->nullable();
            // store HASHED value here (model mutator will hash on set)
            $table->string('host_password')->nullable();

            $table->unsignedBigInteger('movie_tmdb_id')->nullable();
            $table->string('movie_title')->nullable();
            $table->string('movie_poster_url')->nullable();
            $table->decimal('movie_vote_avg', 3, 1)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
