<?php

use App\Api\Presentation\Http\Controllers\CommentController;
use App\Api\Presentation\Http\Controllers\PostController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::prefix('posts/{post}')->group(function () {
        Route::apiResource('comments', CommentController::class)->shallow();
        Route::get('comments/{comment}/vote-status', [CommentController::class, 'voteStatus']);
        Route::post('comments/{comment}/vote/{direction}', [CommentController::class, 'vote'])
            ->where('direction', 'up|down');
    });
});

