# Staff Management UI Sync Plan

## Task
Make the Admin Staff Management (StaffManagement.vue) have the same layout, UI, and functions as the Owner Staff Management (OwnerStaffManagement.vue).

## Current State

### OwnerStaffManagement.vue (SOURCE - Good UI)
- Orange gradient background (#ff8c42 to #ff6b1c)
- Uses OwnerStaffModal.vue for add/edit
- Branch grouping with headers
- Rich filters (search, branch, role, department)
- Role priority sorting
- Modern orange-themed styling

### StaffManagement.vue (TARGET - Needs Update)
- Basic gray/white UI
- Uses inline modal
- Basic table without branch grouping
- Different styling

## Implementation Plan

### Step 1: Update Template Structure
- Add "Back to Dashboard" button (route: /admin-panel)
- Add branch grouping structure (groupedStaff computed)
- Use OwnerStaffModal component

### Step 2: Update Script
- Import OwnerStaffModal
- Keep admin-specific logic (isBranchManager, currentUserRole)
- Add groupedStaff computed property
- Keep reset password functionality

### Step 3: Update Styling
- Copy orange gradient background
- Copy card styling
- Copy table styling
- Copy modal styling (will be handled by OwnerStaffModal)

## Files to Modify
- resources/js/components/StaffManagement.vue

## Dependencies
- OwnerStaffModal.vue (already exists)
- AddressCascader.vue (already exists)

