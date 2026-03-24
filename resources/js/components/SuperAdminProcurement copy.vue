<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Super Admin Procurement Panel'"
    :panelDescription="'Monitor and manage procurement across all branches. Select branch to view scoped data.'"
    :enableProfileUpdate="false"
    :canEditProfile="false"
    :canChangePassword="true"
    @logout="showLogoutConfirm = true"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <!-- Branch Selector -->
      <div class="branch-selector-section" style="margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
        <label style="font-weight: 600; color: #1e293b; margin-right: 0.75rem; font-size: 0.95rem;">Select Branch:</label>
        <select v-model="selectedBranch" @change="onBranchChange" style="padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; background: white; font-size: 0.9rem; min-width: 220px;">
          <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }} (ID: {{ b.id }})</option>
        </select>
        <span v-if="branchName" style="margin-left: 1rem; color: #64748b; font-size: 0.9rem;">Viewing: <strong>{{ branchName }}</strong></span>
      </div>

      <div class="hr-stats-grid">
        <div class="hr-stat-card hr-stat-card--total">
          <div class="hr-stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Total Suppliers</span>
            <span class="hr-stat-value">{{ dashboardTotals.totalSuppliers }}</span>
          </div>
        </div>
        <div class="hr-stat-card hr-stat-card--active">
          <div class="hr-stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Active Suppliers</span>
            <span class="hr-stat-value">{{ dashboardTotals.activeSuppliers }}</span>
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

      <section class="supplier-products" style="margin-top:1rem">
        <h2>Supplier Products ({{ branchName }})</h2>
        <div v-if="loadingProducts">Loading products...</div>
        <div v-else-if="!products.length">No products available for this branch.</div>
        <div v-else>
          <div>
            <h3 style="margin:0 0 8px 0">Published Products ({{ publishedProducts.length }})</h3>
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

      <section class="budget-requests" style="margin-top:1rem">
        <h2>Budget Requests ({{ branchName }})</h2>
        <p class="section-description">View and manage budget requests for the selected branch.</p>

        <div style="margin-bottom:0.5rem">
          <button class="btn-primary" v-if="!showBudgetForm" @click="showBudgetForm = true">+ New Budget Request</button>
          <button class="btn-outline" v-else @click="showBudgetForm = false">Cancel</button>
        </div>

        <div v-if="showBudgetForm" class="budget-form" style="margin-top:0.75rem; max-width:520px">
          <div class="form-group full-span">
            <label>Purpose</label>
            <textarea v-model="budgetForm.purpose" rows="3" placeholder="Describe the purpose of the budget"></textarea>
          </div>
          <div class="form-group">
            <label>Requested Amount</label>
            <input v-model="budgetForm.requested_amount" type="number" step="0.01" placeholder="0.00" />
          </div>
          <div style="margin-top:8px">
            <button class="btn-primary" @click="submitBudgetRequest" :disabled="budgetSubmitting">{{ budgetSubmitting ? 'Submitting...' : 'Submit Request' }}</button>
          </div>
          <div v-if="budgetError" class="error-msg" style="margin-top:8px">{{ budgetError }}</div>
        </div>

        <div style="margin-top:1rem">
          <h3>Branch Budget Requests</h3>
          <div v-if="budgetLoading">Loading...</div>
          <div v-else-if="!budgetRequests.length">No budget requests.</div>
          <table v-else class="data-table" style="width:100%">
            <thead>
              <tr><th>Date</th><th>Purpose</th><th>Amount</th><th>Status</th></tr>
            </thead>
            <tbody>
              <tr v-for="r in budgetRequests" :key="r.id">
                <td>{{ formatDate(r.created_at || r.date_requested) }}</td>
                <td>{{ r.purpose }}</td>
                <td>₱{{ r.requested_amount }}</td>
                <td>{{ r.status }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="requested-products" style="margin-top:1rem">
        <h2>Procurement Requests ({{ branchName }})</h2>
        <p class="section-description">Manage logistics requests and supplier orders for the selected branch.</p>

        <div v-if="requestedProductsLoading">Loading requests...</div>
        <div v-else-if="!requestedProducts.length">No procurement requests for this branch.</div>
        <div v-else>
          <div style="display:flex; gap:0.5rem; align-items:center; margin-bottom:1rem">
            <h3 style="margin:0">Pending Requests ({{ requestedProducts.length }})</h3>
            <button class="btn-primary" @click="loadRequestedProducts" style="padding:6px 12px; font-size:0.85rem">🔄 Refresh</button>
          </div>
          <div class="product-grid">
            <div v-for="p in requestedProducts" :key="'req-'+p.id" class="product-card">
              <div class="product-name">{{ p.name }}</div>
              <div class="product-meta">
                <div class="product-price">{{ formatPrice(p.price) }}</div>
                <div>
                  <template v-if="(p.procurement_status === 'pending' || p.status === 'pending') && !p.needs_supplier && (p.acknowledge_allowed === undefined ? true : p.acknowledge_allowed)">
                    <button class="btn-primary" @click="acknowledgeRequest(p)" style="padding:6px 10px; border-radius:8px">Acknowledge</button>
                  </template>
                  <template v-else-if="(p.procurement_status === 'pending' || p.status === 'pending') && p.needs_supplier">
                    <button class="btn-primary" @click="requestSupplier(p)" style="padding:6px 10px; border-radius:8px; background:#f59e0b;">Request Supplier for Product</button>
                  </template>
                  <template v-else-if="p.procurement_status === 'budget_pending' || p.status === 'budget_pending'">
                    <button class="btn-outline" disabled style="padding:6px 10px; border-radius:8px">Budget Pending</button>
                  </template>
                  <template v-else-if="p.procurement_status === 'pending_order_to_supplier' || p.status === 'pending_order_to_supplier' || p.procurement_status === 'ongoing_delivery' || p.status === 'ongoing_delivery'">
                    <div v-if="p.existingOrder" style="display:flex; gap:0.5rem; align-items:center">
                      <div class="status-badge" style="background:#fbbf24; color:#92400e; padding:6px 10px; border-radius:8px; font-size:0.9rem; font-weight:600;">
                        Order Pending (ID: {{ p.existingOrder.id }})
                      </div>
                      <div v-if="(p.existingOrder && (p.existingOrder.status === 'on_delivery' || p.existingOrder.status === 'ongoing_delivery' || p.existingOrder.status === 'fulfilled')) || p.procurement_status === 'delivery_pending' || p.procurement_status === 'ongoing_delivery'">
                        <button class="btn-primary" @click="markDeliveryComplete(p)" :disabled="isCompletingDelivery" style="padding:6px 10px; border-radius:8px">{{ isCompletingDelivery ? 'Submitting...' : 'Delivery Complete' }}</button>
                      </div>
                    </div>
                    <div v-else>
                      <button class="btn-primary" 
                        @click="placeOrder(p)" 
                        :disabled="isPlacingOrder"
                        style="padding:6px 10px; border-radius:8px">
                        {{ isPlacingOrder ? 'Placing...' : 'Place Order to Supplier' }}
                      </button>
                    </div>
                  </template>
                  <template v-else>
                    <span class="status-badge" style="background:#d1d5db; color:#6b7280; padding:4px 8px; border-radius:6px; font-size:0.85rem;">Completed</span>
                  </template>
                </div>
              </div>
              <div class="supplier-badge" style="margin-top:6px">{{ p.supplier_name || (p.supplier?.full_name || 'Unknown Supplier') }}</div>
            </div>
          </div>
        </div>
      </section>
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
        <h3>Logout from Super Admin Procurement?</h3>
        <p>This will end your current session.</p>
        <div class="logout-actions">
          <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
          <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'

const router = useRouter()
const userProfile = ref({})
const dashboardTotals = ref({ totalSuppliers: 0, activeSuppliers: 0, pendingRequests: 0 })
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)

