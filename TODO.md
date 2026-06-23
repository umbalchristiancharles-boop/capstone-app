# TODO - HR Open Positions Feature

- [x] Inspect existing positions schema/table (positions.sql / DB models / migrations)
- [x] Add DB migration + model for `position_open_requests` (position_id, branch_id, requested_by_user_id, quantity, notes, status)
- [x] Add API endpoints:
  - [x] GET `/api/hr/positions` (list active positions)
  - [x] POST `/api/hr/positions/requests` (create request; validate role + quantity)
- [ ] Update `resources/js/components/MainBranchHrPanel.vue`:
  - [ ] Add “Request Open Positions” button
  - [ ] Add modal popup listing positions
  - [ ] Add quantity + notes inputs per request
  - [ ] Wire up API calls + success/error handling
- [ ] Smoke test UI:
  - [ ] Modal opens
  - [ ] Positions list loads
  - [ ] Submitting creates DB record


