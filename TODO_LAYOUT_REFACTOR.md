# Layout Refactor Task: Staff Profile & Attendance in ProductList

## Objective
Move the entire `<aside class="pl-stats">` (profile + attendance section) inside the main `<div class="pl-container">` layout of ProductList.vue and integrate it properly into the container structure.

## Files Edited

### 1. ProductList.vue ✅
- [x] Updated `.pl-root` grid layout to `grid-template-columns: 320px 1fr`
- [x] Added slots: `#profile`, `#attendance`, `#stats` for left panel content
- [x] Added responsive styles for mobile: `grid-template-columns: 1fr`
- [x] Added `.pl-left-panel` and `.pl-right-column` structure
- [x] Kept existing table/product functionality unchanged

### 2. InventoryStaffPanel.vue ✅
- [x] Removed `<aside class="pl-stats">` from `.pl-content`
- [x] Passed profile data to ProductList via `#profile` slot
- [x] Passed attendance data to ProductList via `#attendance` slot
- [x] Passed stats data to ProductList via `#stats` slot
- [x] Simplified layout - now just ProductList with slots inside

## Implementation Details

### Desktop Layout:
```
+---------------------------+----------------------------------+
| Profile/Attendance Panel | Product List                    |
| (320px fixed)            | (flex: 1)                       |
| - Avatar/Name             | - Header/Search                 |
| - Account ID/QR           | - Table/Cards                   |
| - Attendance Clock        | - Pagination                    |
| - Stats Cards             |                                  |
+---------------------------+----------------------------------+
```

### Mobile Layout:
```
+----------------------------------+
| Profile/Attendance (stacked top) |
+----------------------------------+
| Product List                     |
| - Table (scrollable)             |
+----------------------------------+
```

## Preserved (No Changes)
- All Vue conditions (v-if, v-show)
- Attendance button states and restrictions
- Clock in/out logic and time restrictions
- Profile data bindings
- Logout functionality
- API calls (fetchProducts, etc.)
- Pagination logic
- Role conditions
- All modal functionality

## Styling
- Uses existing theme variables (colors from current CSS)
- Maintained glassmorphism/card style
- Responsive breakpoints at 880px

## Status: COMPLETED ✅
