<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MoviesController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->query('q');
        if (!$q) return response()->json(['results' => []]);

        $key = config('services.tmdb.key');
        if (!$key) abort(500, 'TMDB key missing');

        $res = Http::get('https://api.themoviedb.org/3/search/movie', [
            'api_key' => $key,
            'query' => $q,
        ])->json();

        $results = collect($res['results'] ?? [])->map(function ($m) {
            return [
                'id' => $m['id'],
                'title' => $m['title'] ?? '',
                'release_year' => isset($m['release_date']) ? substr($m['release_date'], 0, 4) : null,
                'vote_average' => $m['vote_average'] ?? null,
            ];
        })->values();

        return response()->json(['results' => $results]);
    }

    public function pick(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string'],
            'tmdb_id' => ['required','integer'],
        ]);

        $game = Game::where('code', $data['code'])->firstOrFail();

        $key = config('services.tmdb.key');
        if (!$key) abort(500, 'TMDB key missing');

        $m = Http::get("https://api.themoviedb.org/3/movie/{$data['tmdb_id']}", [
            'api_key' => $key,
        ])->json();

        $posterPath = $m['poster_path'] ?? null;
        $voteAvg = (float) ($m['vote_average'] ?? 0);

        if (!$posterPath) {
            return response()->json([
                'message' => 'This movie has no poster on TMDb. Pick another one.'
            ], 422);
        }

        if ($voteAvg < 5.0) {
            return response()->json([
                'message' => 'This movie rating is too low (TMDb < 5.0). Pick another one.'
            ], 422);
        }

        $poster = 'https://image.tmdb.org/t/p/w500' . $posterPath;

        $game->update([
            'movie_tmdb_id' => $m['id'] ?? $data['tmdb_id'],
            'movie_title' => $m['title'] ?? null,
            'movie_poster_url' => $poster,
            'movie_vote_avg' => $voteAvg,
        ]);

        return response()->json(['ok' => true]);
    }
}
