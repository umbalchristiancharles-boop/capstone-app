<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductComment;
use Illuminate\Http\Request;

class ProductCommentController extends Controller
{
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
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'author' => ['required', 'string', 'max:60'],
            'text' => ['required', 'string', 'max:500'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
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
}
