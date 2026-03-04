# TODO: Refactor Status Display in OwnerStaffManagement.vue

## Objective
Make the Status badge accurately reflect whether a staff user is truly Online or Offline, instead of showing a static "Active" label.

## Steps Completed:
- [x] 1. Analyze codebase and understand current implementation
- [x] 2. Modify StaffController.php to add is_online to API response
- [x] 3. Modify OwnerStaffManagement.vue to display Online/Offline status

## Implementation Details:

### Backend Changes (StaffController.php):
- Added `isUserOnline()` helper method that checks Laravel sessions table
- Uses 5-minute threshold to determine if user is online
- Added `is_online` field to:
  - Branch Manager data
  - Staff data
  - HR data
  - Owner data

### Frontend Changes (OwnerStaffManagement.vue):
- Changed status text from "Active"/"Inactive" to "Online"/"Offline"
- Changed badge classes from `badge-active`/`badge-inactive` to `badge-online`/`badge-offline`
- Added CSS styles for new badge classes:
  - `badge-online`: Green background (#28a745), white text
  - `badge-offline`: Gray background (#6c757d), white text

