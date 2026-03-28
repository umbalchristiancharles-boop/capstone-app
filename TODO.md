# Fix Custom Panel Logistics 401 Unauthorized

## Steps:
- [ ] 1. Create TODO.md ✅
- [x] 2. Edit resources/js/components/CustomPanel.vue: Store token from /api/me response in localStorage ✅
- [x] 3. Update TODO.md after edit ✅
- [ ] 4. Test: Login custom user → navigate to /manager/logistics → verify APIs load without 401 (check Network tab for Authorization: Bearer header)
- [ ] 5. Complete task

**Root cause:** Custom users lack Bearer token for sanctum APIs. Fix ensures token storage after /api/me.

**Status:** CustomPanel.vue fixed. Test step 4 manually:
- Login custom user (with logistics permission)
- Visit http://localhost/capstone-app/public/custom-panel (adjust port)
- Click Logistics → verify no 401 in console/Network tab

Run `npm run dev` + refresh if needed.


