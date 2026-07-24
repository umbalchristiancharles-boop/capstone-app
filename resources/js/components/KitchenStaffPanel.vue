<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Kitchen Staff Panel'"
    :panelDescription="'Manage kitchen orders and preparation.'"
    :enableProfileUpdate="true"
    :canEditProfile="false"
    :showProfileColumn="false"
    :ownerTwoColumnLayout="true"
    @profile-updated="onProfileUpdated"
    @logout="confirmLogout"
  >
    <template #main>
      <section class="panel-block">
        <div class="panel-header"><h2>Kitchen Tasks</h2></div>
        <div class="panel-body">
          <div class="kitchen-grid">
            <div class="kitchen-column">
              <h3>My Dishes</h3>
              <div v-if="loading">Loading...</div>
              <div v-else>
                <div v-if="dishes.length === 0">No dishes yet.</div>
                <div class="dish-cards" v-else>
                  <div class="dish-card" v-for="d in dishes" :key="d.id">
                    <div class="dish-card-header">
                      <div>
                        <strong class="dish-name">{{ d.name }}</strong>
                        <div class="dish-status"><small>({{ d.status }})</small></div>
                      </div>
                    </div>

                    <div class="dish-ingredients">
                      <div class="ingredient-card" v-for="ing in d.ingredients" :key="ing.id || ing.name">
                        <div class="ingredient-info">
                          <div class="ingredient-name">{{ ing.name }}</div>
                          <div class="ingredient-brand" v-if="ing.brand"><small>Brand: {{ ing.brand }}</small></div>
                          <div class="ingredient-per" v-if="ing.unit"><em>- per serving: {{ formatPerServing(ing.per_serving) }} {{ ing.unit }}</em></div>
                          <div class="ingredient-publish" v-if="ing.product">
                            <small v-if="ing.product && !ing.product.is_published" style="color:#b91c1c">(product unpublished)</small>
                            <small v-else style="color:#059669">(product published)</small>
                          </div>
                        </div>
                        <div class="ingredient-actions">
                          <button class="update-stock-btn" :disabled="(!ing.product_id)" @click.prevent="showUpdateStock(ing)">Reduce Stock</button>
                          <div v-if="updateStockVisible[ingKey(ing)]" class="update-stock-form">
                            <input type="number" v-model.number="updateStockForm[ingKey(ing)].reduce" min="1" max="9999" />
                            <button @click.prevent="submitUpdateStock(ing)" :disabled="updateStockSubmitting[ingKey(ing)]">
                              {{ updateStockSubmitting[ingKey(ing)] ? 'Saving...' : 'Save' }}
                            </button>
                            <button @click.prevent="hideUpdateStock(ing)" :disabled="updateStockSubmitting[ingKey(ing)]">Cancel</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="queue-card">
            <div class="queue-header">
              <div>
                <h3>
                  Orders Queue
                  <span v-if="pendingKitchenCount > 0" class="panel-badge">{{ pendingKitchenCount }}</span>
                </h3>
                <p class="sub">Pending / In Kitchen orders for this branch</p>
              </div>
              <button type="button" class="refresh-btn" @click="loadOrderQueue" :disabled="queueLoading">
                {{ queueLoading ? 'Refreshing...' : 'Refresh' }}
              </button>
            </div>
            <div v-if="queueLoading">Loading queue...</div>
            <div v-else-if="queueError" class="muted">{{ queueError }}</div>
            <div v-else-if="queueForbidden" class="muted">Access requires kitchen.orders permission.</div>
            <div v-else-if="orderQueue.length === 0" class="muted">No orders in queue.</div>
            <div v-else class="queue-list">
              <div v-for="order in orderQueue" :key="order.id" class="queue-item">
                <div class="queue-main">
                  <strong>{{ order.title }}</strong>
                  <span class="queue-meta">{{ order.meta }}</span>
                </div>
                <div class="queue-actions">
                  <span :class="['badge', order.badgeClass]">{{ order.badgeLabel }}</span>
                  <button
                    v-if="order.badgeLabel && order.badgeLabel.toLowerCase().includes('kitchen')"
                    type="button"
                    class="btn-done"
                    @click="markOrderDone(order.id)"
                    :disabled="markingDoneId === order.id"
                  >
                    {{ markingDoneId === order.id ? 'Marking...' : 'Mark Done' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>
    <template #headerActions>
      <div ref="profileWrapper" class="kitchen-header-actions">
        <div class="header-profile-wrapper">
        <div
          class="header-profile-container"
          style="background:#fff;border:1px solid #eef2f5;border-radius:12px;padding:6px 10px;display:inline-flex;align-items:center;"
        >
          <button
            class="header-profile-btn"
            type="button"
            style="background: transparent; border: 0; cursor: pointer; display: flex; align-items: center; gap: 0.6rem; padding:0;"
            @click.stop="toggleProfileDropdown"
          >
            <div class="header-avatar" style="width:28px;height:28px;border-radius:50%;background:rgb(238,238,238);display:flex;align-items:center;justify-content:center;font-weight:600;">
              <div class="header-avatar-initials">{{ (userProfile.fullName || userProfile.full_name || userProfile.name || 'K').charAt(0) }}</div>
            </div>
            <div class="header-name" style="font-size:0.85rem;font-weight:700;color:#111827;white-space:nowrap;">
              {{ (userProfile.role || 'STAFF').toString().toUpperCase() }}
              <span v-if="userProfile.branch || userProfile.branch_name" style="font-weight:600;opacity:0.85"> - {{ (userProfile.branch || userProfile.branch_name).toString().toUpperCase() }}</span>
            </div>
          </button>
        </div>

        <div
          v-if="showProfileDropdown"
          class="header-profile-dropdown"
          style="position:absolute;right:0;top:46px;background:#fff;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.08);padding:8px;display:flex;flex-direction:column;gap:6px;min-width:140px;z-index:30"
        >
          <button class="dropdown-item" style="background:transparent;border:0;padding:8px;text-align:left;" @click.prevent="handleInfoClick">Info</button>
          <button class="dropdown-item" style="background:transparent;border:0;padding:8px;text-align:left;" @click.prevent="handleLogoutClick">Logout</button>
        </div>
      </div>
      </div>
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed, watch, nextTick } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import { showToast } from './toastStore'

const userProfile = ref({})
const dishes = ref([])
const loading = ref(false)
const queueLoading = ref(false)
const markingDoneId = ref(null)
const products = ref([])
const orderQueue = ref([])
const queueForbidden = ref(false)
const queueError = ref('')
const hasNotified = ref(false)
const pendingKitchenCount = computed(() => (orderQueue.value || []).length)

watch(pendingKitchenCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have pending kitchen orders.', 'info')
    hasNotified.value = true
  }
})

