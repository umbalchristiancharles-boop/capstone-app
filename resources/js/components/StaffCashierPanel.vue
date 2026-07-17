<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="`Cashier - ${branchName}`"
    panelDescription="Process transactions and manage sales"
    :enableProfileUpdate="false"
    :canChangePassword="false"
    :showProfileColumn="false"
    :showAnnouncements="false"
    :ownerTwoColumnLayout="true"
    @logout="confirmLogout"
  >
    <template #main>
      <div v-if="!branchId" class="loading-text">Loading branch information...</div>
      <div v-else class="cashier-body">
        <!-- LEFT: Product catalogue -->
        <section class="product-catalogue">
          <h2>Products</h2>
          <div class="search-bar">
            <input v-model="productSearch" type="text" placeholder="Search products..." />
          </div>
          <div v-if="isLoadingProducts" class="loading-text">Loading products...</div>
          <div v-else-if="filteredProducts.length === 0" class="empty-text">No products available</div>
          <div v-else>
            <div v-for="cat in productCategories" :key="cat" class="category-section">
              <h3 class="category-header">{{ cat || 'Uncategorized' }}</h3>
              <div class="product-grid">
                <div
                  v-for="p in getProductsByCategory(cat)"
                  :key="p.id"
                  class="product-card"
                  :class="{ 'out-of-stock': p.stock <= 0 }"
                  @click="p.stock > 0 && addToCart(p)"
                >
                  <div class="product-name">{{ p.name }}</div>
                  <div v-if="p.per_pack_or_individual" class="product-type" :class="'type-' + p.per_pack_or_individual">
                    {{ formatPricingType(p.per_pack_or_individual) }}
                  </div>
                  <div class="product-price">₱{{ fmt(p.price) }}</div>
                  <div class="product-stock" :class="{ 'stock-zero': p.stock <= 0 }">
                    {{ p.stock > 0 ? 'Stock: ' + p.stock : 'Out of stock' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Cart moved to side slot -->
      </div>

      <!-- Recent Transactions -->
      <section v-if="branchId" class="transactions-section">
        <h2>
          Recent Transactions
          <span v-if="pendingTransactionsCount > 0" class="panel-badge">{{ pendingTransactionsCount }}</span>
        </h2>
        <div v-if="transactions.length === 0" class="empty-text">No transactions yet</div>
        <div v-else class="tx-table-wrap">
          <table class="tx-table">
            <thead>
              <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Items Bought</th>
              <th>Total</th>
              <th>Paid</th>
              <th>Change</th>
              <th>Status</th>
              <th>Action</th>
              <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="tx in transactions" :key="tx.id">
                <td>{{ tx.order_code }}</td>
                <td>{{ tx.customer_name }}</td>
                <td class="items-cell">
                  <span v-for="(item, idx) in tx.items" :key="idx" class="item-badge">
                    {{ item.quantity }}x {{ item.product_name }}
                  </span>
                </td>
                <td>₱{{ fmt(tx.grand_total) }}</td>
                <td>₱{{ fmt(tx.amount_paid) }}</td>
                <td>₱{{ fmt(tx.change_amount) }}</td>
                <td>
                  <span :class="['status-badge', tx.status === 'cancelled' ? 'status-rejected' : (tx.status === 'completed' ? 'status-approved' : 'status-pending')]">{{ tx.status }}</span>
                </td>
                <td>
                  <button v-if="tx.status !== 'cancelled' && (tx.status === 'completed' || tx.status === 'approved')" class="refund-btn" @click="refundOrder(tx)" :disabled="tx.isRefunding">Refund</button>
                  <span v-else class="small-muted">—</span>
                </td>
                <td>{{ formatDate(tx.ordered_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>

    <template #headerActions>
      <button class="logout-btn" @click="confirmLogout">Logout</button>
    </template>
    <template #side>
      <div v-if="!hideAttendanceCard" class="attendance-card" style="margin-bottom:12px; background:#ffffff;">
        <div class="attendance-header">
          <span class="attendance-title">Attendance</span>
          <span :class="['attendance-status-badge', attendanceStatus.is_clocked_in ? 'status-on-duty' : 'status-off-duty']">
            {{ attendanceStatus.is_clocked_in ? 'On Duty' : 'Off Duty' }}
          </span>
        </div>
        <div class="attendance-times" v-if="attendanceStatus.clock_in_time || attendanceStatus.clock_out_time">
          <div class="time-row"><span class="time-label">Clock In:</span><span class="time-value">{{ attendanceStatus.clock_in_time || '-' }}</span></div>
          <div class="time-row"><span class="time-label">Clock Out:</span><span class="time-value">{{ attendanceStatus.clock_out_time || '-' }}</span></div>
          <div class="time-row" v-if="attendanceStatus.hours_worked > 0"><span class="time-label">Hours:</span><span class="time-value">{{ attendanceStatus.hours_worked }} hrs</span></div>
        </div>
        <div class="attendance-buttons">
          <button @click="performClockIn" :disabled="attendanceStatus.is_clocked_in || isAttendanceProcessing" class="btn-clock-in">{{ isAttendanceProcessing ? '...' : 'Clock In' }}</button>
          <button @click="performClockOut" :disabled="!attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockOut" class="btn-clock-out" :class="{ 'btn-disabled': !canClockOut && attendanceStatus.is_clocked_in }">{{ isAttendanceProcessing ? '...' : 'Clock Out' }}</button>
        </div>
        <div v-if="!canClockOut && attendanceStatus.is_clocked_in" class="clockout-restriction"><span class="restriction-icon">LOCK</span><span>Cannot clock out before {{ scheduledTimeOut }}</span></div>
        <div v-if="attendanceMessage" :class="['attendance-message', attendanceMessageType]">{{ attendanceMessage }}</div>
      </div>

      <!-- Branch Budget Card -->
      <div v-if="branchId" style="margin-bottom:12px;">
        <h3 style="margin:0 0 8px;color:#7a2b00;font-size:0.95rem">Branch Budget</h3>
        <div style="background:#ffffff;padding:12px;border-radius:8px;box-shadow:0 4px 12px rgba(2,6,23,0.04);">
          <div style="font-weight:700;color:#065f46;font-size:1.05rem">₱{{ fmt(branchBudget) }}</div>
          <div style="color:#6b7280;margin-top:6px">{{ branchName }}</div>
        </div>
      </div>

      <!-- Announcements (moved below Branch Budget) -->
      <div class="announcements-card" style="margin-bottom:12px;" v-if="branchId">
        <h3 class="announcements-title">Announcements</h3>
        <div class="announcements-list">
          <div v-if="loadingAnnouncements" class="loading-text">Loading announcements...</div>
          <div v-else>
            <div v-if="announcements.length">
              <div v-for="a in announcements" :key="a.id" class="announcement-item">
                <div class="announcement-title">{{ a.title }}</div>
                <div class="announcement-message">{{ a.message }}</div>
                <div class="announcement-meta">{{ formatDate(a.created_at) }}</div>
              </div>
            </div>
            <div v-else class="empty-text">No announcements</div>
          </div>
        </div>
      </div>

      <section class="cart-section">
        <h2>Current Order</h2>

        <div v-if="cart.length === 0" class="empty-text">No items in cart. Click a product to add.</div>

        <div v-else class="cart-list">
          <div v-for="(item, idx) in cart" :key="item.product_id" class="cart-item">
            <div class="cart-item-info">
              <span class="cart-item-name">{{ item.name }}</span>
              <span class="cart-item-price">₱{{ fmt(item.unit_price) }}</span>
            </div>
            <div class="cart-item-controls">
              <button class="qty-btn" @click="decrementQty(idx)">−</button>
              <input
                type="number"
                class="qty-input"
                :value="item.quantity"
                min="1"
                :max="item.max_stock"
                @change="setQty(idx, $event)"
              />
              <button class="qty-btn" @click="incrementQty(idx)">+</button>
              <span class="cart-item-subtotal">₱{{ fmt(item.subtotal) }}</span>
              <button class="remove-btn" @click="removeItem(idx)">✕</button>
            </div>
          </div>
        </div>

        <!-- Totals -->
        <div class="totals-box">
          <div class="total-row">
            <span>Items:</span>
            <span>{{ totalItems }}</span>
          </div>
          <div class="total-row">
            <span>Subtotal:</span>
            <span>₱{{ fmt(subtotal) }}</span>
          </div>
          <div class="total-row">
            <span>Discount:</span>
            <span>-₱{{ fmt(discountAmount) }}</span>
          </div>
          <div class="total-row">
            <span>Taxable:</span>
            <span>₱{{ fmt(taxable) }}</span>
          </div>
          <div class="total-row">
            <span>VAT ({{ vatPercent * 100 }}%):</span>
            <span>₱{{ fmt(vatAmount) }}</span>
          </div>
          <div class="total-row total-grand">
            <span>Grand Total:</span>
            <span>₱{{ fmt(grandTotal) }}</span>
          </div>
        </div>

        <!-- Customer & Payment -->
        <div class="payment-section">
          <div class="form-group">
            <label>Discount / Concession</label>
            <select v-model="discountType">
              <option value="none">None</option>
              <option value="discount">Discount (custom %)</option>
              <option value="pwd">PWD</option>
              <option value="senior">Senior</option>
            </select>
            <div v-if="discountType === 'discount'" style="margin-top:8px;">
              <input v-model.number="discountPercent" type="number" min="0" max="100" step="0.1" /> %
            </div>
          </div>
          <div class="form-group">
            <label>Customer Name (optional)</label>
            <input v-model="customerName" type="text" placeholder="Walk-in" />
          </div>
          <div class="form-group">
            <label>Amount Paid (₱)</label>
            <input
              v-model.number="amountPaid"
              type="number"
              min="0"
              step="0.01"
              placeholder="0.00"
            />
          </div>
          <div v-if="amountPaid >= grandTotal && cart.length" class="change-display">
            Change: <strong>₱{{ fmt(amountPaid - grandTotal) }}</strong>
          </div>
          <div v-else-if="amountPaid > 0 && amountPaid < grandTotal" class="insufficient-display">
            Insufficient by ₱{{ fmt(grandTotal - amountPaid) }}
          </div>
        </div>

        <div v-if="checkoutError" class="error-msg">{{ checkoutError }}</div>
        <div v-if="checkoutSuccess" class="success-msg">{{ checkoutSuccess }}</div>

        <div class="checkout-actions">
          <button class="btn-cancel" @click="clearCart" :disabled="isProcessing || !!pendingOrderCode">
            {{ pendingOrderCode ? 'Cancel Pending' : 'Clear' }}
          </button>
          <button
            class="btn-confirm"
            :disabled="!canCheckout || isProcessing"
            @click="processCheckout"
          >
            {{ isProcessing ? 'Processing...' : 'Checkout' }}
          </button>
        </div>
      </section>
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import { showToast } from './toastStore'

const router = useRouter()

// State
const branchId = ref(null)
const branchName = ref('')
const products = ref([])
const userProfile = ref({})
const productSearch = ref('')
const isLoadingProducts = ref(false)

const cart = ref([])
const customerName = ref('')
const amountPaid = ref(null)
const isProcessing = ref(false)
const pendingOrderCode = ref(null)
const checkoutError = ref('')
const checkoutSuccess = ref('')
const transactions = ref([])
const branchBudget = ref(0)
const hasNotified = ref(false)
// track refunding state per transaction
// we will set `tx.isRefunding = true` temporarily when refund is in progress

// Announcements
const announcements = ref([])
const loadingAnnouncements = ref(false)

// Attendance state
const attendanceStatus = ref({
  is_clocked_in: false,
  clock_in_time: null,
  clock_out_time: null,
  hours_worked: 0
})
const isAttendanceProcessing = ref(false)
const attendanceMessage = ref('')
const attendanceMessageType = ref('')

const attendanceSettings = ref({
  early_clockout_override: false,
  scheduled_time_out: '17:00:00'
})

async function fetchAnnouncements() {
  loadingAnnouncements.value = true
  try {
    const res = await axios.get('/api/announcements', { withCredentials: true })
    if (res.data && res.data.ok) announcements.value = res.data.announcements || []
  } catch (e) {
    console.error('Failed to load announcements:', e)
  } finally {
    loadingAnnouncements.value = false
  }
}

// Logout state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)

// Helpers
function fmt(n) {
  return Number(n || 0).toFixed(2)
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleString()
}

function formatPricingType(type) {
  const typeMap = {
    'individual': 'Individual',
    'per_pack': 'Per Pack',
    'both': 'Both'
  }
  return typeMap[type] || type
}

const filteredProducts = computed(() => {
  const q = (productSearch.value || '').toLowerCase()
  return products.value.filter(p =>
    p.name.toLowerCase().includes(q) || (p.sku || '').toLowerCase().includes(q)
  )
})

// Product categories for organizing cashier display
const productCategories = computed(() => {
  const categories = new Set()
  filteredProducts.value.forEach(p => {
    categories.add(p.category || 'Uncategorized')
  })
  return Array.from(categories).sort()
})

function getProductsByCategory(category) {
  return filteredProducts.value.filter(p => (p.category || 'Uncategorized') === category)
}

const totalItems = computed(() => cart.value.reduce((s, i) => s + i.quantity, 0))
const subtotal = computed(() => cart.value.reduce((s, i) => s + i.subtotal, 0))

// Defaults (frontend fallback) - VAT and concession percentages
const FRONTEND_VAT = 0.12
const FRONTEND_PWD = 20
const FRONTEND_SENIOR = 20

const discountType = ref('none')
const discountPercent = ref(0)

const isCustomAccount = computed(() => {
  try {
    const raw = localStorage.getItem('user') || 'null'
    const u = JSON.parse(raw)
    return (u?.role || '').toLowerCase() === 'custom'
  } catch (e) {
    return false
  }
})

const hideAttendanceCard = computed(() => {
  try {
    return new URLSearchParams(window.location.search).get('from') === 'custom-panel' || isCustomAccount.value
  } catch (e) {
    return isCustomAccount.value
  }
})

const pendingTransactionsCount = computed(() => {
  return (transactions.value || []).filter(t => (t.status || '').toLowerCase() === 'pending').length
})

watch(pendingTransactionsCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have pending cashier transactions.', 'info')
    hasNotified.value = true
  }
})

const computedDiscountPercent = computed(() => {
  if (discountType.value === 'pwd') return FRONTEND_PWD
  if (discountType.value === 'senior') return FRONTEND_SENIOR
  if (discountType.value === 'discount') return Number(discountPercent.value) || 0
  return 0
})

const scheduledTimeOut = computed(() => {
  const time = attendanceSettings.value.scheduled_time_out || '17:00:00'
  const [hours, minutes] = time.split(':')
  const hour = parseInt(hours)
  const ampm = hour >= 12 ? 'PM' : 'AM'
  const hour12 = hour % 12 || 12
  return `${hour12}:${minutes} ${ampm}`
})

const canClockOut = computed(() => {
  if (!attendanceStatus.value.is_clocked_in) return false
  if (attendanceSettings.value.early_clockout_override) return true

  const now = new Date()
  const currentHours = now.getHours()
  const currentMinutes = now.getMinutes()
  const [scheduledHours, scheduledMinutes] = (attendanceSettings.value.scheduled_time_out || '17:00:00').split(':')
  const currentTotalMinutes = currentHours * 60 + currentMinutes
  const scheduledTotalMinutes = parseInt(scheduledHours) * 60 + parseInt(scheduledMinutes)
  return currentTotalMinutes >= scheduledTotalMinutes
})

const discountAmount = computed(() => (subtotal.value * (computedDiscountPercent.value || 0)) / 100)
const taxable = computed(() => Math.max(0, subtotal.value - discountAmount.value))
const vatPercent = FRONTEND_VAT
const vatAmount = computed(() => taxable.value * vatPercent)
const grandTotal = computed(() => Number((taxable.value + vatAmount.value).toFixed(2)))

const canCheckout = computed(() =>
  cart.value.length > 0 && amountPaid.value >= grandTotal.value && grandTotal.value > 0
)

function goBack() {
  try { router.push({ path: '/custom-panel', query: { from: 'custom-panel' } }) } catch (e) { window.location.href = '/custom-panel' }
}

// Load staff profile and set branch
async function loadStaffProfile() {
  try {
    console.log('[StaffCashierPanel] localStorage user:', localStorage.getItem('user'))
    const res = await axios.get('/api/staff/profile', { withCredentials: true })
    console.log('[StaffCashierPanel] /api/staff/profile response:', res && res.data)
    if (res.data && res.data.ok && res.data.user) {
      const user = res.data.user
      // Persist authoritative user object to localStorage so router guards use correct role
      try {
        const normalized = {
          id: user.id,
          username: user.username || user.user_name || user.name || '',
          role: (user.role || '').toLowerCase(),
          department: (user.department || '').toLowerCase(),
          full_name: user.full_name || user.name || '',
          branch_id: user.branch_id || null,
          permissions: user.permissions || {}
        }
        localStorage.setItem('user', JSON.stringify(normalized))
        userProfile.value = normalized
        // Ensure axios has Authorization header if token exists
        try {
          const t = localStorage.getItem('token')
          if (t) axios.defaults.headers.common['Authorization'] = `Bearer ${t}`
        } catch (e) {}
      } catch (e) {
        console.warn('[StaffCashierPanel] failed to update localStorage user:', e)
      }

      branchId.value = user.branch_id
      branchName.value = user.branch_name || 'Unknown Branch'

      // Auto-load products and transactions for this branch
      if (branchId.value) {
        await loadProducts()
        await loadTransactions()
        await loadBranchBudget()
      }
    }
  } catch (e) {
    console.error('Failed to load staff profile:', e)
  }
}

// Load products for the staff's branch
async function loadProducts() {
  if (!branchId.value) {
    products.value = []
    return
  }
  isLoadingProducts.value = true
  try {
    const res = await axios.get('/api/superadmin/cashier/products', {
      params: { branch_id: branchId.value }
    })
    products.value = res.data
  } catch (e) {
    console.error('Failed to load products', e)
  } finally {
    isLoadingProducts.value = false
  }
}

// Load recent transactions for the staff's branch
async function loadTransactions() {
  if (!branchId.value) return
  try {
    const res = await axios.get('/api/superadmin/cashier/transactions', {
      params: { branch_id: branchId.value }
    })
    transactions.value = res.data
  } catch (e) {
    console.error('Failed to load transactions', e)
  }
}

// Load branch budget by querying branches endpoint and picking matching id
async function loadBranchBudget() {
  if (!branchId.value) return
  try {
    const res = await axios.get('/api/superadmin/cashier/branches')
    const list = res.data || []
    const b = list.find(x => String(x.id) === String(branchId.value))
    branchBudget.value = b ? Number(b.budget || 0) : 0
  } catch (e) {
    console.error('Failed to load branch budget', e)
    branchBudget.value = 0
  }
}

async function refundOrder(tx) {
  if (!(await window.swalConfirm(`Refund order ${tx.order_code}? You will be asked for a reason.`))) return
  const reason = await window.swalPrompt('Enter refund reason (required):', '', 'text')
  if (reason === null || !reason || !reason.trim()) {
    alert('Refund cancelled: reason is required.')
    return
  }

  tx.isRefunding = true
  try {
    const res = await axios.post('/api/superadmin/cashier/refund', {
      order_code: tx.order_code,
      branch_id: branchId.value,
      reason: reason.trim(),
    })
    alert(res.data?.message || 'Order refunded')
    await loadTransactions()
    await loadBranchBudget()
  } catch (e) {
    const msg = e.response?.data?.error || e.response?.data?.message || e.message || 'Refund failed'
    alert(msg)
  } finally {
    tx.isRefunding = false
  }
}

// Attendance helpers
async function loadAttendanceStatus() {
  try {
    const res = await axios.get('/api/staff/attendance/status', { withCredentials: true })
    if (res.data && res.data.ok) {
      attendanceStatus.value = {
        is_clocked_in: res.data.status?.is_clocked_in || false,
        clock_in_time: res.data.status?.clock_in_time || null,
        clock_out_time: res.data.status?.clock_out_time || null,
        hours_worked: res.data.status?.hours_worked || 0
      }
    }
  } catch (e) {
    console.error('Failed to load attendance status:', e)
  }
}

async function loadAttendanceSettings() {
  try {
    const res = await axios.get('/api/attendance/settings', { withCredentials: true })
    if (res.data && res.data.ok && res.data.data) {
      attendanceSettings.value = {
        early_clockout_override: res.data.data.early_clockout_override || false,
        scheduled_time_out: res.data.data.scheduled_time_out || '17:00:00'
      }
    }
  } catch (e) {
    console.error('Failed to load attendance settings:', e)
    attendanceSettings.value = {
      early_clockout_override: false,
      scheduled_time_out: '17:00:00'
    }
  }
}

async function performClockIn() {
  if (isAttendanceProcessing.value) return
  isAttendanceProcessing.value = true
  attendanceMessage.value = ''

  try {
    const res = await axios.post('/api/staff/clock-in', {}, { withCredentials: true })
    if (res.data && (res.data.success || res.data.ok)) {
      attendanceMessage.value = 'Clocked in successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock in'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    attendanceMessage.value = e.response?.data?.message || 'Error clocking in'
    attendanceMessageType.value = 'error'
  } finally {
    isAttendanceProcessing.value = false
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
  }
}

async function performClockOut() {
  if (isAttendanceProcessing.value) return
  isAttendanceProcessing.value = true
  attendanceMessage.value = ''

  try {
    const res = await axios.post('/api/staff/clock-out', {}, { withCredentials: true })
    if (res.data && (res.data.success || res.data.ok)) {
      attendanceMessage.value = 'Clocked out successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock out'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    attendanceMessage.value = e.response?.data?.message || 'Error clocking out'
    attendanceMessageType.value = 'error'
  } finally {
    isAttendanceProcessing.value = false
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
  }
}

// Cart operations
function addToCart(product) {
  // detect supplier-specific options with stock
  const options = getSupplierOptionsFor(product)
  let chosen = product
  if (options.length >= 2) {
    const msg = options.map((o, idx) => `${idx+1}: ${o.name} — ₱${fmt(o.price)} (stock: ${o.stock})`).join('\n')
    const sel = window.prompt('Multiple supplier options available:\n' + msg + '\nEnter option number to add:', '1')
    const n = parseInt(sel)
    if (!isNaN(n) && n >= 1 && n <= options.length) chosen = options[n-1]
    else return
  }

  const existing = cart.value.find(i => i.product_id === chosen.id)
  if (existing) {
    if (existing.quantity < (chosen.real_stock ?? chosen.stock)) {
      existing.quantity++
      existing.subtotal = existing.quantity * existing.unit_price
    }
  } else {
    cart.value.push({
      product_id: chosen.id,
      name: chosen.name,
      unit_price: Number(chosen.price),
      quantity: 1,
      subtotal: Number(chosen.price),
      max_stock: chosen.real_stock ?? chosen.stock,
    })
  }
}

function getSupplierOptionsFor(product) {
  try {
    const key = (product.sku || '').toString().trim()
    const nameKey = (product.name || '').toString().trim().toUpperCase()
    return products.value.filter(p => {
      if (!p.supplier_id) return false
      if (!p.stock || p.stock <= 0) return false
      if (key && p.sku === key && p.branch_id === product.branch_id) return true
      if (!key && (p.name || '').toString().trim().toUpperCase() === nameKey && p.branch_id === product.branch_id) return true
      return false
    })
  } catch (e) { return [] }
}

function decrementQty(idx) {
  const item = cart.value[idx]
  if (item.quantity > 1) {
    item.quantity--
    item.subtotal = item.quantity * item.unit_price
  }
}

function incrementQty(idx) {
  const item = cart.value[idx]
  if (item.quantity < item.max_stock) {
    item.quantity++
    item.subtotal = item.quantity * item.unit_price
  }
}

function setQty(idx, event) {
  const item = cart.value[idx]
  let val = parseInt(event.target.value) || 1
  val = Math.max(1, Math.min(val, item.max_stock))
  item.quantity = val
  item.subtotal = val * item.unit_price
  event.target.value = val
}

function removeItem(idx) {
  cart.value.splice(idx, 1)
}

async function clearCart() {
  if (pendingOrderCode.value && cart.value.length === 0) {
    try {
      await axios.post('/api/superadmin/cashier/cancel-pending', {
        order_code: pendingOrderCode.value,
        branch_id: branchId.value
      })
      checkoutSuccess.value = 'Pending order cancelled.'
      pendingOrderCode.value = null
    } catch (e) {
      console.warn('Cancel pending failed:', e)
    }
  }

  cart.value = []
  customerName.value = ''
  amountPaid.value = null
  checkoutError.value = ''
  checkoutSuccess.value = ''
  discountType.value = 'none'
  discountPercent.value = 0
}

// Checkout
async function processCheckout() {
  if (!canCheckout.value) return
  isProcessing.value = true
  checkoutError.value = ''
  checkoutSuccess.value = ''

  try {
    const payload = {
      branch_id: branchId.value,
      customer_name: customerName.value || 'Walk-in',
        amount_paid: amountPaid.value,
        discount_type: discountType.value || 'none',
        discount_percent: computedDiscountPercent.value || 0,
      items: cart.value.map(i => ({
        product_id: i.product_id,
        quantity: i.quantity,
      })),
    }

    const res = await axios.post('/api/superadmin/cashier/checkout', payload)
    const order = res.data.order || {}
    // If backend created a pending order, keep pending flow. Otherwise treat as completed.
    if (order.status && order.status === 'pending') {
      checkoutSuccess.value = `${res.data.message} Order: ${order.order_code} (PENDING) | Change: ₱${fmt(res.data.change)}`
      pendingOrderCode.value = order.order_code
    } else {
      checkoutSuccess.value = `${res.data.message} Order: ${order.order_code || ''} | Change: ₱${fmt(res.data.change)}`
      pendingOrderCode.value = null
      // clear cart on completed orders
      cart.value = []
    }

    customerName.value = ''
    amountPaid.value = null
    discountType.value = 'none'
    discountPercent.value = 0

    await loadTransactions()
    await loadBranchBudget()
  } catch (e) {
    const msg = e.response?.data?.message || e.response?.data?.error || e.message || 'Checkout failed'
    checkoutError.value = msg
  } finally {
    isProcessing.value = false
  }
}

// Initialize on mount
onMounted(async () => {
  console.log('[StaffCashierPanel] mounted - localStorage user:', localStorage.getItem('user'))
  await loadStaffProfile()
  fetchAnnouncements()
  if (!hideAttendanceCard.value) {
    loadAttendanceStatus()
    loadAttendanceSettings()
  }
})

// Logout functions
async function confirmLogout() {
  if (isLoggingOut.value) return
  if (!(await window.swalConfirm('Are you sure you want to logout?'))) return
  performLogout()
}

async function performLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  setTimeout(() => {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    try { window.location.replace('/staff-landing') } catch (e) {}
  }, 500)
}
</script>

<style scoped>
.cashier-page {
  min-height: 100vh;
  background: linear-gradient(180deg, #FF9A4A 0%, #FF6A3D 100%);
  padding: 20px;
}

/* Header */
.cashier-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 20px;
}

.transactions-section h2 {
  position: relative;
  display: inline-block;
}

.panel-badge {
  position: absolute;
  top: -8px;
  right: -16px;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: #ef4444;
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(239, 68, 68, 0.35);
}

.header-title h1 { margin: 0; color: white; font-size: 1.5rem; }
.header-title p { margin: 5px 0 0; color: rgba(255,255,255,0.9); }

.logout-btn {
  padding: 10px 20px;
  background: white;
  border: none;
  border-radius: 8px;
  color: #7a2b00;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.logout-btn:hover { background: #fff3e6; }

/* Body: 2-column layout */
.cashier-body {
  display: grid;
  /* Use the same wide main + side column as OwnerPanelLayout when profile column hidden */
  /* reduce right-side whitespace so main column is wider */
  grid-template-columns: 1fr 320px;
  gap: 20px;
  margin-bottom: 24px;
  align-items: start;
}

/* Product catalogue */
.product-catalogue,
.cart-section {
  background: rgba(255,255,255,0.95);
  border-radius: 12px;
  padding: 20px;
}

.product-catalogue {
  /* Make the whole left column scrollable so categories share one scrollbar */
  max-height: calc(100vh - 180px);
  overflow-y: auto;
  padding-right: 8px;
  /* Span the full grid width so Products match the width of Recent Transactions */
  grid-column: 1 / -1;
}

.product-catalogue h2,
.cart-section h2 {
  margin: 0 0 12px;
  color: #7a2b00;
  font-size: 1.15rem;
}

.search-bar input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid rgba(255,211,107,0.4);
  border-radius: 6px;
  font-size: 14px;
  margin-bottom: 12px;
}
.search-bar input:focus { outline: none; border-color: #ff7a18; }

.category-section {
  margin-bottom: 20px;
}

.category-header {
  font-size: 0.95rem;
  font-weight: 700;
  color: #7a2b00;
  margin: 10px 0 8px 0;
  padding-bottom: 6px;
  border-bottom: 2px solid #ff9a4a;
}

.product-grid {
  display: grid;
  /* slightly larger product tiles for readability */
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 18px;
  /* let the parent column handle scrolling */
  max-height: none;
  overflow: visible;
  padding-right: 6px;
}

.product-card {
  background: #fff8f0;
  border: 1px solid rgba(255,211,107,0.3);
  border-radius: 8px;
  padding: 14px;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s;
  min-height: 96px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  border-color: #ff7a18;
}
.product-card.out-of-stock {
  opacity: 0.5;
  cursor: not-allowed;
}
.product-card.out-of-stock:hover {
  transform: none;
  box-shadow: none;
  border-color: rgba(255,211,107,0.3);
}
.stock-zero { color: #dc3545; font-weight: 600; }
.product-name { font-weight: 600; color: #7a2b00; margin-bottom: 4px; }
.product-type { display: inline-block; font-size: 0.7rem; font-weight: 600; padding: 2px 6px; border-radius: 5px; margin-bottom: 4px; }
.product-type.type-individual { background: #dbeafe; color: #1e40af; }
.product-type.type-per_pack { background: #d1fae5; color: #065f46; }
.product-type.type-both { background: #fef3c7; color: #92400e; }
.product-price { color: #e65100; font-weight: 700; font-size: 1.05rem; }
.product-stock { color: #888; font-size: 0.85rem; margin-top: 4px; }

/* Cart */
.cart-list {
  max-height: 40vh;
  overflow-y: auto;
  margin-bottom: 12px;
}

/* Make cart section sticky so totals remain visible while browsing products */
.cart-section {
  position: sticky;
  top: 96px;
  align-self: start;
}

.cart-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(255,211,107,0.25);
}

.cart-item-info {
  display: flex;
  justify-content: space-between;
}
.cart-item-name { font-weight: 600; color: #7a2b00; }
.cart-item-price { color: #888; font-size: 0.9rem; }

.cart-item-controls {
  display: flex;
  align-items: center;
  gap: 6px;
}

.qty-btn {
  width: 28px;
  height: 28px;
  border: 1px solid rgba(255,211,107,0.4);
  border-radius: 4px;
  background: white;
  font-size: 1rem;
  color: #7a2b00;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.qty-btn:hover { background: #fff3e6; }

.qty-input {
  width: 48px;
  text-align: center;
  border: 1px solid rgba(255,211,107,0.4);
  border-radius: 4px;
  padding: 4px;
  font-size: 14px;
  color: #7a2b00;
}
.qty-input:focus { outline: none; border-color: #ff7a18; }

.cart-item-subtotal {
  margin-left: auto;
  font-weight: 700;
  color: #e65100;
  min-width: 70px;
  text-align: right;
}

.remove-btn {
  background: none;
  border: none;
  color: #dc3545;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 2px 6px;
}
.remove-btn:hover { color: #a71d2a; }

/* Totals */
.totals-box {
  background: #fff8f0;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 12px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
  color: #7a2b00;
}
.total-grand {
  font-size: 1.2rem;
  font-weight: 700;
  border-top: 2px solid rgba(255,211,107,0.4);
  padding-top: 8px;
  margin-top: 4px;
  color: #e65100;
}

/* Payment */
.payment-section { margin-bottom: 12px; }
.form-group { margin-bottom: 10px; }
.form-group label {
  display: block;
  margin-bottom: 4px;
  font-weight: 600;
  color: #7a2b00;
  font-size: 0.9rem;
}
.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid rgba(255,211,107,0.4);
  border-radius: 6px;
  font-size: 14px;
  box-sizing: border-box;
}
.form-group input:focus { outline: none; border-color: #ff7a18; }

.change-display {
  background: #d4edda;
  color: #155724;
  padding: 10px;
  border-radius: 6px;
  font-size: 1.1rem;
  text-align: center;
}

.insufficient-display {
  background: #f8d7da;
  color: #721c24;
  padding: 10px;
  border-radius: 6px;
  font-size: 0.95rem;
  text-align: center;
}

.error-msg {
  color: #dc3545;
  background: #f8d7da;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 10px;
}
.success-msg {
  color: #155724;
  background: #d4edda;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 10px;
}

.checkout-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.btn-cancel {
  padding: 10px 16px;
  border: 1px solid rgba(255,211,107,0.4);
  border-radius: 6px;
  background: white;
  color: #7a2b00;
  cursor: pointer;
  font-weight: 600;
}
.btn-cancel:hover { background: #f5f5f5; }

.btn-confirm {
  padding: 10px 24px;
  border: none;
  border-radius: 6px;
  background: linear-gradient(180deg, #ff7a18, #ff6a3d);
  color: white;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
}
.btn-confirm:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.btn-confirm:not(:disabled):hover {
  filter: brightness(1.1);
}

/* Transactions Section */
.transactions-section {
  background: rgba(255,255,255,0.95);
  border-radius: 12px;
  padding: 20px;
  margin-top: 24px;
}
.transactions-section h2 {
  margin: 0 0 12px;
  color: #7a2b00;
  font-size: 1.15rem;
}

.tx-table-wrap {
  overflow-x: auto;
  /* limit height and allow vertical scrolling for long transaction lists */
  max-height: 44vh;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.tx-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}
.tx-table th {
  background: #fff3e6;
  color: #7a2b00;
  font-weight: 600;
  padding: 10px 12px;
  text-align: left;
  border-bottom: 2px solid rgba(255,211,107,0.4);
}
.tx-table td {
  padding: 8px 12px;
  border-bottom: 1px solid rgba(255,211,107,0.2);
  color: #333;
}
.tx-table tbody tr:hover { background: #fff8f0; }

.refund-btn {
  padding: 6px 10px;
  border-radius: 6px;
  background: #fff3f3;
  color: #a71d2a;
  border: 1px solid #f5c2c2;
  cursor: pointer;
  font-weight: 600;
}
.refund-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.small-muted { color: #888; font-size: 0.9rem; }

/* Items cell in transactions */
.items-cell {
  max-width: 250px;
}
.item-badge {
  display: inline-block;
  background: #fff3e6;
  color: #7a2b00;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.8rem;
  margin: 2px;
  white-space: nowrap;
}

/* Misc */
.loading-text,
.empty-text {
  color: #888;
  text-align: center;
  padding: 24px 0;
}

/* Softer, less-saturated visual style overrides */
.product-card {
  background: #ffffff;
  border: 1px solid #ececec;
  border-radius: 10px;
  box-shadow: 0 1px 6px rgba(16,24,40,0.04);
  transition: transform .12s ease, box-shadow .12s ease;
}
.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(16,24,40,0.06);
  border-color: #ececec;
}
.product-name { color: #1f2937; font-weight: 600; }
.product-stock { color: #6b7280; font-weight: 500; }
.product-price { color: #2f6f4a; font-weight: 700; }
.product-type {
  background: #f3f4f6;
  color: #374151;
  font-size: 0.75rem;
  padding: 2px 6px;
  border-radius: 999px;
}
.item-badge {
  background: #f8fafc;
  color: #374151;
  border: 1px solid #eef2f7;
  font-size: 0.78rem;
}
.tx-table td:nth-child(4),
.tx-table td:nth-child(5),
.tx-table td:nth-child(6) {
  text-align: right;
}
.cashier-page { background: #ffffff; }
.refund-btn { background: #fff7f7; color: #9b1f2d; border: 1px solid #fde2e2; }
.tx-table th {
  background: #fafafa;
  color: #374151;
  border-bottom-color: #ececec;
}

/* Attendance card */
.attendance-card {
  background: #ffffff;
  border-radius: 10px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}

.attendance-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.attendance-title {
  font-weight: 700;
  color: #333333;
  font-size: 0.9rem;
}

.attendance-status-badge {
  padding: 3px 8px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
}

.status-on-duty {
  background: #d4edda;
  color: #155724;
}

.status-off-duty {
  background: #f8d7da;
  color: #721c24;
}

.attendance-times {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.8rem;
}

.time-row {
  display: flex;
  justify-content: space-between;
}

.time-label {
  color: #8a4b1a;
}

.time-value {
  font-weight: 600;
  color: #7a2b00;
}

.attendance-buttons {
  display: flex;
  gap: 8px;
}

.btn-clock-in,
.btn-clock-out {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-clock-in {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
}

.btn-clock-in:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.btn-clock-in:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-clock-out {
  background: linear-gradient(135deg, #dc3545, #ff6b6b);
  color: white;
}

.btn-clock-out:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.btn-clock-out:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-disabled {
  background: #999 !important;
  cursor: not-allowed !important;
}

.clockout-restriction {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px;
  background: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 6px;
  color: #856404;
  font-size: 0.7rem;
}

.restriction-icon {
  font-size: 1rem;
}

.attendance-message {
  padding: 8px;
  border-radius: 4px;
  text-align: center;
  font-size: 0.75rem;
  font-weight: 500;
}

.attendance-message.success {
  background: #d4edda;
  color: #155724;
}

.attendance-message.error {
  background: #f8d7da;
  color: #721c24;
}

/* Responsive */
@media (max-width: 860px) {
  .cashier-body {
    grid-template-columns: 1fr;
  }
}
</style>
<style scoped>
/* Override OwnerPanelLayout announcements styles for this panel to match Cashier look */
:deep(.announcements-panel) {
  background: var(--surface-card);
  border-radius: 12px;
  padding: 12px;
  border: 1px solid rgba(255,211,107,0.4);
  box-sizing: border-box;
}
:deep(.announcements-panel .panel-header h2) {
  margin: 0 0 8px;
  color: var(--text-dark);
  font-size: 0.95rem;
}
:deep(.announcements-panel .panel-body) {
  padding: 0;
}
:deep(.announcements-panel .announcement-list) {
  max-height: 320px;
  overflow: auto;
  padding-right: 6px;
  margin: 0;
}
:deep(.announcements-panel .announcement-item) {
  margin-bottom: 8px;
  padding: 10px;
  border-radius: 6px;
  background: var(--surface-card);
}
:deep(.announcements-panel .announcement-title) {
  font-weight: 700;
  color: var(--text-dark);
}
:deep(.announcements-panel .announcement-message) {
  color: rgba(66,33,11,0.9);
  margin-top: 6px;
}
:deep(.announcements-panel .announcement-meta) {
  font-size: 0.75rem;
  color: rgba(66,33,11,0.7);
  margin-top: 6px;
}
</style>

