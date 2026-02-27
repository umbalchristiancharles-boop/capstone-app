# Admin Dashboard Refactoring TODO

## Backend (DashboardController.php)
- [ ] Add date range filtering (today, yesterday, thisWeek, lastWeek, thisMonth, lastMonth)
- [ ] Add sales, completed, pending order counts
- [ ] Add recent orders with details
- [ ] Add production queue (in_kitchen orders)
- [ ] Add top products by order count
- [ ] Add low stock items

## Frontend (adminpanel.vue)
- [ ] Update loadDashboard to use date range params
- [ ] Map backend data to UI properly
- [ ] Ensure reactive updates

## Modular Design
- [ ] Create composable for dashboard data fetching
- [ ] Make extensible for future data sources
