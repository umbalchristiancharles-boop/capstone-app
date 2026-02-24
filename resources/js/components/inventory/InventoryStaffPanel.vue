<template>
  <div class="pl-page">
    <div class="pl-container">
      <!-- Page Header -->
      <header class="pl-page-header">
        <div>
          <h1 class="pl-h1">Product List</h1>
          <p class="pl-lead">Manage your branch inventory</p>
        </div>
      </header>

      <!-- Controls removed per request -->

      <!-- Content: stats left, table right -->
      <section class="pl-content">
        <aside class="pl-stats">
          <!-- Owner / Staff profile card (restored) -->
          <div class="profile-card">
            <div class="profile-avatar"> 
              <div class="avatar-circle">{{ staffProfile.fullName ? (staffProfile.fullName.charAt(0) || 'U') : 'U' }}</div>
            </div>
            <div class="profile-info">
              <div class="profile-role">ACCOUNT</div>
              <div class="profile-name">{{ staffProfile.fullName || 'Owner' }}</div>
              <div class="profile-sub">{{ staffProfile.role || 'OWNER' }}</div>
            </div>
            <div class="profile-box">
              <div class="account-id">Account I.D: <strong>{{ staffProfile.accountId || 'kk00001' }}</strong></div>
              <button class="btn-info-small" @click="openInfoModal">Info</button>
              <div class="qr-placeholder">QR</div>
            </div>
            <div class="profile-actions">
              <div class="small-stats">
                <div><div class="small-stat-title">Total Branches:</div><div class="small-stat-val">5</div></div>
                <div><div class="small-stat-title">Total Employees:</div><div class="small-stat-val">4</div></div>
              </div>
              <button class="btn-primary" @click="$emit('open-staff-management')">Staff Management</button>
              <button class="btn-light" @click="logout">Logout</button>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-title">Total products</div>
            <div class="stat-value">{{ totalProducts }}</div>
          </div>
          <div class="stat-card">
            <div class="stat-title">Low stock</div>
            <div class="stat-value">{{ lowStockCount }}</div>
          </div>
          <div class="stat-card">
            <div class="stat-title">Out of stock</div>
            <div class="stat-value">{{ outOfStockCount }}</div>
          </div>
        </aside>

        <main class="pl-main">
          <ProductList ref="productListRef" fetchUrl="/api/staff/inventory/products" compact
            @open-add="openAddProduct"
            @edit="handleEdit"
            @delete="deleteProduct"
            @adjust="openAdjustModal"
            @count="openCountModal"
          />
        </main>
      </section>

      <!-- COUNT / ADJUST / ADD MODALS -->
      <transition name="fade">
        <div v-if="showCountModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Stock Count - {{ activeProduct ? activeProduct.name : '' }}</h3>
            <p class="info-sub">Enter counted quantity for this product.</p>
            <div class="info-grid">
              <div class="info-row"><span class="info-label">Counted Qty</span>
                <input v-model.number="countValue" class="info-input" type="number" min="0" />
              </div>
            </div>
            <div v-if="formError" class="info-error">{{ formError }}</div>
            <div v-if="formSuccess" class="info-success">{{ formSuccess }}</div>
            <div class="info-actions">
              <button class="btn-outline" @click="showCountModal = false">Cancel</button>
              <button class="btn-primary" @click="submitCount">Save Count</button>
            </div>
          </div>
        </div>
      </transition>

      <transition name="fade">
        <div v-if="showAdjustModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Adjust Stock - {{ activeProduct ? activeProduct.name : '' }}</h3>
            <p class="info-sub">Add or subtract quantity. Use negative number to subtract.</p>
            <div class="info-grid">
              <div class="info-row"><span class="info-label">Delta</span>
                <input v-model.number="adjust.delta" class="info-input" type="number" step="1" />
              </div>
              <div class="info-row"><span class="info-label">Note</span>
                <input v-model="adjust.note" class="info-input" type="text" placeholder="Reason (optional)" />
              </div>
            </div>
            <div v-if="formError" class="info-error">{{ formError }}</div>
            <div v-if="formSuccess" class="info-success">{{ formSuccess }}</div>
            <div class="info-actions">
              <button class="btn-outline" @click="showAdjustModal = false">Cancel</button>
              <button class="btn-primary" @click="submitAdjust">Apply Adjustment</button>
            </div>
          </div>
        </div>
      </transition>

      <transition name="fade">
        <div v-if="showAddModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Add Product</h3>
            <p class="info-sub">Create a new product for your branch.</p>
            <div class="info-grid">
              <div class="info-row"><span class="info-label">Name</span>
                <input v-model="newProduct.name" class="info-input" type="text" />
              </div>
              <div class="info-row"><span class="info-label">SKU (optional)</span>
                <input v-model="newProduct.sku" class="info-input" type="text" placeholder="Leave empty to auto-generate" />
              </div>
              <div class="info-row"><span class="info-label">Generated SKU Preview</span>
                <div class="info-value" style="display:flex;align-items:center;gap:8px;">
                  <span>{{ displaySku }}</span>
                  <button class="btn-outline" @click.prevent="regeneratePreview">Regenerate</button>
                </div>
              </div>
              <div class="info-row"><span class="info-label">Price</span>
                <input v-model.number="newProduct.price" class="info-input" type="number" step="0.01" />
              </div>
              <div class="info-row"><span class="info-label">Initial Stock</span>
                <input v-model.number="newProduct.stock" class="info-input" type="number" />
              </div>
            </div>
            <div v-if="formError" class="info-error">{{ formError }}</div>
            <div v-if="formSuccess" class="info-success">{{ formSuccess }}</div>
            <div class="info-actions">
              <button class="btn-outline" @click="showAddModal = false">Cancel</button>
              <button class="btn-primary" @click="submitAddProduct">Create Product</button>
            </div>
          </div>
        </div>
      </transition>

      <!-- INFO MODAL -->
      <transition name="fade">
        <div v-if="showInfoModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Staff Information</h3>
            <p class="info-sub">Personal details for this staff can be updated from this panel.</p>
            <div class="info-grid">
              <div class="info-row"><span class="info-label">Full name</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.fullName }}</span>
                <input v-else v-model="staffProfile.fullName" class="info-input" type="text" />
              </div>
              <div class="info-row"><span class="info-label">Role</span><span class="info-value">{{ staffProfile.role }}</span></div>
              <div class="info-row"><span class="info-label">Username</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.username }}</span>
                <input v-else v-model="staffProfile.username" class="info-input" type="text" placeholder="Enter username" />
              </div>
              <div class="info-row"><span class="info-label">Email</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.email }}</span>
                <input v-else v-model="staffProfile.email" class="info-input" type="email" />
              </div>
              <div class="info-row"><span class="info-label">Contact</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.contact }}</span>
                <input v-else v-model="staffProfile.contact" class="info-input" type="text" />
              </div>
              <!-- Password fields - only shown when editing -->
              <template v-if="isEditingInfo">
                <div class="info-row info-row--password">
                  <span class="info-label">New Password</span>
                  <input v-model="staffProfile.password" class="info-input" type="password" placeholder="Leave blank to keep current" />
                </div>
                <div class="info-row info-row--password">
                  <span class="info-label">Confirm Password</span>
                  <input v-model="staffProfile.password_confirmation" class="info-input" type="password" placeholder="Re-enter new password" />
                </div>
              </template>
            </div>
            <div v-if="profileError" class="info-error">{{ profileError }}</div>
            <div v-if="profileSuccess" class="info-success">{{ profileSuccess }}</div>
            <div class="info-actions">
              <button class="btn-outline" @click="handleInfoClose">{{ isEditingInfo ? 'Cancel' : 'Close' }}</button>
              <button class="btn-primary" @click="isEditingInfo ? saveStaffInfo() : (isEditingInfo = true)" :disabled="isSavingProfile">
                {{ isEditingInfo ? (isSavingProfile ? 'Saving...' : 'Save changes') : 'Edit information' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- LOGOUT CONFIRM MODAL -->
      <transition name="fade">
        <div v-if="showLogoutConfirm" class="info-backdrop">
          <div class="info-modal">
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to logout?</p>
            <div class="info-actions">
              <button class="btn-outline" @click="showLogoutConfirm = false">Cancel</button>
              <button class="btn-primary" @click="logout" :disabled="isLoggingOut">{{ isLoggingOut ? 'Logging out...' : 'Logout' }}</button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import ProductList from './ProductList.vue'

const router = useRouter();

const staffProfile = ref({
  avatarUrl: '',
  fullName: '',
  role: '',
  username: '',
  email: '',
  contact: '',
  accountId: '',
  password: '',
  password_confirmation: ''
});
const isProfileLoading = ref(true);
const isEditingInfo = ref(false);
const isSavingProfile = ref(false);
const showInfoModal = ref(false);
const profileError = ref('');
const profileSuccess = ref('');
const showLogoutConfirm = ref(false);
const isLoggingOut = ref(false);

// optional products prop (we will rely on ProductList fetch by default)
const props = defineProps({
  products: { type: Array, default: () => [] }
});

// ref to the ProductList child so we can trigger refreshes
const productListRef = ref(null)

// Modals / forms
const showCountModal = ref(false);
const showAdjustModal = ref(false);
const showAddModal = ref(false);
const activeProduct = ref(null);
const countValue = ref(0);
const adjust = ref({ delta: 0, note: '' });
const newProduct = ref({ name: '', price: 0, stock: 0, sku: '' });
const previewSku = ref('');
// small helper to force ProductList to refresh after server changes
function refreshList() {
  if (productListRef.value && typeof productListRef.value.fetchProducts === 'function') {
    return productListRef.value.fetchProducts().then(() => updateStats()).catch(() => {})
  }
}

function makePreviewSku(name) {
  let base = (name || '').toUpperCase().replace(/[^A-Z0-9]+/g, '').substring(0, 6)
  if (!base) base = 'PRD'
  const random = Math.random().toString(36).replace(/[^a-z]+/g, '').substring(0,4).toUpperCase() || (Math.random()*1e6|0).toString(36).substring(0,4).toUpperCase()
  return `${base}-${random}`
}

function regeneratePreview() {
  previewSku.value = makePreviewSku(newProduct.value.name || '')
}

const displaySku = computed(() => {
  return newProduct.value.sku && newProduct.value.sku.trim() !== '' ? newProduct.value.sku : (previewSku.value || makePreviewSku(newProduct.value.name || ''))
})
const formError = ref('');
const formSuccess = ref('');
const isLoading = ref(false)

// controls state for header-area filters
const searchQuery = ref('')
const selectedStockFilter = ref('all')

// stats shown in sidebar
const totalProducts = ref(0)
const lowStockCount = ref(0)
const outOfStockCount = ref(0)

function onSearchInput() {
  if (productListRef.value && typeof productListRef.value.setQuery === 'function') {
    productListRef.value.setQuery(searchQuery.value)
  }
}

function onStockFilterChange() {
  if (productListRef.value && typeof productListRef.value.setStockFilter === 'function') {
    productListRef.value.setStockFilter(selectedStockFilter.value)
  }
}

async function updateStats() {
  if (productListRef.value && typeof productListRef.value.getStats === 'function') {
    const s = productListRef.value.getStats()
    totalProducts.value = s.total || 0
    lowStockCount.value = s.low || 0
    outOfStockCount.value = s.out || 0
  }
}

onMounted(async () => {
  isProfileLoading.value = true;
  try {
    const res = await axios.get('/api/me', { withCredentials: true });
    if (res.data && res.data.ok && res.data.user) {
      const u = res.data.user;
      staffProfile.value = {
        avatarUrl: u.avatar_url || '',
        fullName: u.full_name || '',
        role: u.role || '',
        username: u.username || '',
        email: u.email || '',
        contact: u.contact || '',
        accountId: u.account_id || '',
        password: '',
        password_confirmation: ''
      };
    }
  } catch (e) {
    profileError.value = 'Failed to load profile info.';
  } finally {
    isProfileLoading.value = false;
  }
  // ProductList will handle fetching when given a fetchUrl; if a parent passed products prop, ProductList will display them.
  // initial stats update after mount
  setTimeout(() => updateStats(), 300)
});

// Note: ProductList can fetch products itself via `fetchUrl`. Parent mutating actions will call `refreshList()` after success.

function formatCurrency(v) {
  if (v === null || v === undefined) return '-';
  return Number(v).toLocaleString(undefined, { style: 'currency', currency: 'PHP' });
}

function openCountModal(prod) {
  activeProduct.value = prod;
  countValue.value = prod.stock || 0;
  formError.value = '';
  formSuccess.value = '';
  showCountModal.value = true;
}

async function submitCount() {
  if (!activeProduct.value) return;
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'Unable to refresh CSRF token. Please reload or login.'; return }
  try {
    const payload = { stock: Number(countValue.value) };
    const res = await axios.put(`/api/staff/inventory/update/${activeProduct.value.id}`, payload, { withCredentials: true });
    // refresh the list from server
    refreshList()
    formSuccess.value = 'Stock updated successfully.';
    showCountModal.value = false;
  } catch (e) {
    formError.value = (e.response && e.response.data && e.response.data.message) || 'Failed to update stock.';
  }
}

function openAdjustModal(prod) {
  activeProduct.value = prod;
  adjust.value = { delta: 0, note: '' };
  formError.value = '';
  formSuccess.value = '';
  showAdjustModal.value = true;
}

function handleEdit(prod) {
  // open the Add/Edit modal prefilled for editing
  newProduct.value = { id: prod.id, name: prod.name, price: prod.price, stock: prod.stock, sku: prod.sku }
  formError.value = '';
  formSuccess.value = '';
  showAddModal.value = true;
}

async function submitAdjust() {
  if (!activeProduct.value) return;
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'Unable to refresh CSRF token. Please reload or login.'; return }
  try {
    const newStock = Number(activeProduct.value.stock) + Number(adjust.value.delta);
    const payload = { stock: newStock };
    const res = await axios.put(`/api/staff/inventory/update/${activeProduct.value.id}`, payload, { withCredentials: true });
    refreshList()
    formSuccess.value = 'Stock adjusted successfully.';
    showAdjustModal.value = false;
  } catch (e) {
    formError.value = (e.response && e.response.data && e.response.data.message) || 'Failed to adjust stock.';
  }
}