async function loadDishes() {
  loading.value = true
  try {
    const res = await axios.get('/api/staff/kitchen/dishes')
    dishes.value = res.data || []
  } catch (e) {
    console.error('Failed to load dishes', e)
  } finally {
    loading.value = false
  }
}

async function loadProducts() {
  try {
    const res = await axios.get('/api/staff/inventory/products?include_unpublished=1')
    products.value = res.data || []
  } catch (e) {
    console.error('Failed to load products for kitchen form', e)
    products.value = []
  }
}

function mapQueueItem(task) {
  const status = String(task.status || task.badgeLabel || '').toLowerCase()
  const badgeLabel = task.badgeLabel || (status ? status.replace(/_/g, ' ') : 'pending')
  const badgeClass = task.badgeClass || (status === 'in_kitchen' ? 'badge--warning' : 'badge--info')
  const title = task.title || `Order #${task.code || task.id || task.order_id || 'N/A'}`
  const meta = task.meta || [task.customer ?? task.customer_name ?? 'Guest', task.created_at ?? task.time ?? ''].filter(Boolean).join(' • ')
  return {
    id: task.id || task.order_id || task.code || Math.random().toString(36).slice(2, 9),
    title,
    meta,
    badgeLabel,
    badgeClass,
  }
}

async function markOrderDone(orderId) {
  markingDoneId.value = orderId
  try {
    await axios.patch(`/api/orders/${orderId}/mark-completed`)
    await loadOrderQueue()
  } catch (e) {
    console.error('Failed to mark order as done', e)
    alert(e?.response?.data?.message || 'Failed to mark order as done')
  } finally {
    markingDoneId.value = null
  }
}

