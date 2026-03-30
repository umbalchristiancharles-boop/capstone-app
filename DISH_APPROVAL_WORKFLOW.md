# Dish Approval Workflow - Implementation Complete ✓

## Overview
Kitchen staff can now create dishes, but they won't appear in the logistics panel until the **Owner** confirms/approves them. This ensures proper inventory management and prevents unapproved dishes from triggering procurement requests.

## Workflow Process

### 1️⃣ Kitchen Staff Creates Dish
```
POST /api/staff/kitchen/dishes
{
  "name": "Grilled Chicken",
  "ingredients": [
    {
      "name": "Chicken Breast",
      "unit": "kg",
      "per_serving": 0.3,
      "product_id": null
    }
  ]
}
```
**Result**: Dish created with status `draft` and `approval_status: pending_approval`
- No placeholder products are created yet
- Not visible in logistics panel

### 2️⃣ Owner Reviews Pending Dishes
```
GET /api/owner/dishes/pending
```
**Response**: List of dishes awaiting approval

### 3️⃣ Owner Approves Dish
```
POST /api/owner/dishes/{dishId}/approve
{
  "notes": "Approved - ingredients sourced from Supplier XYZ"
}
```
**Result**:
- Dish status changes to `active`
- `approval_status: approved`
- Placeholder products created for ingredients
- Products flagged as `logistics_request_available: true`
- **Now visible in logistics panel**

### 4️⃣ Owner Rejects Dish (Optional)
```
POST /api/owner/dishes/{dishId}/reject
{
  "reason": "Recipe needs modification - too expensive"
}
```
**Result**: Dish marked as `inactive`, stays rejected

## Logistics & Procurement Flow After Approval

```
Kitchen Creates Dish (pending)
    ↓
Owner Approves ✓
    ↓
Placeholder Products Created
    ↓
Logistics Sees Ingredients in Panel
    ↓
Logistics Requests from Procurement
    ↓
If No Price/Supplier → Procurement Broadcasts to Suppliers
    ↓
Suppliers Submit Prices
    ↓
Procurement Creates Orders
```

## Key Features

✅ **No Unapproved Ingredients in Logistics** - Only approved dish ingredients appear  
✅ **Branch-scoped** - Owners only see/approve dishes from their branch  
✅ **Audit Trail** - `approved_by`, `approved_at`, `approval_notes` tracked  
✅ **Automatic Product Creation** - Placeholder products created on approval  
✅ **Procurement Integration** - Products automatically flagged for logistics  

## API Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/owner/dishes/pending` | List dishes awaiting approval |
| GET | `/api/owner/dishes/approved` | List approved dishes |
| POST | `/api/owner/dishes/{id}/approve` | Approve a dish |
| POST | `/api/owner/dishes/{id}/reject` | Reject a dish |

## Database Schema Changes

**dishes table** - Added columns:
- `approval_status` varchar - pending_approval / approved / rejected
- `approved_by` bigint - Foreign key to users table
- `approved_at` timestamp - When approved
- `approval_notes` text - Approval/rejection reason

## Testing Scenarios

### Scenario 1: Approve a Dish
1. Kitchen staff creates "Spicy Adobo" with Chicken, Soy Sauce, Vinegar
2. Owner sees it in pending list
3. Owner approves with notes
4. Logistics panel now shows Chicken, Soy Sauce, Vinegar with `logistics_request_available: true`
5. Procurement creates requests for items with missing suppliers/prices

### Scenario 2: Reject a Dish
1. Kitchen staff creates "New Item" 
2. Owner reviews ingredients - too expensive/unavailable
3. Owner rejects with reason "Budget constraints"
4. Item stays in draft, not visible to logistics
5. Kitchen staff can edit and resubmit

## Admin Notes

- Old pending dishes must be manually approved/rejected after migration
- Each branch owner can manage dishes independently
- Kitchen staff cannot bypass approval - dishes created with `pending_approval` status
- Logistics filter automatically applied in ProcurementProductController
