# TODO: Fix Staff Inventory Panel Warnings

## Task
Fix warnings in Staff Inventory Panel edit info section

## Plan:
- [x] 1. Wrap password inputs in OwnerPanelLayout.vue with a <form> element
- [x] 2. Remove automatic /sanctum/csrf-cookie call from bootstrap.js

## Files Edited:
- resources/js/components/OwnerPanelLayout.vue
- resources/js/bootstrap.js

## Summary:
1. ✅ Wrapped password inputs in a `<form>` element to fix DOM warnings
2. ✅ Removed automatic CSRF cookie call from bootstrap.js to prevent repeated console messages

## Not Modified (as requested):
- Avatar/profile picture functionality
- Attendance API calls functionality

## Completed:
- [x] Understanding the codebase and identifying the issues
- [x] Fix DOM warnings by wrapping password inputs in form
- [x] Fix repeated Axios CSRF requests by removing automatic call
