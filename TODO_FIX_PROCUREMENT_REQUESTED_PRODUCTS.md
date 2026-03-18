# Fix Procurement Requested Products 500 Error

## Status: Planning

**Issue**: GET /api/procurement-requests/requested-products returns 500 from ProcurementManagerPanel.vue

**Root Cause Analysis**:
- Route and controller exist
- 5 pending ProcurementRequest records exist
- Logged in as procurement_br001467 (MANAGER/PROCUREMENT)
- No recent error logs (old migration/DB issues fixed)
- Likely Eloquent relation failure in `ProcurementRequest::with(['product'])` or Product query

**Files to Edit**:
1. `app/Http/Controllers/Api/ProcurementRequestController.php` - Add try-catch/logging to `requestedProducts()`

**Plan Steps**:
1. [x] Add logging to controller method
2. [x] Fix role check for MANAGER/PROCUREMENT
3. [ ] Test endpoint 
4. [ ] Fix relation/query issue
4. [ ] Update frontend error handling
5. [ ] Test in UI

**Tests**:
- Login as PROCUREMENT_MANAGER
- Load ProcurementManagerPanel
- Verify API returns products without 500

