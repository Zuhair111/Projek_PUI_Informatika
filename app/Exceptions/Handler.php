<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        //
    }

    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return ApiResponse::error('Validasi gagal.', $exception->errors(), $exception->status);
    }

    public function render($request, Throwable $e)
    {
        return parent::render($request, $e);
    }
}
