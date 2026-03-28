<template>
  <div class="cashier-page">
    <!-- Back to Dashboard Button - Same as Finance Panel -->
    <button @click="router.push('/super-admin-panel')" class="btn-secondary back-to-dashboard-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back to Super Admin
    </button>

    <!-- Header -->
    <header class="cashier-header">
      <div class="header-title">
        <h1>Cashier</h1>
        <p>Process transactions and manage sales</p>
      </div>
    </header>

    <!-- Branch Selector -->
    <div class="branch-filter">
      <label>Select Branch:</label>
      <select v-model="selectedBranch" @change="loadProducts">
        <option value="">-- Choose a branch --</option>
        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
      </select>
    </div>

    <div v-if="selectedBranch" class="cashier-body">
      <!-- LEFT: Product catalogue -->
      <section class="product-catalogue">
        <h2>Products</h2>
        <div class="search-bar">
          <input v-model="productSearch" type="text" placeholder="Search products..." />
        </div>
        <div v-if="isLoadingProducts" class="loading-text">Loading products...</div>
        <div v-else-if="filteredProducts.length === 0" class="empty-text">No products available</div>
        <div v-else class="product-grid">
          <div
            v-for="p in filteredProducts"
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
            <div v-if="p.computed_cost" class="product-cost">Cost: ₱{{ fmt(p.computed_cost) }}</div>
            <div class="product-stock" :class="{ 'stock-zero': p.stock <= 0 }">
              {{ p.stock > 0 ? 'Stock: ' + p.stock : 'Out of stock' }}
            </div>
          </div>
        </div>
      </section>

      <!-- RIGHT: Cart + Payment -->
      <section class="cart-section">
        <h2>Current Order</h2>

        <div v-if="cart.length === 0" class="empty-text">No items in cart. Click a product to add.</div>

        <div v-else class="cart-list">
          <div v-for="(item, idx) in cart" :key="item.product_id" class="cart-item">
            <div class="cart-item-info">
              <span class="cart-item-name">{{ item.name }}</span>
              <div style="text-align:right">
                <span class="cart-item-price">₱{{ fmt(item.unit_price) }}</span>
                <div v-if="item.computed_cost" class="cart-item-cost">Cost: ₱{{ fmt(item.computed_cost) }}</div>
              </div>
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

        <!-- Announcements -->
            <div class="announcements-card" style="margin:12px 0;padding:10px;border-radius:8px;background:var(--surface-card);border:1px solid rgba(255,211,107,0.4)">
              <h3 style="margin:0 0 8px;color:var(--text-dark);font-size:0.95rem">Announcements</h3>
          <div v-if="loadingAnnouncements" class="loading-text">Loading announcements...</div>
          <div v-else>
            <div v-if="announcements.length">
              <div v-for="a in announcements" :key="a.id" style="margin-bottom:8px;padding:8px;border-radius:6px;background:var(--surface-card)">
                <div style="font-weight:700;color:var(--text-dark)">{{ a.title }}</div>
                <div style="color:rgba(66,33,11,0.9)">{{ a.message }}</div>
                <div style="font-size:0.75rem;color:rgba(66,33,11,0.7);margin-top:6px">{{ formatDate(a.created_at) }}</div>
              </div>
            </div>
            <div v-else class="empty-text">No announcements</div>
          </div>
        </div>

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
    </div>

    <!-- Recent Transactions -->
    <section v-if="selectedBranch" class="transactions-section">
      <h2>Recent Transactions</h2>
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
              <td>{{ formatDate(tx.ordered_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// State
const branches = ref([])
const selectedBranch = ref('')
const products = ref([])
const productSearch = ref('')
const isLoadingProducts = ref(false)

const cart = ref([])
const customerName = ref('')
const amountPaid = ref(null)
const isProcessing = ref(false)
const pendingOrderCode = ref(null)  // Track latest pending order for cancel
const checkoutError = ref('')
const checkoutSuccess = ref('')
const transactions = ref([])

// Announcements state
const announcements = ref([])
const loadingAnnouncements = ref(false)

async function fetchAnnouncements() {
  loadingAnnouncements.value = true
  try {
    const res = await axios.get('/api/announcements', { withCredentials: true })
    if (res.data) {
      if (Array.isArray(res.data)) announcements.value = res.data
      else if (Array.isArray(res.data.announcements)) announcements.value = res.data.announcements
      else if (Array.isArray(res.data.data)) announcements.value = res.data.data
      else announcements.value = []
    }
  } catch (e) {
    console.error('Failed to load announcements:', e)
    announcements.value = []
  } finally {
    loadingAnnouncements.value = false
  }
}

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

const totalItems = computed(() => cart.value.reduce((s, i) => s + i.quantity, 0))
const subtotal = computed(() => cart.value.reduce((s, i) => s + i.subtotal, 0))

// Defaults (frontend fallback) - VAT and concession percentages
const FRONTEND_VAT = 0.12
const FRONTEND_PWD = 20
const FRONTEND_SENIOR = 20

const discountType = ref('none')
const discountPercent = ref(0)

const computedDiscountPercent = computed(() => {
  if (discountType.value === 'pwd') return FRONTEND_PWD
  if (discountType.value === 'senior') return FRONTEND_SENIOR
  if (discountType.value === 'discount') return Number(discountPercent.value) || 0
  return 0
})

const discountAmount = computed(() => (subtotal.value * (computedDiscountPercent.value || 0)) / 100)
const taxable = computed(() => Math.max(0, subtotal.value - discountAmount.value))
const vatPercent = FRONTEND_VAT
const vatAmount = computed(() => taxable.value * vatPercent)
const grandTotal = computed(() => Number((taxable.value + vatAmount.value).toFixed(2)))

const canCheckout = computed(() =>
  cart.value.length > 0 && amountPaid.value >= grandTotal.value && grandTotal.value > 0
)

// Navigation
function goBack() {
  router.push('/super-admin-panel')
}

// Fetch branches on mount
onMounted(async () => {
  try {
    const res = await axios.get('/api/superadmin/cashier/branches')
    branches.value = res.data
    // load announcements for cashier/staff
    fetchAnnouncements()
  } catch (e) {
    console.error('Failed to load branches', e)
  }
})

// Load products when branch changes
async function loadProducts() {
  if (!selectedBranch.value) {
    products.value = []
    return
  }
  isLoadingProducts.value = true
  try {
    const res = await axios.get('/api/superadmin/cashier/products', {
      params: { branch_id: selectedBranch.value }
    })
    products.value = res.data
  } catch (e) {
    console.error('Failed to load products', e)
  } finally {
    isLoadingProducts.value = false
  }
  loadTransactions()
}

// Load recent transactions
async function loadTransactions() {
  try {
    const res = await axios.get('/api/superadmin/cashier/transactions', {
      params: { branch_id: selectedBranch.value }
    })
    transactions.value = res.data
  } catch (e) {
    console.error('Failed to load transactions', e)
  }
}

// Cart operations
function addToCart(product) {
  const existing = cart.value.find(i => i.product_id === product.id)
  if (existing) {
    if (existing.quantity < product.stock) {
      existing.quantity++
      existing.subtotal = existing.quantity * existing.unit_price
    }
  } else {
    cart.value.push({
      product_id: product.id,
      name: product.name,
      unit_price: Number(product.price),
      computed_cost: product.computed_cost ? Number(product.computed_cost) : null,
      quantity: 1,
      subtotal: Number(product.price),
      max_stock: product.stock,
    })
  }
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
    // Cancel pending order if exists and cart empty (user cancelled after checkout)
    try {
      await axios.post('/api/superadmin/cashier/cancel-pending', {
        order_code: pendingOrderCode.value,
        branch_id: selectedBranch.value
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
      branch_id: selectedBranch.value,
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
    checkoutSuccess.value = `${res.data.message} Order: ${res.data.order.order_code} | Change: ₱${fmt(res.data.change)}`

    // Clear the cart and reset the form — cashier transactions complete immediately
    clearCart()

    // Refresh transactions to show the completed order
    await loadTransactions()

    // Refresh products so updated stocks are reflected immediately in the UI
    await loadProducts()
  } catch (e) {
    const msg = e.response?.data?.message || e.response?.data?.error || e.message || 'Checkout failed'
    checkoutError.value = msg
  } finally {
    isProcessing.value = false
  }
}
</script>

<style scoped>
/* Page background and container */
.cashier-page,
.cashier-container {
  background-color: #F8FAFC;
  padding: 20px;
  min-height: 100vh;
}

/* Basic typography for headers */
.cashier-page h1,
.cashier-page h2 {
  font-family: 'Inter', 'Poppins', sans-serif;
  font-weight: 800;
  color: var(--text-dark);
  margin: 0 0 8px;
}

/* Larger responsive H1 like StaffIndex */
.cashier-page h1 {
  font-size: clamp(2.4rem, 3.2vw, 3.0rem);
  line-height: 1.1;
}

/* Global button styles (visual only) */
button {
  background: #0066FF;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 14px;
  font-weight: 600;
  transition: background 0.3s ease, transform 0.06s ease;
  cursor: pointer;
}

button:hover { background: #3B82F6; }

/* Secondary / accent buttons */
.btn-secondary,
.btn-cancel {
  background: #FACC15;
  color: #1F2937;
  border: none;
  border-radius: 8px;
  padding: 8px 12px;
}

/* Keep confirm button prominent */
.btn-confirm {
  background: #0066FF;
  color: #ffffff;
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 700;
}
.btn-confirm:disabled { opacity: 0.55; cursor: not-allowed; }

/* Inputs / selects */
input,
select,
textarea {
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  padding: 8px;
  font-size: 14px;
  background: #fff;
  box-sizing: border-box;
}

input:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #0066FF;
  box-shadow: 0 0 0 3px rgba(59,130,246,0.08);
}

/* Cards and panels */
.product-catalogue,
.cart-section,
.transactions-section,
.branch-filter,
.totals-box,
.announcements-card,
.product-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 15px;
  box-shadow: 0 4px 12px rgba(2,6,23,0.04);
}

/* Keep older helper classes available */
.cashier-card,
.cart-panel,
.product-panel {
  background: #ffffff;
  border-radius: 12px;
  padding: 15px;
}

/* Layout */
.cashier-header { display:flex; align-items:center; gap:16px; margin-bottom:12px; }
.back-to-dashboard-btn { display:inline-flex; align-items:center; gap:8px; }
.back-icon { width:16px; height:16px; }

.branch-filter { display:flex; align-items:center; gap:10px; margin-bottom:16px; }
.branch-filter label { font-weight:600; color:var(--text-dark); }

.cashier-body { display:grid; grid-template-columns: 1fr 420px; gap:20px; margin-bottom:20px; }

.product-catalogue h2,
.cart-section h2,
.transactions-section h2 { font-size:1.1rem; }

.search-bar input { width:100%; padding:10px; border-radius:8px; border:1px solid #E5E7EB; }

.product-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(150px,1fr)); gap:12px; max-height:55vh; overflow-y:auto; }

.product-card {
  cursor:pointer;
  transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
  border: 1px solid rgba(15,23,42,0.03);
}
.product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(2,6,23,0.06); border-color: rgba(59,130,246,0.18); }
.product-card.out-of-stock { opacity:0.6; cursor:not-allowed; }

.product-name { font-weight:700; color:var(--text-dark); margin-bottom:6px; }
.product-type { display: inline-block; font-size: 0.75rem; font-weight: 600; padding: 3px 8px; border-radius: 6px; margin-bottom: 6px; }
.product-type.type-individual { background: #dbeafe; color: #1e40af; }
.product-type.type-per_pack { background: #d1fae5; color: #065f46; }
.product-type.type-both { background: #fef3c7; color: #92400e; }
.product-price { color:var(--text-dark); font-weight:800; font-size:1.05rem; }
.product-stock { color:rgba(66,33,11,0.6); font-size:0.85rem; }

/* Cart */
.cart-list { max-height:32vh; overflow-y:auto; margin-bottom:12px; }
.cart-item { padding:10px 0; border-bottom:1px solid rgba(2,6,23,0.04); display:flex; flex-direction:column; gap:6px; }
.cart-item-info { display:flex; justify-content:space-between; }
.cart-item-name { font-weight:700; color:var(--text-dark); }
.cart-item-price { color:rgba(66,33,11,0.8); }

.cart-item-controls { display:flex; align-items:center; gap:8px; }
.qty-btn { width:32px; height:32px; border-radius:8px; border:1px solid var(--border-stroke); background:#fff; color:var(--text-dark); }
.qty-btn:hover { background: #F1F5F9; }
.qty-input { width:60px; text-align:center; border-radius:8px; border:1px solid #E5E7EB; }

.cart-item-subtotal { margin-left:auto; font-weight:700; color:#0066FF; min-width:70px; text-align:right; }
.remove-btn { background:none; border:none; color:#ef4444; cursor:pointer; }

/* Totals */
.totals-box { padding:12px; }
.total-row { display:flex; justify-content:space-between; padding:6px 0; color:#0f172a; }
.total-grand { font-size:1.2rem; font-weight:800; color:#0066FF; border-top:2px solid rgba(6,95,212,0.06); padding-top:8px; margin-top:6px; }

/* Payment */
.form-group label { display:block; margin-bottom:6px; font-weight:600; color:#0f172a; }
.form-group input { width:100%; }

.change-display { background: rgba(16,185,129,0.08); color:#065f46; padding:10px; border-radius:8px; text-align:center; }
.insufficient-display { background: rgba(239,68,68,0.06); color:#7f1d1d; padding:10px; border-radius:8px; text-align:center; }

.error-msg { color:#b91c1c; background: rgba(255,234,234,0.8); padding:8px; border-radius:8px; margin-bottom:10px; }
.success-msg { color:#064e3b; background: rgba(220,252,231,0.9); padding:8px; border-radius:8px; margin-bottom:10px; }

.checkout-actions { display:flex; gap:10px; justify-content:flex-end; }

/* Transactions table */
.tx-table th { background:#ffffff; color:#0f172a; font-weight:700; padding:10px 12px; border-bottom:2px solid rgba(2,6,23,0.04); }
.tx-table td { padding:8px 12px; border-bottom:1px solid rgba(2,6,23,0.03); color:#0f172a; }
.tx-table tbody tr:hover { background: #ffffff; }
.item-badge { display:inline-block; background: rgba(59,130,246,0.08); color:#0f172a; padding:4px 8px; border-radius:9999px; font-size:0.8rem; margin:2px; }

/* Misc */
.loading-text, .empty-text { color:#6b7280; text-align:center; padding:20px 0; }

@media (max-width:860px) { .cashier-body { grid-template-columns: 1fr; } }
</style>
