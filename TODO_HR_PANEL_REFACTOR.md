# HR Panel UI Refactor TODO

## Task
Refactor and clean the UI of ManagerHRPanel.vue only.

## Constraints
- Keep existing background as-is
- Use #FFF8F0 for sidebar elements
- Use #5D4037 for text accents
- DO NOT modify business logic, API calls, role permissions
- Preserve all Vue bindings and methods

## Files Edited
- [x] resources/js/components/ManagerHRPanel.vue - Bento stats grid
- [x] resources/js/components/hr/HrPanelContent.vue - Table headers, buttons, empty state
- [x] resources/js/css/adminpanel.css - New CSS styles

## Completed Changes
1. [x] Bento-style 3 stat cards (Total Staff, Active Staff, On Leave)
2. [x] Pill-shaped buttons (Refresh, + Add Staff in green)
3. [x] Clean search bar with icon
4. [x] Table headers: Name, Department, Position, Status, Actions
5. [x] Empty state with icon
6. [x] All Vue bindings preserved
7. [x] All business logic untouched
