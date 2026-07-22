# Email Reply Fetching Setup Guide

## Problem
Customer replies to emails sent from the CRM system are not appearing in the email communication history. The system can send emails but cannot automatically fetch replies from Gmail.

## Solution
This implementation provides automatic fetching of email replies from Gmail using IMAP.

## Prerequisites

### 1. Enable IMAP in Gmail
1. Go to Gmail settings (gear icon) → "See all settings"
2. Go to "Forwarding and POP/IMAP" tab
3. Enable IMAP: "Enable IMAP"
4. Save changes

### 2. Generate Gmail App Password
Since you're using 2-Factor Authentication (which you should be), you need an App Password:

1. Go to https://myaccount.google.com/apppasswords
2. Select "Mail" as the app
3. Select "Other (Custom name)" and name it "CRM System"
4. Google will generate a 16-character password
5. Copy this password

### 3. Install PHP IMAP Extension

The IMAP extension is currently NOT installed on your system. You need to install it:

#### For XAMPP on Windows:

1. Open your `php.ini` file (usually at `C:\xampp\php\php.ini`)

2. Find the line:
   ```ini
   ;extension=imap
   ```

3. Uncomment it (remove the semicolon):
   ```ini
   extension=imap
   ```

4. Also find and uncomment these lines if they exist:
   ```ini
   ;extension=openssl
   ```
   Should be:
   ```ini
   extension=openssl
   ```

5. Save the file and restart Apache from XAMPP Control Panel

6. Verify installation by running:
   ```bash
   php -m | findstr imap
   ```
   You should see `imap` in the output.

### 4. Update .env Configuration

Add your Gmail app password to `.env`:

```env
# Gmail IMAP App Password (for fetching replies)
# Generate this at: https://myaccount.google.com/apppasswords
GMAIL_APP_PASSWORD="your-16-character-app-password-here"
```

**Note:** The app password has already been added to your `.env` file using the same password as your SMTP credentials.

### 5. Run Migrations

The migrations have already been run. If you need to run them again:

```bash
php artisan migrate
```

## Usage

### Manual Fetch (Testing)

To manually fetch emails and test the system:

```bash
php artisan emails:fetch-gmail
```

This will:
- Connect to Gmail via IMAP
- Search for unseen (unread) emails
- Match them to customer reports
- Store them as inbound email communications
- Update report status to "in_progress" if it was "pending"

### Options

```bash
# Fetch with custom limit (default is 50)
php artisan emails:fetch-gmail --limit=100

# Fetch and mark emails as seen/read in Gmail
php artisan emails:fetch-gmail --mark-seen
```

### Automated Fetching (Recommended)

Set up a cron job or scheduled task to automatically fetch emails:

#### On Linux/Mac (Cron):
```bash
# Edit crontab
crontab -e

# Add this line to fetch emails every 5 minutes
*/5 * * * * cd /path/to/capstone-app && php artisan emails:fetch-gmail --mark-seen >> storage/logs/email-fetch.log 2>&1
```

#### On Windows (Task Scheduler):
1. Open Task Scheduler
2. Create Basic Task
3. Trigger: Daily, repeat every 5 minutes for a duration of 1 day
4. Action: Start a program
5. Program: `php.exe` (full path, e.g., `C:\xampp\php\php.exe`)
6. Arguments: `artisan emails:fetch-gmail --mark-seen`
7. Start in: `C:\xampp\htdocs\capstone-app`

## How It Works

### Email Matching Logic

The system uses multiple strategies to match incoming emails to customer reports:

1. **Thread Matching (Primary)**: Uses `Message-ID`, `In-Reply-To`, and `References` headers to match replies to previously sent emails
2. **Sender Email Matching**: Matches by customer email address
3. **Subject Matching**: Strips "Re:", "Fwd:", "Fw:" prefixes and matches by subject

### Email Storage

When an email is fetched:
- It's stored in the `email_communications` table with `direction = 'inbound'`
- The customer report status is updated to "in_progress" if it was "pending"
- The email appears in the CRM panel's email history

### Viewing in CRM

1. Go to Main Branch CRM Panel
2. Find the customer report
3. Click "Show Email History"
4. Inbound emails (from customers) will be marked with 📥 Received
5. Outbound emails (from staff) will be marked with 📤 Sent

## Troubleshooting

### IMAP Extension Not Found
If you see "IMAP extension is not installed":
1. Follow the installation steps above
2. Restart Apache
3. Verify with `php -m | findstr imap`

### Connection Failed
If you see "Failed to connect to Gmail":
1. Verify IMAP is enabled in Gmail settings
2. Check your app password is correct
3. Ensure you're using the full email address: `support@chikintayo.com`
4. Check that port 993 is not blocked by firewall

### Emails Not Matching
If emails are fetched but not matched to reports:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Look for "Email could not be matched to a customer report" entries
3. Ensure the customer email matches the report's `customer_email` field
4. Ensure the subject line is similar to the original report subject

## Alternative: Manual Import (Without IMAP)

If you cannot install the IMAP extension, you can manually import emails:

1. Export emails from Gmail as .eml or .mbox files
2. Create a simple import script to parse and store them
3. Or use the `receiveEmail` API endpoint manually

### Using the receiveEmail Endpoint

```bash
curl -X POST http://localhost:8000/api/customer-reports/{report_id}/receive-email \
  -H "Content-Type: application/json" \
  -d '{
    "subject": "Re: Complaint",
    "message": "Customer reply message here",
    "sender_email": "customer@example.com",
    "sender_name": "Customer Name"
  }'
```

## Security Notes

- Never commit your `.env` file with real credentials
- Use App Passwords, not your main Gmail password
- The app password has limited access (only mail)
- You can revoke app passwords anytime from your Google Account
- Store the password in `.env` which should be in `.gitignore`

## Testing the Complete Flow

1. **Send a test email from CRM:**
   - Create a customer report
   - Change status to "in_progress"
   - Send an email to the customer

2. **Reply from Gmail:**
   - Open the email in the customer's inbox
   - Reply to it

3. **Fetch the reply:**
   - Run: `php artisan emails:fetch-gmail --mark-seen`
   - Check the CRM panel - the reply should appear in email history

4. **Verify in database:**
   ```sql
   SELECT * FROM email_communications 
   WHERE customer_report_id = {report_id} 
   ORDER BY created_at DESC;
   ```

## Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check email fetch logs if using cron: `storage/logs/email-fetch.log`
3. Verify IMAP connection manually using a tool like Thunderbird or Outlook