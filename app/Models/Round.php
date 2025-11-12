<?php

namespace App\Models;

use App\Enums\RoundStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    protected $fillable = [
        'game_id',
        'number',
        'status',
        'created_by_player_id',
        'locked_at',
    ];

    protected $casts = [
        'status' => RoundStatus::class,
        'locked_at' => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function guesses(): HasMany
    {
        return $this->hasMany(Guess::class);
    }

    // scopes
    public function scopeForGame($q, int $gameId)
    {
        return $q->where('game_id', $gameId);
    }

    public function scopeNumber($q, int $number)
    {
        return $q->where('number', $number);
    }
}
