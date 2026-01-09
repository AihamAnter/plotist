<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Player;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PlayersController extends Controller
{
    public function index(string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();

        $players = Player::where('game_id', $game->id)
            ->orderByDesc('is_host')
            ->orderBy('name')
            ->get(['id','name','is_host','score']);

        return response()->json([
            'players' => $players,
        ]);
    }

    public function store(Request $request, string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();

        $data = $request->validate([
            'name' => ['required','string','min:2','max:30'],
            'password' => ['required','string','min:4','max:100'],
        ]);

        // simplest: one shared password = host_password
        if (!$game->host_password || !Hash::check($data['password'], $game->host_password)) {
            return response()->json(['message' => 'Wrong game password'], 401);
        }

        // create player (name unique per game enforced by DB)
        $player = Player::create([
            'game_id' => $game->id,
            'name' => $data['name'],
            'is_host' => false,
            'score' => 0,
        ]);

        // create session token and set cookie
        $token = (string) Str::uuid();
        Session::create([
            'player_id' => $player->id,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        return response()
            ->json(['ok' => true, 'player_id' => $player->id], 201)
            ->cookie(
                'mgg_session',
                $token,
                60 * 24 * 7,
                '/',
                null,
                false,
                true,   // HttpOnly
                false,
                'Lax'
            );
    }
}
