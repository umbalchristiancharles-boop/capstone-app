<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductComment;
use App\Models\Product;
use App\Models\DishIngredient;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ProductCommentFlag;
use App\Notifications\CommentFlaggedNotification;
use App\Notifications\AccountBannedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class ProductCommentController extends Controller
{
    /**
     * Get all products available for commenting (public)
     * Only returns active and published products to ensure valid product_ids for comments
     */
    public function listProducts(Request $request)
    {
        // Exclude products that are used as dish ingredients so customers
        // only see actual products and representative dish products.
        $ingredientIds = DishIngredient::whereNotNull('product_id')
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $ingredientNames = DishIngredient::pluck('name')
            ->filter()
            ->map(fn($n) => trim(strtoupper((string) $n)))
            ->unique()
            ->values()
            ->all();

        $productsQuery = Product::where('is_active', 1)
            ->where('is_published', 1)
            ->select('id', 'name', 'branch_id')
            ->orderBy('name');

        if (!empty($ingredientIds)) {
            $productsQuery->whereNotIn('id', $ingredientIds);
        }

        if (!empty($ingredientNames)) {
            $productsQuery->whereNotIn(DB::raw('TRIM(UPPER(name))'), $ingredientNames);
        }

        // If a public branch filter is supplied, only return products for that branch
        if ($request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
            if ($branchId > 0) {
                $productsQuery->where('branch_id', $branchId);
            }
        }

        $products = $productsQuery->get();

        // Deduplicate products by normalized name to avoid duplicate supplier entries
        $products = $products->unique(fn($p) => trim(strtoupper($p->name)))->values();

        Log::info('ProductCommentController::listProducts', [
            'count' => $products->count(),
            'products' => $products->pluck('id')->toArray()
        ]);

        return response()->json($products);
    }

    /**
     * Public endpoint to retrieve active branches for the customer landing page
     */
    public function publicBranches(Request $request)
    {
        $branches = Branch::where('is_active', 1)
            ->select('id', 'name', 'address')
            ->orderBy('name')
            ->get();

        return response()->json($branches);
    }

    public function index(Request $request)
    {
        $query = ProductComment::query()
            ->whereNull('parent_comment_id')
            ->where('is_hidden', 0)
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

    /**
     * Flag a comment from the CRM/admin panel. Sends a warning email to the comment's author.
     * If the user's comments accumulate 3 or more flags, ban their customer account.
     */
    public function flag(Request $request, $id)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:100'],
        ]);

        $admin = $request->user();

        $comment = ProductComment::findOrFail($id);

        // Record the flag
        $flag = ProductCommentFlag::create([
            'product_comment_id' => $comment->id,
            'admin_user_id' => $admin->id,
            'reason' => $data['reason'] ?? null,
        ]);

        // Increment fast counter on comment
        $comment->increment('flags');

        // Hide the comment from public views immediately when flagged
        try {
            $comment->is_hidden = 1;
            $comment->save();
            Log::info('ProductCommentController::flag - comment hidden', ['comment_id' => $comment->id]);
        } catch (\Throwable $e) {
            Log::error('ProductCommentController::flag - failed to hide comment', ['error' => $e->getMessage(), 'comment_id' => $comment->id]);
        }

        // Resolve an email address to send to. Priority:
        // 1) explicit email provided in request (admin typed it),
        // 2) comment's associated user account email (if exists),
        // 3) email stored in comment->author (guest comment),
        // 4) lookup a user by comment->author as username or email.
        $user = $comment->user;
        $email = $data['email'] ?? null;

        if (empty($email) && $user) {
            $email = $user->email ?? null;
            if (empty($email) && $user->customerAccount && !empty($user->customerAccount->email)) {
                $email = $user->customerAccount->email;
            }
        }

        // If still no email, try using comment author field directly (often contains guest email)
        if (empty($email) && !empty($comment->author)) {
            $author = trim((string)$comment->author);
            if (filter_var($author, FILTER_VALIDATE_EMAIL)) {
                $email = $author;
            } else {
                try {
                    $possibleUser = User::where('email', $author)
                        ->orWhere('username', $author)
                        ->first();
                    if ($possibleUser && !empty($possibleUser->email)) {
                        $email = $possibleUser->email;
                        $user = $possibleUser;
                    }
                } catch (\Throwable $_) {
                    // ignore lookup failures
                }
            }
        }

        if (!empty($email)) {
                try {
                    Log::info('ProductCommentController::flag - sending flagged email via Mail::raw', ['user_id' => $user->id ?? null, 'email' => $email, 'comment_id' => $comment->id]);

                    $body = [];
                    $body[] = 'Hello ' . ($user->full_name ?? $email);
                    $body[] = '';
                    $body[] = 'One of your comments was flagged by an administrator for review.';
                    if (!empty($data['reason'])) {
                        $body[] = '';
                        $body[] = 'Reason: ' . $data['reason'];
                    }
                    $body[] = '';
                    $body[] = 'Comment: ' . (is_string($comment->text) ? mb_strimwidth($comment->text, 0, 500, '...') : '');
                    $body[] = '';
                    $body[] = 'If you believe this is a mistake, please contact support.';

                    Mail::raw(implode("\n", $body), function ($message) use ($email) {
                        $message->to($email)
                            ->subject('Warning: Your comment has been flagged');
                    });

                    Log::info('ProductCommentController::flag - Mail::raw sent', ['email' => $email, 'comment_id' => $comment->id]);
                } catch (\Throwable $e) {
                    Log::error('Failed to send flagged email via Mail::raw', ['error' => $e->getMessage(), 'user_id' => $user->id, 'comment_id' => $comment->id, 'email' => $email]);

                    // As a last resort, attempt the Notification path
                    try {
                        Notification::route('mail', $email)->notify(new CommentFlaggedNotification($comment, $data['reason'] ?? null));
                        Log::info('ProductCommentController::flag - fallback Notification route used', ['email' => $email, 'comment_id' => $comment->id]);
                    } catch (\Throwable $e2) {
                        Log::error('Fallback Notification also failed for CommentFlaggedNotification', ['error' => $e2->getMessage(), 'email' => $email, 'comment_id' => $comment->id]);
                    }
                }
        } else {
            Log::warning('ProductCommentController::flag - no email found or provided; cannot notify', [
                'user_id' => $user->id ?? null,
                'comment_id' => $comment->id,
            ]);
        }

        // If we have an associated user, count total flags against this user's comments
        if ($user) {
            $commentIds = ProductComment::where('user_id', $user->id)->pluck('id');
            $totalFlags = ProductCommentFlag::whereIn('product_comment_id', $commentIds)->count();

            if ($totalFlags >= 3) {
                try {
                    // Ban user's customer account if present
                    $customerAccount = $user->customerAccount;
                    if ($customerAccount) {
                        $customerAccount->status = 'banned';
                        $customerAccount->save();
                        Log::info('Customer account banned due to flags', ['customer_account_id' => $customerAccount->id, 'user_id' => $user->id, 'total_flags' => $totalFlags]);
                    } else {
                        // Fallback: deactivate user record
                        $user->is_active = false;
                        $user->save();
                        Log::info('User deactivated due to flags', ['user_id' => $user->id, 'total_flags' => $totalFlags]);
                    }

                    if (!empty($email)) {
                        try {
                            $user->notify(new AccountBannedNotification('Reached ' . $totalFlags . ' flags'));
                        } catch (\Throwable $e) {
                            Log::error('Failed to send AccountBannedNotification', ['error' => $e->getMessage(), 'user_id' => $user->id]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error('Error while enforcing ban after flags', ['error' => $e->getMessage(), 'user_id' => $user->id]);
                }
            }
        } else {
            Log::info('ProductCommentController::flag - comment has no associated user; cannot notify', ['comment_id' => $comment->id]);
        }

        return response()->json(['message' => 'Comment flagged', 'flag_id' => $flag->id], 200);
    }

    /**
     * Unhide a previously hidden comment (admin action)
     */
    public function unhide(Request $request, $id)
    {
        $admin = $request->user();
        $comment = ProductComment::findOrFail($id);

        try {
            $comment->is_hidden = 0;
            $comment->save();
            Log::info('ProductCommentController::unhide - comment unhidden', ['comment_id' => $comment->id, 'admin_user_id' => $admin->id]);
            return response()->json(['message' => 'Comment unhidden'], 200);
        } catch (\Throwable $e) {
            Log::error('ProductCommentController::unhide - failed to unhide comment', ['error' => $e->getMessage(), 'comment_id' => $comment->id]);
            return response()->json(['message' => 'Failed to unhide comment'], 500);
        }
    }
}
