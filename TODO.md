# TODO: Create Separate SuperAdminStaffManagement Component

## Task Summary
Create a separate SuperAdminStaffManagement.vue component by copying HRStaffManagement.vue, and add the corresponding route.

## Steps to Complete:

### Step 1: Create SuperAdminStaffManagement.vue
- [x] Copy HRStaffManagement.vue to SuperAdminStaffManagement.vue
- [x] Keep all functions, methods, and logic exactly the same (no modifications)

### Step 2: Update Router
- [x] Add route `/super-admin/hr` in router/index.js
- [x] Import SuperAdminStaffManagement.vue component
- [x] Set meta: { requiresAuth: true, role: 'superadmin' }

### Step 3: Verify
- [x] SuperAdmin panel uses SuperAdminStaffManagement.vue (via /super-admin/hr route)
- [x] HR panel continues to use HRStaffManagement.vue
- [x] All functionality remains identical

