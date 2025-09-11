<?php

declare(strict_types=1);


namespace App\Exceptions;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ApiErrorHandler
{
    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (!$request->is('api/*')) {
            return null;
        }

        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        return response()->json([
            'title' => env('APP_DEBUG') ? $e->getMessage() : 'Server error',
            'status' => $status,
        ], $status);
    }
}
