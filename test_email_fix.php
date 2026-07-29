<?php

// Test script to verify the email sending fix
// This script tests the send_raw_mail_notification helper function

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

// Test the helper function
echo "Testing send_raw_mail_notification function...\n\n";

try {
    echo "Attempting to send email...\n";
    $result = send_raw_mail_notification(
        'test@example.com',
        'Test Subject',
        'This is a test email body.'
    );
    
    echo "Function returned: " . var_export($result, true) . "\n";
    
    if ($result !== null) {
        echo "✓ Email sent successfully!\n";
        echo "  Message-ID: " . $result . "\n";
    } else {
        echo "✗ Email failed to send (returned null)\n";
        echo "  This usually means the Mail facade threw an exception\n";
        echo "  Check storage/logs/laravel.log for the actual error\n";
    }
} catch (\Throwable $e) {
    echo "✗ Exception occurred: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
}

echo "\nTest completed.\n";
