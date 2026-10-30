<?php

use App\Http\Controllers\EmbedChatController;
use Illuminate\Support\Facades\Route;

/*
 * Cross-origin embed API — external websites embed a tenant's AI chatbot
 * via <script src=".../embed/{key}/chat.js">. Stateless + CORS-enabled;
 * the tenant is resolved by ResolveEmbedTenant from the embed key.
 */
Route::middleware([\App\Http\Middleware\ResolveEmbedTenant::class])
    ->prefix('embed/{key}')
    ->group(function () {
        Route::options('/{path?}', fn () => response('', 204))->where('path', '.*');
        Route::get('/chat.js', [EmbedChatController::class, 'script']);
        Route::post('/chat', [EmbedChatController::class, 'send'])->middleware('throttle:20,1');
        Route::post('/chat/stream', [EmbedChatController::class, 'stream'])->middleware('throttle:20,1');
        Route::get('/chat/feed', [EmbedChatController::class, 'feed'])->middleware('throttle:60,1');
        Route::post('/chat/handoff', [EmbedChatController::class, 'handoff'])->middleware('throttle:10,1');
    });
