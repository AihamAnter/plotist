<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RoundStatus;
use App\Http\Controllers\Api\V1\Concerns\HasMe;
use App\Http\Controllers\Controller;
use App\Models\FinalVote;
use App\Models\Guess;
use App\Models\Player;
use App\Models\Rating;
use App\Models\Round;
use Illuminate\Http\Request;

class GuessesController extends Controller
{
    use HasMe;

    public function index(Request $request, int $id)
    {
        $round = Round::findOrFail($id);
        $me = $this->me();

        $player = Player::where('id', $me->id)
            ->where('game_id', $round->game_id)
            ->firstOrFail();

    
        $mine = $request->boolean('mine');
        $sort = $request->query('sort', 'created_at');
        $dir  = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowedSort = ['created_at', 'avg_rating'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'created_at';
        }

        $q = Guess::with(['player:id,name'])
            ->where('round_id', $round->id);

        if ($mine) {
            $q->where('player_id', $player->id);
        }

        $q->orderBy($sort, $dir);

        $guesses = $q->get();

        $guessIds = $guesses->pluck('id')->all();

        $myRatings = Rating::whereIn('guess_id', $guessIds)
            ->where('rater_player_id', $player->id)
            ->pluck('value', 'guess_id');

        $out = $guesses->map(function ($g) use ($player, $round, $myRatings) {
            $isMine = $g->player_id === $player->id;
            $authorVisible = $isMine || ($round->status === RoundStatus::LOCKED);
            $myRatingValue = $myRatings->get($g->id);

            return [
                'id' => $g->id,
                'text' => $g->text,
                'player_id' => $g->player_id,
                'is_mine' => $isMine,
                'author_visible' => $authorVisible,
                'author' => $authorVisible ? $g->player->name : null,
                'avg_rating' => $g->avg_rating,
                'my_rating' => $myRatingValue,
                'can_rate' => !$isMine && ($round->status === RoundStatus::OPEN) && ($myRatingValue === null),
                'created_at' => $g->created_at?->toISOString(),
            ];
        });

        return response()->json(['guesses' => $out]);
    }

    public function store(Request $request, int $id)
    {
        $round = Round::findOrFail($id);
        $me = $this->me();

        if ($round->status === RoundStatus::LOCKED) {
            return response()->json(['message' => 'Round locked'], 409);
        }

        $player = Player::where('id', $me->id)
            ->where('game_id', $round->game_id)
            ->firstOrFail();

        $data = $request->validate([
            'text' => ['required', 'string', 'min:1', 'max:200'],
        ]);

        $guess = Guess::create([
            'round_id' => $round->id,
            'player_id' => $player->id,
            'text' => $data['text'],
        ]);

        return response()->json(['guess' => $guess], 201);
    }

    public function update(Request $request, int $id)
    {
        $guess = Guess::with('round')->findOrFail($id);
        $me = $this->me();

        if ($guess->round->status === RoundStatus::LOCKED) {
            return response()->json(['message' => 'Round locked'], 409);
        }

        $player = Player::where('id', $me->id)
            ->where('game_id', $guess->round->game_id)
            ->firstOrFail();

        if ($guess->player_id !== $player->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $hasRatings = Rating::where('guess_id', $guess->id)->exists();
        if ($hasRatings) {
            return response()->json(['message' => 'Cannot edit a guess that was already rated'], 409);
        }

        $hasFinalVotes = FinalVote::where('guess_id', $guess->id)->exists();
        if ($hasFinalVotes) {
            return response()->json(['message' => 'Cannot edit a guess that was already final-voted'], 409);
        }

        $data = $request->validate([
            'text' => ['required', 'string', 'min:1', 'max:200'],
        ]);

        $guess->text = $data['text'];
        $guess->save();

        return response()->json(['ok' => true, 'guess' => $guess]);
    }

    public function destroy(int $id)
    {
        $guess = Guess::with('round')->findOrFail($id);
        $me = $this->me();

        if ($guess->round->status === RoundStatus::LOCKED) {
            return response()->json(['message' => 'Round locked'], 409);
        }

        $player = Player::where('id', $me->id)
            ->where('game_id', $guess->round->game_id)
            ->firstOrFail();

        if ($guess->player_id !== $player->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $hasRatings = Rating::where('guess_id', $guess->id)->exists();
        if ($hasRatings) {
            return response()->json(['message' => 'Cannot delete a guess that was already rated'], 409);
        }

        $hasFinalVotes = FinalVote::where('guess_id', $guess->id)->exists();
        if ($hasFinalVotes) {
            return response()->json(['message' => 'Cannot delete a guess that was already final-voted'], 409);
        }

        $guess->delete();

        return response()->json(['ok' => true]);
    }
}
