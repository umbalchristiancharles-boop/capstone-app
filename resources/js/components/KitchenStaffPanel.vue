<template>
  <OwnerPanelLayout
    ref="ownerLayout"
    :userProfile="userProfile"
    :panelEyebrow="'KITCHEN DASHBOARD'"
    :panelTitle="'Kitchen overview'"
    :panelDescription="''"
    :enableProfileUpdate="true"
    :canEditProfile="false"
    pageClass="kitchen-staff-page"
    :showHeader="true"
    :showProfileColumn="false"
    :showOwnerSidebar="true"
    :showOwnerTopbar="true"
    :topbarLabel="kitchenTopbarLabel"
    accountInfoStyle="finance"
    :showAnnouncements="true"
    :announcementsAfterAttendance="true"
    :ownerTwoColumnLayout="true"
    @profile-updated="onProfileUpdated"
    @logout="requestLogout"
  >
    <template #ownerSidebar>
      <nav class="kitchen-sidebar-nav" aria-label="Kitchen sections">
        <button type="button" class="kitchen-sidebar-link" :class="{ 'kitchen-sidebar-link--active': activeKitchenSection === 'kitchen-overview' }" @click="scrollKitchenSection('kitchen-overview')">Overview</button>
        <button type="button" class="kitchen-sidebar-link" :class="{ 'kitchen-sidebar-link--active': activeKitchenSection === 'kitchen-tasks' }" @click="scrollKitchenSection('kitchen-tasks')">Kitchen Tasks</button>
        <button type="button" class="kitchen-sidebar-link" :class="{ 'kitchen-sidebar-link--active': activeKitchenSection === 'kitchen-orders' }" @click="scrollKitchenSection('kitchen-orders')">Orders Queue</button>
      </nav>
    </template>

    <template #ownerSidebarFooter>
      <div class="kitchen-sidebar-actions">
        <button type="button" class="kitchen-sidebar-account" @click="ownerLayout?.openInfoModal()">Account Info</button>
        <button type="button" class="kitchen-sidebar-logout" @click="requestLogout">Logout</button>
      </div>
    </template>

    <template #headerActions>
      <button class="kitchen-header-refresh" type="button" @click="loadOrderQueue" :disabled="queueLoading">
        {{ queueLoading ? 'Loading...' : 'Refresh Orders' }}
      </button>
    </template>

    <template #main>
      <Transition name="kitchen-section" mode="out-in">
      <section id="kitchen-overview" :key="activeKitchenSection" class="panel-block">
        <Transition name="kitchen-section" mode="out-in">
          <div v-if="activeKitchenSection !== 'kitchen-orders'" key="kitchen-tasks" class="kitchen-view-section">
            <div id="kitchen-tasks" class="panel-header"><h2>Kitchen Tasks</h2></div>
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
                          <button v-if="canReduceStock(ing)" class="update-stock-btn" :disabled="(!ing.product_id)" @click.prevent="showUpdateStock(ing)">Reduce Stock</button>
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
            </div>
          </div>
        </Transition>

        <Transition name="kitchen-section" mode="out-in">
          <div v-if="activeKitchenSection !== 'kitchen-tasks'" id="kitchen-orders" key="kitchen-orders" class="queue-card">
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
        </Transition>
      </section>
      </Transition>
    </template>
    <template #sideTop>
      <div ref="profileWrapper" class="kitchen-header-actions" style="margin-bottom: 12px;">
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

  <LoadingOverlay :show="isLoggingOut" text="Logging out..." />

  <transition name="fade">
    <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
      <div class="logout-confirm-box logout-confirm-dialog">
        <h3>Confirm logout</h3>
        <p>This will end your current session as kitchen staff.</p>
        <div class="logout-actions">
          <button class="btn-cancel" type="button" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
          <button class="btn-confirm" type="button" @click="confirmLogout" :disabled="isLoggingOut">Yes</button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed, watch, nextTick } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import LoadingOverlay from './LoadingOverlay.vue'
import axios from 'axios'
import { showToast } from './toastStore'

const userProfile = ref({})
const ownerLayout = ref(null)
const activeKitchenSection = ref('kitchen-overview')
const kitchenTopbarLabel = computed(() => {
  const branchName = userProfile.value?.branch?.name || userProfile.value?.branch_name || userProfile.value?.branch || ''
  return `KITCHEN STAFF - ${(branchName || 'DASMA BRANCH').toString().toUpperCase()}`
})
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

