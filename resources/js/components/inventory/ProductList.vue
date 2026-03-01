<template>
  <div class="pl-root">
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
            <option value="in_stock">In stock &gt; 10</option>
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

    <!-- Optional sidebar stats (collapsible on small screens) -->
    <aside class="pl-sidebar" :class="{ collapsed: sidebarCollapsed }">
      <button class="sidebar-toggle" @click="sidebarCollapsed = !sidebarCollapsed" aria-expanded="!sidebarCollapsed">
        {{ sidebarCollapsed ? 'Show' : 'Hide' }} Quick Stats
      </button>
      <div class="sidebar-body">
        <div class="stat">
          <div class="stat-value">{{ totalProducts }}</div>
          <div class="stat-label">Total products</div>
        </div>
        <div class="stat">
          <div class="stat-value">{{ lowStockCount }}</div>
          <div class="stat-label">Low stock alerts</div>
        </div>
        <div class="stat">
          <div class="stat-value">{{ outOfStockCount }}</div>
          <div class="stat-label">Out of stock</div>
        </div>
      </div>
    </aside>

    <!-- Main table / card list -->
    <main class="pl-main">
      <section class="pl-table-wrap" v-if="!isLoading">
        <table class="pl-table" role="table" aria-label="Product list table">
          <thead>
            <tr>
              <th class="col-thumb">Image</th>
              <th class="col-name" @click="toggleSort('name')" role="button" tabindex="0">Product Name <span class="sort">{{ sortIndicator('name') }}</span></th>
              <th class="col-sku" @click="toggleSort('sku')" role="button" tabindex="0">SKU <span class="sort">{{ sortIndicator('sku') }}</span></th>
              <th class="col-price" @click="toggleSort('price')" role="button" tabindex="0">Price (PHP) <span class="sort">{{ sortIndicator('price') }}</span></th>
              <th class="col-stock" @click="toggleSort('stock')" role="button" tabindex="0">Stock <span class="sort">{{ sortIndicator('stock') }}</span></th>
              <th class="col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in pageItems" :key="p.id" class="pl-row" :title="p.name">
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
              <td class="col-actions">
                <button class="btn btn-icon" @click="$emit('edit', p)" :aria-label="`Edit ${p.name}`">Edit</button>
                <button class="btn btn-icon" @click="$emit('count', p)" :aria-label="`Count ${p.name}`">Count</button>
                <button class="btn btn-icon" @click="$emit('adjust', p)" :aria-label="`Adjust ${p.name}`">Adjust</button>
                <button class="btn btn-icon btn-danger" @click="$emit('delete', p)" :aria-label="`Delete ${p.name}`">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>

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
              <button class="btn btn-small" @click="$emit('edit', p)">Edit</button>
              <button class="btn btn-small" @click="$emit('count', p)">Count</button>
              <button class="btn btn-small" @click="$emit('adjust', p)">Adjust</button>
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
*/

import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
  products: { type: Array, default: () => [] },
  fetchUrl: { type: String, default: '' }, // optional API endpoint to fetch products
  compact: { type: Boolean, default: false } // when true hide header/sidebar for embedding
})

