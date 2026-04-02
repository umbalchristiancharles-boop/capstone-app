<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Kitchen Staff Panel'"
    :panelDescription="'Manage kitchen orders and preparation.'"
    :enableProfileUpdate="true"
    :canEditProfile="false"
    :showProfileColumn="false"
    @profile-updated="onProfileUpdated"
    @logout="confirmLogout"
  >
    <template #main>
      <section class="panel-block">
        <div class="panel-header"><h2>Kitchen Tasks</h2></div>
        <div class="panel-body">
          <div class="kitchen-grid">
            <div class="kitchen-column">
              <h3>Create Dish</h3>
              <form @submit.prevent="submitDish">
                <div class="form-row">
                  <label>Dish name</label>
                  <input v-model="form.name" type="text" required />
                </div>

                <div class="form-row">
                  <label>Ingredients</label>
                  <div class="ingredients">
                    <div v-for="(ing, idx) in form.ingredients" :key="idx" class="ingredient-row">
                      <select v-model="ing.product_id" @change="onProductSelect(idx)">
                        <option value="">-- choose from stock (or leave blank to type new) --</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">
                          {{ p.name }} ({{ p.stock }} in stock) <span v-if="!p.is_published">— unpublished</span>
                        </option>
                      </select>

                      <input v-if="!ing.product_id" v-model="ing.name" placeholder="Ingredient name" required />
                      <input v-else v-model="ing.name" placeholder="Ingredient name" readonly />

                      <input v-model="ing.per_serving" placeholder="Per serving (optional)" class="small" />
                      <select v-model="ing.unit">
                        <option value="">unspecified</option>
                        <option value="pcs">pcs</option>
                        <option value="g">g</option>
                        <option value="kg">kg</option>
                        <option value="ml">ml</option>
                        <option value="l">l</option>
                        <option value="pack">pack</option>
                      </select>
                      <button type="button" @click="removeIngredient(idx)">Remove</button>
                    </div>
                    <button type="button" @click="addIngredient">Add ingredient</button>
                  </div>
                </div>

                <div class="form-actions">
                  <button type="submit">Create Dish</button>
                </div>
              </form>

              <div v-if="message" class="message">{{ message }}</div>
            </div>

            <div class="kitchen-column">
              <h3>My Dishes</h3>
              <div v-if="loading">Loading...</div>
                <div v-else>
                  <div v-if="dishes.length === 0">No dishes yet.</div>
                  <div class="dish-cards">
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
                            <div class="ingredient-per" v-if="ing.unit"><em>- per serving: {{ formatPerServing(ing.per_serving) }} {{ ing.unit }}</em></div>
                            <div class="ingredient-publish" v-if="ing.product">
                              <small v-if="ing.product && !ing.product.is_published" style="color:#b91c1c">(product unpublished)</small>
                              <small v-else style="color:#059669">(product published)</small>
                            </div>
                          </div>
                          <div class="ingredient-actions">
                            <button class="mark-low-btn" @click.prevent="showLowStock(ing)">Mark Low Stock</button>
                            <div v-if="lowStockVisible[ing.id]" class="low-stock-form">
                              <select v-model="lowStockForm[ing.id].unit">
                                <option value="pcs">pcs</option>
                                <option value="g">g</option>
                                <option value="kg">kg</option>
                                <option value="ml">ml</option>
                                <option value="l">l</option>
                                <option value="pack">pack</option>
                              </select>
                              <button @click.prevent="submitLowStock(ing)">Submit</button>
                              <button @click.prevent="hideLowStock(ing)">Cancel</button>
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
                <h3>Orders Queue</h3>
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
      <div ref="profileWrapper" class="header-profile-wrapper" style="position:relative;">
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
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'

const userProfile = ref({})
const dishes = ref([])
const loading = ref(false)
const queueLoading = ref(false)
const markingDoneId = ref(null)
const message = ref('')
const products = ref([])
const orderQueue = ref([])
const queueForbidden = ref(false)
const queueError = ref('')

const form = ref({
  name: '',
  ingredients: [ { name: '', product_id: '', unit: 'pcs', per_serving: 0 } ]
})

function addIngredient() {
  form.value.ingredients.push({ name: '', product_id: '', unit: 'pcs', per_serving: 0 })
}

function removeIngredient(idx) {
  form.value.ingredients.splice(idx, 1)
}

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

