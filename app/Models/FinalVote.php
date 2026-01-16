<?php

namespace App\Models;

use App\Enums\FinalDecision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalVote extends Model
{
    protected $fillable = [
        'guess_id',
        'voter_player_id',
        'decision',
    ];

    protected $casts = [
        'decision' => FinalDecision::class,
    ];

    public function guess(): BelongsTo
    {
        return $this->belongsTo(Guess::class);
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'voter_player_id');
    }

    public function scopeForGuess($q, int $guessId)
    {
        return $q->where('guess_id', $guessId);
    }
}
