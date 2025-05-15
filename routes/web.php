<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\RespondsController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\SongController;

Route::get('/', function () {
    return Inertia::render('Slides/Slide1', []);
});

Route::get('/slides/{slide}', function (string $slide) {
    return Inertia::render('Slides/'.$slide);
})->whereAlphaNumeric('slide');


Route::get('/stream', StreamController::class);
Route::post('/responds', RespondsController::class);
Route::post('/tool', ToolController::class);
Route::post('/song', SongController::class);
