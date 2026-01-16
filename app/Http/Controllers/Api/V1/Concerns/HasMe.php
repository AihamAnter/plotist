<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Models\Player;
use App\Models\Session;

trait HasMe
{
    protected function me(): Player
    {
        $token = request()->cookie('mgg_session');
        if (!$token) abort(401, 'Missing session');

        $session = Session::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$session) abort(401, 'Invalid session');

        return $session->player;
    }
}
