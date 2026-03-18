<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Manager Procurement Panel'"
    :panelDescription="'Manage procurement staff, view procurement reports, and monitor procurement status.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    @logout="showLogoutConfirm = true"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <div class="hr-stats-grid">
        <div class="hr-stat-card hr-stat-card--total">
          <div class="hr-stat-icon">
            <!-- icon reused from HR panel -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Total Suppliers</span>
{{ dashboardTotals.totalSuppliers }}
          </div>
        </div>
        <div class="hr-stat-card hr-stat-card--active">
          <div class="hr-stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Active Suppliers</span>
{{ dashboardTotals.activeSuppliers }}
          </div>
        </div>
        <div class="hr-stat-card hr-stat-card--leave">
          <div class="hr-stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Pending Requests</span>
            <span class="hr-stat-value">{{ dashboardTotals.pendingRequests }}</span>
          </div>
        </div>
      </div>
      <div class="panel-actions" style="margin-top:1rem">
        <button class="btn-primary" @click="openAddSupplier">Add Supplier</button>
      </div>
      <section class="supplier-products" style="margin-top:1rem">
        <h2>Supplier Products (this branch)</h2>
        <div v-if="loadingProducts">Loading products...</div>
        <div v-else-if="!products.length">No products available in your branch.</div>
        <div v-else>
          <div v-if="pendingProducts.length" style="margin-bottom:0.5rem">
            <h3 style="margin:0 0 8px 0">Pending Supplier Products</h3>
            <div class="product-grid">
              <div v-for="p in pendingProducts" :key="'pending-'+p.id" class="product-card">
                <div class="product-name">{{ p.name }}</div>
                <div class="product-meta">
                  <div class="product-price">{{ formatPrice(p.price) }}</div>
                  <div>
                    <button class="btn-primary" @click="placeOrder(p)" style="padding:6px 10px; border-radius:8px">Place Order</button>
                  </div>
                </div>
                <div class="supplier-badge" style="margin-top:6px">{{ p.supplier_name || 'Unknown Supplier' }}</div>
              </div>
            </div>
          </div>

          <div>
            <h3 style="margin:0 0 8px 0">Published Products</h3>
            <div class="product-grid">
              <div v-for="p in publishedProducts" :key="p.id" class="product-card">
                <div class="product-name">{{ p.name }}</div>
                <div class="product-meta">
                  <div class="product-price">{{ formatPrice(p.price) }}</div>
                  <div class="supplier-badge">{{ p.supplier_name || 'Unknown Supplier' }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <transition name="fade">
        <div v-if="showAddModal" class="modal-backdrop" @click.self="closeAddSupplier">
          <div class="modal">
            <div class="modal-card">
              <div class="modal-header">
                <h3>Create Supplier Account</h3>
              </div>
              <div class="modal-body">
                <div class="form-group full-span">
                  <label>Full Name</label>
                  <input v-model="supplierForm.fullName" type="text" placeholder="Supplier full name" />
                </div>

                <div class="form-group full-span">
                  <label>Business Name</label>
                  <input v-model="supplierForm.businessName" type="text" placeholder="Company/Business name" />
                </div>

                <div class="form-group">
                  <label>Username</label>
                  <input v-model="supplierForm.username" type="text" placeholder="username" />
                </div>

                <div class="form-group">
                  <label>Email</label>
                  <input v-model="supplierForm.email" type="email" placeholder="supplier@example.com" />
                </div>

                <div class="form-group">
                  <label>Phone</label>
                  <input v-model="supplierForm.phone" type="text" placeholder="optional" />
                </div>

                <div class="form-group password-group">
                  <label>Default Password</label>
                  <div class="password-display-container">
                    <!-- Password Display Card -->
                    <div class="password-display-card">
                      <div class="password-display-label">Default Password (will be set automatically):</div>
                      <div class="password-display-value">
                        <span class="password-text">{{ fetchedDefaultPassword || 'Chikintayo_123' }}</span>
                        <button type="button" class="btn btn-primary btn-copy" @click="copyDefaultToClipboard">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                          </svg>
                          Copy Password
                        </button>
                      </div>
                      <div class="form-hint">This password will be assigned to the supplier account. Leave blank to use default (backend auto-generates if needed).</div>
                    </div>
                    
                    <!-- Loading state -->
                    <div v-if="fetchingDefaultPassword" class="password-loading">
                      <span style="color:#6b7280; font-size:0.9rem;">Loading default password...</span>
                    </div>
                  </div>
                </div>

                <div v-if="formError" class="error-msg">{{ formError }}</div>
                <div v-if="formSuccess" class="success-msg">{{ formSuccess }}</div>
              </div>
              <div class="modal-footer">
                <button class="btn-outline" @click="closeAddSupplier" :disabled="isSubmitting">Cancel</button>
                <button class="btn-primary" @click="submitAddSupplier" :disabled="isSubmitting">Create</button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </template>

    <template #side>
      <section class="panel-block hr-settings-panel">
        <div class="panel-header"><h2>Procurement Settings</h2></div>
        <div class="panel-body panel-body--list">
          <div class="side-item"><span>View procurement orders and supplier info</span></div>
        </div>
      </section>
    </template>
  
  </OwnerPanelLayout>

  <transition name="fade">
    <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
      <div class="logout-confirm-box">
        <h3>Logout from Procurement Manager Panel?</h3>
        <p>This will end your current session for Chikin Tayo Manager.</p>
        <div class="logout-actions">
          <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
          <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'

const router = useRouter()
const userProfile = ref({})
const dashboardTotals = ref({ totalSuppliers: 0, activeSuppliers: 0, pendingRequests: 0 })
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)

