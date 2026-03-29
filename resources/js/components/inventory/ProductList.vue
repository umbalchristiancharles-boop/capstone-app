<template>
  <div class="pl-root">
    <!-- Left Column: Profile + Attendance (when provided via slots) -->
    <aside class="pl-left-panel" v-if="$slots.profile || $slots.stats">
      <slot name="profile"></slot>
      <slot name="attendance"></slot>
      <slot name="stats"></slot>
    </aside>

    <!-- Right Column: Main Content -->
    <div class="pl-right-column">
      <!-- Top header: title + actions -->
      <header class="pl-header">
        <div>
          <h2 class="pl-title">Product List</h2>
          <p class="pl-sub">Manage your branch inventory — search, filter, export and act quickly.</p>
        </div>
        <div class="pl-actions">
          <div class="pl-search">
            <input
              v-model="q"
              @input="goToPage(1)"
              aria-label="Search products"
              placeholder="Search products, SKU..."
            />
          </div>
          <div class="pl-filters">
            <select v-model="stockFilter" @change="goToPage(1)" aria-label="Filter by stock level">
              <option value="all">All stock levels</option>
              <option value="in_stock">In stock > 10</option>
              <option value="low">Low (1-10)</option>
              <option value="out">Out of stock</option>
            </select>
            <select v-model="categoryFilter" @change="goToPage(1)" aria-label="Filter by category">
              <option value="">All categories</option>
              <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
          </div>
          <div class="pl-top-buttons">
            <button class="btn btn-light" @click="exportCsv" aria-label="Export CSV">Export CSV</button>
            <button class="btn btn-primary" @click="$emit('open-add')" aria-label="Add product">+ Add Product</button>
          </div>
        </div>
      </header>

      <!-- Main table / card list -->
      <main class="pl-main">
        <section class="pl-table-wrap" v-if="!isLoading">
          <!-- Group by category if applicable -->
          <div v-if="groupByCategory" class="category-groups">
            <div v-for="cat in groupedCategories" :key="cat" class="category-group">
              <h3 class="category-title">{{ cat || 'Uncategorized' }}</h3>
              <table class="pl-table" role="table" :aria-label="`Product list for ${cat}`">
                <thead>
                  <tr>
                    <th class="col-thumb">Image</th>
                    <th class="col-name" @click="toggleSort('name')" role="button" tabindex="0">Product Name <span class="sort">{{ sortIndicator('name') }}</span></th>
                    <th class="col-sku" @click="toggleSort('sku')" role="button" tabindex="0">SKU <span class="sort">{{ sortIndicator('sku') }}</span></th>
                    <th class="col-price" @click="toggleSort('price')" role="button" tabindex="0">Price (PHP) <span class="sort">{{ sortIndicator('price') }}</span></th>
                    <th class="col-stock" @click="toggleSort('stock')" role="button" tabindex="0">Stock <span class="sort">{{ sortIndicator('stock') }}</span></th>
                    <th class="col-expiry">Expires</th>
                    <th class="col-actions">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in getProductsByCategory(cat)" :key="p.id" class="pl-row" :title="p.name" :class="{ expired: isProductExpired(p) }">
                    <td class="col-thumb">
                      <img v-if="p.image_url" :src="p.image_url" :alt="p.name" class="thumb" />
                      <div v-else class="thumb thumb-placeholder" aria-hidden="true">{{ p.name ? p.name.charAt(0) : '?' }}</div>
                    </td>
                    <td class="col-name">
                      <div class="name-block">
                        <div class="name">{{ p.name }}</div>
                        <div class="meta">{{ p.category || '—' }}</div>
                      </div>
                    </td>
                    <td class="col-sku"><span class="sku">{{ p.sku || '-' }}</span></td>
                    <td class="col-price">{{ formatCurrency(p.price) }}</td>
                    <td class="col-stock"><span :class="stockClass(p.stock)">{{ p.stock }}</span></td>
                    <td class="col-expiry">
                      <span v-if="p.expires_at" :class="getExpiryClass(p)">{{ formatDate(p.expires_at) }}</span>
                      <span v-else class="expiry-none">—</span>
                    </td>
                    <td class="col-actions">
                      <button class="btn btn-icon btn-danger" @click="$emit('delete', p)" :aria-label="`Delete ${p.name}`">Delete</button>
                    </td>
                  </tr>
                </tbody>
              </table>

            </div>
          </div>

          <!-- Separate Confirmed Stock History panel (moved out of Uncategorized group) -->
          <div v-if="groupByCategory" class="panel-section" style="margin-top:18px;">
            <h2 class="section-title">Confirmed Stock History</h2>
            <div class="section-description">Recent confirmed stock changes for your branch</div>
            <div style="margin-top:12px;">
              <table class="history-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Confirmed By</th>
                    <th>When</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td>60</td><td>pepper</td><td>10</td><td>Vince Hannibal Bido</td><td>3/28/2026, 5:42:51 PM</td></tr>
                  <tr><td>57</td><td>Water Bottle</td><td>20</td><td>Vince Hannibal Bido</td><td>3/27/2026, 5:33:15 AM</td></tr>
                  <tr><td>58</td><td>Flour</td><td>1</td><td>Vince Hannibal Bido</td><td>3/27/2026, 5:33:12 AM</td></tr>
                  <tr><td>56</td><td>Seaweeds</td><td>40</td><td>Vince Hannibal Bido</td><td>3/27/2026, 4:43:41 AM</td></tr>
                  <tr><td>53</td><td>Salt</td><td>5</td><td>Vince Hannibal Bido</td><td>3/27/2026, 4:36:57 AM</td></tr>
                  <tr><td>54</td><td>frozen hot Dogs</td><td>5</td><td>Vince Hannibal Bido</td><td>3/27/2026, 4:28:47 AM</td></tr>
                  <tr><td>55</td><td>Flour</td><td>1</td><td>Procurement Manager - Dasma Branch</td><td>3/27/2026, 3:46:07 AM</td></tr>
                  <tr><td>52</td><td>Water Bottle</td><td>10</td><td>Procurement Manager - Dasma Branch</td><td>3/26/2026, 6:17:23 PM</td></tr>
                  <tr><td>51</td><td>Flour</td><td>4</td><td>Procurement Manager - Dasma Branch</td><td>3/26/2026, 4:36:33 PM</td></tr>
                  <tr><td>44</td><td>Seaweeds</td><td>10</td><td>Procurement Manager - Dasma Branch</td><td>3/26/2026, 4:20:05 PM</td></tr>
                  <tr><td>48</td><td>Salt</td><td>10</td><td>Procurement Manager - Dasma Branch</td><td>3/24/2026, 5:16:08 PM</td></tr>
                  <tr><td>46</td><td>Flour</td><td>10</td><td>Procurement Manager - Dasma Branch</td><td>3/24/2026, 3:58:46 PM</td></tr>
                  <tr><td>45</td><td>frozen hot Dogs</td><td>10</td><td>Procurement Manager - Dasma Branch</td><td>3/24/2026, 3:43:03 PM</td></tr>
                  <tr><td>30</td><td>Samjang</td><td>50</td><td>Procurement Manager - Dasma Branch</td><td>3/22/2026, 7:42:33 PM</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Regular table view (no grouping) -->
          <div v-else>
            <table class="pl-table" role="table" aria-label="Product list table">
              <thead>
                <tr>
                  <th class="col-thumb">Image</th>
                  <th class="col-name" @click="toggleSort('name')" role="button" tabindex="0">Product Name <span class="sort">{{ sortIndicator('name') }}</span></th>
                  <th class="col-sku" @click="toggleSort('sku')" role="button" tabindex="0">SKU <span class="sort">{{ sortIndicator('sku') }}</span></th>
                  <th class="col-price" @click="toggleSort('price')" role="button" tabindex="0">Price (PHP) <span class="sort">{{ sortIndicator('price') }}</span></th>
                  <th class="col-stock" @click="toggleSort('stock')" role="button" tabindex="0">Stock <span class="sort">{{ sortIndicator('stock') }}</span></th>
                  <th class="col-expiry">Expires</th>
                  <th class="col-actions">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in pageItems" :key="p.id" class="pl-row" :title="p.name" :class="{ expired: isProductExpired(p) }">
                  <td class="col-thumb">
                    <img v-if="p.image_url" :src="p.image_url" :alt="p.name" class="thumb" />
                    <div v-else class="thumb thumb-placeholder" aria-hidden="true">{{ p.name ? p.name.charAt(0) : '?' }}</div>
                  </td>
                  <td class="col-name">
                    <div class="name-block">
                      <div class="name">{{ p.name }}</div>
                      <div class="meta">{{ p.category || '—' }}</div>
                    </div>
                  </td>
                  <td class="col-sku"><span class="sku">{{ p.sku || '-' }}</span></td>
                  <td class="col-price">{{ formatCurrency(p.price) }}</td>
                  <td class="col-stock"><span :class="stockClass(p.stock)">{{ p.stock }}</span></td>
                  <td class="col-expiry">
                    <span v-if="p.expires_at" :class="getExpiryClass(p)">{{ formatDate(p.expires_at) }}</span>
                    <span v-else class="expiry-none">—</span>
                  </td>
                  <td class="col-actions">
                    <button class="btn btn-icon btn-danger" @click="$emit('delete', p)" :aria-label="`Delete ${p.name}`">Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="pl-pagination" role="navigation" aria-label="Pagination">
            <button class="page-btn" :disabled="page === 1" @click="goToPage(1)">« First</button>
            <button class="page-btn" :disabled="page === 1" @click="goToPage(page-1)">‹ Prev</button>
            <span class="page-info">Page {{ page }} of {{ totalPages }}</span>
            <button class="page-btn" :disabled="page === totalPages" @click="goToPage(page+1)">Next ›</button>
            <button class="page-btn" :disabled="page === totalPages" @click="goToPage(totalPages)">Last »</button>
            <select v-model.number="perPage" @change="goToPage(1)" aria-label="Items per page">
              <option :value="10">10 / page</option>
              <option :value="25">25 / page</option>
              <option :value="50">50 / page</option>
            </select>
          </div>


        </section>

        <!-- Mobile: show card layout when screen narrow or if table not ideal -->
        <section class="pl-cards" v-if="!isLoading && isMobile">
          <article v-for="p in pageItems" :key="p.id" class="card">
            <div class="card-left">
              <img v-if="p.image_url" :src="p.image_url" :alt="p.name" class="thumb" />
              <div v-else class="thumb thumb-placeholder">{{ p.name ? p.name.charAt(0) : '?' }}</div>
            </div>
            <div class="card-body">
              <div class="card-title">{{ p.name }}</div>
              <div class="card-sub">SKU: <span class="sku">{{ p.sku || '-' }}</span></div>
              <div class="card-meta">{{ formatCurrency(p.price) }} · <span :class="stockClass(p.stock)">{{ p.stock }}</span></div>
              <div class="card-actions">
                <button class="btn btn-small btn-danger" @click="$emit('delete', p)">Delete</button>
              </div>
            </div>
          </article>
        </section>

        <!-- Loading state -->
        <div v-if="isLoading" class="pl-loading">Loading products…</div>

        <!-- Empty state -->
        <div v-if="!isLoading && filtered.length === 0" class="pl-empty">No products matched your filters.</div>
      </main>
    </div>
  </div>
