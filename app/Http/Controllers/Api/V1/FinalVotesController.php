<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\FinalDecision;
use App\Enums\RoundStatus;
use App\Http\Controllers\Api\V1\Concerns\HasMe;
use App\Http\Controllers\Controller;
use App\Models\FinalVote;
use App\Models\Guess;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Http\Request;

class FinalVotesController extends Controller
{
    use HasMe;

    public function store(Request $request, int $id)
    {
        $guess = Guess::with('round')->findOrFail($id);
        $me = $this->me();

        $player = Player::where('id', $me->id)
            ->where('game_id', $guess->round->game_id)
            ->firstOrFail();

        // block final voting if any round is still open
        $hasOpenRounds = Round::where('game_id', $guess->round->game_id)
            ->where('status', RoundStatus::OPEN)
            ->exists();

        if ($hasOpenRounds) {
            return response()->json(['message' => 'All rounds must be locked before final voting'], 409);
        }

        if ($guess->round->status !== RoundStatus::LOCKED) {
            return response()->json(['message' => 'Round must be locked before final voting'], 409);
        }

        $data = $request->validate([
            'decision' => ['required', 'in:correct,incorrect'],
        ]);

        // unique (guess_id, voter_player_id)
        $vote = FinalVote::updateOrCreate(
            ['guess_id' => $guess->id, 'voter_player_id' => $player->id],
            ['decision' => FinalDecision::from($data['decision'])]
        );

        return response()->json(['ok' => true, 'vote_id' => $vote->id], 201);
    }
}
