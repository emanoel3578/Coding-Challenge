<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Throwable;

abstract class Controller
{
    protected function renderInertiaError(Throwable $exception): Response
    {
        return Inertia::render(
            'Error',
            [
                'message' => !empty($exception->getMessage()) ? $exception->getMessage() : 'An error occurred. Please try again later.',
                'status' => !empty($exception->getCode()) ? $exception->getCode() : HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR,
                'stack' => $exception->getTraceAsString() ?? ''
            ]
        );
    }
}
