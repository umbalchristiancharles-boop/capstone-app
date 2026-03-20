# Messaging 401 Fix - Steps

## 1. Create TODO.md [DONE]

## 2. Edit MessageWidget.vue [DONE]
- Add redirect to /staff-landing + localStorage.clear() on /me 401 ✓
- Disable retries/polling after unauth ✓
- 401 handling on fetch/load/send ✓
- Ensure sender account shows correctly [user request] ✓ (already m.from_user.name)

## 3. Verify/create HR test user
- Check `create_hr_user.php` or seed DB
- Ensure is_active=1

## 4. Clear caches
- php artisan route:clear config:clear

## 5. Test
- Login → /manager/hr → no 401 errors
- Send message → shows sender account name
- Logout → redirect, no lingering errors
- Navigate back → proper login flow

## 6. [ ] Complete & attempt_completion