</template>

<script setup>
/*
  ProductList.vue
  - Self-contained Vue 3 component implementing a responsive, accessible product list UI.
  - Props / integration:
      * Accepts `products` prop (array) or can fetch via `fetchProducts()` when `fetchUrl` prop provided.
      * Emits `open-add`, `edit`, `delete` events to integrate with parent logic (e.g., modals / API calls).
  - Features:
      * Search, filters, sort, pagination
      * CSV export
      * Desktop table + mobile cards
      * Stock color coding
      * Slots for profile and attendance sections
*/

import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
  products: { type: Array, default: () => [] },
  fetchUrl: { type: String, default: '' }, // optional API endpoint to fetch products
  compact: { type: Boolean, default: false } // when true hide header/sidebar for embedding
})

const emit = defineEmits(['open-add', 'edit', 'delete', 'count', 'adjust'])

const q = ref('')
const stockFilter = ref('all')
const categoryFilter = ref('')
const sortBy = ref('name')
const sortDir = ref('asc')
const page = ref(1)
const perPage = ref(25)
const isLoading = ref(false)
const sidebarCollapsed = ref(false)

const internal = ref(props.products ? props.products.slice() : [])

watch(() => props.products, (v) => { internal.value = v ? v.slice() : [] })

// optional fetch from API
async function fetchProducts() {
  if (!props.fetchUrl) {
    console.debug('[ProductList] No fetchUrl provided')
    return
  }
  isLoading.value = true
  try {
    console.debug('[ProductList] Fetching from:', props.fetchUrl)
    const res = await fetch(props.fetchUrl, { credentials: 'same-origin' })

    console.debug('[ProductList] Response status:', res.status)

    // Validate JSON response before parsing
    const contentType = res.headers.get('content-type')
    if (!contentType || !contentType.includes('application/json')) {
      console.error('[ProductList] Invalid JSON response - received', contentType || 'no content-type header')
      // Try to get text for debugging
      const text = await res.text()
      console.error('[ProductList] Response body (first 200 chars):', text.substring(0, 200))
      internal.value = []
      return
    }

    const data = await res.json()
    console.debug('[ProductList] Response data:', data, 'Length:', Array.isArray(data) ? data.length : 'not array')
    if (Array.isArray(data)) {
      internal.value = data
      console.debug('[ProductList] Set products array, count:', data.length)
    }
    else if (data && Array.isArray(data.products)) {
      internal.value = data.products
      console.debug('[ProductList] Set products from data.products, count:', data.products.length)
    }
    else {
      console.warn('[ProductList] Unexpected response format:', data)
      internal.value = []
    }
  } catch (e) {
    console.warn('[ProductList] fetchProducts failed', e)
  } finally { isLoading.value = false }
}

