<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = [
        'guess_id',
        'rater_player_id',
        'value',
    ];

    protected $casts = [
        'value' => 'integer',
    ];

    public function guess(): BelongsTo
    {
        return $this->belongsTo(Guess::class);
    }

    public function rater(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'rater_player_id');
    }

    public function scopeForGuess($q, int $guessId)
    {
        return $q->where('guess_id', $guessId);
    }

    public function scopeForRater($q, int $playerId)
    {
        return $q->where('rater_player_id', $playerId);
    }
}
