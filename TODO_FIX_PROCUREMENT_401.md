# Fix Procurement 401 Errors in ManagerLogisticsPanel

## Status: ✅ ALL STEPS COMPLETED

### Steps Completed:
- [x] Updated `ProcurementRequestController.php` - Flexible role/dept auth for logistics managers
- [x] Added `logisticsProducts()` method + route in `ManagerProfileController.php`
- [x] Updated `ManagerLogisticsPanel.vue` - Uses `/api/manager/logistics/products`
- [x] Added route `/api/manager/logistics/products` in `routes/api.php`

### Test:
1. Login as logistics manager
2. Verify `/manager/logistics` loads without 401s
3. Products list loads, procurement requests work

### Next: 
- `php artisan route:cache`
- Test in browser
