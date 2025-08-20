<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\Finally_;

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

                if (!$categories) {
                    Log::info('Cache miss - fetching from database...');
                    sleep(2); // simulate heavy DB operation

                    // Fetch fresh data
                    $categories = Category::all();

                    // Store in cache
                    Cache::put($cacheKey, $categories, $ttl);
                    Log::info("Categories stored in cache for {$ttl} seconds.");
                } else {
                    Log::info('Cache hit (inside lock).');
                }
            } finally {
                $lock->release();
                Log::info('Lock released.');
            }
        } else {
            // If another process has the lock, wait for it
            Log::info('Another process rebuilding cache - waiting...');
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
            'message' => 'Display form for creating a new category (not implemented for API).'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'is_active' => 'required|boolean',
            'sort_order' => 'required|integer',
            // Add other fields as necessary
        ]);

        // Create the category
        $category = Category::create($validated);

        // Optionally clear the cache so the new category appears in the list
        Cache::forget('categories.all');

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'category' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $categories = Cache::get('categories.all');
        $cacheKey = "categories.{$id}";

        if (!$categories) {
            $categories = Category::all();
            Cache::put('categories.all', $categories, 60);
            $source = 'database';
        } else {
            $source = 'cache';
        }

        $category = $categories->where('id', $id)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        return response()->json([
            'source' => $source,
            'success' => true,
            'message' => 'Category retrieved successfully.',
            'category' => $category
        ], 200);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // Not needed for API, but could return a form or structure if required
        return response()->json([
            'message' => 'Display form for editing the category (not implemented for API).',
            'category' => $category
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:categories,slug,' . $category->id,
            'is_active' => 'sometimes|required|boolean',
            'sort_order' => 'sometimes|required|integer',
            // Add other fields as necessary
        ]);

        $category->update($validated);

        // Optionally clear the cache so the updated category appears in the list
        Cache::forget('categories.all');

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'category' => $category
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        // Optionally clear the cache so the deleted category is removed from the list
        Cache::forget('categories.all');

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.'
        ], 200);
    }
}