// Branch management
const branches = ref([])
const selectedBranch = ref(null)
const branchName = computed(() => {
  const b = branches.value.find(x => x.id === selectedBranch.value)
  return b ? b.name : 'No branch selected'
})

// Budget request state (scoped to branch)
const budgetRequests = ref([])
const budgetLoading = ref(false)
const showBudgetForm = ref(false)
const budgetForm = ref({ purpose: '', requested_amount: '' })
const budgetSubmitting = ref(false)
const budgetError = ref('')

// Products for branch (published)
const products = ref([])
const loadingProducts = ref(false)
const publishedProducts = computed(() => (products.value || []).filter(p => p.is_published))

// Procurement requests / logistics requests
const requestedProducts = ref([])
const requestedProductsLoading = ref(false)
const isPlacingOrder = ref(false)
const isCompletingDelivery = ref(false)

// Default password utilities (kept but unused since no supplier modal)
const fetchedDefaultPassword = ref('Chikintayo_123')

async function loadBranches() {
  try {
    const res = await axios.get('/api/superadmin/logistics/branches', { withCredentials: true })
    branches.value = res.data || []
    if (branches.value.length && !selectedBranch.value) {
      selectedBranch.value = branches.value[0].id
    }
  } catch (e) {
    console.error('Failed loading branches', e)
    branches.value = []
  }
}

