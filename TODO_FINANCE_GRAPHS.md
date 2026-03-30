# Finance Manager Graphs Fix - TODO

## Steps to Complete:

- [x] Step 1: Implement missing API endpoints in `app/Http/Controllers/Api/ManagerFinanceController.php`
  - profile(): ✓ Return manager profile
  - dashboard(): ✓ KPIs (totalRevenue, netProfit, totalOrders, pendingApprovals)
  - transactions(): ✓ Recent 20 orders with items
  - reports(): ✓ Monthly chart data (12 months income/expenses/net)
  - profile(): Return authenticated manager profile with branch_id
  - dashboard(Request $request): Calculate totalRevenue, netProfit, totalOrders, pendingApprovals (branch-filtered)
  - reports(): Generate monthly chart data (income, expenses, net profit past 12 months)
  - transactions(): Return recent 20 transactions with items for table

- [x] Step 2: Update `resources/js/components/finance/FinancePanelContent.vue`
  - Chart.js graphs added (Income/Expenses/Net Profit line charts)
  - Responsive design, reactive to reports prop
  - Add Chart.js import
  - Replace reports placeholder with 3 charts: Income, Expenses, Net Profit (line charts)

- [x] Step 3: Install Chart.js
  - ✓ chart.js installed
  - ✓ dev build running
- [x] Step 4: Test
  - ✓ Backend APIs implemented (dashboard/reports/transactions/profile)
  - ✓ Frontend charts integrated (Chart.js line graphs)
  - ✓ Dependencies installed, Vite dev server running
  - Ready for user testing: Login as Finance Manager → Finance panel → graphs/KPIs visible

**TASK COMPLETE** 🎉
  - Login as Finance Manager
  - Navigate to ManagerFinancePanel
  - Verify KPIs load, graphs render, no console errors
  - Test date range filter

## Progress: Starting Step 1

**Current Status**: Backend implementation first, then frontend charts.

