<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'game_id',
        'name',
        'is_host',
        'score',
    ];

    protected $casts = [
        'is_host' => 'boolean',
        'score' => 'decimal:2',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function guesses(): HasMany
    {
        return $this->hasMany(Guess::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'rater_player_id');
    }

    public function finalVotes(): HasMany
    {
        return $this->hasMany(FinalVote::class, 'voter_player_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    // scopes
    public function scopeForGame($q, int $gameId)
    {
        return $q->where('game_id', $gameId);
    }
}
