<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'status',
        'host_name',
        'host_password',
        'movie_tmdb_id',
        'movie_title',
        'movie_poster_url',
        'movie_vote_avg',
    ];

    protected $casts = [
        'status' => GameStatus::class,
        'movie_vote_avg' => 'decimal:1',
    ];

    public function setHostPasswordAttribute($value): void
    {
        $this->attributes['host_password'] = bcrypt($value);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }

    public function scopeByCode($q, string $code)
    {
        return $q->where('code', $code);
    }

    public function scopeStatus($q, GameStatus $status)
    {
        return $q->where('status', $status->value);
    }
}
