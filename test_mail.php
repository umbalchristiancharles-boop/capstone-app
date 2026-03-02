<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

try {
    echo "Testing email configuration...\n";
    echo "=============================\n\n";

    $testEmail = 'test@example.com';
    $testCode = '123456';

    Mail::raw(
        "Your CHIKIN TAYO verification code is: {$testCode}\n\nThis code will expire in 10 minutes.",
        function ($message) use ($testEmail) {
            $message->to($testEmail)
                    ->subject('CHIKIN TAYO - Email Verification Code');
        }
    );

    echo "✓ Email sent successfully!\n";
    echo "To: {$testEmail}\n";
    echo "Code: {$testCode}\n\n";
    echo "Check Mailpit at: http://127.0.0.1:8025\n";

} catch (\Exception $e) {
    echo "✗ Failed to send email:\n";
    echo $e->getMessage() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
