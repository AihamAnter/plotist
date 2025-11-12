<?php

namespace App\Observers;

use App\Models\Guess;
use App\Models\Rating;
use Illuminate\Support\Facades\DB;

class RatingObserver
{
    public function created(Rating $rating): void
    {
        $this->recomputeGuessAverage($rating->guess_id);
    }

    public function updated(Rating $rating): void
    {
        $this->recomputeGuessAverage($rating->guess_id);
    }

    public function deleted(Rating $rating): void
    {
        $this->recomputeGuessAverage($rating->guess_id);
    }

    /**
     * Average of the latest rating per rater for this guess.
     * (We keep history; newest per rater is active.)
     */
    protected function recomputeGuessAverage(int $guessId): void
    {
        // subquery: pick latest rating id per (rater, guess)
        $latestIds = Rating::select(DB::raw('MAX(id) as id'))
            ->where('guess_id', $guessId)
            ->groupBy('rater_player_id')
            ->pluck('id');

        if ($latestIds->isEmpty()) {
            Guess::whereKey($guessId)->update(['avg_rating' => null]);
            return;
        }

        $avg = Rating::whereIn('id', $latestIds)->avg('value');

        Guess::whereKey($guessId)->update([
            'avg_rating' => $avg ? round($avg, 2) : null,
        ]);
    }
}
