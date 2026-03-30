# Price Markup UI Components - Integration Guide

## Files Created

### 1. PriceMarkupManagerPanel.vue
**Location:** `resources/js/components/finance/PriceMarkupManagerPanel.vue`
**For:** Finance Manager of any branch

**Features:**
- Display current markup percentage
- Request form to propose percentage changes
- Shows pending request status
- Real-time multiplier preview
- Auto-refreshes every 30 seconds

### 2. PriceMarkupMainFinancePanel.vue
**Location:** `resources/js/components/finance/PriceMarkupMainFinancePanel.vue`
**For:** Main Branch Finance Manager (Organization level)

**Features:**
- View all pending requests
- Approve/reject with optional notes
- Shows complete request workflow
- Different status indicators for each stage
- Shows why change was requested + previous approvals

### 3. PriceMarkupOwnerPanel.vue
**Location:** `resources/js/components/finance/PriceMarkupOwnerPanel.vue`
**For:** Owner (Final Authority)

**Features:**
- Two tabs: Awaiting Approval & History
- Timeline view of approval workflow
- Shows all previous approvals
- Approve/reject with notes
- Full approval history with activation dates

---

## Integration Steps

### Step 1: Import Components in Your Panel Files

**In ManagerFinancePanel.vue:**
```vue
<script setup>
import PriceMarkupManagerPanel from '@/components/finance/PriceMarkupManagerPanel.vue'

// ... rest of component setup
</script>

<template>
  <OwnerPanelLayout ...>
    <template #main>
      <!-- Existing content -->
      
      <!-- Add Price Markup Section -->
      <div class="panel-section">
        <h2 class="section-title">Price Markup Management</h2>
        <PriceMarkupManagerPanel :branchId="userProfile.branch_id" />
      </div>
    </template>
  </OwnerPanelLayout>
</template>
```

**In MainBranchFinancePanel.vue:**
```vue
<script setup>
import PriceMarkupMainFinancePanel from '@/components/finance/PriceMarkupMainFinancePanel.vue'

// ... rest of component setup
</script>

<template>
  <div>
    <!-- Existing content -->
    
    <!-- Add Price Markup Section -->
    <section class="panel-section">
      <PriceMarkupMainFinancePanel />
    </section>
  </div>
</template>
```

**In OwnerPanel.vue:**
```vue
<script setup>
import PriceMarkupOwnerPanel from '@/components/finance/PriceMarkupOwnerPanel.vue'

// ... rest of component setup
</script>

<template>
  <OwnerPanelLayout ...>
    <template #main>
      <!-- Existing content -->
      
      <!-- Add Price Markup Section -->
      <div class="panel-section">
        <PriceMarkupOwnerPanel />
      </div>
    </template>
  </OwnerPanelLayout>
</template>
```

### Step 2: Add Routes (if using separate pages)

In `resources/js/components/router/index.js`:

```javascript
// Price Markup Management Pages
{
  path: '/manager/finance/price-markup',
  component: () => import('@/components/finance/PriceMarkupManagerPanel.vue'),
  meta: { requiresAuth: true, roles: ['FINANCE_MANAGER', 'MANAGER'] }
},
{
  path: '/main-branch/finance/markup-approvals',
  component: () => import('@/components/finance/PriceMarkupMainFinancePanel.vue'),
  meta: { requiresAuth: true, roles: ['MANAGER'] }
},
{
  path: '/owner/markup-approvals',
  component: () => import('@/components/finance/PriceMarkupOwnerPanel.vue'),
  meta: { requiresAuth: true, roles: ['OWNER'] }
}
```

### Step 3: Add Menu Items (Optional)

**For Finance Manager:**
```html
<button @click="router.push('/manager/finance/price-markup')" class="menu-item">
  📊 Price Markup Settings
</button>
```

**For Main Finance Manager:**
```html
<button @click="router.push('/main-branch/finance/markup-approvals')" class="menu-item">
  ✓ Markup Approvals ({{ pendingMarkupCount }})
</button>
```

**For Owner:**
```html
<button @click="router.push('/owner/markup-approvals')" class="menu-item">
  🔏 Markup Approvals
</button>
```

---

## Component Props

### PriceMarkupManagerPanel
```javascript
props: {
  branchId: {
    type: Number,
    required: true  // Pass user's branch ID
  }
}
```

### PriceMarkupMainFinancePanel
```javascript
props: {
  branchId: {
    type: Number,
    default: null  // Optional - shows all branches if null
  }
}
```

