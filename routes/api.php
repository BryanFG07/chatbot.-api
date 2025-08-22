<?php
use App\Http\Controllers\ChatController;

Route::post('/ask', [ChatController::class, 'ask']);
Route::get('/history', [ChatController::class, 'history']);
