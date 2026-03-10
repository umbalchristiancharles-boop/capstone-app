<template>
  <div class="logistics-page">
    <!-- Back to Dashboard Button - Same as Finance Panel -->
    <button @click="router.push('/super-admin-panel')" class="btn-secondary back-to-dashboard-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back to Super Admin
    </button>

    <!-- Header -->
    <header class="logistics-header">
      <div class="header-title">
        <h1>Super Admin Logistics</h1>
        <p>Manage inventory across all branches</p>
      </div>
    </header>

    <!-- Branch Filter -->
    <div class="branch-filter">
      <label>Select Branch:</label>
      <select v-model="selectedBranch" @change="filterByBranch">
        <option value="">All Branches</option>
        <option v-for="branch in branches" :key="branch.id" :value="branch.id">
          {{ branch.name }}
        </option>
      </select>
    </div>

    <!-- Product List -->
    <ProductList
      ref="productListRef"
      :fetchUrl="fetchUrl"
      :products="internalProducts"
      @open-add="openAddProduct"
      @edit="handleEdit"
      @delete="deleteProduct"
      @adjust="openAdjustModal"
      @count="openCountModal"
    />

    <!-- COUNT MODAL -->
    <transition name="fade">
      <div v-if="showCountModal" class="modal-backdrop">
        <div class="modal-content">
          <h3>Stock Count - {{ activeProduct ? activeProduct.name : '' }}</h3>
          <div class="form-group">
            <label>Counted Qty</label>
            <input v-model.number="countValue" type="number" min="0" />
          </div>
          <div v-if="formError" class="error-msg">{{ formError }}</div>
          <div v-if="formSuccess" class="success-msg">{{ formSuccess }}</div>
          <div class="modal-actions">
            <button class="btn-cancel" @click="showCountModal = false">Cancel</button>
            <button class="btn-confirm" @click="submitCount">Save Count</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ADJUST MODAL -->
    <transition name="fade">
      <div v-if="showAdjustModal" class="modal-backdrop">
        <div class="modal-content">
          <h3>Adjust Stock - {{ activeProduct ? activeProduct.name : '' }}</h3>
          <div class="form-group">
            <label>Delta (use negative to subtract)</label>
            <input v-model.number="adjust.delta" type="number" step="1" />
          </div>
          <div class="form-group">
            <label>Note (optional)</label>
            <input v-model="adjust.note" type="text" placeholder="Reason" />
          </div>
          <div v-if="formError" class="error-msg">{{ formError }}</div>
          <div v-if="formSuccess" class="success-msg">{{ formSuccess }}</div>
          <div class="modal-actions">
            <button class="btn-cancel" @click="showAdjustModal = false">Cancel</button>
            <button class="btn-confirm" @click="submitAdjust">Apply</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ADD/EDIT PRODUCT MODAL -->
    <transition name="fade">
      <div v-if="showAddModal" class="modal-backdrop">
        <div class="modal-content">
          <h3>{{ newProduct.id ? 'Edit Product' : 'Add Product' }}</h3>
          <div class="form-group">
            <label>Name</label>
            <input v-model="newProduct.name" type="text" />
          </div>
          <div class="form-group">
            <label>SKU (optional)</label>
            <input v-model="newProduct.sku" type="text" placeholder="Leave empty to auto-generate" />
          </div>
          <div class="form-group">
            <label>Price</label>
            <input v-model.number="newProduct.price" type="number" step="0.01" />
          </div>
          <div class="form-group">
            <label>Stock</label>
            <input v-model.number="newProduct.stock" type="number" />
          </div>
          <div class="form-group">
            <label>Branch</label>
            <select v-model.number="newProduct.branch_id">
              <option value="">Select Branch</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                {{ branch.name }}
              </option>
            </select>
          </div>
          <div v-if="formError" class="error-msg">{{ formError }}</div>
          <div v-if="formSuccess" class="success-msg">{{ formSuccess }}</div>
          <div class="modal-actions">
            <button class="btn-cancel" @click="showAddModal = false">Cancel</button>
            <button class="btn-confirm" @click="submitAddProduct">
              {{ newProduct.id ? 'Update' : 'Create' }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import ProductList from './inventory/ProductList.vue'
import axios from 'axios'

const router = useRouter()

// Products state
const products = ref([])
const internalProducts = ref([])
const allProducts = ref([])
const branches = ref([])
const fetchUrl = '/api/superadmin/logistics/products'
const selectedBranch = ref('')

// Refs
const productListRef = ref(null)

// Modals
const showCountModal = ref(false)
const showAdjustModal = ref(false)
const showAddModal = ref(false)
const activeProduct = ref(null)
const countValue = ref(0)
const adjust = ref({ delta: 0, note: '' })
const newProduct = ref({ name: '', price: 0, stock: 0, sku: '', branch_id: '' })

const formError = ref('')
const formSuccess = ref('')

// API endpoints
const endpoints = {
  store: '/api/superadmin/logistics/products',
  update: (id) => `/api/superadmin/logistics/products/${id}`,
  destroy: (id) => `/api/superadmin/logistics/products/${id}`
}

function goBack() {
  router.push('/super-admin-panel')
}

async function fetchProducts() {
  try {
    const res = await axios.get(fetchUrl, { withCredentials: true })
    allProducts.value = res.data || []
    filterByBranch()
  } catch (e) {
    console.error('Failed to fetch products:', e)
  }
}

function filterByBranch() {
  if (!selectedBranch.value) {
    internalProducts.value = allProducts.value
  } else {
    internalProducts.value = allProducts.value.filter(p => p.branch_id === selectedBranch.value)
  }
}

async function fetchBranches() {
  try {
    const res = await axios.get('/api/superadmin/logistics/branches', { withCredentials: true })
    branches.value = res.data || []
  } catch (e) {
    console.error('Failed to fetch branches:', e)
  }
}

function refreshList() {
  fetchProducts()
}

onMounted(async () => {
  await fetchProducts()
  await fetchBranches()
})

function openCountModal(prod) {
  activeProduct.value = prod
  countValue.value = prod.stock || 0
  formError.value = ''
  formSuccess.value = ''
  showCountModal.value = true
}

async function submitCount() {
  if (!activeProduct.value) return
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'CSRF error'; return }
  try {
    const payload = { stock: Number(countValue.value) }
    await axios.put(endpoints.update(activeProduct.value.id), payload, { withCredentials: true })
    refreshList()
    formSuccess.value = 'Stock updated!'
    setTimeout(() => { showCountModal.value = false }, 500)
  } catch (e) {
    formError.value = e.response?.data?.message || 'Failed'
  }
}

