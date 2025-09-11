<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Middleware\ApiEnsureJson;
use App\Http\Middleware\ApiProblem;

Route::middleware(ApiEnsureJson::class)
    ->group(function () {
        Route::apiResource('posts', PostController::class);
    });
