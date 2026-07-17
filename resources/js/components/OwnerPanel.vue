<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    panelTitle="Owner Panel"
    panelDescription="Overview and controls for store owners"
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

        <div class="owner-action-grid" aria-label="Owner quick actions">
          <router-link to="/owner/dish-approval" class="owner-action-card owner-action-card--orange">
            <div class="owner-action-card__icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            </div>
            <div class="owner-action-card__body">
              <span class="owner-action-card__label">Dish Approval</span>
              <span class="owner-action-card__text">Review pending dishes before they reach the branch.</span>
            </div>
            <span class="owner-action-card__badge">{{ pendingCounts.kitchen }}</span>
          </router-link>

          <router-link to="/owner/branch-confirmations" class="owner-action-card owner-action-card--emerald">
            <div class="owner-action-card__icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div class="owner-action-card__body">
              <span class="owner-action-card__label">Branch Confirmations</span>
              <span class="owner-action-card__text">Confirm branch-related requests in one place.</span>
            </div>
            <span class="owner-action-card__badge">{{ pendingCounts.branchOwner }}</span>
          </router-link>

          <router-link to="/owner/price-markup-approvals" class="owner-action-card owner-action-card--rose">
            <div class="owner-action-card__icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="owner-action-card__body">
              <span class="owner-action-card__label">Price Markup</span>
              <span class="owner-action-card__text">Check pricing changes before they go live.</span>
            </div>
            <span class="owner-action-card__badge">{{ pendingCounts.priceMarkup }}</span>
          </router-link>
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

:global(.dark-mode) .owner-quicklink-icon--orange  { background: rgba(249, 115, 22, 0.2); color: #fb923c; }
:global(.dark-mode) .owner-quicklink-icon--blue    { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
:global(.dark-mode) .owner-quicklink-icon--emerald { background: rgba(16, 185, 129, 0.15); color: #34d399; }
:global(.dark-mode) .owner-quicklink-icon--rose    { background: rgba(244, 63, 94, 0.15); color: #fb7185; }

:global(.dark-mode) .owner-quicklink-badge {
  background: rgba(239, 68, 68, 0.2);
  color: #fca5a5;
}

:global(.dark-mode) .owner-quicklink-chevron {
  color: #475569;
}
</style>
