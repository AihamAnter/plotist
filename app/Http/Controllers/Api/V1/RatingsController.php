<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RoundStatus;
use App\Http\Controllers\Api\V1\Concerns\HasMe;
use App\Http\Controllers\Controller;
use App\Models\Guess;
use App\Models\Player;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingsController extends Controller
{
    use HasMe;

    public function store(Request $request, int $id)
    {
        $guess = Guess::with('round')->findOrFail($id);
        $me = $this->me(); 

        $player = Player::where('id', $me->id)
            ->where('game_id', $guess->round->game_id)
            ->firstOrFail();

        if ($guess->round->status === RoundStatus::LOCKED) {
            return response()->json(['message' => 'Round locked'], 409);
        }

        if ($guess->player_id === $player->id) {
            return response()->json(['message' => 'Cannot rate own guess'], 409);
        }

        $hasMyGuess = Guess::where('round_id', $guess->round->id)
            ->where('player_id', $player->id)
            ->exists();

        if (!$hasMyGuess) {
            return response()->json(['message' => 'Submit at least one guess before rating'], 409);
        }

        $playersCount = Player::where('game_id', $guess->round->game_id)->count();

        $playersWithGuessCount = Guess::where('round_id', $guess->round->id)
            ->distinct('player_id')
            ->count('player_id');

        if ($playersWithGuessCount < $playersCount) {
            return response()->json(['message' => 'Wait until all players submit at least one guess before rating'], 409);
        }

        $data = $request->validate([
            'value' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $rating = null;
        $avg = null;

        DB::transaction(function () use ($guess, $player, $data, &$rating, &$avg) {
            $rating = Rating::updateOrCreate(
                [
                    'guess_id' => $guess->id,
                    'rater_player_id' => $player->id,
                ],
                [
                    'value' => $data['value'],
                ]
            );

            $avg = (float) Rating::where('guess_id', $guess->id)->avg('value');
            $guess->avg_rating = $avg;
            $guess->save();
        });

        return response()->json([
            'ok' => true,
            'rating_id' => $rating->id,
            'avg_rating' => $avg,
        ], 201);
    }
}
