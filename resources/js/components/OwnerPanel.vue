<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    panelTitle="Owner Panel"
    panelDescription="Overview and controls for store owners"
    :showHeader="false"
    :ownerTwoColumnLayout="true"
    :enableProfileUpdate="true"
    :canEditProfile="true"
    :canChangePassword="true"
    :showAnnouncements="false"
    :showAttendanceCard="false"
    profileEndpoint="/api/profile"
    updateEndpoint="/api/profile/update"
    avatarEndpoint="/api/profile/avatar"
    @logout="handleLogout"
  >
    <template #main>
      <section class="owner-main-section">

        <!-- ── Welcome Hero Banner ── -->
        <div class="owner-hero-card">
          <!-- Top accent bar -->
          <div class="owner-hero-accent-bar"></div>

          <!-- Decorative background blobs -->
          <div class="owner-hero-blob owner-hero-blob--1"></div>
          <div class="owner-hero-blob owner-hero-blob--2"></div>

          <div class="owner-hero-content">
            <div class="owner-hero-left">
              <span class="owner-hero-eyebrow">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Owner Dashboard
              </span>
              <h2 class="owner-hero-title">
                Welcome back, <span class="owner-hero-name">{{ userProfile.full_name || userProfile.fullName || userProfile.username }}</span>
              </h2>
              <p class="owner-hero-subtitle">Here's a snapshot of your store's pending actions and activity.</p>
            </div>

            <!-- Quick stat pills -->
            <div class="owner-hero-stats">
              <div class="owner-stat-pill owner-stat-pill--orange">
                <div class="owner-stat-pill__icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                </div>
                <div class="owner-stat-pill__body">
                  <span class="owner-stat-pill__value">{{ pendingCounts.kitchen }}</span>
                  <span class="owner-stat-pill__label">Dish Approvals</span>
                </div>
              </div>

              <div class="owner-stat-pill owner-stat-pill--amber">
                <div class="owner-stat-pill__icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                </div>
                <div class="owner-stat-pill__body">
                  <span class="owner-stat-pill__value">{{ pendingCounts.branchOwner }}</span>
                  <span class="owner-stat-pill__label">Branch Confirmations</span>
                </div>
              </div>

              <div class="owner-stat-pill owner-stat-pill--rose">
                <div class="owner-stat-pill__icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div class="owner-stat-pill__body">
                  <span class="owner-stat-pill__value">{{ pendingCounts.priceMarkup }}</span>
                  <span class="owner-stat-pill__label">Price Markups</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </section>

      <!-- Create New Dish Section -->
      <section class="panel-block owner-dish-section">
        <div class="panel-header">
          <h2>Create New Dish</h2>
        </div>
        <div class="panel-body">
          <form @submit.prevent="submitDish" data-no-overlay="1">
            <div class="form-row">
              <label>Dish Name</label>
              <input v-model="dishForm.name" type="text" required placeholder="Enter dish name" />
            </div>

            <div class="form-row">
              <label>Ingredients</label>
              <div class="ingredients">
                <div v-for="(ing, idx) in dishForm.ingredients" :key="idx" class="ingredient-row">
                  <select v-model="ing.product_id" @change="onProductSelect(idx)">
                    <option value="">-- choose from stock (or leave blank to type new) --</option>
                    <option v-for="p in products" :key="p.id" :value="p.id">
                      {{ p.name }} ({{ p.stock }} in stock) <span v-if="!p.is_published">— unpublished</span>
                    </option>
                  </select>

                  <input v-if="!ing.product_id" v-model="ing.name" placeholder="Ingredient name" required />
                  <input v-else v-model="ing.name" placeholder="Ingredient name" readonly />

                  <input v-model="ing.brand" placeholder="Brand (optional)" />

                  <input v-model="ing.per_serving" placeholder="Per serving" class="small" type="number" step="0.0001" />
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
                <button type="button" @click="addIngredient">Add Ingredient</button>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" :disabled="dishSubmitting">
                {{ dishSubmitting ? 'Creating Dish...' : 'Create Dish (All Branches)' }}
              </button>
            </div>
          </form>
          <div v-if="dishMessage" :class="['message', dishMessageType]">{{ dishMessage }}</div>
        </div>
      </section>
    </template>

    <template #profileBottom="{ announcements, loadingAnnouncements, attendanceStatus, scheduledTimeOut, canClockOut, isAttendanceProcessing, attendanceMessage, attendanceMessageType, performClockIn, performClockOut }">
      <div class="owner-profile-bottom">

        <!-- ── Quick Links Card ── -->
        <div class="owner-quicklinks-card">
          <div class="owner-quicklinks-header">
            <div class="owner-quicklinks-header__icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
            </div>
            <div>
              <p class="owner-quicklinks-header__eyebrow">Quick Links</p>
              <h3 class="owner-quicklinks-header__title">Owner Actions</h3>
            </div>
          </div>

          <ul class="owner-quicklinks-list">

            <!-- Dish Approval -->
            <li class="owner-quicklink-item">
              <router-link to="/owner/dish-approval" class="owner-quicklink-row">
                <span class="owner-quicklink-icon owner-quicklink-icon--orange">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                </span>
                <span class="owner-quicklink-label">Dish Approval</span>
                <span class="owner-quicklink-spacer"></span>
                <span v-if="pendingCounts.kitchen > 0" class="owner-quicklink-badge">{{ pendingCounts.kitchen }}</span>
                <svg class="owner-quicklink-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
              </router-link>
            </li>

            <!-- Staff Management -->
            <li class="owner-quicklink-item">
              <router-link to="/owner/staff-management" class="owner-quicklink-row">
                <span class="owner-quicklink-icon owner-quicklink-icon--blue">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <span class="owner-quicklink-label">Staff Management</span>
                <span class="owner-quicklink-spacer"></span>
                <svg class="owner-quicklink-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
              </router-link>
            </li>

            <!-- Branch Confirmations -->
            <li class="owner-quicklink-item">
              <router-link to="/owner/branch-confirmations" class="owner-quicklink-row">
                <span class="owner-quicklink-icon owner-quicklink-icon--emerald">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </span>
                <span class="owner-quicklink-label">Branch Confirmations</span>
                <span class="owner-quicklink-spacer"></span>
                <span v-if="pendingCounts.branchOwner > 0" class="owner-quicklink-badge">{{ pendingCounts.branchOwner }}</span>
                <svg class="owner-quicklink-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
              </router-link>
            </li>

            <!-- Price Markup Approvals -->
            <li class="owner-quicklink-item">
              <router-link to="/owner/price-markup-approvals" class="owner-quicklink-row">
                <span class="owner-quicklink-icon owner-quicklink-icon--rose">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </span>
                <span class="owner-quicklink-label">Price Markup Approvals</span>
                <span class="owner-quicklink-spacer"></span>
                <span v-if="pendingCounts.priceMarkup > 0" class="owner-quicklink-badge">{{ pendingCounts.priceMarkup }}</span>
                <svg class="owner-quicklink-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
              </router-link>
            </li>

          </ul>
        </div>

        <section class="owner-announcements-card" aria-label="Announcements">
          <div class="owner-announcements-card__header">
            <div class="owner-announcements-card__icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
            </div>
            <div>
              <p class="owner-announcements-card__eyebrow">Announcements</p>
              <h3 class="owner-announcements-card__title">Latest Updates</h3>
            </div>
          </div>

          <div class="owner-announcements-card__body">
            <div v-if="loadingAnnouncements" class="owner-announcements-card__state">Loading...</div>
            <div v-else-if="!announcements || announcements.length === 0" class="owner-announcements-card__state">No announcements</div>
            <ul v-else class="owner-announcements-list">
              <li v-for="announcement in announcements" :key="announcement.id" class="owner-announcement-item">
                <div class="owner-announcement-item__title">{{ announcement.title }}</div>
                <div class="owner-announcement-item__meta">{{ new Date(announcement.created_at).toLocaleString() }} • {{ announcement.target }}</div>
                <div class="owner-announcement-item__message">{{ announcement.message }}</div>
              </li>
            </ul>
          </div>
        </section>

        <section class="owner-attendance-card" aria-label="Attendance">
          <div class="owner-attendance-card__header">
            <span class="owner-attendance-card__title">Attendance</span>
            <span :class="['owner-attendance-card__status', attendanceStatus.is_clocked_in ? 'owner-attendance-card__status--on' : 'owner-attendance-card__status--off']">
              {{ attendanceStatus.is_clocked_in ? 'On Duty' : 'Off Duty' }}
            </span>
          </div>

          <div v-if="attendanceStatus.clock_in_time || attendanceStatus.clock_out_time" class="owner-attendance-card__times">
            <div class="owner-attendance-card__time-row"><span>Clock In:</span><strong>{{ attendanceStatus.clock_in_time || '-' }}</strong></div>
            <div class="owner-attendance-card__time-row"><span>Clock Out:</span><strong>{{ attendanceStatus.clock_out_time || '-' }}</strong></div>
            <div v-if="attendanceStatus.hours_worked > 0" class="owner-attendance-card__time-row"><span>Hours:</span><strong>{{ attendanceStatus.hours_worked }} hrs</strong></div>
          </div>

          <div class="owner-attendance-card__actions">
            <button @click="performClockIn" :disabled="attendanceStatus.is_clocked_in || isAttendanceProcessing" class="owner-attendance-card__clock-btn owner-attendance-card__clock-btn--in">
              {{ isAttendanceProcessing ? '...' : 'Clock In' }}
            </button>
            <button @click="performClockOut" :disabled="!attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockOut" class="owner-attendance-card__clock-btn owner-attendance-card__clock-btn--out" :class="{ 'is-disabled': !canClockOut && attendanceStatus.is_clocked_in }">
              {{ isAttendanceProcessing ? '...' : 'Clock Out' }}
            </button>
          </div>

          <div v-if="!canClockOut && attendanceStatus.is_clocked_in" class="owner-attendance-card__restriction">
            <span class="owner-attendance-card__restriction-icon">🔒</span>
            <span>Cannot clock out before {{ scheduledTimeOut }}</span>
          </div>

          <div v-if="attendanceMessage" :class="['owner-attendance-card__message', attendanceMessageType]">
            {{ attendanceMessage }}
          </div>
        </section>

      </div>
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import { showToast } from './toastStore'
import Swal from 'sweetalert2'

