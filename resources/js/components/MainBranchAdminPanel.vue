<template>
  <div class="main-branch-page">
    <section class="panel-layout">
      <aside class="profile-col">
        <div class="profile-card">
          <div class="profile-head">
            <div class="avatar">A</div>
            <div>
              <div class="label">Main Branch Account</div>
              <h2>{{ profile.full_name || 'Admin Main Branch' }}</h2>
              <p>{{ (profile.role || 'ADMIN').toUpperCase() }}</p>
            </div>
          </div>
          <div class="profile-meta">
            <div><strong>Username:</strong> {{ profile.username || 'admin_main_branch' }}</div>
            <div><strong>Branch:</strong> Main Branch (HQ)</div>
          </div>
          <button class="action-btn" @click="router.push('/admin-panel')">Open Admin Operations</button>
        </div>
      </aside>

      <main class="main-col">
        <header class="panel-header">
          <h1>Main Branch Admin Dashboard</h1>
          <p>Super-admin inspired layout for HQ administration.</p>
        </header>

        <section class="overview-grid">
          <article class="overview-card">
            <span class="k">Total Staff</span>
            <strong>{{ metrics.staff }}</strong>
          </article>
          <article class="overview-card">
            <span class="k">Active Staff</span>
            <strong>{{ metrics.active_staff }}</strong>
          </article>
          <article class="overview-card">
            <span class="k">Today Attendance</span>
            <strong>{{ metrics.attendance_today }}</strong>
          </article>
          <article class="overview-card">
            <span class="k">Open Tasks</span>
            <strong>{{ metrics.open_tasks }}</strong>
          </article>
        </section>

        <section class="panel-block">
          <h3>HQ Notes</h3>
          <ul>
            <li>Main Branch is marked as protected (cannot be deleted).</li>
            <li>Use this panel as the command center for admin operations.</li>
            <li>Go to Admin Operations for full staff and branch controls.</li>
          </ul>
        </section>
      </main>

      <aside class="side-col">
        <section class="panel-block">
          <h3>Quick Links</h3>
          <button class="link-btn" @click="router.push('/staff-management')">Staff Management</button>
          <button class="link-btn" @click="router.push('/admin/deleted-staff')">Deleted Staff</button>
          <button class="link-btn" @click="router.push('/main-branch/branches')">Branch Management</button>
          <button class="logout-btn" @click.prevent="askLogout">Logout</button>
        </section>
      </aside>
    </section>

    <transition name="fade">
      <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
        <div class="logout-confirm-box">
          <h3>Logout from Main Branch Admin Panel?</h3>
          <p>This will end your current session for Chikin Tayo.</p>
          <div class="logout-actions">
            <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
            <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const profile = ref({})
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const metrics = ref({
  staff: 0,
  active_staff: 0,
  attendance_today: 0,
  open_tasks: 0,
})

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear() } catch (e) {}
  setTimeout(() => {
    window.location.replace('/staff-landing')
  }, 350)
}

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

async function loadProfile() {
  try {
    const res = await axios.get('/api/me', { withCredentials: true })
    if (res.data?.ok) {
      profile.value = res.data.user || {}
    }
  } catch (e) {}
}

async function loadMetrics() {
  try {
    const res = await axios.get('/api/admin/dashboard', { withCredentials: true })
    const d = res.data || {}
    metrics.value = {
      staff: d.total_staff ?? d.totalStaff ?? 0,
      active_staff: d.active_staff ?? d.activeStaff ?? 0,
      attendance_today: d.attendance_today ?? d.today_attendance ?? 0,
      open_tasks: d.open_tasks ?? 0,
    }
  } catch (e) {}
}

onMounted(async () => {
  await loadProfile()
  await loadMetrics()
})
</script>

<style scoped>
.main-branch-page { min-height: 100vh; padding: 24px; background: linear-gradient(180deg, #ff9a4a 0%, #ff6a3d 100%); }
.panel-layout { display: grid; grid-template-columns: 320px 1fr 300px; gap: 16px; }
.profile-card, .panel-block, .overview-card, .panel-header { background: #fff; border-radius: 16px; padding: 16px; }
.profile-head { display: flex; gap: 12px; align-items: center; }
.avatar { width: 56px; height: 56px; border-radius: 50%; background: #1f2d3d; color: #fff; display: grid; place-items: center; font-weight: 700; }
.label { font-size: 12px; color: #7a7a7a; }
.profile-meta { margin: 12px 0; display: grid; gap: 6px; font-size: 14px; }
.action-btn, .link-btn { border: 0; border-radius: 10px; padding: 10px 12px; background: #1f2d3d; color: #fff; cursor: pointer; }
.main-col { display: grid; gap: 16px; }
.overview-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.overview-card .k { color: #6b7280; font-size: 12px; display: block; }
.overview-card strong { font-size: 22px; color: #1f2d3d; }
.side-col { display: grid; gap: 16px; align-content: start; }
.panel-block ul { margin: 0; padding-left: 18px; }
.panel-block li { margin: 8px 0; }
.panel-header h1 { margin: 0 0 6px; }
.panel-header p { margin: 0; color: #6b7280; }
.logout-btn { border: 0; border-radius: 10px; padding: 10px 12px; background: #b91c1c; color: #fff; cursor: pointer; margin-top: 8px; }

.logout-confirm-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.logout-confirm-box {
  width: min(92vw, 420px);
  background: #fff;
  border-radius: 14px;
  padding: 18px;
}

.logout-confirm-box h3 { margin: 0 0 8px; }
.logout-confirm-box p { margin: 0 0 14px; color: #64748b; }
.logout-actions { display: flex; gap: 10px; justify-content: flex-end; }
.btn-cancel, .btn-confirm { border: 0; border-radius: 8px; padding: 8px 12px; cursor: pointer; }
.btn-cancel { background: #e2e8f0; color: #0f172a; }
.btn-confirm { background: #b91c1c; color: #fff; }

.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
@media (max-width: 1100px) {
  .panel-layout { grid-template-columns: 1fr; }
  .overview-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
</style>