async function refreshAllData() {
  if (!selectedBranch.value) return
  try {
    const params = { branch_id: selectedBranch.value }
    const dash = await axios.get('/api/manager/procurement/dashboard', { params, withCredentials: true })
    dashboardTotals.value = dash.data || {}
  } catch (e) {
    dashboardTotals.value = { totalSuppliers: 0, activeSuppliers: 0, pendingRequests: 0 }
  }
}

function goToStaffManagement() {
  // Superadmin version - navigate to staff view for branch procurement managers
  router.push(`/super-admin/staff?role=procurement_manager&branch=${selectedBranch.value}`)
}

function onProfileUpdated(updatedProfile) { 
  userProfile.value = { ...userProfile.value, ...updatedProfile } 
}

function cancelLogout() { showLogoutConfirm.value = false }
async function confirmLogout() { 
  try { await axios.post('/api/logout', {}, { withCredentials: true }) } catch (e) {} finally { 
    localStorage.clear(); 
    sessionStorage.clear(); 
    router.push('/super-admin') 
  } 
}

async function onBranchChange() {
  await Promise.all([loadProducts(), loadRequestedProducts(), refreshAllData(), fetchBudgetRequests()])
}

async function loadProducts() {
  if (!selectedBranch.value) return
  loadingProducts.value = true
  try {
    const params = { branch_id: selectedBranch.value }
    const pres = await axios.get('/api/superadmin/logistics/products', { params, withCredentials: true })
    products.value = pres.data?.data ?? pres.data ?? []
  } catch (e) {
    console.warn('Failed to load procurement products', e)
    products.value = []
  } finally {
    loadingProducts.value = false
  }
}

async function loadRequestedProducts() {
  if (!selectedBranch.value) return
  requestedProductsLoading.value = true
  try {
    const params = { branch_id: selectedBranch.value }
    const res = await axios.get('/api/procurement-requests', { params, withCredentials: true })
    requestedProducts.value = res.data?.data ?? res.data ?? []
  } catch (e) {
    console.warn('Failed to load requested products', e)
    requestedProducts.value = []
  } finally {
    requestedProductsLoading.value = false
  }
}

async function acknowledgeRequest(product) {
  if (!confirm(`Acknowledge logistics request for ${product.name}? (Sends to finance for budget)`)) return
  try {
    const requestId = product.procurement_request_id || product.id
    const payload = { branch_id: selectedBranch.value }
    const res = await axios.post(`/api/procurement-requests/${requestId}/status`, payload, { withCredentials: true })
    alert('Request acknowledged and sent to finance')
    await loadRequestedProducts()
    await loadProducts()
  } catch (e) {
    alert('Failed to acknowledge request')
  }
}

async function requestSupplier(product) {
  if (!confirm(`Request suppliers to provide ${product.name} for branch ${branchName.value}?`)) return
  try {
    const requestId = product.procurement_request_id || product.id
    const payload = { branch_id: selectedBranch.value }
    const res = await axios.post(`/api/procurement-requests/${requestId}/broadcast`, payload, { withCredentials: true })
    alert(res.data?.message || 'Supplier request broadcasted')
    await loadRequestedProducts()
    await loadProducts()
    await refreshAllData()
  } catch (e) {
    console.error('requestSupplier failed', e)
    alert(e.response?.data?.message || 'Failed to request supplier')
  }
}