// Products for procurement manager (branch-scoped)
const products = ref([])
const loadingProducts = ref(false)

const pendingProducts = computed(() => (products.value || []).filter(p => !p.is_published))
const publishedProducts = computed(() => (products.value || []).filter(p => p.is_published))

// Add Supplier modal state
const showAddModal = ref(false)
const isSubmitting = ref(false)
const supplierForm = ref({ 
  username: '', 
  email: '', 
  fullName: '', 
  businessName: '',
  phone: '', 
  password: '' 
})
const formError = ref('')
const formSuccess = ref('')

// Default password state
const fetchedDefaultPassword = ref(null)
const fetchingDefaultPassword = ref(false)

async function refreshAllData() {
  try {
    const dash = await axios.get('/api/manager/procurement/dashboard', { withCredentials: true })
    dashboardTotals.value = dash.data || {}
  } catch (e) {
    dashboardTotals.value = { totalSuppliers: 0, activeSuppliers: 0, pendingRequests: 0 }
  }
}

function goToStaffManagement() {
  window.location.href = '/manager/procurement/staff-management'
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/manager/procurement/profile', { withCredentials: true })
    userProfile.value = res.data.user || {}
  } catch (e) {
    // ignore
  }
  await refreshAllData()
  try {
    await loadProducts()
  } catch (e) {}
})

function cancelLogout() { showLogoutConfirm.value = false }
async function confirmLogout() { 
  try { await axios.post('/api/logout', {}, { withCredentials: true }) 
  } catch (e) {} finally { 
    localStorage.clear(); 
    sessionStorage.clear(); 
    window.location.replace('/staff-landing') 
  } 
}

function onProfileUpdated(updatedProfile) { 
  userProfile.value = { ...userProfile.value, ...updatedProfile } 
}

defineExpose({ refreshAllData, onProfileUpdated })

async function fetchDefaultPassword() {
  const userRole = window.userRole || '';
  if (userRole !== 'OWNER' && userRole !== 'ADMIN' && userRole !== 'SUPER_ADMIN' && userRole !== 'SUPERADMIN') {
    fetchedDefaultPassword.value = 'Chikintayo_123';
    return;
  }
  
  if (fetchingDefaultPassword.value) return
  fetchingDefaultPassword.value = true
  try {
    const res = await axios.get('/api/admin/config/default-password', { withCredentials: true })
    if (res.data && res.data.success && res.data.default_password) {
      fetchedDefaultPassword.value = res.data.default_password
    } else {
      fetchedDefaultPassword.value = 'Chikintayo_123'
    }
  } catch (e) {
    fetchedDefaultPassword.value = 'Chikintayo_123'
  } finally {
    fetchingDefaultPassword.value = false
  }
}

function copyDefaultToClipboard() {
  const passwordToCopy = fetchedDefaultPassword.value || 'Chikintayo_123'
  if (!passwordToCopy) return
  try {
    navigator.clipboard?.writeText(passwordToCopy)
    alert('Password copied to clipboard: ' + passwordToCopy)
  } catch (e) {
    const textArea = document.createElement('textarea')
    textArea.value = passwordToCopy
    document.body.appendChild(textArea)
    textArea.select()
    document.execCommand('copy')
    document.body.removeChild(textArea)
    alert('Password copied to clipboard: ' + passwordToCopy)
  }
}

function openAddSupplier() {
  supplierForm.value = { 
    username: '', 
    email: '', 
    fullName: '', 
    businessName: '',
    phone: '', 
    password: '' 
  }
  formError.value = ''
  formSuccess.value = ''
  fetchedDefaultPassword.value = null
  showAddModal.value = true
  fetchDefaultPassword()
  console.log('openAddSupplier called')
}

function closeAddSupplier() {
  if (isSubmitting.value) return
  showAddModal.value = false
}

async function submitAddSupplier() {
  if (isSubmitting.value) return
  isSubmitting.value = true
  try {
    const payload = {
      username: supplierForm.value.username,
      email: supplierForm.value.email,
      fullName: supplierForm.value.fullName,
      businessName: supplierForm.value.businessName,
      phone: supplierForm.value.phone,
      password: supplierForm.value.password || undefined, // optional
    }
    const res = await axios.post('/api/manager/procurement/suppliers', payload, { withCredentials: true })
    // refresh and close
    await refreshAllData()
    showAddModal.value = false
    alert(res.data.message || 'Supplier created successfully')
  } catch (err) {
    const msg = err?.response?.data?.message || 'Failed to create supplier'
    formError.value = msg
    alert(msg)
  } finally {
    isSubmitting.value = false
  }
}