function openAdjustModal(prod) {
  activeProduct.value = prod
  adjust.value = { delta: 0, note: '' }
  formError.value = ''
  formSuccess.value = ''
  showAdjustModal.value = true
}

async function submitAdjust() {
  if (!activeProduct.value) return
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'CSRF error'; return }
  try {
    const newStock = Number(activeProduct.value.stock) + Number(adjust.value.delta)
    const payload = { stock: newStock }
    await axios.put(endpoints.update(activeProduct.value.id), payload, { withCredentials: true })
    refreshList()
    formSuccess.value = 'Stock adjusted!'
    setTimeout(() => { showAdjustModal.value = false }, 500)
  } catch (e) {
    formError.value = e.response?.data?.message || 'Failed'
  }
}

function openAddProduct() {
  newProduct.value = { name: '', price: 0, stock: 0, sku: '', branch_id: '' }
  formError.value = ''
  formSuccess.value = ''
  showAddModal.value = true
}

function handleEdit(prod) {
  newProduct.value = {
    id: prod.id,
    name: prod.name,
    price: prod.price,
    stock: prod.stock,
    sku: prod.sku,
    branch_id: prod.branch_id
  }
  formError.value = ''
  formSuccess.value = ''
  showAddModal.value = true
}

async function submitAddProduct() {
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'CSRF error'; return }
  try {
    const payload = { ...newProduct.value }
    let res
    if (payload.id) {
      res = await axios.put(endpoints.update(payload.id), payload, { withCredentials: true })
    } else {
      res = await axios.post(endpoints.store, payload, { withCredentials: true })
    }
    refreshList()
    formSuccess.value = payload.id ? 'Product updated!' : 'Product created!'
    setTimeout(() => { showAddModal.value = false }, 500)
  } catch (e) {
    formError.value = e.response?.data?.message || 'Failed'
  }
}