function openAddProduct() {
  newProduct.value = { name: '', price: 0, stock: 0, sku: '' };
  formError.value = '';
  formSuccess.value = '';
  showAddModal.value = true;
  // prepare preview SKU
  previewSku.value = makePreviewSku('')
}

async function submitAddProduct() {
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'Unable to refresh CSRF token. Please reload or login.'; return }
  try {
    // If user didn't provide SKU, send the preview so server and UI match
    const payload = { ...newProduct.value };
    if (!payload.sku || payload.sku.trim() === '') payload.sku = previewSku.value || makePreviewSku(payload.name || '')
    let res
    if (payload.id) {
      // update existing product
      res = await axios.put(`/api/staff/inventory/update/${payload.id}`, payload, { withCredentials: true })
    } else {
      res = await axios.post('/api/staff/inventory/store', payload, { withCredentials: true });
    }
    if (res.data && (res.data.product || res.data.ok)) {
      // refresh the list so ProductList reflects the change
      refreshList()
      formSuccess.value = payload.id ? 'Product updated.' : 'Product added.';
      showAddModal.value = false;
    }
  } catch (e) {
    formError.value = (e.response && e.response.data && e.response.data.message) || 'Failed to create product.';
  }
}

async function deleteProduct(prod) {
  if (!confirm('Delete product "' + prod.name + '"? This cannot be undone.')) return;
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { alert('Unable to refresh CSRF token. Please reload or login.'); return }
  try {
    await axios.delete(`/api/staff/inventory/destroy/${prod.id}`, { withCredentials: true });
    refreshList()
  } catch (e) {
    alert((e.response && e.response.data && e.response.data.message) || 'Failed to delete product');
  }
}

