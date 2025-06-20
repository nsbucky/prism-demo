<?php

use App\Http\Controllers\ChatHistoryController;
use App\Http\Controllers\ChatWithToolsController;
use App\Http\Controllers\EmbeddingController;
use App\Http\Controllers\ParodySongController;
use App\Http\Controllers\RespondsController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\ToolController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Slides/Slide1', []);
});

Route::get('/slides/{slide}', function (string $slide) {
    return Inertia::render('Slides/'.$slide);
})->whereAlphaNumeric('slide');

Route::get('/stream', StreamController::class);
Route::post('/chat', ChatWithToolsController::class);
Route::post('/responds', RespondsController::class);
Route::post('/tool', ToolController::class);
Route::post('/song', SongController::class);
Route::post('/embedding', EmbeddingController::class);
Route::post('/parody-song', ParodySongController::class);

// Chat history routes
Route::get('/chat-history', [ChatHistoryController::class, 'index']);
Route::get('/chat-history/{sessionId?}', [ChatHistoryController::class, 'show']);
Route::post('/chat-history', [ChatHistoryController::class, 'store']);

// Include conversation flow routes
require __DIR__.'/conversation-flow.php';
