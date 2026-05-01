<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('auth', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Too many requests. Please wait before retrying.',
                    'retry_after' => (int) ($headers['Retry-After'] ?? 60),
                ], 429, $headers);
            }),
        ]);

        RateLimiter::for('apply', fn (Request $request) => [
            Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Too many requests. Please wait before retrying.',
                    'retry_after' => (int) ($headers['Retry-After'] ?? 60),
                ], 429, $headers);
            }),
        ]);
    }
}