function canReduceStock(ing) {
  const unit = String(ing.unit || '').trim().toLowerCase()
  const category = String(ing.product?.category || '').trim().toLowerCase()
  return ['g', 'gram', 'grams'].includes(unit) || category === 'condiment'
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
const showLogoutConfirm = ref(false)
const showProfileDropdown = ref(false)
const profileWrapper = ref(null)

function toggleProfileDropdown() {
  showProfileDropdown.value = !showProfileDropdown.value
}

function closeProfileDropdown() {
  showProfileDropdown.value = false
}

async function scrollKitchenSection(sectionId) {
  activeKitchenSection.value = sectionId
  await nextTick()
  const mainPanel = document.querySelector('.kitchen-staff-container .admin-main') || document.querySelector('.admin-layout--owner-sidebar-layout .admin-main')
  if (mainPanel) {
    mainPanel.scrollTop = 0
    mainPanel.scrollTo({ top: 0, behavior: 'smooth' })
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function handleInfoClick() {
  closeProfileDropdown()
  try { window.dispatchEvent(new Event('open-owner-info')) } catch (e) {}
}

function handleLogoutClick() {
  closeProfileDropdown()
  requestLogout()
}

function requestLogout() {
  if (!isLoggingOut.value) showLogoutConfirm.value = true
}

function cancelLogout() {
  if (!isLoggingOut.value) showLogoutConfirm.value = false
}

function onDocumentClick(e) {
  try {
    if (profileWrapper.value && !profileWrapper.value.contains(e.target)) closeProfileDropdown()
  } catch (er) {}
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  showLogoutConfirm.value = false
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  setTimeout(() => {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    try { window.location.replace('/admin-login') } catch (e) {}
  }, 500)
}
</script>

<style scoped>
:deep(.admin-page.kitchen-staff-page) {
  background: #f5f5f3;
  padding: 8px;
}

:deep(.admin-page.kitchen-staff-page .admin-layout) {
  background: transparent;
  border: 0;
  border-radius: 0;
  padding: 20px 24px;
  gap: 16px;
  box-shadow: none;
}

:deep(.admin-page.kitchen-staff-page .admin-layout.no-profile-column) {
  grid-template-columns: minmax(0, 1fr) minmax(260px, 360px);
}

:deep(.admin-page.kitchen-staff-page .admin-side) {
  position: static !important;
  align-self: start;
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100% !important;
  max-height: none;
  overflow: visible;
}

:deep(.admin-page.kitchen-staff-page .kitchen-staff-header) {
  margin: 0 0 20px;
  padding: 24px 28px;
  background: #fffdfb;
  border: 1px solid #eadfce;
  border-radius: 14px;
  box-shadow: 0 3px 12px rgba(78, 61, 45, 0.04);
}

.kitchen-staff-hero {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  padding: 18px 16px;
  margin-bottom: 14px;
  background: linear-gradient(135deg, #fffaf5 0%, #ffffff 72%);
  border: 1px solid #f1e5d8;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(66, 33, 11, 0.05);
}

.kitchen-staff-eyebrow {
  display: inline-block;
  margin-bottom: 6px;
  color: #c25a12;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.kitchen-staff-title {
  margin: 0;
  color: #1f2937;
  font-size: 26px;
  line-height: 1.1;
}

.kitchen-staff-subtitle {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 13px;
  max-width: 560px;
}

.kitchen-staff-hero__action {
  flex-shrink: 0;
  margin-top: 2px;
  background: #4b5563;
  color: #ffffff;
  border: 0;
  padding: 8px 14px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(75, 85, 99, 0.1);
}

.kitchen-staff-hero__action:hover:not(:disabled) {
  background: #374151;
}

.kitchen-staff-hero__action:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

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

:deep(.admin-page.kitchen-staff-page) .announcements-panel {
  background: #ffffff;
  color: #111827;
  border: 1px solid #eadfce;
  border-radius: 12px;
  padding: 12px;
  box-shadow: 0 3px 12px rgba(78, 61, 45, 0.04);
}

:deep(.admin-page.kitchen-staff-page) .announcements-panel .announcements-header {
  padding: 2px 0 10px;
  background: transparent;
  border: 0;
  border-radius: 0;
  box-shadow: none;
}

:deep(.admin-page.kitchen-staff-page) .announcements-panel .announcements-header h2 {
  margin: 0;
  color: #111827;
  font-size: 16px;
  line-height: 1.2;
  font-weight: 700;
}

:deep(.admin-page.kitchen-staff-page) .announcements-panel .panel-body {
  padding: 0;
}

:deep(.admin-page.kitchen-staff-page) .announcements-panel .announcement-list {
  margin: 0;
  padding: 0;
}

:deep(.admin-page.kitchen-staff-page) .announcements-panel .announcement-item {
  padding: 14px 12px;
  border-bottom: 1px solid #f1f1f1;
  border-radius: 0;
}

:deep(.admin-page.kitchen-staff-page) .announcements-panel .announcement-item:last-child {
  padding-bottom: 8px;
  border-bottom: 0;
}

:deep(.admin-page.kitchen-staff-page) .announcements-panel .announcement-title {
  margin-bottom: 4px;
  color: #111827;
  font-size: 16px;
  line-height: 1.25;
  font-weight: 700;
}

:deep(.admin-page.kitchen-staff-page) .announcements-panel .announcement-meta {
  margin-bottom: 6px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.35;
}

:deep(.admin-page.kitchen-staff-page) .announcements-panel .announcement-message {
  color: #334155;
  font-size: 16px;
  line-height: 1.45;
}

:deep(.admin-page.kitchen-staff-page) .attendance-card {
  margin-top: 125px;
}

:deep(.admin-page.kitchen-staff-page) .attendance-card .attendance-header {
  margin-top: 0 !important;
}

:deep(.admin-page.kitchen-staff-page) .admin-side .announcements-panel {
  position: static !important;
  margin-top: 0 !important;
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

/* Match the HR panel's compact section and card treatment. */
:deep(.admin-page.kitchen-staff-page) .panel-block {
  padding: 0;
  background: transparent;
  border: 0;
  box-shadow: none;
}

:deep(.admin-page.kitchen-staff-page) .panel-block.announcements-panel {
  padding: 12px;
  background: #ffffff;
  border: 1px solid #eadfce;
  border-radius: 12px;
  box-shadow: 0 3px 12px rgba(78, 61, 45, 0.04);
}

:deep(.admin-page.kitchen-staff-page) .panel-header,
:deep(.admin-page.kitchen-staff-page) .queue-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

:deep(.admin-page.kitchen-staff-page) .panel-header {
  padding: 12px 14px;
  margin-bottom: 12px;
}

:deep(.admin-page.kitchen-staff-page) .panel-header h2,
:deep(.admin-page.kitchen-staff-page) .kitchen-column h3,
:deep(.admin-page.kitchen-staff-page) .queue-header h3 {
  color: #111827;
  font-size: 16px;
  font-weight: 700;
}

:deep(.admin-page.kitchen-staff-page) .panel-body {
  color: #111827;
}

:deep(.admin-page.kitchen-staff-page) .kitchen-column {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 12px;
}

:deep(.admin-page.kitchen-staff-page) .dish-card,
:deep(.admin-page.kitchen-staff-page) .queue-item {
  background: #fafafa;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

:deep(.admin-page.kitchen-staff-page) .ingredient-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
}

:deep(.admin-page.kitchen-staff-page) .refresh-btn,
:deep(.admin-page.kitchen-staff-page) .update-stock-btn,
:deep(.admin-page.kitchen-staff-page) .btn-done {
  background: #4b5563;
  color: #ffffff;
  border: 0;
  border-radius: 6px;
  font-weight: 600;
}

:deep(.admin-page.kitchen-staff-page) .refresh-btn:hover:not(:disabled),
:deep(.admin-page.kitchen-staff-page) .update-stock-btn:hover:not(:disabled),
:deep(.admin-page.kitchen-staff-page) .btn-done:hover:not(:disabled) {
  background: #374151;
}

@media (max-width: 900px) {
  :deep(.admin-page.kitchen-staff-page .admin-layout) {
    padding: 12px 14px;
  }

  :deep(.admin-page.kitchen-staff-page .admin-layout.no-profile-column) {
    grid-template-columns: minmax(0, 1fr);
  }

  :deep(.admin-page.kitchen-staff-page .kitchen-staff-header) {
    padding: 18px;
  }

  .kitchen-staff-hero {
    flex-direction: column;
    gap: 12px;
  }

  .kitchen-staff-hero__action {
    width: 100%;
  }

  .kitchen-grid { grid-template-columns: 1fr; }
}

/* Finance-style shell for Kitchen Staff while preserving kitchen content. */
:global(.owner-panel-light-mode),
:global(.owner-panel-light-mode .admin-page),
:global(.owner-panel-light-mode .admin-layout),
:global(.owner-panel-light-mode .admin-main) {
  min-height: 100vh !important;
  height: 100% !important;
  background: #e7d9cf !important;
}

:deep(.admin-page.kitchen-staff-page) {
  padding: 0 !important;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container) {
  padding: 0 !important;
}

:deep(.owner-panel-light-mode),
:deep(.owner-panel-light-mode > .admin-page),
:deep(.admin-page.kitchen-staff-page),
:deep(.admin-page.kitchen-staff-page .admin-layout),
:deep(.admin-page.kitchen-staff-page .admin-main) {
  min-height: 100vh;
  background: #e7d9cf !important;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container) {
  display: block;
  min-height: 100vh;
  overflow: visible;
  background: #e7d9cf;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .owner-panel-sidebar) {
  position: fixed;
  inset: 0 auto 0 0;
  z-index: 400;
  width: 156px;
  min-width: 156px;
  min-height: 100vh;
  padding: 1.5rem 1rem 1rem;
  box-sizing: border-box;
  background: rgba(255, 255, 255, 0.42) !important;
  border-right: 1px solid rgba(115, 93, 84, 0.18) !important;
  transform: translateX(0);
  opacity: 1;
  transition: transform 260ms ease, opacity 180ms ease, width 260ms ease;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .owner-panel-topbar) {
  position: fixed;
  top: 0;
  right: 0;
  left: 156px;
  z-index: 300;
  width: calc(100% - 156px) !important;
  height: 66px;
  min-height: 66px;
  margin-left: 0 !important;
  box-sizing: border-box;
  background: linear-gradient(180deg, #e7d9cf 0%, #eee5df 100%) !important;
  border-bottom: 1px solid rgba(115, 93, 84, 0.18) !important;
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
  transition: left 260ms ease;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .admin-main) {
  display: flex;
  width: auto;
  min-width: 0;
  height: calc(100vh - 66px);
  min-height: 0;
  margin: 66px 0 0 156px;
  padding: 1.2rem 1.3rem 1.25rem;
  box-sizing: border-box;
  flex-direction: column;
  gap: 0.25rem;
  overflow-x: hidden;
  overflow-y: auto;
  overscroll-behavior: contain;
  background: transparent;
  transition: margin-left 260ms ease;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .admin-main-header) {
  margin: 0;
  padding: 0;
  background: transparent;
  border: 0;
  box-shadow: none;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .admin-main-header-top) {
  width: 100%;
  align-items: flex-start;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .header-left-slot) {
  display: none;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .header-actions-top) {
  display: flex;
  align-items: flex-start;
  margin-left: auto !important;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .admin-main-header__eyebrow) {
  display: block;
  margin-bottom: 0.3rem;
  color: #c46632;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  line-height: 1.2;
  text-transform: uppercase;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .admin-main-header h1) {
  margin: 0 0 0.35rem;
  color: #12304c;
  font-size: 2rem;
  font-weight: 900;
  line-height: 1.1;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .admin-main-header p) {
  margin: 0;
  color: #94735f;
  font-size: 0.9rem;
}

:deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .admin-main-header p:empty) {
  display: none;
}

.kitchen-header-refresh {
  padding: 0.72rem 1rem;
  border: 1px solid #243447;
  border-radius: 10px;
  background: #243447;
  color: #ffffff;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 8px 18px rgba(36, 52, 71, 0.15);
  transition: background 180ms ease, transform 180ms ease, box-shadow 180ms ease;
}

.kitchen-header-refresh:hover:not(:disabled) {
  background: #172536;
  transform: translateY(-1px);
  box-shadow: 0 10px 22px rgba(36, 52, 71, 0.2);
}

.kitchen-header-refresh:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.kitchen-section-enter-active,
.kitchen-section-leave-active {
  transition: opacity 220ms ease, transform 220ms cubic-bezier(0.22, 1, 0.36, 1);
}

.kitchen-section-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.kitchen-section-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

:deep(.owner-sidebar-collapsed.admin-layout--owner-sidebar-layout.kitchen-staff-container .owner-panel-sidebar) {
  width: 0;
  min-width: 0;
  padding-left: 0;
  padding-right: 0;
  overflow: hidden;
  opacity: 0;
  transform: translateX(-100%);
  pointer-events: none;
}

:deep(.owner-sidebar-collapsed.admin-layout--owner-sidebar-layout.kitchen-staff-container .owner-panel-topbar) {
  left: 0;
  width: 100% !important;
  margin-left: 0 !important;
}

:deep(.owner-sidebar-collapsed.admin-layout--owner-sidebar-layout.kitchen-staff-container .admin-main) {
  margin-left: 0;
}

.kitchen-sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 0.5rem 0;
}

.kitchen-sidebar-link {
  width: 100%;
  padding: 0.72rem 0.9rem;
  border: 1px solid transparent;
  border-radius: 12px;
  background: transparent;
  color: #29384a;
  text-align: left;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 180ms ease, border-color 180ms ease, color 180ms ease, transform 180ms ease;
}

.kitchen-sidebar-link:hover,
.kitchen-sidebar-link--active {
  background: #fffaf5;
  border-color: #efb47f;
  color: #111827;
  box-shadow: 0 5px 12px rgba(184, 111, 61, 0.08);
  transform: translateX(2px);
}

.kitchen-sidebar-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.kitchen-sidebar-account,
.kitchen-sidebar-logout {
  width: 100%;
  padding: 0.7rem 0.8rem;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
  color: #374151;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 180ms ease, border-color 180ms ease, transform 180ms ease;
}

.kitchen-sidebar-account {
  border-color: #c9d9e5;
  background: #f7fbff;
  color: #30445a;
}

.kitchen-sidebar-logout {
  border-color: #e3b1a5;
  background: #fff9f7;
  color: #a23d32;
}

.kitchen-sidebar-account:hover,
.kitchen-sidebar-logout:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 12px rgba(83, 57, 37, 0.1);
}

.kitchen-staff-hero,
:deep(.admin-page.kitchen-staff-page) .panel-header,
:deep(.admin-page.kitchen-staff-page) .kitchen-column,
:deep(.admin-page.kitchen-staff-page) .queue-card {
  background: #ffffff;
  border: 1px solid rgba(226, 232, 240, 0.9);
  border-radius: 14px;
  box-shadow: 0 8px 20px rgba(83, 57, 37, 0.1);
}

.kitchen-staff-hero {
  margin-bottom: 0;
  padding: 1.25rem;
  background: linear-gradient(135deg, #fffaf5 0%, #ffffff 72%);
}

.kitchen-staff-title {
  color: #12304c;
  font-size: 1.7rem;
  font-weight: 900;
}

.kitchen-staff-hero__action,
:deep(.admin-page.kitchen-staff-page) .refresh-btn,
:deep(.admin-page.kitchen-staff-page) .update-stock-btn,
:deep(.admin-page.kitchen-staff-page) .btn-done {
  border: 1px solid #243447;
  border-radius: 10px;
  background: #243447;
  color: #ffffff;
  box-shadow: 0 8px 18px rgba(36, 52, 71, 0.15);
  transition: background 180ms ease, transform 180ms ease, box-shadow 180ms ease;
}

.kitchen-staff-hero__action:hover:not(:disabled),
:deep(.admin-page.kitchen-staff-page) .refresh-btn:hover:not(:disabled),
:deep(.admin-page.kitchen-staff-page) .update-stock-btn:hover:not(:disabled),
:deep(.admin-page.kitchen-staff-page) .btn-done:hover:not(:disabled) {
  background: #172536;
  transform: translateY(-1px);
  box-shadow: 0 10px 22px rgba(36, 52, 71, 0.2);
}

:deep(.admin-page.kitchen-staff-page) .panel-header {
  margin-bottom: 1rem;
  padding: 1.25rem;
}

:deep(.admin-page.kitchen-staff-page) .panel-header h2,
:deep(.admin-page.kitchen-staff-page) .kitchen-column h3,
:deep(.admin-page.kitchen-staff-page) .queue-header h3 {
  color: #3d2a1f;
}

:deep(.admin-page.kitchen-staff-page) .queue-card {
  margin-top: 1rem;
  padding: 1.25rem;
}

:deep(.admin-page.kitchen-staff-page) .queue-list {
  max-height: 31rem;
  overflow-y: auto;
  padding: 0.1rem 0.45rem 0.35rem 0;
  scrollbar-width: thin;
  scrollbar-color: #d58a55 rgba(231, 217, 207, 0.55);
}

:deep(.admin-page.kitchen-staff-page) .queue-list::-webkit-scrollbar {
  width: 9px;
}

:deep(.admin-page.kitchen-staff-page) .queue-list::-webkit-scrollbar-track {
  background: rgba(231, 217, 207, 0.55);
  border-radius: 999px;
}

:deep(.admin-page.kitchen-staff-page) .queue-list::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #efb47f, #c46632);
  border: 2px solid rgba(255, 255, 255, 0.8);
  border-radius: 999px;
}

:deep(.admin-page.kitchen-staff-page) .queue-list::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #e89b62, #a94f25);
}

:deep(.admin-page.kitchen-staff-page) .dish-card,
:deep(.admin-page.kitchen-staff-page) .queue-item,
:deep(.admin-page.kitchen-staff-page) .ingredient-card {
  border-color: #e5e7eb;
  border-radius: 10px;
  background: #fffaf7;
  transition: border-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
}

:deep(.admin-page.kitchen-staff-page) .dish-card:hover,
:deep(.admin-page.kitchen-staff-page) .queue-item:hover {
  border-color: #efb47f;
  box-shadow: 0 6px 16px rgba(83, 57, 37, 0.08);
  transform: translateY(-1px);
}

@media (max-width: 767px) {
  :deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .owner-panel-sidebar) {
    width: 156px;
    min-width: 156px;
  }

  :deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .owner-panel-topbar) {
    left: 0;
  }

  :deep(.admin-layout--owner-sidebar-layout.kitchen-staff-container .admin-main) {
    width: 100%;
    margin: 60px 0 0;
    height: calc(100vh - 60px);
    padding: 0.75rem;
  }

  .kitchen-staff-hero {
    padding: 1rem;
  }

  .kitchen-staff-title {
    font-size: 1.45rem;
  }

  :deep(.admin-page.kitchen-staff-page) .queue-item {
    align-items: flex-start;
    flex-direction: column;
    gap: 0.65rem;
  }

  :deep(.admin-page.kitchen-staff-page) .queue-actions {
    width: 100%;
    justify-content: space-between;
  }

  :deep(.admin-page.kitchen-staff-page) .queue-list {
    max-height: 28rem;
  }
}

