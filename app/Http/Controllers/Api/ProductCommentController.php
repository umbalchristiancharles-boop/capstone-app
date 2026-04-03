<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductComment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductCommentController extends Controller
{
    /**
     * Get all products available for commenting (public)
     * Only returns active and published products to ensure valid product_ids for comments
     */
    public function listProducts(Request $request)
    {
        $products = Product::where('is_active', 1)
            ->where('is_published', 1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        Log::info('ProductCommentController::listProducts', [
            'count' => $products->count(),
            'products' => $products->pluck('id')->toArray()
        ]);

        return response()->json($products);
    }

    public function index(Request $request)
    {
        $query = ProductComment::query()
            ->whereNull('parent_comment_id')
            ->latest('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        $comments = $query->get();

        // Load replies for each parent comment
        $comments->load('replies');

        return response()->json($comments);
    }

    public function store(Request $request)
    {
        Log::info('ProductCommentController::store - incoming request', [
            'payload' => $request->all()
        ]);

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'author' => ['required', 'string', 'max:60'],
            'text' => ['required', 'string', 'max:500'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        Log::info('ProductCommentController::store - validation passed', [
            'product_id' => $data['product_id'],
            'author' => $data['author'],
            'text_length' => strlen($data['text']),
            'rating' => $data['rating']
        ]);

        $data['ip_address'] = $request->ip();

        $comment = ProductComment::create($data);

        return response()->json($comment, 201);
    }

    public function storeReply(Request $request)
    {
        $data = $request->validate([
            'parent_comment_id' => ['required', 'integer', 'exists:product_comments,id'],
            'author' => ['required', 'string', 'max:60'],
            'text' => ['required', 'string', 'max:500'],
        ]);

        $parentComment = ProductComment::findOrFail($data['parent_comment_id']);

        $data['product_id'] = $parentComment->product_id;
        $data['ip_address'] = $request->ip();
        // Don't set rating for replies - it will use the default value from the database

        $reply = ProductComment::create($data);

        return response()->json($reply, 201);
    }

    /**
     * Get all customer comments with product and author info (for CRM/Admin panels)
     */
    public function allComments(Request $request)
    {
        $query = ProductComment::query()
            ->whereNull('parent_comment_id')
            ->with(['product', 'user', 'replies'])
            ->latest('created_at');

        $perPage = $request->integer('per_page', 50);
        $comments = $query->paginate($perPage);

        return response()->json($comments);
    }

    /**
     * Hard delete a comment and all its replies permanently from database
     */
    public function destroy($id)
    {
        $comment = ProductComment::findOrFail($id);
        $commentId = $comment->id;

        // Permanently delete all replies to this comment
        ProductComment::where('parent_comment_id', $commentId)->forceDelete();

        // Permanently delete the comment itself
        $comment->forceDelete();

        return response()->json(['message' => 'Comment permanently deleted'], 200);
    }
}