// Ensure a fresh CSRF cookie/header is present before mutating requests
async function ensureCsrf() {
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    const match = document.cookie.match(new RegExp('(^|; )' + 'XSRF-TOKEN' + '=([^;]*)'));
    const token = match ? decodeURIComponent(match[2]) : null;
    if (token) axios.defaults.headers.common['X-XSRF-TOKEN'] = token;
    return true;
  } catch (e) {
    return false;
  }
}

function openInfoModal() {
  showInfoModal.value = true;
  isEditingInfo.value = false;
  profileError.value = '';
  profileSuccess.value = '';
}

function handleInfoClose() {
  if (isEditingInfo.value) {
    isEditingInfo.value = false;
    profileError.value = '';
    profileSuccess.value = '';
  } else {
    showInfoModal.value = false;
  }
}

async function saveStaffInfo() {
  isSavingProfile.value = true;
  profileError.value = '';
  profileSuccess.value = '';
  // TODO: Replace with real API call
  setTimeout(() => {
    isSavingProfile.value = false;
    isEditingInfo.value = false;
    profileSuccess.value = 'Profile updated!';
  }, 1000);
}

function onAvatarChange(e) {
  // TODO: Implement avatar upload
  profileError.value = 'Avatar upload not implemented.';
}

async function logout() {
  if (isLoggingOut.value) return;
  isLoggingOut.value = true;
  try {
    await axios.post('/api/logout', {}, { withCredentials: true });
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  // Optional: show overlay (if you have one)
  showLogoutConfirm.value = false;
  setTimeout(() => {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    try { window.location.replace('/') } catch (e) { router.push('/').catch(() => {}) }
  }, 600);
}
</script>

<style scoped>
.inventory-table th, .inventory-table td {
  padding: 0.55rem 0.7rem;
  font-size: 0.92rem;
  vertical-align: middle;
}
.inventory-table thead th {
  font-size: 0.95rem;
}
.prod-thumb {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
  border: 1px solid rgba(255,211,107,0.6);
  background: #fff4e6;
  display: inline-block;
}
.prod-thumb--placeholder {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #4b2a06;
  font-weight: 700;
  background: rgba(255,232,163,0.9);
}
.sku-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  background: rgba(255,255,255,0.9);
  border: 1px solid rgba(255,211,107,0.7);
  color: #4b2a06;
  font-weight: 700;
  font-size: 0.85rem;
}
.prod-name { font-size: 0.98rem; }
.staff-table td.actions { white-space: nowrap; }