async function fetchBudgetRequests() {
  if (!selectedBranch.value) return
  budgetLoading.value = true
  try {
    const params = { branch_id: selectedBranch.value }
    const res = await axios.get('/api/procurement/budget/branch-requests', { params, withCredentials: true })
    if (res.data && res.data.ok) {
      budgetRequests.value = res.data.requests || []
    } else {
      budgetRequests.value = []
    }
  } catch (e) {
    console.error('Failed to load budget requests', e)
    budgetRequests.value = []
  } finally {
    budgetLoading.value = false
  }
}

async function submitBudgetRequest() {
  if (budgetSubmitting.value) return
  budgetError.value = ''
  if (!budgetForm.value.purpose || !budgetForm.value.requested_amount) {
    budgetError.value = 'Please fill purpose and amount.'
    return
  }
  budgetSubmitting.value = true
  try {
    const payload = {
      purpose: budgetForm.value.purpose,
      requested_amount: budgetForm.value.requested_amount,
      branch_id: selectedBranch.value
    }
    const res = await axios.post('/api/procurement/budget/create', payload, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Budget request created for branch')
      showBudgetForm.value = false
      budgetForm.value.purpose = ''
      budgetForm.value.requested_amount = ''
      await fetchBudgetRequests()
    } else {
      budgetError.value = res.data?.message || 'Failed to create request'
    }
  } catch (e) {
    console.error('Create budget request failed', e)
    budgetError.value = e.response?.data?.message || 'Failed to create request'
  } finally {
    budgetSubmitting.value = false
  }
}

function formatPrice(val) {
  if (val === null || val === undefined) return '0.00'
  const n = Number(val)
  if (Number.isNaN(n)) return '0.00'
  return n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-PH')
}

async function placeOrder(product) {
  if (!product || !product.id || isPlacingOrder.value) return
  
  isPlacingOrder.value = true
  
  try {
    const qtyInput = prompt('Enter quantity to order from supplier (leave blank to accept request quantity):', '')
    let qty = null
    if (qtyInput !== null && qtyInput !== '') {
      qty = parseInt(qtyInput, 10)
      if (Number.isNaN(qty) || qty < 1) {
        alert('Invalid quantity (must be 1+)')
        return
      }
    }

    const payload = { 
      branch_id: selectedBranch.value,
      quantity: qty 
    }
    
    const res = await axios.post(`/api/procurement.products/${product.id}/place-order`, payload, { withCredentials: true })
    
    alert(res.data.message || 'Order placed successfully')

    await loadProducts()
    await loadRequestedProducts()
    await refreshAllData()
  } catch (e) {
    console.error('Place order failed', e)
    alert(e.response?.data?.error || e.response?.data?.message || 'Failed to place order')
  } finally {
    isPlacingOrder.value = false
  }
}

async function markDeliveryComplete(product) {
  if (!product || !product.procurement_request_id) return
  if (!confirm(`Mark delivery complete for ${product.name}? This will set the request as completed.`)) return
  isCompletingDelivery.value = true
  try {
    const payload = { branch_id: selectedBranch.value }
    const res = await axios.post(`/api/procurement-requests/${product.procurement_request_id}/complete`, payload, { withCredentials: true })
    alert(res.data?.message || 'Procurement request marked completed')
    await loadRequestedProducts()
    await loadProducts()
    await refreshAllData()
  } catch (e) {
    console.error('Mark delivery complete failed', e)
    alert(e.response?.data?.error || e.response?.data?.message || 'Failed to mark delivery complete')
  } finally {
    isCompletingDelivery.value = false
  }
}

onMounted(async () => {
  try { 
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) 
  } catch (e) {}
  
  await loadBranches()
  
  if (selectedBranch.value) {
    try {
      const res = await axios.get('/api/superadmin/profile', { withCredentials: true })
      userProfile.value = res.data.user || {}
    } catch (e) {
      // Fallback to generic superadmin profile
      userProfile.value = { role: 'SUPER_ADMIN', full_name: 'Super Admin' }
    }
    
    await Promise.all([
      refreshAllData(),
      loadProducts(),
      loadRequestedProducts(),
      fetchBudgetRequests()
    ])
  }
})