const userProfile = ref({})
const pendingCounts = ref({
  kitchen: 0,
  branchOwner: 0,
  priceMarkup: 0,
})
const hasNotified = ref(false)

onMounted(async () => {
  try {
    const local = JSON.parse(localStorage.getItem('user') || 'null')
    if (local) {
      userProfile.value = {
        full_name: local.full_name || local.fullName,
        username: local.username,
        role: local.role,
        department: local.department,
        account_id: local.id,
        avatarUrl: local.avatar_url || null,
      }
    }

    const res = await axios.get('/api/me', { withCredentials: true })
    if (res && res.data && res.data.user) {
      userProfile.value = Object.assign({}, userProfile.value, res.data.user)
    }
  } catch (e) {
    console.warn('OwnerPanel: failed to load profile', e)
  }

  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (res.data && res.data.ok) {
      pendingCounts.value = {
        kitchen: Number(res.data.counts?.kitchen || 0),
        branchOwner: Number(res.data.extras?.branchPendingOwner || 0),
        priceMarkup: Number(res.data.extras?.priceMarkupPending || 0),
      }
      const total = pendingCounts.value.kitchen + pendingCounts.value.branchOwner + pendingCounts.value.priceMarkup
      if (!hasNotified.value && total > 0) {
        showToast('You have pending approvals to review.', 'info')
        hasNotified.value = true
      }
    }
  } catch (e) {
    pendingCounts.value = { kitchen: 0, branchOwner: 0, priceMarkup: 0 }
  }
})