onMounted(() => {
  if (props.fetchUrl) fetchProducts()
})

// Derived categories from data
const categories = computed(() => {
  const s = new Set()
  internal.value.forEach(p => { if (p.category) s.add(p.category) })
  return Array.from(s)
})

// Filtering
const filtered = computed(() => {
  const qv = (q.value || '').trim().toLowerCase()
  return internal.value.filter(p => {
    if (stockFilter.value === 'in_stock' && (p.stock == null || p.stock <= 10)) return false
    if (stockFilter.value === 'low' && (p.stock == null || p.stock < 1 || p.stock > 10)) return false
    if (stockFilter.value === 'out' && (p.stock == null || p.stock > 0)) return false
    if (categoryFilter.value && p.category !== categoryFilter.value) return false
    if (!qv) return true
    return (p.name && p.name.toLowerCase().includes(qv)) || (p.sku && p.sku.toLowerCase().includes(qv))
  })
})

// Sorting
function toggleSort(field) {
  if (sortBy.value === field) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortDir.value = 'asc'
  }
}
function sortIndicator(field) {
  if (sortBy.value !== field) return '—'
  return sortDir.value === 'asc' ? '▲' : '▼'
}

const sorted = computed(() => {
  const arr = filtered.value.slice()
  const dir = sortDir.value === 'asc' ? 1 : -1
  arr.sort((a, b) => {
    const va = (a[sortBy.value] == null) ? '' : a[sortBy.value]
    const vb = (b[sortBy.value] == null) ? '' : b[sortBy.value]
    if (typeof va === 'number' && typeof vb === 'number') return (va - vb) * dir
    return String(va).localeCompare(String(vb)) * dir
  })
  return arr
})

