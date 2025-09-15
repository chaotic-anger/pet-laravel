<?php

declare(strict_types=1);


namespace App\Api\Shared\Exceptions;


use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ApiProblem
{
    private int $code;
    private array $map = [
        Response::HTTP_BAD_REQUEST => 'Bad Request',
        Response::HTTP_UNAUTHORIZED => 'Unauthorized',
        Response::HTTP_FORBIDDEN => 'Forbidden',
        Response::HTTP_NOT_FOUND => 'Not Found',
        Response::HTTP_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        Response::HTTP_UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        Response::HTTP_UNPROCESSABLE_ENTITY => 'Validation Error',
        Response::HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
    ];

    public function __construct(
        private readonly Throwable $exception
    ) {
        $this->code = $this->ensureCode($this->exception);
    }

    private function ensureCode(Throwable $exception): int
    {
        if ($exception instanceof AuthenticationException) {
            return Response::HTTP_UNAUTHORIZED;
        }

        if ($exception instanceof ValidationException) {
            return Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        if ($statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : null) {
            return $statusCode;
        }

        return in_array($exception->getCode(), array_keys($this->map))
            ? $exception->getCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function format(): array
    {
        $formatted = [
            'title' => $this->map[$this->code],
            'status' => $this->code,
        ];

        if (is_a($this->exception, ValidationException::class)) {
            $formatted['errors'] = $this->exception->errors();
        }

        return $formatted;
    }

    public function code(): int
    {
        return $this->code;
    }

    public function toResponse(): JsonResponse
    {
        return response()->json($this->format(), $this->code);
    }
}