// Dish Creation State
const dishForm = ref({
  name: '',
  ingredients: [{ name: '', brand: '', product_id: '', unit: 'pcs', per_serving: 0 }]
})
const products = ref([])
const dishSubmitting = ref(false)
const dishMessage = ref('')
const dishMessageType = ref('')

// Load products for dish creation form
onMounted(() => {
  loadProducts().catch(() => {})
})

function addIngredient() {
  dishForm.value.ingredients.push({ name: '', brand: '', product_id: '', unit: 'pcs', per_serving: 0 })
}

function removeIngredient(idx) {
  dishForm.value.ingredients.splice(idx, 1)
}

function onProductSelect(idx) {
  const ing = dishForm.value.ingredients[idx]
  if (!ing) return
  if (ing.product_id) {
    const already = dishForm.value.ingredients.find((it, i) => i !== idx && it.product_id && String(it.product_id) === String(ing.product_id))
    if (already) {
      alert('This ingredient is already selected in another row.')
      ing.product_id = ''
      return
    }
    const p = products.value.find(p => String(p.id) === String(ing.product_id))
    if (p) {
      ing.name = p.name
    }
  }
}

async function loadProducts() {
  try {
    const res = await axios.get('/api/staff/inventory/products?include_unpublished=1')
    products.value = res.data || []
  } catch (e) {
    console.error('Failed to load products for dish form', e)
    products.value = []
  }
}

async function submitDish() {
  dishMessage.value = ''
  dishSubmitting.value = true
  try {
    const payload = {
      name: dishForm.value.name,
      ingredients: dishForm.value.ingredients.map(i => ({
        name: i.name,
        brand: i.brand || null,
        unit: i.unit,
        per_serving: i.per_serving,
        product_id: i.product_id || null
      }))
    }
    const res = await axios.post('/api/owner/dishes', payload)
    dishMessage.value = 'Dish created successfully and applied to all branches!'
    dishMessageType.value = 'success'
    dishForm.value.name = ''
    dishForm.value.ingredients = [{ name: '', brand: '', product_id: '', unit: 'pcs', per_serving: 0 }]
    // Refresh products in case new placeholder products were created
    await loadProducts()
  } catch (e) {
    console.error('Failed to create dish', e)
    dishMessage.value = e?.response?.data?.message || e?.response?.data?.error || 'Failed to create dish'
    dishMessageType.value = 'error'
  } finally {
    dishSubmitting.value = false
  }
}