/* New layout styles for InventoryStaffPanel */
.pl-page { padding: 18px; background: linear-gradient(180deg,#FF9A4A 0%,#FF6A3D 100%); min-height: 100vh }
.pl-container { max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; gap: 16px }
.pl-page-header { background: transparent; padding: 4px 0 }
.pl-h1 { margin:0; font-size:1.4rem; color:#7a2b00 }
.pl-lead { margin:0; color:#8a4b1a }
.pl-controls { display:flex; justify-content:space-between; gap:12px; align-items:center }
.pl-controls-left { flex:1 }
.pl-controls-right { display:flex; gap:8px; align-items:center }
.pl-search { width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,211,107,0.4); background:#fff3e6 }
.pl-content { display:grid; grid-template-columns: 280px 1fr; gap:16px; align-items:start }
@media (max-width:880px) { .pl-content { grid-template-columns: 1fr } }
.pl-stats { display:flex; flex-direction:column; gap:12px }
.stat-card { background: rgba(255,244,230,0.9); padding:12px; border-radius:10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
.stat-title { color:#8a4b1a; font-size:0.85rem }
.stat-value { font-weight:800; font-size:1.25rem; color:#7a2b00 }
.pl-main { min-width:0 }

/* compact ProductList overrides when embedded */
ProductList[compact] { width:100% }

/* Profile card styles (restored owner look) */
.profile-card { background: rgba(255,244,230,0.92); border-radius: 14px; padding: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); display:flex; flex-direction:column; gap:12px }
.profile-avatar { display:flex; justify-content:center }
.avatar-circle { width:72px; height:72px; border-radius:50%; background: linear-gradient(180deg,#ff7a18,#ff6a3d); color:white; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.25rem; border:4px solid rgba(255,244,230,0.9) }
.profile-info { text-align:center }
.profile-role { font-size:0.75rem; color:#8a4b1a; font-weight:700 }
.profile-name { font-size:1.05rem; font-weight:800; color:#7a2b00 }
.profile-sub { font-size:0.8rem; color:#a65a2a }
.profile-box { background: rgba(255,255,255,0.6); padding:12px; border-radius:10px; display:flex; flex-direction:column; gap:8px; align-items:center }
.account-id { color:#7a2b00; font-weight:700 }
.btn-info-small { padding:6px 12px; border-radius:999px; background: #ffb686; border:none; color:#7a2b00 }
.qr-placeholder { width:84px; height:84px; border-radius:8px; border:2px dashed rgba(255,211,107,0.6); display:flex; align-items:center; justify-content:center; color:#7a2b00 }
.profile-actions { display:flex; flex-direction:column; gap:10px }
.small-stats { display:flex; justify-content:space-between; gap:12px }
.small-stat-title { font-size:0.75rem; color:#8a4b1a }
.small-stat-val { font-weight:800; color:#7a2b00 }

</style>
