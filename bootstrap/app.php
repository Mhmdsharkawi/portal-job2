<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Console\Commands\ServeCommand;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        ServeCommand::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'verified.api' => \App\Http\Middleware\EnsureEmailIsVerifiedApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            if (! $request->wantsJson() && ! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json(['message' => 'Resource not found'], 404);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            if ($e instanceof HttpExceptionInterface) {
                return response()->json(['message' => $e->getMessage() ?: 'Server error'], $e->getStatusCode());
            }

            return response()->json(['message' => 'Server error'], 500);
        });
    })->create();
