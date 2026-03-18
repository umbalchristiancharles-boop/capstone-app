# Order Cancel Fix - Pending Order Flow
## Status: [IN PROGRESS]

### Step 1: [DONE] Create TODO.md with steps ✅
Breakdown approved plan.

### Step 2: DB Migration - Add pending workflow fields
- Add `is_cancelled boolean default 0` to orders table
- Add `cancelled_at timestamp nullable`
- Add `approved_at timestamp nullable` 
- Add `approved_by int nullable` (user_id)

### Step 3: Update Order Model & Checkout Controller
- Checkout: status='pending', NO stock deduction
- Add cancelPendingOrder() method (set is_cancelled=1 or status='cancelled')

### Step 4: Frontend Updates
- Cashier.vue/StaffCashierPanel.vue: clearCart → POST /api/cashier/cancel-pending (if pending_order_id)
- Track pending_order_id in local state
- Checkout → create pending → show pending order code

### Step 5: New Endpoints & Routes
- POST /api/cashier/cancel-pending
- GET /api/cashier/pending-orders (for finance/managers)

### Step 6: Finance Panel Integration
- Show pending orders in ManagerFinancePanel, SuperAdminFinance
- Approve button → status='approved', deduct stock?

### Step 7: Test & Cleanup
- Test full flow: place→cancel (clear)→no stock deduct
- Migrate, test production

**Next:** Run migration for DB changes.

