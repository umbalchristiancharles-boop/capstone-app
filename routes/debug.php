<?php
/**
 * Debug routes for troubleshooting
 * Available at: http://localhost:8000/debug/*
 */

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Models\ProductComment;

Route::prefix('debug')->group(function () {
    // Check products status for comments
    Route::get('/products-status', function () {
        $total = Product::count();
        $active = Product::where('is_active', 1)->count();
        $published = Product::where('is_published', 1)->count();
        $activeAndPublished = Product::where('is_active', 1)->where('is_published', 1)->count();

        $publishedProducts = Product::where('is_active', 1)
            ->where('is_published', 1)
            ->select('id', 'name', 'is_active', 'is_published')
            ->limit(20)
            ->get();

        return response()->json([
            'summary' => [
                'total' => $total,
                'active' => $active,
                'published' => $published,
                'active_and_published' => $activeAndPublished,
            ],
            'published_products' => $publishedProducts,
        ]);
    });

    // Check comments
    Route::get('/comments-count', function () {
        $total = ProductComment::count();
        $latestComments = ProductComment::with('product')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'total' => $total,
            'latest' => $latestComments,
        ]);
    });

    // Test comment validation
    Route::post('/test-comment-validation', function (Illuminate\Http\Request $request) {
        $productId = $request->input('product_id');
        
        $exists = Product::where('id', $productId)->where('is_active', 1)->where('is_published', 1)->exists();
        
        return response()->json([
            'product_id' => $productId,
            'exists_in_db' => Product::where('id', $productId)->exists(),
            'is_active' => Product::where('id', $productId)->value('is_active'),
            'is_published' => Product::where('id', $productId)->value('is_published'),
            'validation_pass' => $exists,
            'notes' => 'For validation to pass, product must have is_active=1 AND is_published=1'
        ]);
    });
});