async function deleteProduct(prod) {
  if (!confirm(`Delete "${prod.name}"?`)) return
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { alert('CSRF error'); return }
  try {
    await axios.delete(endpoints.destroy(prod.id), { withCredentials: true })
    refreshList()
  } catch (e) {
    alert(e.response?.data?.message || 'Failed')
  }
}

async function ensureCsrf() {
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const match = document.cookie.match(new RegExp('(^|; )' + 'XSRF-TOKEN' + '=([^;]*)'))
    const token = match ? decodeURIComponent(match[2]) : null
    if (token) axios.defaults.headers.common['X-XSRF-TOKEN'] = token
    return true
  } catch (e) {
    return false
  }
}
</script>

<style scoped>
.logistics-page {
  min-height: 100vh;
  background: linear-gradient(180deg, #FF9A4A 0%, #FF6A3D 100%);
  padding: 24px;
}

/* Back to Dashboard Button - Same as Finance Panel */
.btn-secondary {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  background: #6c757d;
  color: #fff;
}

.btn-secondary:hover {
  background: #5a6268;
}

.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.back-icon {
  flex-shrink: 0;
}

.logistics-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 20px;
}

.back-btn {
  padding: 10px 20px;
  background: white;
  border: none;
  border-radius: 8px;
  color: #7a2b00;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.back-btn:hover {
  background: #fff3e6;
}

.header-title h1 {
  margin: 0;
  color: white;
  font-size: 1.5rem;
}

.header-title p {
  margin: 5px 0 0;
  color: rgba(255,255,255,0.9);
}

/* Branch Filter */
.branch-filter {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
  background: rgba(255,255,255,0.9);
  padding: 12px 16px;
  border-radius: 8px;
}

.branch-filter label {
  font-weight: 600;
  color: #7a2b00;
}

.branch-filter select {
  padding: 8px 12px;
  border: 1px solid rgba(255,211,107,0.4);
  border-radius: 6px;
  font-size: 14px;
  min-width: 200px;
  color: #7a2b00;
  background: white;
}

.branch-filter select:focus {
  outline: none;
  border-color: #ff7a18;
}

/* Modal Styles */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 24px;
  max-width: 450px;
  width: 90%;
}

.modal-content h3 {
  margin: 0 0 16px;
  color: #7a2b00;
}

.form-group {
  margin-bottom: 12px;
}

.form-group label {
  display: block;
  margin-bottom: 4px;
  font-weight: 600;
  color: #7a2b00;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid rgba(255,211,107,0.4);
  border-radius: 6px;
  font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #ff7a18;
}

.error-msg {
  color: #dc3545;
  background: #f8d7da;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 12px;
}

.success-msg {
  color: #155724;
  background: #d4edda;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 12px;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 16px;
}

.btn-cancel {
  padding: 10px 16px;
  border: 1px solid rgba(255,211,107,0.4);
  border-radius: 6px;
  background: white;
  color: #7a2b00;
  cursor: pointer;
}

.btn-confirm {
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  background: linear-gradient(180deg,#ff7a18,#ff6a3d);
  color: white;
  cursor: pointer;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

