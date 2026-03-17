    # TODO: Sync Admin Staff Management UI with Owner Staff Management

## Task
Make the Admin staff management (accessed via `/staff-management`) have the same design, UI, and functionality as the Owner's staff management (`/owner/staff-management`).

## Current State
- **OwnerStaffManagement.vue**: Beautiful orange gradient UI, grouped by branch, uses OwnerStaffModal
- **StaffList.vue**: Different white-card design, grouped by branch, uses StaffModal

## Plan
1. [ ] Update StaffList.vue to use the same design/UI as OwnerStaffManagement.vue
    - Orange gradient background
    - Branch-grouped layout with branch headers
    - Same header with search and filters
    - Same table styling
2. [ ] Keep using StaffModal for admin-specific functionality
3. [ ] Update back button navigation to go to admin-panel
4. [ ] Verify all functionality works correctly

## Files to Edit
- `resources/js/components/StaffList.vue`

## Followup Steps
- Test the staff management page
- Verify filters, search, add/edit staff work correctly
- Verify navigation works properly

