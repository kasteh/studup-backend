<?php

use App\Enums\HttpStatus;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (Throwable $e, Request $request) {
            if ($request->is(patterns: 'api/*')) {
                $status = match (true) {
                    $e instanceof NotFoundHttpException => HttpStatus::NotFound,
                    $e instanceof AuthenticationException => HttpStatus::Unauthorized,
                    default => HttpStatus::InternalServerError,
                };

                $isProduction = app()->isProduction();

                $message = ($isProduction && $status === HttpStatus::InternalServerError)
                    ? 'Internal server error, cannot processed the request.'
                    : $e->getMessage();

                $errors = $isProduction
                    ? ($e instanceof ValidationException ? $e->errors() : [])
                    : ($e instanceof ValidationException ? $e->errors() : $e->getTrace());

                return response()->json(
                    data: [
                        'status' => 'error',
                        'message' => $message,
                        'errors' => $errors,
                    ],
                    status: $status->value,
                );
            }

            return null;
        });
    })->create();
