<?php

declare(strict_types=1);


namespace App\Exceptions;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApiErrorHandler
{
    private array $titles = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        422 => 'Validation Error',
        500 => 'Internal Server Error',
    ];

    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (!$request->is('api/*')) {
            return null;
        }

        if ($e instanceof ValidationException) {
            return $this->responseForValidation($e);
        }

        return $this->responseForCommon($e);
    }

    protected function responseForValidation(ValidationException $e): JsonResponse
    {
        return response()->json([
            'title' => 'Validation failed',
            'status' => 422,
            'errors' => $e->errors(),
        ], 422);
    }

    protected function responseForCommon(Throwable $e): JsonResponse
    {
        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        return response()->json([
            'title' => $this->titles[$status],
            'status' => $status,
        ], $status);
    }
}
