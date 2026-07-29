<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\CustomerReport;
use App\Models\EmailCommunication;
use App\Http\Controllers\Api\CustomerReportController;

class FetchGmailReplies extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'emails:fetch-gmail 
                            {--limit=50 : Maximum number of emails to fetch per run}
                            {--mark-seen : Mark fetched emails as seen/read}';

    /** The console command description. */
    protected $description = 'Fetch email replies from Gmail via IMAP and store them as inbound communications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $markSeen = $this->option('mark-seen');

        $this->info('Starting Gmail IMAP fetch...');

        // Check if IMAP extension is available
        if (!function_exists('imap_open')) {
            $this->error('IMAP extension is not installed. Please install php-imap extension.');
            Log::error('Gmail fetch failed: IMAP extension not available');
            return 1;
        }

        // Use the actual Gmail account username for IMAP login, not the from address alias
        // MAIL_USERNAME is the real Gmail account (e.g. ccsumbal12@gmail.com)
        // MAIL_FROM_ADDRESS may be a "Send As" alias (e.g. support@chikintayo.com)
        $mailbox = config('mail.mailers.smtp.username') ?: config('mail.from.address');
        $password = config('services.gmail.app_password') ?: env('GMAIL_APP_PASSWORD');
        
        if (!$password) {
            $this->error('Gmail app password not configured in .env (services.gmail.app_password)');
            Log::error('Gmail fetch failed: App password not configured');
            return 1;
        }

        // Gmail IMAP connection
        $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
        
        $this->info("Connecting to Gmail mailbox: {$mailbox}");
        
        try {
            $inbox = @imap_open($hostname, $mailbox, $password);
            
            if (!$inbox) {
                $error = imap_last_error();
                $this->error("Failed to connect to Gmail: {$error}");
                Log::error("Gmail IMAP connection failed", ['error' => $error]);
                return 1;
            }

            $this->info('Connected successfully. Searching for recent emails...');

            // Search for all recent emails (replies from customers)
            // Using 'ALL' instead of 'UNSEEN' to fetch both read and unread emails
            // Deduplication logic prevents re-processing already imported emails
            $emails = imap_search($inbox, 'ALL', SE_UID);
            
            if (!$emails) {
                $this->info('No new emails found.');
                imap_close($inbox);
                return 0;
            }

            // Sort emails in reverse order (newest first) and limit
            rsort($emails);
            $emails = array_slice($emails, 0, $limit);

            $this->info('Found ' . count($emails) . ' new email(s). Processing...');

            $processed = 0;
            $failed = 0;

            foreach ($emails as $emailId) {
                try {
                    $this->processEmail($inbox, $emailId, $markSeen);
                    $processed++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::error("Failed to process email UID {$emailId}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            imap_close($inbox);

            $this->info("Processing complete: {$processed} processed, {$failed} failed");
            Log::info("Gmail fetch completed", [
                'processed' => $processed,
                'failed' => $failed
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Gmail fetch exception', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Process a single email from Gmail
     */
    private function processEmail($inbox, $emailId, $markSeen)
    {
        $structure = imap_fetchstructure($inbox, $emailId, FT_UID);
        
        if (!$structure) {
            throw new \Exception("Failed to fetch email structure");
        }

        // Get email headers using msgno (sequence number) - FT_UID is NOT valid for imap_headerinfo!
        // imap_headerinfo expects a message sequence number, not a UID
        $msgNo = imap_msgno($inbox, $emailId);
        if (!$msgNo) {
            throw new \Exception("Could not convert UID {$emailId} to message number");
        }
        
        $headers = imap_headerinfo($inbox, $msgNo);
        
        if (!$headers) {
            throw new \Exception("Failed to fetch headers for message #{$msgNo}");
        }
        
        $subject = $this->decodeMimeStr($headers->subject ?? '(No Subject)');
        $fromEmail = $headers->from[0]->mailbox . '@' . $headers->from[0]->host;
        $fromName = $headers->from[0]->personal ?? $fromEmail;
        $toEmail = isset($headers->to[0]) ? $headers->to[0]->mailbox . '@' . $headers->to[0]->host : '';
        $messageId = $headers->message_id ?? null;
        $inReplyTo = $headers->in_reply_to ?? null;
        $references = $headers->references ?? null;

        // Get email body
        $body = $this->getEmailBody($inbox, $emailId, $structure);

        // Try to match this email to a customer report
        $report = $this->findMatchingReport($fromEmail, $subject, $messageId, $inReplyTo);

        if (!$report) {
            Log::info("Email could not be matched to a customer report", [
                'from' => $fromEmail,
                'subject' => $subject,
                'message_id' => $messageId
            ]);
            return;
        }

        // Check if this email is already recorded
        $existing = EmailCommunication::where('message_id', $messageId)
            ->where('customer_report_id', $report->id)
            ->first();

        if ($existing) {
            Log::info("Email already recorded", ['message_id' => $messageId]);
            if ($markSeen) {
                imap_setflag_full($inbox, $emailId, "\\Seen", ST_UID);
            }
            return;
        }

        // Save as inbound email communication
        EmailCommunication::create([
            'customer_report_id' => $report->id,
            'sender_email' => $fromEmail,
            'sender_name' => $fromName,
            'recipient_email' => $toEmail,
            'recipient_name' => 'Support Team',
            'subject' => $subject,
            'message' => $body,
            'direction' => 'inbound',
            'status' => 'sent',
            'message_id' => $messageId,
            'in_reply_to' => $inReplyTo,
            'references' => $references,
        ]);

        // Update report status to in_progress if it was pending
        if ($report->status === 'pending') {
            $report->update(['status' => 'in_progress']);
        }

        Log::info("Successfully processed inbound email", [
            'report_id' => $report->id,
            'from' => $fromEmail,
            'subject' => $subject,
            'message_id' => $messageId
        ]);

        // Mark as seen if requested
        if ($markSeen) {
            imap_setflag_full($inbox, $emailId, "\\Seen", ST_UID);
        }
    }

    /**
     * Find matching customer report based on email
     */
    private function findMatchingReport($fromEmail, $subject, $messageId, $inReplyTo)
    {
        // First, try to match by In-Reply-To header against outbound message_id
        // This is the most reliable method - the customer's reply has In-Reply-To set
        // to the Message-ID of the email we sent them
        if ($inReplyTo) {
            // Clean angle brackets from the in-reply-to value if present
            $cleanInReplyTo = trim($inReplyTo, '<> ');
            
            // Check against message_id of any outbound email
            $outboundEmail = EmailCommunication::where('message_id', $cleanInReplyTo)
                ->where('direction', 'outbound')
                ->first();
            
            if ($outboundEmail) {
                return CustomerReport::find($outboundEmail->customer_report_id);
            }

            // Also check if in_reply_to appears in any email's references or in_reply_to columns
            $referencedEmail = EmailCommunication::where('customer_report_id', '>', 0)
                ->where(function ($q) use ($cleanInReplyTo) {
                    $q->where('message_id', $cleanInReplyTo)
                      ->orWhere('references', 'like', "%{$cleanInReplyTo}%")
                      ->orWhere('in_reply_to', $cleanInReplyTo);
                })
                ->where('direction', 'outbound')
                ->first();
            
            if ($referencedEmail) {
                return CustomerReport::find($referencedEmail->customer_report_id);
            }
        }

        // Second, try to match by References header threading
        // If the inbound email has References header, check if any outbound communication
        // contains those reference message IDs
        if ($messageId) {
            // Check if any outbound email has this message_id in its references
            $referencingEmail = EmailCommunication::where('references', 'like', "%{$messageId}%")
                ->where('direction', 'outbound')
                ->first();
            
            if ($referencingEmail) {
                return CustomerReport::find($referencingEmail->customer_report_id);
            }
        }

        // Third, try to match by sender email to active reports
        $report = CustomerReport::where('customer_email', $fromEmail)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($report) {
            return $report;
        }

        // Fourth, try to match by cleaned subject (remove Re:/Fwd: prefixes)
        $cleanSubject = preg_replace('/^(Re:|Fwd:|Fw:)\s*/i', '', $subject);
        $report = CustomerReport::where('subject', 'like', "%{$cleanSubject}%")
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($report) {
            return $report;
        }

        // Fifth, try to match by sender email to any report regardless of status
        // (the customer may have replied even if the report was marked resolved)
        $report = CustomerReport::where('customer_email', $fromEmail)
            ->orderBy('created_at', 'desc')
            ->first();

        return $report;
    }

    /**
     * Extract email body from IMAP structure
     */
    private function getEmailBody($inbox, $emailId, $structure)
    {
        $body = '';

        if ($structure->type == 1) { // multipart
            // Fetch parts properly - use the full structure with parts
            $parts = $structure->parts ?? [];
            foreach ($parts as $partNum => $part) {
                if ($part->type == 0 || $part->ifsubtype) { // text/plain or text/html
                    $sectionNum = $partNum + 1; // IMAP part numbers are 1-indexed
                    $encoding = $part->encoding;
                    $bodyPart = @imap_fetchbody($inbox, $emailId, $sectionNum, FT_UID);
                    if ($bodyPart) {
                        $decodedBody = $this->decodeText($bodyPart, $encoding);
                        // Prefer plain text over HTML
                        if ($part->subtype == 'PLAIN' || empty($body)) {
                            $body = $decodedBody;
                        }
                        // If we found plain text, stop looking
                        if ($part->subtype == 'PLAIN') {
                            break;
                        }
                    }
                }
            }
        } else { // not multipart
            $encoding = $structure->encoding;
            $body = imap_body($inbox, $emailId, FT_UID);
            $body = $this->decodeText($body, $encoding);
        }

        return trim($body);
    }

    /**
     * Decode text based on encoding
     */
    private function decodeText($text, $encoding)
    {
        switch ($encoding) {
            case 0: // 7BIT
            case 1: // 8BIT
                return imap_utf8($text);
            case 2: // BINARY
                return $text;
            case 3: // BASE64
                return base64_decode($text);
            case 4: // QUOTED-PRINTABLE
                return quoted_printable_decode($text);
            default:
                return imap_utf8($text);
        }
    }

    /**
     * Decode MIME encoded string
     */
    private function decodeMimeStr($string)
    {
        if (preg_match("/=\?([^?]+)\?([BQ])\?([^?]+)\?=/i", $string, $matches)) {
            $charset = $matches[1];
            $encoding = $matches[2];
            $data = $matches[3];

            switch ($encoding) {
                case 'B':
                    $data = base64_decode($data);
                    break;
                case 'Q':
                    $data = quoted_printable_decode($data);
                    break;
            }

            if ($charset && $charset !== 'UTF-8') {
                $data = mb_convert_encoding($data, 'UTF-8', $charset);
            }

            return $data;
        }

        return $string;
    }
}