// Pagination
const totalItems = computed(() => sorted.value.length)
const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / perPage.value)))
function goToPage(n) {
  page.value = Math.max(1, Math.min(n, totalPages.value))
}
const pageItems = computed(() => {
  const start = (page.value - 1) * perPage.value
  return sorted.value.slice(start, start + perPage.value)
})

// CSV export
function exportCsv() {
  const headers = ['id','name','sku','price','stock','category']
  const rows = [headers.join(',')]
  sorted.value.forEach(p => {
    const line = headers.map(h => '"' + (p[h] ?? '') + '"').join(',')
    rows.push(line)
  })
  const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'products.csv'
  a.click()
  URL.revokeObjectURL(url)
}

// Utilities
function formatCurrency(v) {
  if (v == null) return '—'
  return Number(v).toLocaleString(undefined, { style: 'currency', currency: 'PHP' })
}
function stockClass(n) {
  if (n == null) return 'stock-null'
  if (n <= 0) return 'stock-out'
  if (n <= 10) return 'stock-low'
  return 'stock-ok'
}

// small screen detection
const isMobile = computed(() => window.matchMedia && window.matchMedia('(max-width: 880px)').matches)

// stats
const totalProducts = computed(() => internal.value.length)
const lowStockCount = computed(() => internal.value.filter(p => {
  if (p.stock == null || p.stock <= 0) return false
  const threshold = p.low_stock_threshold ?? 10
  return p.stock <= threshold
}).length)
const outOfStockCount = computed(() => internal.value.filter(p => p.stock != null && p.stock <= 0).length)

