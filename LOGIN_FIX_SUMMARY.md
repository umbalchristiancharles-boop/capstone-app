# Login Fix Summary - Role-Based Auto-Logout Issue

## Problem
- Login works for Owner, Admin, and Staff roles
- Manager roles (HR, Finance, Inventory, Logistics) get auto-logged out a few seconds after login

## Root Cause Found
The Manager API routes in `routes/api.php` were **missing authentication middleware**. While the routes were protected by the frontend Vue router, they were not protected on the backend. This caused:
1. Unauthenticated API calls from Manager panels returning unexpected responses
2. Frontend detecting HTML responses (from redirect to login) and triggering auto-logout

## Fix Applied

### 1. Updated routes/api.php
Added `auth` middleware to all Manager and Staff routes:

```
php
// BRANCH MANAGER API - Protected routes requiring authentication
Route::prefix('manager')->group(function () {
    Route::middleware(['auth'])->group(function () {
        // All manager routes now require authentication
        Route::get('/dashboard', [ManagerDashboardController::class, 'index']);
        // ... other routes
    });
});

// STAFF API - Protected routes requiring authentication  
Route::prefix('staff')->group(function () {
    Route::middleware(['auth'])->group(function () {
        // All staff routes now require authentication
        Route::get('/dashboard', [StaffDashboardController::class, 'index']);
        // ... other routes
    });
});
```

### 2. The Authenticate Middleware
The custom `app/Http/Middleware/Authenticate.php` already handles:
- JSON 401 responses for API requests (`/api/*`)
- Session-based authentication using `Session::has('user_id')`

## How It Works Now

1. **Login**: User logs in via `/api/login`, session is created with `user_id`
2. **Session Check**: Each API request checks `Session::has('user_id')`
3. **Valid Session**: Request proceeds normally
4. **Invalid Session**: Returns JSON 401 with message "Unauthenticated"

## Files Modified
- `routes/api.php` - Added auth middleware to Manager and Staff routes

## Testing Checklist

### Test Login for Each Role:
- [ ] Owner - Login -> Dashboard works, no auto-logout
- [ ] Admin - Login -> Dashboard works, no auto-logout  
- [ ] Manager HR - Login -> /manager/hr works, no auto-logout
- [ ] Manager Finance - Login -> /manager/finance works, no auto-logout
- [ ] Manager Inventory - Login -> /manager/inventory works, no auto-logout
- [ ] Manager Logistics - Login -> /manager/logistics works, no auto-logout
- [ ] Staff - Login -> Staff panel works, no auto-logout

### Network Verification:
1. Open browser DevTools -> Network tab
2. Login as Manager
3. Make a request to `/api/manager/inventory/profile`
4. Verify response is JSON (not HTML redirect)
5. Verify status is 200 (not 401)

## Adding New Roles in Future

When adding new roles (e.g., "SUPERVISOR"):

1. **Database**: Add role to users table
2. **Frontend** (resources/js/app.js): Add route guard check
3. **Backend** (routes/api.php): Add new route group with auth middleware

## Debug Commands

Test API login:
```
bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"charles","password":"ChikinTayo_2526"}' \
  -c cookies.txt -b cookies.txt
```

Test authenticated route:
```
bash
curl http://localhost:8000/api/manager/inventory \
  -H "Accept: application/json" \
  -b cookies.txt
