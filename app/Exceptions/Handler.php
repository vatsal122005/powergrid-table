<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        Log::info('Unauthenticated user is trying to access the system');

        try {
            if ($request->expectsJson() || $request->is('api/*')) {
                Log::info('Unauthenticated user is trying to access the API');

                return response()->json([
                    'success' => false,
                    'message' => 'Please Login For Access',
                ], 401);
            }

            return redirect()->guest(route('login'));
        } catch (Exception $e) {
            Log::error('Failed to handle unauthenticated user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Authentication Failed',
            ], 401);
        }
    }

    public function register()
    {
        //
    }
}
