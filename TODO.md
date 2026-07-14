# TODO - Inventory expiry per order (Supplier -> Inventory)

## Step 1: Inventory model of expiry
- [ ] Update server/client so Staff Inventory expiry indicators do NOT use `products.expires_at` for supplier-order stock.
- [ ] Make inventory expiry display depend on `supplier_orders.expires_at` tied to the procurement/delivery flow.


## Step 2: Fix override issue
- [ ] Ensure that when another supplier order is created for the same product, it does not overwrite prior order expiry.
- [ ] Add/adjust server logic so expiry is stored only at `supplier_orders.expires_at` (and later, at confirmation time we persist it for that delivered batch).

## Step 3: Supplier enters expiry at transaction complete
- [x] (Plan confirmed) expiry entry should occur at “Transaction complete”.
- [ ] Update SupplierPanel UI so expiry is collected at the “Transaction complete / accept” step.
- [ ] Enforce expiry required when the supplier completes the transaction.



## Step 4: Pass expiry into procurement completion
- [x] Persist delivered expiry per batch/line item by creating `inventory_lots` inside `StaffInventoryController@confirmProcurementStock`.


## Step 5: Validate end-to-end
- [ ] Run through flow:
  1) supplier submits product -> no overriding
  2) supplier completes transaction -> expiry stored
  3) inventory panel shows correct expiry per delivered batch
- [ ] Fix any API shape mismatches in Vue components.

