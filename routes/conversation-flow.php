<?php

use App\Http\Controllers\ConversationFlowController;
use Illuminate\Support\Facades\Route;

// Example routes for conversation flow
Route::prefix('conversation')->group(function () {
    Route::post('/start', [ConversationFlowController::class, 'startConversation']);
    Route::post('/respond', [ConversationFlowController::class, 'processResponse']);
});