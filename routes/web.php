<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
    //test comment
});

Route::get('/plotist', function () {
    return "<h1>Welcome to PlotistC!</h1>";
});

Route::get('/plotist/createGame', function () {
    return "<h1>create new game</h1>";
});

Route::get('/plotist/{id}', function ($id) {
    return App\Models\plotist::find($id);
    
});