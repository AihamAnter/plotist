<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RoundStatus;
use App\Http\Controllers\Api\V1\Concerns\HasMe;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Http\Request;

class RoundsController extends Controller
{
    use HasMe;

    public function index(string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();

        if ($game->status === \App\Enums\GameStatus::FINISHED) {
            return response()->json(['message' => 'Game finished'], 409);
        }

        $rounds = Round::where('game_id', $game->id)
            ->orderBy('number')
            ->get(['id', 'number', 'status', 'locked_at']);

        return response()->json(['rounds' => $rounds]);
    }

    public function store(Request $request, string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();
        $me = $this->me();

        $player = Player::where('id', $me->id)
            ->where('game_id', $game->id)
            ->firstOrFail();

        if (!$player->is_host) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $hasOpenRound = Round::where('game_id', $game->id)
            ->where('status', RoundStatus::OPEN)
            ->exists();

        if ($hasOpenRound) {
            return response()->json(['message' => 'A round is already open'], 409);
        }

        $nextNumber = (int) (Round::where('game_id', $game->id)->max('number') ?? 0) + 1;

        $round = Round::create([
            'game_id' => $game->id,
            'number' => $nextNumber,
            'status' => RoundStatus::OPEN,
            'created_by_player_id' => $player->id,
        ]);

        return response()->json(['round' => $round], 201);
    }

    public function lock(int $id)
    {
        $round = Round::findOrFail($id);
        $me = $this->me();

        $player = Player::where('id', $me->id)
            ->where('game_id', $round->game_id)
            ->firstOrFail();

        if (!$player->is_host) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $round->status = RoundStatus::LOCKED;
        $round->locked_at = now();
        $round->save();

        return response()->json(['ok' => true]);
    }
}
