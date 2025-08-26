<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cacheKey = 'categories.all';
        $ttl = 60; // in seconds
        $cacheStore = config('cache.default');
        $lockKey = "lock:{$cacheKey}";
        $lockSeconds = 5;

        $fromCacheInitially = Cache::has($cacheKey);

        // Distributed lock to prevent cache stampede
        $lock = Cache::lock($lockKey, $lockSeconds);

        if ($lock->get()) {
            try {
                // Check again inside lock
                $categories = Cache::get($cacheKey);

                if (! $categories) {
                    Log::info(__('messages.cache_miss_fetching_db'));
                    sleep(2); // simulate heavy DB operation

                    // Fetch fresh data
                    $categories = Category::all();

                    // Store in cache
                    Cache::put($cacheKey, $categories, $ttl);
                    Log::info("Categories stored in cache for {$ttl} seconds.");
                } else {
                    Log::info(__('messages.cache_hit_inside_lock'));
                }
            } finally {
                $lock->release();
                Log::info(__('messages.lock_released'));
            }
        } else {
            // If another process has the lock, wait for it
            Log::info(__('messages.another_process_waiting'));
            $lock->block($lockSeconds);
            $categories = Cache::get($cacheKey);
        }

        // Get TTL remaining (works for Redis, not database driver)
        $ttlRemaining = null;
        if ($cacheStore === 'redis') {
            $redis = app('redis')->connection();
            $ttlRemaining = $redis->ttl($cacheKey);
        }

        return response()->json([
            'source' => $fromCacheInitially ? 'cache' : 'database',
            'from_cache' => Cache::has($cacheKey),
            'ttl_remaining_seconds' => $ttlRemaining,
            'cache_store' => $cacheStore,
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not needed for API, but could return a form or structure if required
        return response()->json([
            'message' => 'Display form for creating a new category (not implemented for API).',
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        try {
            // Create the category
            $category = Category::create($request->all());

            // Optionally clear the cache so the new category appears in the list
            Cache::forget('categories.all');

            return response()->json([
                'success' => true,
                'message' => __('messages.category_created'),
                'category' => $category,
            ], 201);
        } catch (Exception $e) {
            Log::error(__('messages.category_create_failed'), ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => __('messages.category_create_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $categories = Cache::get('categories.all');
            $cacheKey = "categories.{$id}";

            if (! $categories) {
                $categories = Category::all();
                Cache::put('categories.all', $categories, 60);
                $source = 'database';
            } else {
                $source = 'cache';
            }

            $category = $categories->where('id', $id)->first();

            if (! $category) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.category_not_found'),
                ], 404);
            }

            return response()->json([
                'source' => $source,
                'success' => true,
                'message' => __('messages.category_list_retrieved'),
                'category' => $category,
            ], 200);
        } catch (Exception $e) {
            Log::error(__('messages.category_not_found'), ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => __('messages.category_not_found'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // Not needed for API, but could return a form or structure if required
        return response()->json([
            'message' => 'Display form for editing the category (not implemented for API).',
            'category' => $category,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        try {
            $validatedData = $request->validated();

            $category->update($validatedData);

            // Optionally clear the cache so the updated category appears in the list
            Cache::forget('categories.all');

            return response()->json([
                'success' => true,
                'message' => __('messages.category_updated'),
                'category' => $category,
            ], 200);
        } catch (Exception $e) {
            Log::error(__('messages.category_update_failed'), ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => __('messages.category_update_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();

            // Optionally clear the cache so the deleted category is removed from the list
            Cache::forget('categories.all');

            return response()->json([
                'success' => true,
                'message' => __('messages.category_deleted'),
            ], 200);
        } catch (Exception $e) {
            Log::error(__('messages.category_delete_failed'), ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => __('messages.category_delete_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
