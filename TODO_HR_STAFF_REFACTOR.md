# TODO: Manager HR Staff Management Refactor

## Objective
Refactor Manager HR Staff Management to have its own standalone page with proper routing, similar to Owner Staff Management.

## Steps Completed:
- [x] 1. Modify ManagerHRPanel.vue - Change button to use router.push
- [x] 2. Refactor ManagerHRStaffManagement.vue - Make standalone (no OwnerPanelLayout)

## Details:

### Step 1: ManagerHRPanel.vue ✓
- Changed "Staff Management" button from toggle to router navigation
- Removed inline staff management section (showStaffManagement)
- Added goToStaffManagement() function that navigates to /manager/hr/staff-management
- Kept dashboard stats cards and header

### Step 2: ManagerHRStaffManagement.vue ✓
- Removed OwnerPanelLayout wrapper - now standalone page
- Added back button to return to HR Dashboard (/manager/hr)
- UI structure similar to Owner Staff Management (table, search, modals)
- Uses existing API endpoint /api/manager/hr/staff

### Backend (Already Done - No Changes Needed)
- ManagerProfileController::hrStaff() already filters by branch_id

