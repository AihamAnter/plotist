<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Player;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GamesController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'host_name' => ['required','string','min:2','max:30'],
            'host_password' => ['required','string','min:4','max:100'],
        ]);

        do {
            $code = strtoupper(Str::random(6));
        } while (Game::where('code', $code)->exists());

        $game = Game::create([
            'code' => $code,
            'status' => GameStatus::DRAFT,
            'host_name' => $data['host_name'],
            'host_password' => $data['host_password'], // hashed by mutator
        ]);

        $hostPlayer = Player::create([
            'game_id' => $game->id,
            'name' => $data['host_name'],
            'is_host' => true,
            'score' => 0,
        ]);

        $token = (string) Str::uuid();

        Session::create([
            'player_id' => $hostPlayer->id,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        return response()
            ->json([
                'code' => $game->code,
                'status' => $game->status->value,
            ], 201)
            ->cookie(
                'mgg_session',
                $token,
                60 * 24 * 7,
                '/',
                null,
                false,
                true,
                false,
                'Lax'
            );
    }

    public function show(string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();

        $token = request()->cookie('mgg_session');

        $mePlayerId = null;
        $meName = null;
        $isHost = false;

        if ($token) {
            $session = Session::where('token', $token)
                ->where('expires_at', '>', now())
                ->first();

            if ($session) {
                // make sure this session player is in THIS game
                $me = Player::where('id', $session->player_id)
                    ->where('game_id', $game->id)
                    ->first();

                if ($me) {
                    $mePlayerId = $me->id;
                    $meName = $me->name;
                    $isHost = (bool) $me->is_host;
                }
            }
        }

        return response()->json([
            'game' => [
                'code' => $game->code,
                'status' => $game->status->value,
                'movie' => [
                    'tmdb_id' => $game->movie_tmdb_id,
                    'title' => $game->movie_title,
                    'poster_url' => $game->movie_poster_url,
                    'vote_avg' => $game->movie_vote_avg,
                ],
            ],
            'me' => [
                'player_id' => $mePlayerId,
                'name' => $meName,
                'is_host' => $isHost,
            ],
        ]);
    }
}
