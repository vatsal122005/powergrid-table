<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $key = 'Login: ' . ($request->user()?->id ?? $request->ip());

            return [
                Limit::perMinute(5)->by($key)->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many login attempts.',
                    ], 429);
                }),
            ];
        });

        RateLimiter::for('api', function (Request $request) {
            if ($user = $request->user()) {
                if ($user->role === 'super_admin') {
                    return Limit::perMinute(15)
                        ->by($user->id)
                        ->response(function () {
                            return response()->json([
                                'success' => false,
                                'message' => 'Too many requests for super admin.',
                            ], 429);
                        });
                } elseif ($user->role === 'admin') {
                    return Limit::perMinute(4)
                        ->by($user->id)
                        ->response(function () {
                            return response()->json([
                                'success' => false,
                                'message' => 'Too many requests for admin.',
                            ], 429);
                        });
                } elseif ($user->role === 'user') {
                    return Limit::perMinute(3)
                        ->by($user->id)
                        ->response(function () {
                            return response()->json([
                                'success' => false,
                                'message' => 'Too many requests for user.',
                            ], 429);
                        });
                } else {
                    return Limit::perMinute(2)
                        ->by($user->id)
                        ->response(function () {
                            return response()->json([
                                'success' => false,
                                'message' => 'Too many requests.',
                            ], 429);
                        });
                }
            }

            return Limit::perMinute(1)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many requests from this IP.',
                    ], 429);
                });
        });

        // Gate::define('edit-product', function ($user, $product) {
        //     return $user->id === $product->user_id;
        // });
    }
}
