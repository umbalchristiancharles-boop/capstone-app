# TODO: Restrict Staff/User Profile Editing Permissions

## Task: Staff should ONLY be able to edit password, NOT name/email/other info

### Backend Changes:
- [x] 1. app/Http/Controllers/Staff/StaffInventoryController.php
  - [x] Add role check in updateProfile() - only allow password update for staff
  - [x] Fix password hashing using Hash::make()
  - [x] Add contact field to profile response

- [x] 2. app/Http/Controllers/Staff/StaffProfileController.php
  - [x] Add role check in updateProfile() - only allow password update for staff
  - [x] Add contact field to profile response

### Frontend Changes:
- [x] 3. resources/js/components/OwnerPanelLayout.vue
  - [x] Add canEditProfile prop (default: true)
  - [x] Make profile fields readonly when canEditProfile=false
  - [x] Hide "Edit information" button and show "Change Password" when canEditProfile=false
  - [x] Keep password change functionality working

- [x] 4. resources/js/components/StaffInventoryPanel.vue
  - [x] Pass :canEditProfile="false" to OwnerPanelLayout

- [x] 5. resources/js/components/StaffFinancePanel.vue
  - [x] Pass :canEditProfile="false" to OwnerPanelLayout

- [x] 6. resources/js/components/StaffCashierPanel.vue
  - [x] Pass :canEditProfile="false" to OwnerPanelLayout

### Notes:
- DO NOT modify Owner functionality - canEditProfile defaults to true
- DO NOT modify avatar upload
- DO NOT modify attendance API
- Manager panels (ManagerHRPanel, ManagerFinancePanel, ManagerLogisticsPanel, ManagerInventoryPanel) should NOT have canEditProfile set (they will use default true)

### Issues Fixed:
- Password hashing now uses Hash::make()
- Contact field now properly returned from backend
- Staff can only change password, not other profile fields
