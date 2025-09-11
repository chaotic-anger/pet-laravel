<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Middleware\EnsureJson;

Route::prefix('api')->middleware(EnsureJson::class)->group(function () {
    Route::apiResource('posts', PostController::class);
});