const handleLogout = async () => {
  const result = await Swal.fire({
    title: 'Confirm logout',
    text: 'This will end your current session for Chikin Tayo.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#FF6A3D',
    cancelButtonColor: '#636B7B',
  })

  if (result.isConfirmed) {
    try {
      await axios.post('/logout', {}, { withCredentials: true })
    } catch (e) {
      console.warn('Logout request failed:', e)
    }
    // Clear local storage
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    // Redirect to login
    window.location.href = '/login'
  }
}
</script>

<style scoped>
/* ═══════════════════════════════════════════════
   OWNER PANEL — MAIN SECTION
   ═══════════════════════════════════════════════ */
.owner-main-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* ── Hero Card ── */
.owner-hero-card {
  position: relative;
  overflow: hidden;
  border-radius: 1.375rem;
  background: linear-gradient(135deg, #ffffff 0%, #fff8f3 60%, #fff1e6 100%);
  border: 1px solid #ffe4cc;
  box-shadow:
    0 4px 6px -1px rgba(249, 115, 22, 0.05),
    0 24px 60px -18px rgba(15, 23, 42, 0.18);
  padding: 1.875rem 1.875rem 1.625rem;
}

/* Top accent bar */
.owner-hero-accent-bar {
  position: absolute;
  left: 0;
  right: 0;
  top: 0;
  height: 3px;
  background: linear-gradient(90deg, #f97316, #fb923c, #fbbf24);
  border-radius: 1.25rem 1.25rem 0 0;
}

/* Decorative blobs */
.owner-hero-blob {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
}
.owner-hero-blob--1 {
  width: 220px;
  height: 220px;
  background: radial-gradient(circle, rgba(251, 146, 60, 0.12) 0%, transparent 70%);
  top: -60px;
  right: -40px;
}
.owner-hero-blob--2 {
  width: 160px;
  height: 160px;
  background: radial-gradient(circle, rgba(251, 191, 36, 0.10) 0%, transparent 70%);
  bottom: -50px;
  left: 20px;
}

.owner-hero-content {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .owner-hero-content {
    flex-direction: row;
    align-items: flex-start;
    justify-content: space-between;
    gap: 2rem;
  }
}

/* Left copy */
.owner-hero-left {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.owner-hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  width: fit-content;
  background: linear-gradient(135deg, #fff7ed, #ffedd5);
  border: 1px solid #fed7aa;
  color: #c2410c;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  padding: 0.3rem 0.75rem;
  border-radius: 999px;
}

.owner-hero-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
  line-height: 1.3;
}

@media (min-width: 640px) {
  .owner-hero-title {
    font-size: 1.75rem;
  }
}

.owner-hero-name {
  color: #ea580c;
}

.owner-hero-subtitle {
  margin: 0;
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.6;
  max-width: 38ch;
}

/* Stat pills */
.owner-hero-stats {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
  flex-shrink: 0;
}

@media (min-width: 480px) {
  .owner-hero-stats {
    flex-direction: row;
    flex-wrap: wrap;
  }
}

@media (min-width: 768px) {
  .owner-hero-stats {
    flex-direction: column;
    min-width: 180px;
  }
}

.owner-stat-pill {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 1rem;
  border-radius: 0.875rem;
  border: 1px solid transparent;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.owner-stat-pill:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.owner-stat-pill--orange {
  background: linear-gradient(135deg, #fff7ed, #ffedd5);
  border-color: #fed7aa;
}
.owner-stat-pill--amber {
  background: linear-gradient(135deg, #fffbeb, #fef3c7);
  border-color: #fde68a;
}
.owner-stat-pill--rose {
  background: linear-gradient(135deg, #fff1f2, #ffe4e6);
  border-color: #fecdd3;
}

.owner-stat-pill__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: 0.5rem;
  flex-shrink: 0;
}

.owner-stat-pill--orange .owner-stat-pill__icon { background: #fed7aa; color: #c2410c; }
.owner-stat-pill--amber  .owner-stat-pill__icon { background: #fde68a; color: #92400e; }
.owner-stat-pill--rose   .owner-stat-pill__icon { background: #fecdd3; color: #be123c; }

.owner-stat-pill__body {
  display: flex;
  flex-direction: column;
  gap: 0.05rem;
}

.owner-stat-pill__value {
  font-size: 1.25rem;
  font-weight: 800;
  line-height: 1;
  color: #1e293b;
}

.owner-stat-pill__label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.owner-action-grid {
  display: grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .owner-action-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

.owner-action-card {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 1rem 1.05rem;
  border-radius: 1rem;
  border: 1px solid #f1f5f9;
  background: #ffffff;
  text-decoration: none;
  color: #1f2937;
  box-shadow: 0 14px 35px rgba(15, 23, 42, 0.06);
  transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}

.owner-action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.1);
}

.owner-action-card--orange:hover {
  border-color: #fdba74;
}

.owner-action-card--emerald:hover {
  border-color: #86efac;
}

.owner-action-card--rose:hover {
  border-color: #fda4af;
}

.owner-action-card__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.9rem;
  flex-shrink: 0;
}

.owner-action-card--orange .owner-action-card__icon {
  color: #c2410c;
  background: linear-gradient(135deg, #ffedd5, #fed7aa);
}

.owner-action-card--emerald .owner-action-card__icon {
  color: #047857;
  background: linear-gradient(135deg, #d1fae5, #a7f3d0);
}

.owner-action-card--rose .owner-action-card__icon {
  color: #be123c;
  background: linear-gradient(135deg, #ffe4e6, #fecdd3);
}

.owner-action-card__body {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.owner-action-card__label {
  font-size: 0.925rem;
  font-weight: 700;
  color: #111827;
}

.owner-action-card__text {
  font-size: 0.78rem;
  line-height: 1.5;
  color: #64748b;
}

.owner-action-card__badge {
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 2rem;
  height: 2rem;
  padding: 0 0.6rem;
  border-radius: 999px;
  background: #fff7ed;
  color: #c2410c;
  font-size: 0.85rem;
  font-weight: 800;
  flex-shrink: 0;
}

.owner-action-card--emerald .owner-action-card__badge {
  background: #ecfdf5;
  color: #047857;
}

.owner-action-card--rose .owner-action-card__badge {
  background: #fff1f2;
  color: #be123c;
}

/* ═══════════════════════════════════════════════
   OWNER PANEL — SIDE SECTION
   ═══════════════════════════════════════════════ */
.owner-profile-bottom {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

/* ── Quick Links Card ── */
.owner-quicklinks-card {
  background: #ffffff;
  border-radius: 1.125rem;
  border: 1px solid #f1f5f9;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.07);
  overflow: hidden;
}

.owner-announcements-card {
  background: #ffffff;
  border-radius: 1.125rem;
  border: 1px solid #f1f5f9;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.07);
  overflow: hidden;
}

.owner-announcements-card__header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.125rem;
  background: linear-gradient(135deg, #fff7ed 0%, #fff4e8 100%);
  border-bottom: 1px solid #fed7aa;
}

.owner-announcements-card__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: 0.5rem;
  background: linear-gradient(135deg, #fb923c, #ea580c);
  color: #ffffff;
  flex-shrink: 0;
}

.owner-announcements-card__eyebrow {
  margin: 0;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #ea580c;
}

.owner-announcements-card__title {
  margin: 0.1rem 0 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
}

.owner-announcements-card__body {
  padding: 0.65rem;
}

.owner-announcements-card__state {
  padding: 0.65rem 0.55rem;
  font-size: 0.875rem;
  color: #64748b;
}

.owner-announcements-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.owner-announcement-item {
  padding: 0.85rem 0.9rem;
  border-radius: 0.9rem;
  border: 1px solid #f1f5f9;
  background: linear-gradient(180deg, #ffffff 0%, #fffaf5 100%);
}

.owner-announcement-item__title {
  font-size: 0.92rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.2rem;
}

.owner-announcement-item__meta {
  font-size: 0.72rem;
  color: #94a3b8;
  margin-bottom: 0.35rem;
}

.owner-announcement-item__message {
  font-size: 0.82rem;
  line-height: 1.5;
  color: #475569;
  word-break: break-word;
}

.owner-attendance-card {
  background: #ffffff;
  border-radius: 1.125rem;
  border: 1px solid #f1f5f9;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.07);
  overflow: hidden;
  padding: 0.9rem;
}

.owner-attendance-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.85rem;
}

.owner-attendance-card__title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
}

.owner-attendance-card__status {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.35rem 0.65rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 800;
}

.owner-attendance-card__status--off {
  background: #ffe4e6;
  color: #9f1239;
}

.owner-attendance-card__status--on {
  background: #dcfce7;
  color: #166534;
}

.owner-attendance-card__times {
  display: grid;
  gap: 0.45rem;
  margin-bottom: 0.85rem;
}

.owner-attendance-card__time-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  font-size: 0.8rem;
  color: #475569;
}

.owner-attendance-card__time-row strong {
  color: #1e293b;
  font-weight: 700;
}

.owner-attendance-card__actions {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.6rem;
}

.owner-attendance-card__clock-btn {
  border: none;
  border-radius: 0.8rem;
  padding: 0.8rem 0.75rem;
  font-size: 0.88rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
}

.owner-attendance-card__clock-btn:hover:not(:disabled) {
  transform: translateY(-1px);
}

.owner-attendance-card__clock-btn:disabled,
.owner-attendance-card__clock-btn.is-disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.owner-attendance-card__clock-btn--in {
  background: linear-gradient(135deg, #16a34a, #22c55e);
  color: #ffffff;
  box-shadow: 0 10px 20px rgba(34, 197, 94, 0.18);
}

.owner-attendance-card__clock-btn--out {
  background: linear-gradient(135deg, #e5e7eb, #d1d5db);
  color: #6b7280;
}

.owner-attendance-card__restriction {
  margin-top: 0.75rem;
  padding: 0.7rem 0.8rem;
  border-radius: 0.8rem;
  background: #fff7ed;
  color: #9a3412;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.45rem;
}

.owner-attendance-card__message {
  margin-top: 0.75rem;
  padding: 0.7rem 0.8rem;
  border-radius: 0.8rem;
  font-size: 0.8rem;
}

.owner-attendance-card__message.success {
  background: #dcfce7;
  color: #166534;
}

.owner-attendance-card__message.error {
  background: #fee2e2;
  color: #b91c1c;
}

.owner-quicklinks-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1.1rem 1.125rem;
  background: linear-gradient(135deg, #fff7ed 0%, #fffbeb 100%);
  border-bottom: 1px solid #fed7aa;
}

.owner-quicklinks-header__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  background: linear-gradient(135deg, #f97316, #ea580c);
  border-radius: 0.5rem;
  color: #ffffff;
  flex-shrink: 0;
}

.owner-quicklinks-header__eyebrow {
  margin: 0;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #ea580c;
}

.owner-quicklinks-header__title {
  margin: 0.1rem 0 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
}

/* List */
.owner-quicklinks-list {
  list-style: none;
  margin: 0;
  padding: 0.65rem;
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.owner-quicklink-item {
  border-radius: 0.625rem;
  overflow: hidden;
}

.owner-quicklink-row {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.8rem 0.85rem;
  border-radius: 0.85rem;
  text-decoration: none;
  color: #374151;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background 0.15s ease, color 0.15s ease, transform 0.15s ease;
  border: 1px solid transparent;
}

.owner-quicklink-row:hover {
  background: linear-gradient(135deg, #fff7ed, #ffedd5);
  border-color: #fed7aa;
  color: #c2410c;
  transform: translateX(3px);
}

.owner-quicklink-row:hover .owner-quicklink-chevron {
  color: #ea580c;
  transform: translateX(2px);
}

/* Icons */
.owner-quicklink-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.875rem;
  height: 1.875rem;
  border-radius: 0.5rem;
  flex-shrink: 0;
}

.owner-quicklink-icon--orange  { background: #ffedd5; color: #c2410c; }
.owner-quicklink-icon--blue    { background: #dbeafe; color: #1d4ed8; }
.owner-quicklink-icon--emerald { background: #d1fae5; color: #065f46; }
.owner-quicklink-icon--rose    { background: #ffe4e6; color: #be123c; }

.owner-quicklink-label {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.owner-quicklink-spacer {
  flex: 1;
}

/* Badge */
.owner-quicklink-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.375rem;
  height: 1.375rem;
  padding: 0 0.375rem;
  border-radius: 999px;
  background: #fee2e2;
  color: #991b1b;
  font-size: 0.7rem;
  font-weight: 800;
  line-height: 1;
  box-shadow: 0 2px 6px rgba(239, 68, 68, 0.2);
  flex-shrink: 0;
}

/* Chevron */
.owner-quicklink-chevron {
  color: #cbd5e1;
  flex-shrink: 0;
  transition: color 0.15s ease, transform 0.15s ease;
}

@media (max-width: 767px) {
  .owner-action-card {
    align-items: flex-start;
  }

  .owner-action-card__badge {
    margin-top: 0.1rem;
  }
}

/* ── Dish Creation Form ── */
.owner-dish-section {
  background: #ffffff;
  border-radius: 1.125rem;
  border: 1px solid #f1f5f9;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.07);
  overflow: hidden;
}

.owner-dish-section .panel-header {
  padding: 1.1rem 1.125rem;
  background: linear-gradient(135deg, #fff7ed 0%, #fffbeb 100%);
  border-bottom: 1px solid #fed7aa;
}

.owner-dish-section .panel-header h2 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
}

.owner-dish-section .panel-body {
  padding: 1.25rem;
}

.owner-dish-section .form-row {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  margin-bottom: 1rem;
}

.owner-dish-section .form-row label {
  font-weight: 600;
  color: #111827;
  font-size: 0.9rem;
}

.owner-dish-section .form-row input[type="text"],
.owner-dish-section .form-row input[type="number"],
.owner-dish-section .form-row select {
  padding: 0.5rem 0.65rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  width: 100%;
  box-sizing: border-box;
  font-size: 0.9rem;
}

.owner-dish-section .form-row input:focus,
.owner-dish-section .form-row select:focus {
  outline: none;
  border-color: #ff6a3d;
  box-shadow: 0 0 0 3px rgba(255, 106, 61, 0.1);
}

.owner-dish-section .ingredients {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.owner-dish-section .ingredient-row {
  display: grid;
  grid-template-columns: 2fr 1.5fr 120px 100px 80px;
  gap: 0.5rem;
  align-items: center;
  padding: 0.6rem;
  background: #f9fafb;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.owner-dish-section .ingredient-row input,
.owner-dish-section .ingredient-row select {
  padding: 0.4rem;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  font-size: 0.85rem;
}

.owner-dish-section .ingredient-row .small {
  width: 100%;
}

.owner-dish-section .ingredient-row button {
  padding: 0.4rem 0.6rem;
  border-radius: 6px;
  border: 1px solid #f3f4f6;
  background: #fff;
  cursor: pointer;
  font-size: 0.85rem;
  transition: all 0.2s;
}

.owner-dish-section .ingredient-row button:hover {
  background: #fee2e2;
  border-color: #ef4444;
  color: #dc2626;
}

.owner-dish-section .form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.25rem;
}

.owner-dish-section .form-actions button[type="submit"] {
  padding: 0.65rem 1.25rem;
  border-radius: 8px;
  border: none;
  background: linear-gradient(135deg, #ff6a3d, #ff8c42);
  color: #fff;
  font-weight: 700;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(255, 106, 61, 0.25);
}

.owner-dish-section .form-actions button[type="submit"]:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(255, 106, 61, 0.35);
}

.owner-dish-section .form-actions button[type="submit"]:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.owner-dish-section .message {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
}

.owner-dish-section .message.success {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #86efac;
}

.owner-dish-section .message.error {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}

@media (max-width: 900px) {
  .owner-dish-section .ingredient-row {
    grid-template-columns: 1fr 1fr;
    gap: 0.4rem;
  }

  .owner-dish-section .ingredient-row button {
    grid-column: span 2;
  }
}

/* ── Dark mode ── */
:global(.dark-mode) .owner-hero-card {
  background: linear-gradient(135deg, #1e293b 0%, #1a2332 60%, #1e1a2e 100%);
  border-color: rgba(249, 115, 22, 0.2);
}

:global(.dark-mode) .owner-hero-eyebrow {
  background: rgba(249, 115, 22, 0.15);
  border-color: rgba(249, 115, 22, 0.3);
  color: #fb923c;
}

:global(.dark-mode) .owner-hero-title {
  color: #f1f5f9;
}

:global(.dark-mode) .owner-hero-name {
  color: #fb923c;
}

:global(.dark-mode) .owner-hero-subtitle {
  color: #94a3b8;
}

:global(.dark-mode) .owner-stat-pill--orange {
  background: rgba(249, 115, 22, 0.1);
  border-color: rgba(249, 115, 22, 0.25);
}
:global(.dark-mode) .owner-stat-pill--amber {
  background: rgba(251, 191, 36, 0.08);
  border-color: rgba(251, 191, 36, 0.2);
}
:global(.dark-mode) .owner-stat-pill--rose {
  background: rgba(244, 63, 94, 0.08);
  border-color: rgba(244, 63, 94, 0.2);
}

:global(.dark-mode) .owner-stat-pill__value {
  color: #f1f5f9;
}
:global(.dark-mode) .owner-stat-pill__label {
  color: #94a3b8;
}

:global(.dark-mode) .owner-quicklinks-card {
  background: #1e293b;
  border-color: rgba(255, 255, 255, 0.06);
}

:global(.dark-mode) .owner-quicklinks-header {
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.12), rgba(251, 191, 36, 0.06));
  border-bottom-color: rgba(249, 115, 22, 0.2);
}

:global(.dark-mode) .owner-quicklinks-header__title {
  color: #f1f5f9;
}

:global(.dark-mode) .owner-quicklink-row {
  color: #cbd5e1;
}

:global(.dark-mode) .owner-quicklink-row:hover {
  background: rgba(249, 115, 22, 0.1);
  border-color: rgba(249, 115, 22, 0.25);
  color: #fb923c;
}

:global(.dark-mode) .owner-hero-card,
:global(.dark-mode) .owner-quicklinks-card,
:global(.dark-mode) .owner-announcements-card,
:global(.dark-mode) .owner-attendance-card,
:global(.dark-mode) .owner-dish-section {
  border-color: rgba(255,255,255,0.06) !important;
}

:global(.dark-mode) .owner-dish-section .panel-header {
  border-bottom-color: rgba(255,255,255,0.05) !important;
}

:global(.dark-mode) .owner-announcements-card__header {
  border-bottom-color: rgba(255,255,255,0.05) !important;
}

:global(.dark-mode) .owner-quicklink-row {
  border: 1px solid transparent !important;
}

:global(.dark-mode) .owner-quicklink-icon--orange  { background: rgba(249, 115, 22, 0.2); color: #fb923c; }
:global(.dark-mode) .owner-quicklink-icon--blue    { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
:global(.dark-mode) .owner-quicklink-icon--emerald { background: rgba(16, 185, 129, 0.15); color: #34d399; }
:global(.dark-mode) .owner-quicklink-icon--rose    { background: rgba(244, 63, 94, 0.15); color: #fb7185; }

:global(.dark-mode) .owner-quicklink-badge {
  background: rgba(239, 68, 68, 0.2);
  color: #fca5a5;
}

:global(.dark-mode) .owner-dish-section {
  background: #181a20;
  border-color: #2d3342;
}

:global(.dark-mode) .owner-dish-section .panel-header {
  background: #16181f;
  border-bottom-color: rgba(255,255,255,0.08);
}

:global(.dark-mode) .owner-dish-section .panel-body {
  background: #1f2028;
}

:global(.dark-mode) .owner-dish-section .form-row {
  background: transparent;
}

:global(.dark-mode) .owner-dish-section .form-row input,
:global(.dark-mode) .owner-dish-section .form-row select,
:global(.dark-mode) .owner-dish-section .ingredient-row input,
:global(.dark-mode) .owner-dish-section .ingredient-row select {
  background: #1f2028;
  color: #f8fafc;
  border-color: rgba(255,255,255,0.12);
}

:global(.dark-mode) .owner-dish-section .ingredient-row {
  background: #1f2028;
  border-color: rgba(255,255,255,0.10);
}

:global(.dark-mode) .owner-dish-section .ingredient-row button {
  background: #22252f;
  color: #f8fafc;
  border-color: rgba(255,255,255,0.12);
}

:global(.dark-mode) .owner-attendance-card {
  background: #181a20;
  border-color: #2d3342;
}

:global(.dark-mode) .owner-attendance-card__header,
:global(.dark-mode) .owner-attendance-card__times,
:global(.dark-mode) .owner-attendance-card__actions {
  background: transparent;
}

:global(.dark-mode) .owner-attendance-card__title,
:global(.dark-mode) .owner-attendance-card__status,
:global(.dark-mode) .owner-attendance-card__time-row strong {
  color: #f8fafc;
}

:global(.dark-mode) .owner-attendance-card__status--off {
  background: rgba(248, 113, 113, 0.18);
  color: #fecaca;
}

:global(.dark-mode) .owner-attendance-card__status--on {
  background: rgba(34, 197, 94, 0.18);
  color: #bbf7d0;
}

:global(.dark-mode) .owner-attendance-card__clock-btn--out {
  background: #272b37;
  color: #e2e8f0;
}

:global(.dark-mode) .owner-attendance-card__clock-btn--in {
  background: #16a34a;
  color: #ffffff;
}

:global(.dark-mode) .owner-attendance-card__restriction {
  background: rgba(248, 113, 113, 0.12);
  color: #f8fafc;
  border: 1px solid rgba(248, 113, 113, 0.28);
}

:global(.dark-mode) .owner-attendance-card__message.success {
  background: rgba(16, 185, 129, 0.18);
  color: #a7f3d0;
  border: 1px solid rgba(16, 185, 129, 0.28);
}

:global(.dark-mode) .owner-attendance-card__message.error {
  background: rgba(248, 113, 113, 0.16);
  color: #fecaca;
  border: 1px solid rgba(248, 113, 113, 0.28);
}

:global(.dark-mode) .owner-announcement-item__meta {
  color: rgba(248, 250, 252, 0.72);
}

:global(.dark-mode) .owner-announcement-item__message {
  color: rgba(248, 250, 252, 0.84);
}

:global(.dark-mode) .owner-quicklink-row:hover {
  background: rgba(255, 255, 255, 0.08);
}

:global(.dark-mode) .owner-quicklink-chevron {
  color: #475569;
}
</style>