async function submitDish() {
  message.value = ''
  try {
      const payload = {
        name: form.value.name,
        ingredients: form.value.ingredients.map(i => ({ name: i.name, unit: i.unit, per_serving: i.per_serving, product_id: i.product_id || null }))
      }
    const res = await axios.post('/api/staff/kitchen/dishes', payload)
    message.value = 'Dish created'
    form.value.name = ''
    form.value.ingredients = [ { name: '', product_id: '', unit: 'pcs', per_serving: 0 } ]
    await loadDishes()
  } catch (e) {
    console.error('Failed to create dish', e)
    message.value = e?.response?.data?.error || 'Failed to create dish'
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
    // Reload the queue to reflect the change
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

function onProductSelect(idx) {
  const ing = form.value.ingredients[idx]
  if (!ing) return
  // if a product was selected, set the name to that product and prevent duplicates
  if (ing.product_id) {
    const already = form.value.ingredients.find((it, i) => i !== idx && it.product_id && String(it.product_id) === String(ing.product_id))
    if (already) {
      alert('This ingredient is already selected in another row.');
      ing.product_id = ''
      return
    }
    const p = products.value.find(p => String(p.id) === String(ing.product_id))
    if (p) {
      ing.name = p.name
    }
  }
}

const lowStockVisible = ref({})
const lowStockForm = ref({})

function showLowStock(ing) {
  lowStockVisible.value[ing.id] = true
  if (!lowStockForm.value[ing.id]) {
    lowStockForm.value[ing.id] = { unit: ing.unit || 'pcs' }
  }
}

function hideLowStock(ing) {
  lowStockVisible.value[ing.id] = false
}

async function submitLowStock(ing) {
  try {
    const payload = { unit: lowStockForm.value[ing.id].unit }
    const res = await axios.post(`/api/staff/kitchen/ingredients/${ing.id}/low-stock`, payload)
    alert(res.data.message || 'Requested')
    lowStockVisible.value[ing.id] = false
    await loadDishes()
  } catch (e) {
    console.error('Failed low-stock', e)
    alert(e?.response?.data?.message || 'Failed to submit')
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

// Logout state and handlers (consistent with other staff panels)
const isLoggingOut = ref(false)

// profile dropdown state for the top-right header profile
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
  // OwnerPanelLayout listens for this global event to open the info modal
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
.queue-header h3 { margin: 0; }
.sub { margin: 0; color: #6b7280; font-size: 0.9rem; }
.refresh-btn { padding: 0.5rem 0.9rem; border: 1px solid #d1d5db; background: #fff; border-radius: 6px; cursor: pointer; }
.queue-list { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.75rem; }
.queue-item { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; }
.queue-main { display: flex; flex-direction: column; gap: 4px; }
.queue-meta { color: #6b7280; font-size: 0.9rem; }
.queue-actions { display: flex; align-items: center; gap: 0.75rem; }
.muted { color: #6b7280; }
.badge { padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.82rem; text-transform: capitalize; }
.badge--warning { background: #fff7ed; color: #b45309; }
.badge--info { background: #e0f2fe; color: #0369a1; }
.btn-done { padding: 0.4rem 0.8rem; background: #10b981; color: white; border: none; border-radius: 6px; font-size: 0.85rem; cursor: pointer; transition: background 0.2s; }
.btn-done:hover:not(:disabled) { background: #059669; }
.btn-done:disabled { background: #d1d5db; cursor: not-allowed; }

/* Kitchen panel layout and form styles */
.kitchen-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; }
.kitchen-column { background: #ffffff; border: 1px solid #eef2f5; border-radius: 8px; padding: 0.85rem; }
.kitchen-column h3 { margin-top: 0; margin-bottom: 0.5rem; font-size: 1.05rem }
.form-row { display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.75rem }
.form-row label { font-weight: 600; color: #111827 }
.form-row input[type="text"], .form-row select, .form-row input[type="number"] { padding: 0.45rem 0.5rem; border: 1px solid #e6e7eb; border-radius: 6px; width: 100%; box-sizing: border-box }
.form-actions { display: flex; gap: 0.5rem; }
.form-actions button { padding: 0.5rem 0.85rem; border-radius: 6px; border: none; background: #2563eb; color: #fff; cursor: pointer }

.ingredients { display:flex; flex-direction:column; gap:0.5rem }
.ingredient-row { display: grid; grid-template-columns: 1fr 1fr 120px 80px 70px; gap: 0.5rem; align-items:center }
.ingredient-row input, .ingredient-row select { padding: 0.4rem; border-radius:6px; border:1px solid #e6e7eb }
.ingredient-row .small { width:100px }
.ingredient-row button { padding:0.35rem 0.5rem; border-radius:6px; border:1px solid #f3f4f6; background:#fff; cursor:pointer }

.dish-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem }
.dish-list li { padding: 0.6rem; border: 1px solid #eef2f5; border-radius: 8px; background: #fff }
.dish-list strong { display:block; font-size:1rem }
.dish-list small { color: #6b7280 }
.dish-list ul { margin: 0.4rem 0 0 0.6rem; padding-left: 0 }
.low-stock { margin-top: 0.4rem }

/* Dish card styles */
.dish-cards { display:flex; flex-direction:column; gap:0.75rem }
.dish-card { border:1px solid #eef2f5; border-radius:10px; padding:0.65rem; background:#fff }
.dish-card-header { display:flex; align-items:center; gap:0.8rem; margin-bottom:0.5rem }
.dish-name { font-size:1rem; display:block }
.dish-status { color:#6b7280 }
.dish-ingredients { display:flex; flex-direction:column; gap:0.5rem }
.ingredient-card { display:flex; align-items:center; justify-content:space-between; gap:0.5rem; padding:0.5rem; border-radius:8px; background:#fbfdff; border:1px solid #f1f5f9 }
.ingredient-info { max-width:calc(100% - 120px) }
.ingredient-name { font-weight:600 }
.ingredient-per { color:#374151; font-style:italic }
.ingredient-actions { display:flex; align-items:center; gap:0.5rem }
.mark-low-btn { padding:0.35rem 0.5rem; border-radius:6px; border:1px solid #e6e7eb; background:#fff; cursor:pointer }
.low-stock-form { display:flex; gap:0.5rem; align-items:center }

@media (max-width: 900px) {
  .kitchen-grid { grid-template-columns: 1fr; }
  .ingredient-row { grid-template-columns: 1fr 1fr 100px 80px 70px }
}
</style>