// pagination reactive reset when perPage changes
watch(perPage, () => goToPage(1))


// expose some helpers for parent control
function getStats() {
  return {
    total: internal.value.length,
    low: internal.value.filter(p => {
      if (p.stock == null || p.stock <= 0) return false
      const threshold = p.low_stock_threshold ?? 10
      return p.stock <= threshold
    }).length,
    out: internal.value.filter(p => p.stock != null && p.stock <= 0).length
  }
}

function setQuery(val) { q.value = val }
function setStockFilter(val) { stockFilter.value = val }
function setCategoryFilter(val) { categoryFilter.value = val }

// Category grouping
const groupByCategory = ref(true)
const groupedCategories = computed(() => {
  const cats = new Set()
  sorted.value.forEach(p => {
    cats.add(p.category || 'Uncategorized')
  })
  return Array.from(cats).sort()
})

function getProductsByCategory(category) {
  return sorted.value.filter(p => (p.category || 'Uncategorized') === category)
}

// Expiration date utilities
function formatDate(dateStr) {
  if (!dateStr) return '—'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
  } catch (e) {
    return dateStr
  }
}

function isProductExpired(product) {
  if (!product.expires_at) return false
  try {
    const expiryDate = new Date(product.expires_at)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return expiryDate < today
  } catch (e) {
    return false
  }
}

function getExpiryClass(product) {
  if (!product.expires_at) return 'expiry-none'
  try {
    const expiryDate = new Date(product.expires_at)
    const today = new Date()
    const tomorrow = new Date(today)
    tomorrow.setDate(tomorrow.getDate() + 1)
    const weekFromNow = new Date(today)
    weekFromNow.setDate(weekFromNow.getDate() + 7)

    today.setHours(0, 0, 0, 0)
    expiryDate.setHours(0, 0, 0, 0)

    if (expiryDate < today) return 'expiry-expired'
    if (expiryDate <= tomorrow) return 'expiry-critical'
    if (expiryDate <= weekFromNow) return 'expiry-warning'
    return 'expiry-ok'
  } catch (e) {
    return 'expiry-none'
  }
}

defineExpose({ fetchProducts, getStats, setQuery, setStockFilter, setCategoryFilter })

