<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guess extends Model
{
    protected $fillable = [
        'round_id',
        'player_id',
        'text',
        'is_correct',
        'avg_rating',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'avg_rating' => 'decimal:2',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function finalVotes(): HasMany
    {
        return $this->hasMany(FinalVote::class);
    }

    public function scopeForRound($q, int $roundId)
    {
        return $q->where('round_id', $roundId);
    }

    public function scopeForGame($q, int $gameId)
    {
        return $q->whereHas('round', fn($r) => $r->where('game_id', $gameId));
    }
}
