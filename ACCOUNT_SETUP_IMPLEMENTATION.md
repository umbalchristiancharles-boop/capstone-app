# Account Setup After Login - Implementation Summary

## Overview
After a user logs in, if their account is missing critical information (phone number, email, current home address, and required documents - SSS, PhilHealth, Drug Test), a setup modal will appear to guide them through completing their profile.

## Implementation Details

### 1. Backend Changes

#### Modified Files:
- `app/Http/Controllers/Api/AuthController.php`
- `routes/api.php`

#### New Methods in AuthController:

**`checkMissingAccountInfo($user)`** - Helper method that checks for missing account information:
- Phone number (`users.phone_number`)
- Email (`users.email` - must be set AND verified)
- Current home address (`users.address`, `users.latitude`, `users.longitude`)
- Required documents:
  - SSS ID (`staff_documents.sss_id_path`)
  - PhilHealth ID (`staff_documents.philhealth_id_path`)
  - Drug Test Result (`staff_documents.drug_test_result_path`)

Returns array of missing field names (e.g., `['email', 'address', 'sss_id']`)

**Modified `login()` method:**
- Now includes `missing_account_info` array in login response
- Smart detection - only reports what's actually missing

**New API Endpoints:**

1. **PUT `/api/auth/setup/account-info`**
   - Updates phone number and address information
   - Requires authentication
   - Body parameters: `phone_number`, `address`, `latitude`, `longitude`

2. **POST `/api/auth/setup/document/{documentType}`**
   - Uploads required documents
   - Valid document types: `sss_id`, `philhealth_id`, `drug_test_result`
   - Requires multipart form data with `file` field
   - Files stored in: `public/storage/account-setup-documents/{user_id}/`

3. **GET `/api/auth/setup/status`**
   - Retrieves current account setup status
   - Returns list of missing fields and current values

### 2. Frontend Changes

#### New Component: `AccountSetupModal.vue`

Multi-step wizard component that:
- **Dynamically generates steps** based on missing fields
- Shows only what needs to be completed
- Features:

  **Email Step (if email missing):**
  - Input email address
  - Send verification code to email
  - Enter 6-digit code received

  **Email Verification Step (if email exists but not verified):**
  - Shows pre-filled email
  - Enter 6-digit code sent to email
  - Option to resend code

  **Phone Number Step:**
  - Input phone number

  **Address Step:**
  - Uses GeolocationMap component to pin location
  - Saves address, latitude, and longitude

  **Documents Step:**
  - Grid layout for document uploads
  - Three required documents: SSS ID, PhilHealth ID, Drug Test Result
  - Only shows documents that are missing
  - File preview with remove option
  - Upload all documents button

  **Completion Step:**
  - Success message
  - Button to proceed to dashboard

#### Modified Component: `adminlogin.vue`

Changes:
- Imported `AccountSetupModal` component
- Added refs for `showAccountSetupModal` and `accountSetupMissingFields`
- Modified login flow:
  1. Check if password change required → show ForcePasswordChangeModal
  2. Check if email verification pending → redirect to verify-email
  3. Check if account setup required → show AccountSetupModal
  4. Otherwise → redirect to dashboard
- Added `handleAccountSetupComplete()` function to handle setup completion

### 3. Data Flow

```
Login Attempt
    ↓
Valid Credentials
    ↓
Check must_change_password → YES: Show password change modal
    ↓ NO
Check email_verification_pending → YES: Redirect to verify-email
    ↓ NO
Check missing_account_info → YES: Show setup modal
    ↓ NO
Redirect to Dashboard
```

### 4. Setup Modal Steps Example

For a new branch default account that's missing all info:
1. Email verification (if no email) or Email entry
2. Phone number entry
3. Address/location pinning on map
4. Document uploads (SSS, PhilHealth, Drug Test)
5. Completion screen → Dashboard

For same user missing only documents:
1. Document uploads (SSS, PhilHealth, Drug Test)
2. Completion screen → Dashboard

### 5. Integration Points

**Email verification:**
- Uses existing email verification system
- 6-digit codes sent to email
- 10-minute expiry

**Address location:**
- Uses existing `GeolocationMap.vue` component
- Stores address string, latitude, longitude in users table

**Document storage:**
- Uses existing storage infrastructure
- Documents stored in: `public/storage/account-setup-documents/{user_id}/`
- Similar to staff documents but in different directory
- Tracks in `staff_documents` table

### 6. Testing Checklist

- [ ] Create new branch with default accounts
- [ ] Login with default account (no email/phone/address/docs)
- [ ] Account setup modal appears
- [ ] Can enter email and verify with code
- [ ] Can enter phone number
- [ ] Can pin location on map
- [ ] Can upload SSS ID, PhilHealth ID, Drug Test Result
- [ ] Can complete setup
- [ ] Redirects to appropriate dashboard
- [ ] Subsequent logins don't show setup modal if complete

### 7. Files Modified/Created

**Created:**
- `resources/js/components/AccountSetupModal.vue` - New setup wizard component

**Modified:**
- `app/Http/Controllers/Api/AuthController.php` - Added setup detection and endpoints
- `routes/api.php` - Added setup routes
- `resources/js/components/adminlogin.vue` - Integrated setup modal into login flow

### 8. Database Considerations

No new tables created. Uses existing:
- `users` table: phone_number, address, latitude, longitude (already existed)
- `staff_documents` table: sss_id_path, philhealth_id_path, drug_test_result_path (already existed)

### 9. User Experience

New branch managers/default accounts now have a smooth onboarding:
1. Receive credentials to log in
2. First login triggers setup wizard
3. Guided through entering missing information step-by-step
4. Each step is clear and focused
5. Document uploads have drag-and-drop support
6. After completion, full access to dashboard
