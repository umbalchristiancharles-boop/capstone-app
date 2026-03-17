# Color Scheme Application TODO
Status: [In Progress] - Apply SuperAdmin.vue colors to all Vue components (visual only)

## Breakdown of Approved Plan (Step-by-step)

### Step 1: Create this TODO.md [✅ COMPLETE]

### Step 2: Update core shared CSS (adminpanel.css)
- Add/enhance rules for avatar-change-text: #0066FF
- Ensure global h1/h2 selectors are robust
- Status: [✅ COMPLETE]

### Step 3: Batch Edit Priority Files (Open Tabs/High Impact)
Edit these 10 first (batch via multiple edit_file):
1. ✅ resources/js/components/adminpanel.vue - Update h1 colors, btn-primary/secondary
2. ✅ resources/js/components/HRStaffManagement.vue - Headers, buttons, avatars
3. ✅ resources/js/components/StaffManagement.vue 
4. ✅ resources/js/components/OwnerStaffManagement.vue
5. ✅ resources/js/components/SuperAdminStaffManagement.vue 
6. ✅ resources/js/components/SuperAdminFinance.vue
7. ✅ resources/js/components/finance/FinancePanelContent.vue
8. ✅ resources/js/components/finance/FinanceLogsPanel.vue
9. ✅ resources/js/components/inventory/InventoryStaffPanel.vue
10. ✅ resources/js/components/ManagerHRStaffManagement.vue
- For each: Replace orange/black h1 styles → #0066FF blue, buttons to blue/gray
- Status: [✅ COMPLETE]

### Step 4: Search & Edit Remaining Components
- Use search_files("resources/js/components", "h1.*color|btn-primary|avatar-overlay|orange", "*.vue")
- Batch edit 10-20 files at a time (patterns identical across staff panels)
- Targets: hr/, finance/, inventory/, cashier/, logistics/, Manager*.*, Owner*.*, Staff*.*
- Status: [Pending]

### Step 5: Verify & Test
- execute_command: npm run dev
- Test key pages: /super-admin-panel, /staff-management, etc.
- Check headers blue, buttons blue/gray, avatars blue text
- Status: [Pending]

### Step 6: Finalize
- Update TODO.md: Mark steps ✅
- attempt_completion with results + demo command
- Status: [Pending]