async function loadProducts() {
  loadingProducts.value = true
  try {
    const pres = await axios.get('/api/manager/procurement/products', { withCredentials: true })
    if (pres && pres.data) {
      // supports both {data: [...] } and direct array
      if (Array.isArray(pres.data)) products.value = pres.data
      else if (Array.isArray(pres.data.data)) products.value = pres.data.data
      else products.value = []
    }
  } catch (e) {
    console.warn('Failed to load procurement products', e)
    products.value = []
  } finally {
    loadingProducts.value = false
  }
}

// Helper to format price nicely for display
function formatPrice(val) {
  if (val === null || val === undefined) return '₱0.00'
  const n = Number(val)
  if (Number.isNaN(n)) return '₱0.00'
  return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function placeOrder(product) {
  if (!product || !product.id) return
  // Prompt for quantity (optional)
  const qtyInput = prompt('Enter quantity to add into inventory (leave blank to accept existing stock):', '0')
  let qty = null
  if (qtyInput !== null && qtyInput !== '') {
    qty = parseInt(qtyInput, 10)
    if (Number.isNaN(qty) || qty < 0) {
      alert('Invalid quantity')
      return
    }
  }

  try {
    const payload = {}
    if (qty !== null) payload.quantity = qty
    const res = await axios.post(`/api/manager/procurement/products/${product.id}/place-order`, payload, { withCredentials: true })
    alert(res.data.message || 'Product placed into inventory')
    await loadProducts()
    await refreshAllData()
  } catch (e) {
    console.warn('Failed to place order', e)
    alert('Failed to place order')
  }
}
</script>

<style scoped>
/* Reuse styles from HR panel; keep minimal overrides */
.hr-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.hr-stat-card { background: white; border-radius: 8px; padding: 1rem; display:flex; gap:0.75rem; align-items:center; color: #1b1b1f; }
.hr-stat-value { font-weight:700; font-size:1.25rem; }

/* Modal overrides for better contrast and layout inside this panel */
.modal {
  background: #ffffff;
  color: #1b1b1f;
  border-radius: 12px;
  width: 92%;
  max-width: 720px;
  margin: 0 12px;
  box-shadow: 0 18px 40px rgba(0,0,0,0.35);
}

.modal-card { overflow: hidden; }

.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #1b1b1f;
}

.modal-body { 
  padding: 1rem 1.25rem; 
  display: grid; 
  grid-template-columns: 1fr 1fr; 
  gap: 0.75rem; 
}
.modal-body .form-group { display: flex; flex-direction: column; gap: 6px; }
.modal-body .form-group.full-span { grid-column: 1 / -1; }
.modal-body label { color: #333; font-size: 0.85rem; }
.modal-body input { padding: 8px 10px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #111; }

.error-msg { color: #a33; grid-column: 1 / -1; padding-top: 6px; }
.success-msg { color: #167a3e; grid-column: 1 / -1; padding-top: 6px; }

.modal-footer { padding: 10px 14px; display:flex; justify-content:flex-end; gap:0.5rem; background: #fafafa; }
.modal-footer .btn-outline { background: transparent; border: 1px solid #ccc; color: #333; }
.modal-footer .btn-primary { background: #4b1ddf; color: #fff; }

/* Password Display Styles */
.password-display-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.password-display-card {
  background: linear-gradient(135deg, #fef3e2 0%, #fde8d4 100%);
  border: 2px solid #ff9a56;
  border-radius: 10px;
  padding: 1.25rem;
}

.password-display-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #92400e;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.password-display-value {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.password-text {
  font-family: 'Courier New', monospace;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  background: #fff;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  letter-spacing: 1px;
}

.btn-copy {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  white-space: nowrap;
  background: #4b1ddf;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.password-display-card .form-hint {
  margin-top: 0.75rem;
  font-size: 0.85rem;
  color: #92400e;
}

.password-loading {
  display: flex;
  align-items: center;
  padding: 0.5rem;
}

/* Ensure backdrop has high z-index inside component scope */
.modal-backdrop { z-index: 2000; }

/* Product grid styles for supplier products */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
  margin-top: 0.75rem;
}

.product-card {
  background: #ffffff;
  border-radius: 10px;
  padding: 0.9rem;
  box-shadow: 0 6px 18px rgba(15,23,42,0.06);
  border: 1px solid #eef2f6;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.product-name { font-weight: 700; color: #111827; }
.product-meta { display:flex; justify-content:space-between; align-items:center; gap:0.5rem }
.product-price { color: #0b6e3a; font-weight:700 }
.supplier-badge { background: #f3f4f6; color: #374151; padding: 4px 8px; border-radius: 12px; font-size: 0.85rem }
</style>
