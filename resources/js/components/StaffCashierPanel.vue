<template>
  <div class="cashier-page">
    <!-- Header -->
    <header class="cashier-header">
      <div class="header-title">
        <h1>Cashier - {{ branchName }}</h1>
        <p>Process transactions and manage sales</p>
      </div>
      <button class="logout-btn" @click="confirmLogout">Logout</button>
    </header>

    <div v-if="!branchId" class="loading-text">
      Loading branch information...
    </div>

    <div v-else class="cashier-body">
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
            <div class="product-price">₱{{ fmt(p.price) }}</div>
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
    </div>

    <!-- Recent Transactions -->
    <section v-if="branchId" class="transactions-section">
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

    <!-- ANNOUNCEMENTS -->
    <section class="panel-block announcements-panel" style="margin-top:18px;">
      <div class="panel-header"><h2>Announcements</h2></div>
      <div class="panel-body panel-body--list">
        <div v-if="loadingAnnouncements">Loading...</div>
        <div v-else-if="announcements.length === 0">No announcements</div>
        <ul v-else class="announcement-list">
          <li v-for="a in announcements" :key="a.id" class="announcement-item">
            <div class="announcement-title">{{ a.title }}</div>
            <div class="announcement-meta">{{ new Date(a.created_at).toLocaleString() }} • {{ a.target }}</div>
            <div class="announcement-message">{{ a.message }}</div>
          </li>
        </ul>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// State
const branchId = ref(null)
const branchName = ref('')
const products = ref([])
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

// Announcements
const announcements = ref([])
const loadingAnnouncements = ref(false)

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

// Load staff profile and set branch
async function loadStaffProfile() {
  try {
    const res = await axios.get('/api/staff/profile', { withCredentials: true })
    if (res.data && res.data.ok && res.data.user) {
      const user = res.data.user
      branchId.value = user.branch_id
      branchName.value = user.branch_name || 'Unknown Branch'
      
      // Auto-load products and transactions for this branch
      if (branchId.value) {
        await loadProducts()
        await loadTransactions()
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
  } catch (e) {
    const msg = e.response?.data?.message || e.response?.data?.error || e.message || 'Checkout failed'
    checkoutError.value = msg
  } finally {
    isProcessing.value = false
  }
}

// Initialize on mount
onMounted(async () => {
  await loadStaffProfile()
  fetchAnnouncements()
})

// Logout functions
function confirmLogout() {
  if (isLoggingOut.value) return
  if (!confirm('Are you sure you want to logout?')) return
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
  grid-template-columns: 1fr 420px;
  gap: 20px;
  margin-bottom: 24px;
}

/* Product catalogue */
.product-catalogue,
.cart-section {
  background: rgba(255,255,255,0.95);
  border-radius: 12px;
  padding: 20px;
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

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 12px;
  max-height: 55vh;
  overflow-y: auto;
}

.product-card {
  background: #fff8f0;
  border: 1px solid rgba(255,211,107,0.3);
  border-radius: 8px;
  padding: 14px;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s;
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
.product-price { color: #e65100; font-weight: 700; font-size: 1.05rem; }
.product-stock { color: #888; font-size: 0.85rem; margin-top: 4px; }

/* Cart */
.cart-list {
  max-height: 32vh;
  overflow-y: auto;
  margin-bottom: 12px;
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

.tx-table-wrap { overflow-x: auto; }

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

/* Responsive */
@media (max-width: 860px) {
  .cashier-body {
    grid-template-columns: 1fr;
  }
}
</style>

