<?php
/**
 * Temporary script to manually import the customer reply from Gmail
 * Run this once to import the existing reply, then delete the file
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// The customer reply details from Gmail
$customerReply = [
    'subject' => 'Re: Complaint',  // or 'anong wait' if that's the actual subject
    'message' => 'anong wait',  // The reply content from Gmail
    'sender_email' => 'umbal.christiancharles@ncst.edu.ph',
    'sender_name' => 'Christian Charles Umbal'
];

// Find the customer report
$report = \App\Models\CustomerReport::where('customer_email', $customerReply['sender_email'])
    ->where('subject', 'like', '%Complaint%')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$report) {
    echo "ERROR: Customer report not found for email: {$customerReply['sender_email']}\n";
    exit(1);
}

echo "Found report #{$report->id}: {$report->subject}\n";

// Check if already imported
$existing = \App\Models\EmailCommunication::where('customer_report_id', $report->id)
    ->where('direction', 'inbound')
    ->where('message', $customerReply['message'])
    ->first();

if ($existing) {
    echo "This reply is already imported (Email ID: {$existing->id})\n";
    exit(0);
}

// Import the reply
try {
    \App\Models\EmailCommunication::create([
        'customer_report_id' => $report->id,
        'sender_email' => $customerReply['sender_email'],
        'sender_name' => $customerReply['sender_name'],
        'recipient_email' => 'support@chikintayo.com',
        'recipient_name' => 'Support Team',
        'subject' => $customerReply['subject'],
        'message' => $customerReply['message'],
        'direction' => 'inbound',
        'status' => 'sent',
    ]);

    // Update report status to in_progress if it was pending
    if ($report->status === 'pending') {
        $report->update(['status' => 'in_progress']);
        echo "Updated report status to 'in_progress'\n";
    }

    echo "SUCCESS: Customer reply imported successfully!\n";
    echo "View it in the CRM by opening report #{$report->id} and clicking 'Show Email History'\n";
    echo "It will appear with a 📥 Received badge\n";

} catch (\Exception $e) {
    echo "ERROR: Failed to import reply: " . $e->getMessage() . "\n";
    exit(1);
}