# Dish Creation Workflow - Before & After

## ❌ BEFORE: Direct Logistics Creation

```
Kitchen Staff Creates Dish
    ↓
Dish Status: ACTIVE (immediately)
    ↓
Placeholder Products Created (IMMEDIATELY)
    ↓
Products Flagged: logistics_request_available = true
    ↓
LOGISTICS PANEL SHOWS INGREDIENTS → Logistics Creates Requests
    ↓
Procurement Sees Products with Missing Suppliers
    ↓
Procurement Broadcasts to Suppliers
```

**Problem**: No owner oversight → Unapproved recipes could generate unnecessary procurement requests

---

## ✅ AFTER: Owner Confirmation Required

```
Kitchen Staff Creates Dish
    ↓
Dish Status: DRAFT
Approval Status: PENDING_APPROVAL
    ↓
NO Products Created Yet
    ↓
NOT YET in Logistics Panel
    ↓
OWNER REVIEWS & APPROVES ✓
    ↓
Dish Status: ACTIVE
Approval Status: APPROVED
    ↓
Placeholder Products Created (on approval)
    ↓
Products Flagged: logistics_request_available = true
    ↓
LOGISTICS PANEL SHOWS INGREDIENTS → Logistics Creates Requests
    ↓
Procurement Sees Products with Missing Suppliers
    ↓
Procurement Broadcasts to Suppliers
```

**Solution**: Owner gate ensures only approved recipes generate procurement requests

---

## Key Changes by Component

### Kitchen Controller (`KitchenDishController`)
| Aspect | Before | After |
|--------|--------|-------|
| Dish Status | `active` | `draft` |
| Approval Status | N/A | `pending_approval` |
| Ingredients | Products created immediately | No products yet |
| Visibility | Visible in logistics | NOT visible |

### Owner Dashboard (`OwnerDashboardController`)
| Aspect | Before | After |
|--------|--------|-------|
| Approval Endpoints | None | New endpoints added |
| Route `/api/owner/dishes/pending` | ❌ | ✅ |
| Route `/api/owner/dishes/approved` | ❌ | ✅ |
| Approve Action | ❌ | ✅ |
| Reject Action | ❌ | ✅ |

### Procurement Products (`ProcurementProductController`)
| Aspect | Before | After |
|--------|--------|-------|
| Kitchen Dish Filtering | None | Checks `approval_status` |
| Unapproved Products | Shown | Filtered out |
| Logic | All `logistics_request_available` shown | Only from approved dishes |

### Database
| Aspect | Before | After |
|--------|--------|-------|
| `approval_status` | ❌ | ✅ (new column) |
| `approved_by` | ❌ | ✅ (new column) |
| `approved_at` | ❌ | ✅ (new column) |
| `approval_notes` | ❌ | ✅ (new column) |

---

## Example Scenarios

### Scenario A: Approve Flow
```
1. Kitchen: Creates "Crispy Taters" dish with Potatoes, Oil, Salt
2. Status: draft | approval_status: pending_approval
3. Logistics: Can't see "Potatoes", "Oil", "Salt"
4. Owner: GET /api/owner/dishes/pending → sees "Crispy Taters"
5. Owner: POST /api/owner/dishes/123/approve → Approves with notes
6. Status: active | approval_status: approved | approved_by: 28 | approved_at: now()
7. Logistics: NOW sees three new products flagged for request
8. Logistics: Can now request from Procurement
```

### Scenario B: Reject + Resubmit Flow
```
1. Kitchen: Creates "Expensive Steak" with Wagyu Beef, Truffle Oil
2. Owner: Reviews → Too costly
3. Owner: POST /api/owner/dishes/456/reject → Rejects with reason "Budget exceeded"
4. Status: inactive | approval_status: rejected | approval_notes: "REJECTED: Budget exceeded"
5. Logistics: Items never appeared (owner rejected first)
6. Kitchen: Updates recipe with cheaper alternatives
7. Kitchen: Resubmit as NEW dish with adjusted ingredients
8. Owner: Approves new version
```

---

## For Frontend Developers

Your kitchen panel should now:
1. Show dishes with `approval_status` field
2. Allow kitchen staff to create dishes (status warning: pending approval)
3. Show status: "Awaiting Owner Approval" for pending dishes
4. Disable production until approved (or allow with warning)

Your owner panel should now:
1. Have a "Pending Approval" section showing new dishes
2. Show dish details with ingredients list
3. Have "Approve" and "Reject" buttons
4. Allow optional approval notes
5. Show "Approved Dishes" section with audit trail

Your logistics/procurement panel should:
1. Filter out unapproved kitchen dish ingredients automatically
2. Show status badge: "Approved" for confirmed dishes
3. Display only products from approved dishes
