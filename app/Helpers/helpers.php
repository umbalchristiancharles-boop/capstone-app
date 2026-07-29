<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Global helper functions shared across web and API routes
if (!function_exists('no_cache_view')) {
    function no_cache_view($view)
    {
        // Wrap view responses with cache-busting headers to prevent back-button issues
        return response()->view($view)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}

if (!function_exists('testHelper')) {
    function testHelper()
    {
        return 'Helper working';
    }
}

if (!function_exists('send_raw_mail_notification')) {
    function send_raw_mail_notification(string $email, string $subject, string $body): ?string
    {
        try {
            // Generate a unique Message-ID before sending
            $messageId = generate_message_id();
            
            // Use Mail::raw() callback approach to send the email
            // We set the Message-ID on the underlying Symfony Mime message after building
            \Illuminate\Support\Facades\Mail::raw($body, function (\Illuminate\Mail\Message $message) use ($email, $subject, $messageId) {
                $message->to($email)
                    ->subject($subject);
                
                // Set the Message-ID on the underlying Symfony Mime Email object
                try {
                    $symfonyMessage = $message->getSymfonyMessage();
                    $symfonyMessage->getHeaders()->addTextHeader('Message-ID', "<{$messageId}>");
                } catch (\Throwable $headerError) {
                    // If header setting fails, log but still consider the email sent
                    Log::warning('Failed to set Message-ID header, but email may still be sent', [
                        'error' => $headerError->getMessage(),
                        'message_id' => $messageId,
                    ]);
                }
            });
            
            Log::info('Email sent successfully', [
                'message_id' => $messageId,
                'to' => $email,
                'subject' => $subject,
            ]);
            
            return $messageId;
        } catch (\Throwable $e) {
            // Log the error but don't throw - allow the caller to handle the failure
            Log::error('Failed to send email notification', [
                'error' => $e->getMessage(),
                'to' => $email,
                'subject' => $subject,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return null;
        }
    }
}

if (!function_exists('generate_message_id')) {
    function generate_message_id(): string
    {
        // Generate a unique Message-ID in the standard email format
        // Format: <timestamp.random@domain>
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        $domain = parse_url(config('app.url', 'localhost'), PHP_URL_HOST) ?: 'localhost';
        
        return "{$timestamp}.{$random}@{$domain}";
    }
}
