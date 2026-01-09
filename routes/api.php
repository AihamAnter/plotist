<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\GamesController;
use App\Http\Controllers\Api\V1\PlayersController;
use App\Http\Controllers\Api\V1\RoundsController;
use App\Http\Controllers\Api\V1\GuessesController;
use App\Http\Controllers\Api\V1\RatingsController;
use App\Http\Controllers\Api\V1\FinalVotesController;
use App\Http\Controllers\Api\V1\ScoreboardController;
use App\Http\Controllers\Api\V1\MoviesController;

Route::prefix('v1')->group(function () {

    // Games
    Route::post('/games', [GamesController::class, 'store']);              // create game
    Route::get('/games/{code}', [GamesController::class, 'show']);         // game info (optional for later)

    // Players
    Route::post('/games/{code}/players', [PlayersController::class, 'store']); // join
    Route::get('/games/{code}/players', [PlayersController::class, 'index']);  // list

    // Rounds
    Route::post('/games/{code}/rounds', [RoundsController::class, 'store']);   // start new round (host)
    Route::get('/games/{code}/rounds', [RoundsController::class, 'index']);    // list rounds
    Route::put('/rounds/{id}/lock', [RoundsController::class, 'lock']);        // lock round (host)

    // Guesses
    Route::post('/rounds/{id}/guesses', [GuessesController::class, 'store']);  // create guess
    Route::get('/rounds/{id}/guesses', [GuessesController::class, 'index']);   // list guesses (with author rules)

    // Ratings
    Route::post('/guesses/{id}/ratings', [RatingsController::class, 'store']); // create rating (history)
    // (optional later) Route::delete('/ratings/{id}', ...)

    // Final votes
    Route::post('/guesses/{id}/final-votes', [FinalVotesController::class, 'store']); // vote correct/incorrect

    // Scoring + scoreboard
    Route::post('/games/{code}/compute-scores', [ScoreboardController::class, 'compute']);
    Route::get('/games/{code}/scoreboard', [ScoreboardController::class, 'show']);

    // TMDb
    Route::get('/movies/search', [MoviesController::class, 'search']);
    Route::post('/movies/pick', [MoviesController::class, 'pick']);
});