</script>

<style scoped>
/* Ensure the layout's side column participates in normal document flow
   when this component is embedded without a profile column, so the
   announcements/attendance column scrolls away instead of sticking. */
:deep(.admin-layout.no-profile-column) .admin-side {
  position: static !important;
  top: auto !important;
  align-self: stretch !important;
  margin-top: 0 !important;
  max-height: none !important;
  overflow: visible !important;
  padding-right: 0 !important;
}

/* Also ensure the announcements panel headers/body are not positioned
   in a way that prevents normal document scrolling. */
:deep(.announcements-panel .panel-header),
:deep(.announcements-panel .panel-body) {
  position: static !important;
  max-height: none !important;
  overflow: visible !important;
}

@media (min-width: 1000px) {
  :deep(.admin-layout.no-profile-column) .admin-side {
    position: static !important;
    margin-top: 0 !important;
  }
}
</style>

<style scoped>
/* Root layout - 2 column grid: profile panel (320px) + content (flex) */
.pl-root {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 18px;
  align-items: start;
  padding: 18px;
  background: transparent;
  color: #3b2b20;
}

/* When no slots are used, collapse to single column */
.pl-root:not(:has(.pl-left-panel)) {
  grid-template-columns: 1fr;
}

/* Left Panel (Profile + Attendance) */
.pl-left-panel {
  display: flex;
  flex-direction: column;
  gap: 12px;
  height: fit-content;
}

/* Right Column */
.pl-right-column {
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-width: 0;
}

/* Header - spans full width of right column */
.pl-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 12px 16px;
  background: rgba(255,244,230,0.92);
  border-radius: 10px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}
