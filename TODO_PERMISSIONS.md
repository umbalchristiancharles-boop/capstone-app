# Permissions mapping and next steps

## Module/function catalog (keep backend + frontend in sync)
- Modules: admin, finance, logistics, inventory, procurement, kitchen, cashier, hr, reports
- Functions:
  - admin: admin.users, admin.branches, admin.settings
  - finance: finance.dashboard, finance.budget, finance.reports, finance.expenses
  - logistics: logistics.dispatch, logistics.receiving, logistics.transfers
  - inventory: inventory.products, inventory.counts, inventory.adjustments
  - procurement: procurement.purchase_orders, procurement.suppliers, procurement.approvals
  - kitchen: kitchen.orders, kitchen.production, kitchen.waste
  - cashier: cashier.pos, cashier.refunds, cashier.shifts
  - hr: hr.attendance, hr.scheduling, hr.payroll
  - reports: reports.sales, reports.inventory, reports.finance

## Applied guards
- Middleware alias `permission` (EnsurePermission): supports params `module1,module2,fn:function.key`.
- Superadmin branches routes now require modules: admin (list), admin+admin.branches (create/delete).
- AdminOnly/AdminMiddleware updated to allow CUSTOM with module admin.
- SuperAdminController profile/update/avatar/dashboard/storeBranch/deleteBranch/announcements now accept CUSTOM when matching modules.
- StaffController create path uses Permission helper: CUSTOM with admin.users or hr can create within allowed tiers; branch-bound restrictions enforced for CUSTOM.
- Announcements visibility: CUSTOM with module admin sees all; CUSTOM with manager-like modules (finance/logistics/inventory/procurement/kitchen/cashier/hr) treated as managers; others as staff.

## Remaining actions (recommended)
1) Apply `permission` middleware to other superadmin/finance/logistics/cashier routes (pick correct modules/functions per route).
2) Apply `permission` middleware or controller checks to manager-facing APIs (finance/logistics/inventory/procurement/kitchen/cashier/hr) and web routes.
3) Frontend: gate navigation/menus/pages using the same modules/functions to hide blocked areas for CUSTOM accounts.
4) Confirm final module/function list with product; update both frontend template and backend allowedFunctions accordingly.
5) Tests: add feature tests covering CUSTOM user with specific module/function can/cannot hit routes; announcement visibility cases (custom-admin, custom-manager, custom-staff).
