<?php

use Illuminate\Support\Facades\Route;


Route::get('/', fn () => view('game.create'));
Route::get('/join', fn () => view('game.join', ['code' => request('code')]));
Route::get('/g/{code}', fn ($code) => view('game.lobby', ['code' => $code]));
Route::get('/g/{code}/rounds/{number}', fn ($code,$number) => view('round.show', ['code'=>$code,'number'=>(int)$number]));
Route::get('/g/{code}/final', fn ($code) => view('final.vote', ['code'=>$code]));
Route::get('/g/{code}/scoreboard', fn ($code) => view('scoreboard', ['code'=>$code]));

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/