### PriceMarkupOwnerPanel
No required props - shows all organizations requests

---

## Toast/Alert Integration

All three components support the global `window.showToast()` method. If you use a toast library (like Pinia or custom), make sure to expose it:

```javascript
// In your main.js or app initialization
window.showToast = (message, type = 'info') => {
  // Call your toast function
  toast.show({
    message: message,
    type: type  // 'success', 'error', 'warning', 'info'
  })
}
```

---

## Styling & Tailwind

All components use:
- **Tailwind CSS** utility classes
- **Color scheme:** 
  - Primary: `#059669` (Green)
  - Secondary: `#6B7280` (Gray)
  - Success: `#10B981` (Green)
  - Error: `#DC2626` (Red)
  - Warning: `#F59E0B` (Amber)

Components are fully responsive and work on mobile, tablet, and desktop screens.

---

## API Endpoints (Backend)

All components use these endpoints (already implemented):

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/price-markup/current/{branchId?}` | Get current percentage |
| POST | `/api/price-markup/request` | Submit change request |
| GET | `/api/price-markup/pending/{branchId?}` | Get pending requests |
| POST | `/api/price-markup/{id}/main-finance-approve` | Main finance approval |
| POST | `/api/price-markup/{id}/owner-approve` | Owner approval |
| GET | `/api/price-markup/history/{branchId}` | Get approval history |

---

## Usage Example - Complete Integration

```vue
<template>
  <div class="finance-dashboard">
    <!-- Existing Finance Content -->
    <div class="dashboard-grid">
      <!-- KPIs, Charts, etc. -->
    </div>

    <!-- Price Markup Section -->
    <section class="price-markup-section border-t pt-8 mt-8">
      <h2 class="text-2xl font-bold mb-4">🎯 Price Management</h2>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- For Finance Manager -->
        <PriceMarkupManagerPanel 
          v-if="userRole === 'FINANCE_MANAGER'"
          :branchId="userBranchId"
        />

        <!-- For Main Finance Manager -->
        <PriceMarkupMainFinancePanel 
          v-if="userRole === 'MANAGER' && isMainBranch"
        />

        <!-- For Owner -->
        <PriceMarkupOwnerPanel 
          v-if="userRole === 'OWNER'"
        />
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuth } from '@/composables/useAuth'
import PriceMarkupManagerPanel from '@/components/finance/PriceMarkupManagerPanel.vue'
import PriceMarkupMainFinancePanel from '@/components/finance/PriceMarkupMainFinancePanel.vue'
import PriceMarkupOwnerPanel from '@/components/finance/PriceMarkupOwnerPanel.vue'

const { userProfile } = useAuth()

const userRole = computed(() => userProfile.value?.role)
const userBranchId = computed(() => userProfile.value?.branch_id)
const isMainBranch = computed(() => userProfile.value?.branch?.is_main_branch)
</script>

<style scoped>
.price-markup-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-top: 32px;
}
</style>
```

---

## Testing the Components

### Manual Testing

1. **Finance Manager:**
   - Navigate to Finance → Price Markup
   - Enter a new percentage (e.g., 25%)
   - Submit the request
   - Check that it appears as "Pending" in Main Finance panel

2. **Main Finance Manager:**
   - See the request in "Pending Approvals"
   - Add approval notes
   - Click "Approve"
   - Request should move to "Awaiting Owner Approval"

3. **Owner:**
   - See the request in "Awaiting Approval" tab
   - Review the full approval timeline
   - Click "Approve & Activate"
   - New percentage should be active immediately

4. **Verification:**
   - Create new order/dish
   - Verify new multiplier is applied automatically
   - Check Finance Manager panel shows new current percentage

---

## Features Overview

| Feature | Manager Panel | Finance Panel | Owner Panel |
|---------|---------------|---------------|------------|
| View Current % | ✓ | ✓ | ✓ |
| Request Change | ✓ | - | - |
| Approve/Reject | - | ✓ | - |
| Final Approval | - | - | ✓ |
| View History | ✓ | ✓ | ✓ |
| See Timeline | - | ✓ | ✓ |
| Auto-Refresh | ✓ | ✓ | ✓ |
| Error Handling | ✓ | ✓ | ✓ |
| Responsive | ✓ | ✓ | ✓ |

---

## Support

All components:
- Handle loading states
- Show error messages
- Support real-time updates
- Are fully accessible
- Work offline-friendly (with error handling)
- Include success confirmations
