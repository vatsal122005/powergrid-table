<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        Log::info('Unauthenticated user is trying to access the system');

        if ($request->expectsJson() || $request->is('api/*')) {
            Log::info('Unauthenticated user is trying to access the API');

            return response()->json([
                'success' => false,
                'message' => 'Please Login For Access',
            ], 401);
        }
    }

    public function register() {}
}
