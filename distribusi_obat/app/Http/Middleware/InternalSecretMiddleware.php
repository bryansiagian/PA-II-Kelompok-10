<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InternalSecretMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $secret = $request->header('X-Internal-Secret')
            ?? $request->header('HTTP_X_INTERNAL_SECRET')
            ?? $request->query('internal_secret'); // ← pastikan baris ini ada

        $expected = config('app.internal_secret');

        if (!$secret || $secret !== $expected) {
            return response()->json([
                'message'  => 'Unauthorized',
                'received' => $secret,   // tambahkan sementara untuk debug
                'expected' => $expected,
            ], 401);
        }

        return $next($request);
    }
}