.logout-confirm-dialog {
  width: min(92vw, 398px) !important;
  min-height: 0;
  padding: 1.9rem 1.75rem 1.35rem !important;
  box-sizing: border-box;
  border: 1px solid #e5e7eb !important;
  border-radius: 4px !important;
  background: #ffffff !important;
  text-align: center;
  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2) !important;
}

.logout-confirm-dialog h3 {
  margin: 0 0 0.85rem !important;
  color: #5b5b5b !important;
  font-size: 1.45rem !important;
  font-weight: 700 !important;
}

.logout-confirm-dialog h3::after,
.logout-confirm-dialog p::after,
.logout-confirm-dialog .btn-confirm::after {
  content: none !important;
}

.logout-confirm-dialog p {
  max-width: 330px;
  margin: 0 auto 1.45rem !important;
  color: #666666 !important;
  font-size: 0.98rem !important;
  line-height: 1.45;
}

.logout-confirm-dialog .logout-actions {
  justify-content: center;
  gap: 0.55rem;
  margin-top: 0;
}

.logout-confirm-dialog .btn-cancel,
.logout-confirm-dialog .btn-confirm {
  min-width: 4.5rem;
  min-height: 2.35rem;
  padding: 0.55rem 0.9rem !important;
  border-radius: 3px !important;
  font-size: 0.82rem !important;
}

.logout-confirm-backdrop {
  z-index: 500 !important;
}
</style>
