<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

try {
    echo "Testing Gmail SMTP configuration...\n";
    echo "====================================\n\n";

    echo "MAIL_MAILER: " . Config::get('mail.default') . "\n";
    echo "MAIL_HOST: " . Config::get('mail.mailers.smtp.host') . "\n";
    echo "MAIL_PORT: " . Config::get('mail.mailers.smtp.port') . "\n";
    echo "MAIL_USERNAME: " . Config::get('mail.mailers.smtp.username') . "\n";
    echo "MAIL_FROM: " . Config::get('mail.from.address') . "\n\n";

    $testEmail = 'ccsumbal12@gmail.com'; // Send to your own Gmail
    $testCode = '123456';

    echo "Sending test email to: {$testEmail}\n";
    echo "Please wait...\n\n";

    Mail::raw(
        "TEST EMAIL from CHIKIN TAYO\n\nYour verification code is: {$testCode}\n\nThis is a test email to verify Gmail SMTP is working.\n\nIf you received this, the setup is successful!",
        function ($message) use ($testEmail) {
            $message->to($testEmail)
                    ->subject('CHIKIN TAYO - Test Email');
        }
    );

    echo "✓ Email sent successfully!\n";
    echo "Check your Gmail inbox: {$testEmail}\n";
    echo "Note: Check spam folder if not in inbox\n";

} catch (\Exception $e) {
    echo "✗ Failed to send email:\n";
    echo $e->getMessage() . "\n\n";
}