async function loadOrderQueue() {
  queueLoading.value = true
  queueForbidden.value = false
  queueError.value = ''
  try {
    const res = await axios.get('/api/staff/dashboard', { params: { range: 'today' } })
    const tasks = res.data?.myTasks || res.data?.ordersQueue || res.data?.data?.myTasks || []
    orderQueue.value = Array.isArray(tasks) ? tasks.map(mapQueueItem) : []
  } catch (e) {
    const status = e?.response?.status
    if (status === 401) {
      queueError.value = 'Please log in again to see the kitchen queue.'
    } else if (status === 403) {
      queueForbidden.value = true
      queueError.value = 'You do not have access to the kitchen orders queue.'
    } else {
      queueError.value = 'Unable to load the kitchen queue right now.'
    }
    console.error('Failed to load order queue', e)
    orderQueue.value = []
  } finally {
    queueLoading.value = false
  }
}

const updateStockVisible = reactive({})
const updateStockForm = reactive({})
const updateStockSubmitting = reactive({})

function ingKey(ing) {
  return String((ing.product && ing.product.id) || ing.product_id || ing.id || Math.random().toString(36).slice(2,9))
}

async function showUpdateStock(ing) {
  const key = ingKey(ing)
  updateStockVisible[key] = true
  if (!updateStockForm[key]) {
    updateStockForm[key] = { reduce: 1 }
  }
  try {
    if (!ing.product && ing.product_id) {
      if (!products.value || products.value.length === 0) {
        await loadProducts()
      }
      const p = products.value.find(p => String(p.id) === String(ing.product_id))
      if (p) {
        ing.product = { ...p }
      }
    }
  } catch (er) {
    console.warn('Failed to load product for showUpdateStock', er)
  }
}

function hideUpdateStock(ing) {
  updateStockVisible[ingKey(ing)] = false
}

async function submitUpdateStock(ing) {
  const productId = (ing.product && ing.product.id) || ing.product_id
  if (!productId) {
    alert('Cannot update stock for an ingredient not linked to a product.')
    return
  }

  const key = ingKey(ing)
  try {
    const reduce = Number((updateStockForm[key] && updateStockForm[key].reduce) || 0)
    if (reduce <= 0) {
      alert('Enter a positive reduce amount')
      return
    }

    const payload = { reduce }
    updateStockSubmitting[key] = true

    const res = await axios.put(`/api/manager/inventory/${productId}`, payload, { withCredentials: true })

    try {
      if (res.data && res.data.product) {
        const updated = res.data.product
        if (ing.product) {
          ing.product.stock = updated.stock
          if (typeof updated.real_stock !== 'undefined') {
            ing.product.real_stock = updated.real_stock
          }
        }
        const globalP = products.value.find(p => String(p.id) === String(productId))
        if (globalP) {
          globalP.stock = updated.stock
          if (typeof updated.real_stock !== 'undefined') {
            globalP.real_stock = updated.real_stock
          }
        }
      }
    } catch (er) { console.warn('Failed updating local stock view', er) }

    showToast(res.data.message || 'Stock reduced', 'success')
    updateStockVisible[key] = false
    loadProducts().catch(()=>{})
    loadDishes().catch(()=>{})
  } catch (e) {
    console.error('Failed updating stock', e)
    const resp = e?.response
    if (resp && resp.data) {
      const body = resp.data
      let msg = body.message || 'Validation error'
      if (body.errors) {
        const firstKey = Object.keys(body.errors)[0]
        if (firstKey && Array.isArray(body.errors[firstKey])) {
          msg = body.errors[firstKey].join(' ')
        }
      }
      alert(msg)
    } else {
      alert(e?.message || 'Failed to update stock')
    }
  } finally {
    updateStockSubmitting[key] = false
    await nextTick()
  }
}

function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

function formatPerServing(val) {
  try {
    const n = Number(val || 0)
    return n.toFixed(4)
  } catch (e) {
    return val
  }
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/staff/profile', { withCredentials: true })
    if (res.data && res.data.user) userProfile.value = res.data.user
  } catch (e) {
    console.error('Failed to load staff profile for kitchen panel', e)
  }
  await Promise.all([loadDishes(), loadProducts(), loadOrderQueue()])
})

onMounted(() => {
  document.addEventListener('click', onDocumentClick)
})

onUnmounted(() => {
  document.removeEventListener('click', onDocumentClick)
})

const isLoggingOut = ref(false)
const showProfileDropdown = ref(false)
const profileWrapper = ref(null)

function toggleProfileDropdown() {
  showProfileDropdown.value = !showProfileDropdown.value
}

function closeProfileDropdown() {
  showProfileDropdown.value = false
}