defineExpose({ refreshAllData, onProfileUpdated })
watch(selectedBranch, onBranchChange)
</script>

<style scoped>
/* Exact copy from ProcurementManagerPanel.vue */
.hr-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.hr-stat-card { background: white; border-radius: 8px; padding: 1rem; display:flex; gap:0.75rem; align-items:center; color: #1b1b1f; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.hr-stat-card:hover { transform: translateY(-1px); transition: all 0.2s; }
.hr-stat-value { font-weight:700; font-size:1.25rem; color: #1e40af; }
.hr-stat-label { font-size: 0.9rem; color: #64748b; }
.hr-stat-icon svg { color: #3b82f6; }

/* Product grid styles */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1.25rem;
  margin-top: 1rem;
}

.product-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  border: 1px solid #f1f5f9;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.product-name { 
  font-weight: 700; 
  color: #111827; 
  font-size: 1.05rem; 
  line-height: 1.3;
}
.product-meta { 
  display:flex; 
  justify-content:space-between; 
  align-items:center; 
}
.product-price { 
  color: #059669; 
  font-weight:700; 
  font-size: 1.1rem;
}
.supplier-badge { 
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); 
  color: #475569; 
  padding: 0.375rem 0.75rem; 
  border-radius: 20px; 
  font-size: 0.85rem;
  font-weight: 500;
  border: 1px solid #e2e8f0;
}
.status-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

/* Form styles */
.form-group { 
  display: flex; 
  flex-direction: column; 
  gap: 0.5rem; 
  margin-bottom: 1rem; 
}
.form-group label { 
  color: #374151; 
  font-weight: 600; 
  font-size: 0.9rem; 
}
.form-group input, .form-group textarea { 
  padding: 0.75rem; 
  border: 1px solid #d1d5db; 
  border-radius: 8px; 
  background: #fff; 
  color: #111827; 
  font-size: 0.95rem; 
  transition: border-color 0.2s; 
}
.form-group input:focus, .form-group textarea:focus { 
  outline: none; 
  border-color: #3b82f6; 
  box-shadow: 0 0 0 3px rgba(59,130,246,0.1); 
}

.budget-form {
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.error-msg { 
  color: #dc2626; 
  background: #fef2f2; 
  padding: 0.75rem; 
  border-radius: 8px; 
  border-left: 4px solid #dc2626; 
}

/* Buttons */
.btn-primary {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 25px rgba(79,70,229,0.4); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.btn-outline {
  background: transparent;
  color: #64748b;
  border: 1px solid #d1d5db;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
}

/* Data table */
.data-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.data-table th {
  background: #f8fafc;
  padding: 1rem 1.25rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e2e8f0;
}
.data-table td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  color: #475569;
}
.data-table tr:hover {
  background: #f8fafc;
}

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Panel section styles */
.panel-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.section-title {
  color: #1e293b;
  font-size: 1.3rem;
  margin: 0 0 0.75rem 0;
  font-weight: 700;
}
.section-description {
  color: #64748b;
  margin-bottom: 1.5rem;
  font-size: 0.95rem;
}

/* Loading */
.loading-container {
  padding: 2rem;
  text-align: center;
  color: #64748b;
}
.small { padding: 1rem; font-size: 0.9rem; }

/* Logout modal */
.logout-confirm-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3000;
}
.logout-confirm-box {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  max-width: 420px;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.logout-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 1.5rem;
}
.btn-cancel {
  padding: 0.75rem 1.5rem;
  border: 1px solid #d1d5db;
  background: white;
  color: #64748b;
  border-radius: 8px;
  cursor: pointer;
}
.btn-confirm {
  padding: 0.75rem 1.5rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

/* Side panel (from OwnerPanelLayout) */
.panel-block {
  background: #f8fafc;
  border-radius: 12px;
  margin-bottom: 1rem;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.panel-header {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  padding: 1rem 1.25rem;
}
.panel-body {
  padding: 1.25rem;
}
.panel-body--list .side-item {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
  margin-bottom: 0.5rem;
  border-left: 4px solid #3b82f6;
}
</style>
