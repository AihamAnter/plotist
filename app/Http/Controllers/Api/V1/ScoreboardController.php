<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\FinalDecision;
use App\Enums\GameStatus;
use App\Enums\RoundStatus;
use App\Http\Controllers\Api\V1\Concerns\HasMe;
use App\Http\Controllers\Controller;
use App\Models\FinalVote;
use App\Models\Game;
use App\Models\Guess;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Support\Facades\DB;

class ScoreboardController extends Controller
{
    use HasMe;

    public function show(string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();

        $players = Player::where('game_id', $game->id)
            ->orderByDesc('score')
            ->get(['id', 'name', 'score', 'is_host']);

        return response()->json(['players' => $players]);
    }

    public function compute(string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();
        $me = $this->me();

        $host = Player::where('id', $me->id)->where('game_id', $game->id)->firstOrFail();
        if (!$host->is_host) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $hasOpenRounds = Round::where('game_id', $game->id)
            ->where('status', RoundStatus::OPEN)
            ->exists();

        if ($hasOpenRounds) {
            return response()->json(['message' => 'All rounds must be locked before computing scores'], 409);
        }

        $guesses = Guess::whereHas('round', fn ($q) => $q->where('game_id', $game->id))->get();

        DB::transaction(function () use ($game, $guesses) {
            Player::where('game_id', $game->id)->update(['score' => 0]);

            foreach ($guesses as $guess) {
                $correct = FinalVote::where('guess_id', $guess->id)
                    ->where('decision', FinalDecision::CORRECT->value)
                    ->count();

                $incorrect = FinalVote::where('guess_id', $guess->id)
                    ->where('decision', FinalDecision::INCORRECT->value)
                    ->count();

                if ($correct === $incorrect) {
                    $guess->is_correct = null;
                } else {
                    $guess->is_correct = $correct > $incorrect;
                }
                $guess->save();

                $avg = (float) ($guess->avg_rating ?? 0);
                if ($avg == 0 || $guess->is_correct === null) {
                    continue;
                }

                $delta = $guess->is_correct ? $avg : -$avg;
                Player::where('id', $guess->player_id)->increment('score', $delta);
            }

            $game->status = GameStatus::FINISHED;
            $game->save();
        });

        return response()->json(['ok' => true]);
    }
}