function handleInfoClick() {
  closeProfileDropdown()
  try { window.dispatchEvent(new Event('open-owner-info')) } catch (e) {}
}

function handleLogoutClick() {
  closeProfileDropdown()
  confirmLogout()
}

function onDocumentClick(e) {
  try {
    if (profileWrapper.value && !profileWrapper.value.contains(e.target)) closeProfileDropdown()
  } catch (er) {}
}

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
.panel-block { padding: 1rem; border-radius: 12px; background: #FFF8F0; border-left: 4px solid #17a2b8; box-shadow: 0 4px 12px rgba(23,162,184,0.08); }
.panel-header h2 { margin: 0 0 8px 0; color: #5b4637 }
.panel-body { color: #374151 }
.queue-card { margin-top: 1.5rem; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb }
.queue-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.queue-header h3 { margin: 0; position: relative; display: inline-block; }
.sub { margin: 0; color: #6b7280; font-size: 0.9rem; }
.refresh-btn { padding: 0.5rem 0.9rem; border: 1px solid #d1d5db; background: #f8fafc; color: #374151; border-radius: 6px; cursor: pointer; transition: background 0.2s, border-color 0.2s; }
.refresh-btn:hover:not(:disabled) { background: #f1f5f9; border-color: #cbd5e1; }
.queue-list { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.75rem; }
.queue-item { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; }
.queue-main { display: flex; flex-direction: column; gap: 4px; }
.queue-meta { color: #6b7280; font-size: 0.9rem; }
.queue-actions { display: flex; align-items: center; gap: 0.75rem; }
.muted { color: #6b7280; }
.badge { padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.82rem; text-transform: capitalize; }
.badge--warning { background: #fff7ed; color: #b45309; }
.badge--info { background: #e0f2fe; color: #0369a1; }
.btn-done { padding: 0.4rem 0.8rem; background: #4b5563; color: white; border: none; border-radius: 6px; font-size: 0.85rem; cursor: pointer; transition: background 0.2s; }
.btn-done:hover:not(:disabled) { background: #374151; }
.btn-done:disabled { background: #d1d5db; cursor: not-allowed; }

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

:deep(.admin-main-header) {
  position: relative;
}

.kitchen-header-actions {
  position: absolute;
  top: 1.25rem;
  right: 1.25rem;
  z-index: 20;
  display: flex;
  justify-content: flex-end;
  align-items: flex-start;
  transform: translate(-4px, 4px);
}

.header-profile-wrapper {
  position: relative;
}

:deep(.admin-layout.no-profile-column) .admin-side .announcements-panel {
  margin-top: 120px !important;
}

.kitchen-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; align-items: start; }
.kitchen-column { background: #ffffff; border: 1px solid #eef2f5; border-radius: 8px; padding: 0.85rem; }
.kitchen-column h3 { margin-top: 0; margin-bottom: 0.5rem; font-size: 1.05rem }

.dish-cards { display:flex; flex-direction:column; gap:0.75rem }
.dish-card { border:1px solid #eef2f5; border-radius:10px; padding:0.65rem; background:#fff }
.dish-card-header { display:flex; align-items:center; gap:0.8rem; margin-bottom:0.5rem }
.dish-name { font-size:1rem; display:block }
.dish-status { color:#6b7280 }
.dish-ingredients { display:flex; flex-direction:column; gap:0.5rem }
.ingredient-card { display:flex; align-items:center; justify-content:space-between; gap:0.5rem; padding:0.5rem; border-radius:8px; background:#fbfdff; border:1px solid #f1f5f9 }
.ingredient-info { max-width:calc(100% - 120px) }
.ingredient-name { font-weight:600 }
.ingredient-brand { color:#6b7280; font-size:0.85rem; }
.ingredient-per { color:#374151; font-style:italic }
.ingredient-actions { display:flex; align-items:center; gap:0.5rem }
.update-stock-btn { padding:0.35rem 0.5rem; border-radius:6px; border:1px solid #cbd5e1; background:#f8fafc; color:#1f2937; cursor:pointer; transition: background 0.2s, border-color 0.2s; }
.update-stock-btn:hover:not(:disabled) { background:#f1f5f9; border-color:#94a3b8; }
.update-stock-form { display:flex; gap:0.5rem; align-items:center }

@media (max-width: 900px) {
  .kitchen-grid { grid-template-columns: 1fr; }
}
</style>
