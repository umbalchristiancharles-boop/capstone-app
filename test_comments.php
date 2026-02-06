<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProductComment;

try {
    echo "Testing ProductComment loading...\n";
    echo "==================================\n\n";
    
    $comments = ProductComment::whereNull('parent_comment_id')
        ->latest('created_at')
        ->get();
    
    $comments->load('replies');
    
    echo "Total comments: " . $comments->count() . "\n\n";
    
    foreach ($comments as $comment) {
        echo "Comment ID: {$comment->id}\n";
        echo "Product ID: {$comment->product_id}\n";
        echo "Author: {$comment->author}\n";
        echo "Text: {$comment->text}\n";
        echo "Rating: {$comment->rating}\n";
        echo "Replies: " . $comment->replies->count() . "\n";
        echo "-------------------\n";
    }
    
    echo "\n✓ Success! Comments loaded correctly.\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
