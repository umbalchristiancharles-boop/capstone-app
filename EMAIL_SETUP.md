# Gmail Email Setup for Verification Codes

## How to Configure Gmail SMTP

### Step 1: Enable 2-Step Verification
1. Go to [myaccount.google.com](https://myaccount.google.com)
2. Click **Security** in the left menu
3. Scroll to "How you sign in to Google"
4. Click **2-Step Verification** and follow the prompts

### Step 2: Generate App Password
1. Go back to [myaccount.google.com](https://myaccount.google.com)
2. Click **Security** → Scroll to "App passwords"
3. Select **Mail** and **Windows Computer** (or your device)
4. Google will generate a 16-character password
5. **Copy this password** (without spaces)

### Step 3: Update .env File
Open `.env` and replace your email credentials:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_FROM_ADDRESS="support@chikintayo.com"
MAIL_FROM_NAME="Chikin Tayo"
```

Replace:
- `your-email@gmail.com` - Your actual Gmail address
- `xxxx xxxx xxxx xxxx` - The 16-character App Password (copy exactly as shown)

### Step 4: Save and Clear Cache
Run in terminal:
```bash
php artisan config:cache
```

### Done! ✅
Your verification codes will now be sent to customer emails.

---

### Troubleshooting

**Email not sending?**
- Check that you used the 16-character App Password exactly
- Make sure 2-Step Verification is enabled
- Check Laravel logs: `storage/logs/laravel.log`

**Code appears in response but email not received?**
- Mail server is logging but not delivering (check 2FA setup)
- Update credentials and run `php artisan config:cache` again
- Test with another Gmail account or email service
