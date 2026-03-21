# TODO: Staff Finance Panel UI Sync with SuperAdminFinance

## Plan Breakdown (Approved)
- Transform StaffFinancePanel.vue to standalone dashboard layout matching SuperAdminFinance.vue
- Preserve functions: Keep /api/staff/finance/logs fetch, derive KPIs from logs client-side
- KPIs: total revenue, total orders, total expenses, total refunds, net profit (computed from log descriptions/amounts)
- Branch performance: Single row for staff's branch (mock from logs)
- No new API calls, no backend changes

## Steps
- [ ] Step 1: Backup current StaffFinancePanel.vue content
- [ ] Step 2: Rewrite StaffFinancePanel.vue with SuperAdmin template structure adapted for logs/KPIs
- [ ] Step 3: Implement log-based KPI computations (filter/sum by keywords)
- [ ] Step 4: Add logs table matching SuperAdmin transactions table
- [ ] Step 5: Copy/adapt all CSS from SuperAdminFinance to .staff-finance
- [ ] Step 6: Test layout/responsiveness
- [ ] Step 7: attempt_completion

**Progress: Step 1 COMPLETE** - Backup created as StaffFinancePanel_backup.vue

**Next: Step 2**


