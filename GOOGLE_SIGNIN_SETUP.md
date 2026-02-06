# Google Sign-In Setup Guide

## What Was Changed

The comment and rating system now requires users to sign in with their Google/Gmail account instead of manually entering their name. This provides:

1. **Automatic email authentication** - Gmail address is automatically captured
2. **User verification** - Only real Google accounts can comment
3. **Better user experience** - No need to type name repeatedly
4. **Profile pictures** - Shows user's Google profile picture

## How It Works

1. When users visit the site, they'll see a "Sign in with Google" button in the comment section
2. After signing in, their name, email, and profile picture are saved
3. Comments and replies automatically use their Gmail address
4. Users stay logged in until they click "Sign Out"

## Setup Instructions

### Step 1: Get Google OAuth Client ID

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Navigate to **APIs & Services** > **Credentials**
4. Click **Create Credentials** > **OAuth client ID**
5. Select **Web application**
6. Add these to **Authorized JavaScript origins**:
   - `http://localhost:8000`
   - `http://localhost:3000`
   - Your production domain (e.g., `https://yourdomain.com`)
7. Copy the **Client ID** (looks like: `123456789-abc123.apps.googleusercontent.com`)

### Step 2: Update Your Code

Open `resources/js/components/index.vue` and find this line around line 335:

```javascript
const GOOGLE_CLIENT_ID = '912345678901-abcdefghijklmnopqrstuvwxyz123456.apps.googleusercontent.com'
```

Replace the entire string with your actual Google Client ID from Step 1.

**Example:**
```javascript
const GOOGLE_CLIENT_ID = '123456789012-abc123xyz456.apps.googleusercontent.com'
```

### Step 3: Build and Test

```bash
npm run dev
```

Or for production:

```bash
npm run build
```

## Features

### For Users:
- ✅ One-click Google sign-in
- ✅ No need to type name every time
- ✅ Profile picture displayed
- ✅ Secure authentication
- ✅ Easy sign-out option

### For You:
- ✅ Valid email addresses only
- ✅ No fake names or spam
- ✅ Better user tracking
- ✅ Professional authentication

## Testing

1. Open your site in the browser
2. Scroll to the comments section
3. Click "Sign in with Google"
4. Select your Google account
5. Try adding a comment - your Gmail and name should be used automatically
6. Your profile picture should appear next to your comment

## Troubleshooting

**"Sign in with Google button doesn't appear"**
- Check that you added the Google Client script in `welcome.blade.php`
- Make sure your Client ID is correct
- Check browser console for errors

**"Sign in doesn't work"**
- Verify your domain is in the authorized JavaScript origins
- Clear browser cache and cookies
- Try incognito/private browsing mode

**"Comments not saving with email"**
- Check that the backend controller receives the author field
- Verify database column accepts the email length (Google emails can be long)

## Files Modified

1. **resources/js/components/index.vue** - Added Google Sign-In logic
2. **resources/views/welcome.blade.php** - Added Google Sign-In script
3. **resources/js/css/index.css** - Added styling for sign-in UI

## Future Enhancements

Consider adding:
- Remember user preference across sessions
- Admin dashboard to manage authenticated users
- Email notifications for comment replies
- Social sharing features
