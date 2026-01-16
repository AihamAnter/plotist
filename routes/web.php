<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('game.create'));

Route::get('/join', fn () => view('game.join', ['code' => request('code')]));

Route::get('/g/{code}', fn ($code) => view('game.lobby', ['code' => $code]));
Route::get('/g/{code}/rounds/{number}', fn ($code,$number) => view('round.show', ['code'=>$code,'number'=>(int)$number]));
Route::get('/g/{code}/final', fn ($code) => view('final.vote', ['code'=>$code]));
Route::get('/g/{code}/scoreboard', fn ($code) => view('scoreboard', ['code'=>$code]));

Route::get('/g/{code}/explorer', function ($code) {return view('game.explorer', ['code' => $code]);});


Route::get('/plotist', fn () => "<h1>Welcome to PlotistC!</h1>");
Route::get('/plotist/createGame', fn () => "<h1>create new game</h1>");
Route::get('/plotist/{id}', fn ($id) => App\Models\plotist::find($id));
