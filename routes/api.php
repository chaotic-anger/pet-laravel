<?php

use App\Http\Controllers\Api\Api\CommentController;
use App\Http\Controllers\Api\Api\PostController;

Route::apiResource('posts', PostController::class);
Route::prefix('posts/{post}')->group(function () {
    Route::apiResource('comments', CommentController::class)->shallow();
    Route::post('comments/{comment}/vote/{direction}', [CommentController::class, 'vote'])
        ->where('direction', 'up|down');
});
