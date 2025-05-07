<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\RespondsController;

Route::get('/', function () {
    return Inertia::render('Slides/Slide1', []);
});

Route::get('/slides/{id}', function (string $id) {

    // make sure the Slide exists
    abort_if(!is_numeric($id) || $id < 1 || $id > 13, 404);

    return Inertia::render('Slides/Slide'.(int) $id);

})->whereNumber('id');

Route::get('/stream', StreamController::class);
Route::post('/responds', RespondsController::class);