const emit = defineEmits(['open-add', 'edit', 'delete'])

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
  if (!props.fetchUrl) return
  isLoading.value = true
  try {
    const res = await fetch(props.fetchUrl, { credentials: 'same-origin' })

    // Validate JSON response before parsing
    const contentType = res.headers.get('content-type')
    if (!contentType || !contentType.includes('application/json')) {
      console.error('Invalid JSON response - received', contentType || 'no content-type header')
      // Try to get text for debugging
      const text = await res.text()
      console.error('Response body (first 200 chars):', text.substring(0, 200))
      internal.value = []
      return
    }

    const data = await res.json()
    if (Array.isArray(data)) internal.value = data
    else if (data && Array.isArray(data.products)) internal.value = data.products
  } catch (e) {
    console.warn('fetchProducts failed', e)
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
const lowStockCount = computed(() => internal.value.filter(p => p.stock != null && p.stock > 0 && p.stock <= 10).length)
const outOfStockCount = computed(() => internal.value.filter(p => p.stock != null && p.stock <= 0).length)

// pagination reactive reset when perPage changes
watch(perPage, () => goToPage(1))


// expose some helpers for parent control
function getStats() {
  return {
    total: internal.value.length,
    low: internal.value.filter(p => p.stock != null && p.stock > 0 && p.stock <= 10).length,
    out: internal.value.filter(p => p.stock != null && p.stock <= 0).length
  }
}

function setQuery(val) { q.value = val }
function setStockFilter(val) { stockFilter.value = val }
function setCategoryFilter(val) { categoryFilter.value = val }

defineExpose({ fetchProducts, getStats, setQuery, setStockFilter, setCategoryFilter })

</script>

<style scoped>
/* Root layout */
.pl-root { display: grid; grid-template-columns: 260px 1fr; gap: 18px; align-items: start; padding: 18px; background: linear-gradient(180deg,#FF9A4A 0%,#FF6A3D 100%); color: #3b2b20; }
@media (max-width:880px) { .pl-root { grid-template-columns: 1fr; padding: 12px; } }

/* Header */
.pl-header { grid-column: 1 / -1; display:flex; justify-content:space-between; align-items:center; gap:16px; padding:12px 16px; background: rgba(255,244,230,0.92); border-radius:10px; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
.pl-title { margin:0; font-size:1.1rem; color:#7a2b00; }
.pl-sub { margin:0; font-size:0.85rem; color:#8a4b1a }
.pl-actions { display:flex; align-items:center; gap:10px }
.pl-search input { padding: 8px 12px; border-radius:8px; border:1px solid #e6eef6; min-width:200px; }
.pl-filters select { padding:8px; border-radius:8px; border:1px solid #e6eef6; margin-left:6px }
.pl-top-buttons { display:flex; gap:8px }
.btn { padding:8px 12px; border-radius:8px; border:1px solid transparent; cursor:pointer; font-weight:600 }
.btn-primary { background: linear-gradient(180deg,#ff7a18,#ff6a3d); color:white; box-shadow: 0 6px 14px rgba(255,106,61,0.18) }
.btn-light { background: #fff3e6; border:1px solid rgba(255,211,107,0.35); color:#7a2b00 }
.btn-danger { background: #d9534f; color: #111 }
.btn-icon { padding:6px 8px; border-radius:6px; background:#fff; border:1px solid #e6eef6 }
.btn-small { padding:6px 8px; border-radius:6px }

/* Sidebar */
.pl-sidebar { background:white; border-radius:10px; padding:12px; box-shadow: 0 1px 6px rgba(16,24,40,0.04); height:fit-content }
@media (max-width:880px) { .pl-sidebar { order:2 } }
.sidebar-toggle { display:block; margin-bottom:10px; background:transparent; border:none; color:#0b213f; font-weight:700; cursor:pointer }
.stat { padding:8px 6px; border-radius:8px; background:#f8fafc; margin-bottom:8px }
.stat-value { font-weight:800; font-size:1.1rem }
.stat-label { color:#475569; font-size:0.85rem }
.pl-sidebar.collapsed .sidebar-body { display:none }

/* Table */
.pl-main { background:transparent }
.pl-table-wrap { background:white; border-radius:10px; padding:12px; box-shadow: 0 1px 6px rgba(16,24,40,0.04) }
.pl-table { width:100%; border-collapse:collapse; font-family:Inter,system-ui,Segoe UI,Roboto,"Helvetica Neue",Arial }
.pl-table thead th { text-align:left; padding:10px; border-bottom:1px solid #eef2f7; color:#0b213f; font-weight:700; font-size:0.9rem }
.pl-table tbody tr { transition: background .12s ease, transform .08s ease; }
.pl-table tbody tr:hover { background: #fbfeff }
.pl-table td { padding:10px; vertical-align:middle; border-bottom:1px solid #f1f5f9 }
.col-thumb .thumb { width:48px; height:48px; object-fit:cover; border-radius:8px; border:1px solid #e6eef6 }
.thumb-placeholder { width:48px; height:48px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; background:#f1f5f9; color:#0b213f; font-weight:700 }
.name-block .name { font-weight:700; color:#0b213f }
.name-block .meta { font-size:0.8rem; color:#64748b }
.sku { display:inline-block; padding:4px 8px; border-radius:999px; background:#f8fafc; border:1px solid #e6eef6; font-weight:700; font-size:0.82rem }
.col-actions { text-align:right }

/* Stock colors */
.stock-ok { color:#065f46; background:rgba(16,185,129,0.08); padding:4px 8px; border-radius:8px; font-weight:700 }
.stock-low { color:#92400e; background:rgba(245,158,11,0.08); padding:4px 8px; border-radius:8px; font-weight:700 }
.stock-out { color:#7f1d1d; background:rgba(239,68,68,0.06); padding:4px 8px; border-radius:8px; font-weight:700 }
.stock-null { color:#475569 }

/* Pagination */
.pl-pagination { display:flex; gap:8px; align-items:center; justify-content:flex-end; margin-top:12px }
.page-btn { padding:8px 10px; border-radius:8px; border:1px solid #e6eef6; background:white }
.page-info { margin:0 8px; color:#475569 }

/* Mobile card layout */
.pl-cards { display:none }
@media (max-width:880px) {
  .pl-root { grid-template-columns: 1fr }
  .pl-table-wrap { display:none }
  .pl-cards { display:block }
  .card { display:flex; gap:12px; background:white; border-radius:10px; padding:12px; margin-bottom:12px; align-items:center }
  .card-left .thumb { width:64px; height:64px }
  .card-body .card-title { font-weight:800 }
  .card-actions { margin-top:8px; display:flex; gap:8px }
}

/* Utility */
.pl-loading, .pl-empty { padding:28px; background:white; border-radius:10px; text-align:center; color:#475569 }

</style>
