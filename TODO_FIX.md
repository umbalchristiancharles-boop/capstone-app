# TODO: Fix POST /api/login 401 Unauthorized Issue

## Analysis Summary
- Issue: Manager/HR accounts cannot login (get 401) while Admin/Owner can
- Root Cause: Role validation happens AFTER Auth::attempt() but response might not reach frontend properly
- Additional issues: Missing Sanctum token creation, role handling

## Fix Plan - COMPLETED:

### 1. Fixed AuthController.php
- [x] Updated valid roles array to include all expected roles (ADMIN, OWNER, MANAGER, MANAGER_HR, HR, STAFF)
- [x] Added Sanctum token creation on successful login
- [x] Improved role handling for "manager_hr" (treat as MANAGER with HR department)
- [x] Updated getRedirectPath to handle MANAGER_HR role
- [x] Added better error handling in try-catch blocks

### 2. Fixed User.php model
- [x] Added Laravel\Sanctum\HasApiTokens trait to enable createToken() method

### 3. Fixed frontend adminlogin.vue
- [x] Added token storage from login response
- [x] Added handling for MANAGER_HR role in resolveRedirectPath
- [x] Improved error handling to show specific error messages from backend

### 4. Created helper script
- [x] Created fix_manager_password.php to reset must_change_password flag

## Files Modified:
1. app/Http/Controllers/Api/AuthController.php
2. app/Models/User.php
3. resources/js/components/adminlogin.vue

## Files Created:
1. fix_manager_password.php - Run this to fix users with must_change_password=1

## How to Test:
1. Run: php fix_manager_password.php to reset any users with must_change_password=1
2. Clear browser cookies and localStorage
3. Try logging in with admin, owner, manager, HR accounts
4. All should return 200 with user data and redirect properly

## Debugging Checklist:
- [ ] Verify hashed password in DB
- [ ] Confirm Laravel guards and role checking
- [ ] Inspect network request payload and headers
- [ ] Check CORS settings
- [ ] Test login for multiple roles (admin, owner, manager, manager_hr, hr, staff)
