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

    Route::post('/games', [GamesController::class, 'store']);             
    Route::get('/games/{code}', [GamesController::class, 'show']);         

    Route::post('/games/{code}/players', [PlayersController::class, 'store']); 
    Route::get('/games/{code}/players', [PlayersController::class, 'index']);  

    Route::post('/games/{code}/rounds', [RoundsController::class, 'store']);   
    Route::get('/games/{code}/rounds', [RoundsController::class, 'index']);  
    Route::put('/rounds/{id}/lock', [RoundsController::class, 'lock']);        

    Route::post('/rounds/{id}/guesses', [GuessesController::class, 'store']); 
    Route::get('/rounds/{id}/guesses', [GuessesController::class, 'index']);  
    Route::put('/guesses/{id}', [GuessesController::class, 'update']);      
    Route::delete('/guesses/{id}', [GuessesController::class, 'destroy']);     

    Route::post('/guesses/{id}/ratings', [RatingsController::class, 'store']); 

    Route::post('/guesses/{id}/final-votes', [FinalVotesController::class, 'store']); 

    Route::post('/games/{code}/compute-scores', [ScoreboardController::class, 'compute']);
    Route::get('/games/{code}/scoreboard', [ScoreboardController::class, 'show']);

    Route::get('/movies/search', [MoviesController::class, 'search']);
    Route::post('/movies/pick', [MoviesController::class, 'pick']);
});