.pl-title { margin: 0; font-size: 1.1rem; color: #7a2b00; }
.pl-sub { margin: 0; font-size: 0.85rem; color: #8a4b1a }
.pl-actions { display: flex; align-items: center; gap: 10px }
.pl-search input { padding: 8px 12px; border-radius: 8px; border: 1px solid #e6eef6; min-width: 200px; }
.pl-filters select { padding: 8px; border-radius: 8px; border: 1px solid #e6eef6; margin-left: 6px }
.pl-top-buttons { display: flex; gap: 8px }

/* Buttons */
.btn { padding: 8px 12px; border-radius: 8px; border: 1px solid transparent; cursor: pointer; font-weight: 600 }
.btn-primary { background: linear-gradient(180deg,#ff7a18,#ff6a3d); color: white; box-shadow: 0 6px 14px rgba(255,106,61,0.18) }
.btn-light { background: #fff3e6; border: 1px solid rgba(255,211,107,0.35); color: #7a2b00 }
.btn-danger { background: #d9534f; color: #111 }
.btn-icon { padding: 6px 8px; border-radius: 6px; background: #fff; border: 1px solid #e6eef6 }
.btn-small { padding: 6px 8px; border-radius: 6px }

/* Main content area */
.pl-main {
  background: transparent;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Table */
.pl-table-wrap { background: white; border-radius: 10px; padding: 12px; box-shadow: 0 1px 6px rgba(16,24,40,0.04) }
.pl-table { width: 100%; border-collapse: collapse; font-family: Inter, system-ui, Segoe UI, Roboto, "Helvetica Neue", Arial }
.pl-table thead th { text-align: left; padding: 10px; border-bottom: 1px solid #eef2f7; color: #0b213f; font-weight: 700; font-size: 0.9rem }
.pl-table tbody tr { transition: background .12s ease, transform .08s ease; }
.pl-table tbody tr:hover { background: #fbfeff }
.pl-table td { padding: 10px; vertical-align: middle; border-bottom: 1px solid #f1f5f9 }
.col-thumb .thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid #e6eef6 }
.thumb-placeholder { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; background: #f1f5f9; color: #0b213f; font-weight: 700 }
.name-block .name { font-weight: 700; color: #0b213f }
.name-block .meta { font-size: 0.8rem; color: #64748b }
.sku { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #f8fafc; border: 1px solid #e6eef6; font-weight: 700; font-size: 0.82rem }
.col-actions { text-align: right }

/* Stock colors */
.stock-ok { color: #065f46; background: rgba(16,185,129,0.08); padding: 4px 8px; border-radius: 8px; font-weight: 700 }
.stock-low { color: #92400e; background: rgba(245,158,11,0.08); padding: 4px 8px; border-radius: 8px; font-weight: 700 }
.stock-out { color: #7f1d1d; background: rgba(239,68,68,0.06); padding: 4px 8px; border-radius: 8px; font-weight: 700 }
.stock-null { color: #475569 }

/* Pagination */
.pl-pagination { display: flex; gap: 8px; align-items: center; justify-content: flex-end; margin-top: 12px }
.page-btn { padding: 8px 10px; border-radius: 8px; border: 1px solid #e6eef6; background: white }
.page-info { margin: 0 8px; color: #475569 }

/* Mobile card layout */
.pl-cards { display: none }

/* Responsive: Stack layout on smaller screens */
@media (max-width: 880px) {
  .pl-root {
    grid-template-columns: 1fr;
    padding: 12px;
  }

  .pl-left-panel {
    order: -1; /* Show profile/attendance on top on mobile */
  }

  .pl-header {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }

  .pl-actions {
    flex-wrap: wrap;
    justify-content: flex-start;
  }

  .pl-search input {
    min-width: 100%;
  }

  .pl-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .pl-filters select {
    margin-left: 0;
  }

  .pl-top-buttons {
    justify-content: flex-end;
  }

  .pl-table-wrap { display: none }
  .pl-cards { display: block }
  .card { display: flex; gap: 12px; background: white; border-radius: 10px; padding: 12px; margin-bottom: 12px; align-items: center }
  .card-left .thumb { width: 64px; height: 64px }
  .card-body .card-title { font-weight: 800 }
  .card-actions { margin-top: 8px; display: flex; gap: 8px }
}

/* Utility */
.pl-loading, .pl-empty { padding: 28px; background: white; border-radius: 10px; text-align: center; color: #475569 }

/* Category Grouping */
.category-groups { display: flex; flex-direction: column; gap: 24px }
.category-group { display: flex; flex-direction: column; gap: 8px }
.category-title { margin: 0; padding: 0 8px; font-size: 1rem; font-weight: 700; color: #0b213f; border-bottom: 2px solid #ff9a4a; padding-bottom: 8px }

/* Expiration date columns */
.col-expiry { text-align: center; width: 120px }
.expiry-expired { background: #fee2e2; color: #7f1d1d; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.85rem }
.expiry-critical { background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.85rem }
.expiry-warning { background: #fef08a; color: #713f12; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.85rem }
.expiry-ok { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.85rem }
.expiry-none { color: #9ca3af; font-size: 0.85rem }

/* Expired product row highlight */
.pl-row.expired { background: #fef2f2 !important }
.pl-row.expired .col-name .name { color: #991b1b }

</style>

<style scoped>
/* Confirmed Stock History styles */
.history-box {
  background: #ffffff;
  padding: 12px;
  border-radius: 10px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
  margin-top: 12px;
  max-height: 340px;
  overflow: auto;
}
.history-box h3 { margin: 0 0 8px; color: #7a2b00; font-size: 1rem }
.history-table { width: 100%; border-collapse: collapse; font-family: inherit }
.history-table thead th { text-align: left; padding: 10px; border-bottom: 1px solid #eef2f7; color: #0b213f; font-weight: 700; font-size: 0.9rem }
.history-table td, .history-table th { padding: 8px; border-bottom: 1px solid rgba(0,0,0,0.06); vertical-align: middle; font-size: 0. ninerem }
.history-table tbody tr:last-child td { border-bottom: none }

@media (max-width: 880px) {
  .history-box { max-height: none; }
}
</style>

