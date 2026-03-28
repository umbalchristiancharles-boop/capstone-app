<template>
  <div class="main-branch-page">
    <section class="panel-layout">
      <aside class="profile-col">
        <div class="profile-card">
          <div class="profile-head">
            <div class="avatar">FN</div>
            <div>
              <div class="label">Main Branch Account</div>
              <h2>{{ profile.full_name || 'Finance Main Branch' }}</h2>
              <p>MANAGER - FINANCE</p>
            </div>
          </div>
          <div class="profile-meta">
            <div><strong>Username:</strong> {{ profile.username || 'finance_main_branch' }}</div>
            <div><strong>Branch:</strong> Main Branch (HQ)</div>
          </div>
          <button class="action-btn" @click="router.push('/manager/finance')">Open Finance Operations</button>
        </div>
      </aside>

      <main class="main-col">
        <header class="panel-header">
          <h1>Main Branch Finance Dashboard</h1>
        </header>

        <section class="overview-grid">
          <article class="overview-card"><span class="k">Revenue</span><strong>{{ currency(metrics.revenue) }}</strong></article>
          <article class="overview-card"><span class="k">Expenses</span><strong>{{ currency(metrics.expenses) }}</strong></article>
          <article class="overview-card"><span class="k">Net Profit</span><strong>{{ currency(metrics.net_profit) }}</strong></article>
          <article class="overview-card"><span class="k">Budget Requests</span><strong>{{ metrics.budget_requests }}</strong></article>
        </section>

        <section class="panel-block">
          <h3>HQ Finance Priorities</h3>
          <ul>
            <li>Keep a healthy revenue-to-expense ratio each week.</li>
            <li>Review and action budget requests without delay.</li>
            <li>Use Finance Operations for approvals and branch budgeting.</li>
          </ul>
        </section>
      </main>

      <aside class="side-col">
        <section class="panel-block">
          <h3>Quick Links</h3>
          <button class="link-btn" @click="router.push('/manager/finance')">Finance Dashboard</button>
          <button class="link-btn" @click="router.push('/super-admin/finance')">Super Admin Finance</button>
          <button class="link-btn" @click="router.push('/manager/procurement')">Procurement View</button>
          <button class="logout-btn" @click.prevent="askLogout">Logout</button>
        </section>
      </aside>
    </section>

    <transition name="fade">
      <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
        <div class="logout-confirm-box">
          <h3>Logout from Main Branch Finance Panel?</h3>
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
const metrics = ref({ revenue: 0, expenses: 0, net_profit: 0, budget_requests: 0 })

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

function currency(v) {
  const n = Number(v || 0)
  return 'PHP ' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function loadProfile() {
  try {
    const res = await axios.get('/api/me', { withCredentials: true })
    if (res.data?.ok) profile.value = res.data.user || {}
  } catch (e) {}
}

async function loadMetrics() {
  try {
    const res = await axios.get('/api/manager/finance/dashboard', { withCredentials: true })
    const d = res.data || {}
    metrics.value = {
      revenue: d.total_revenue ?? d.revenue ?? 0,
      expenses: d.total_expenses ?? d.expenses ?? 0,
      net_profit: d.net_profit ?? d.total_net_profit ?? 0,
      budget_requests: d.pending_budget_requests ?? d.budget_requests ?? 0,
    }
  } catch (e) {}
}

onMounted(async () => {
  await loadProfile()
  await loadMetrics()
})
</script>

<style scoped>
/* Modern, softer UI theme — layout, spacing, typography and controls only */
.main-branch-page {
  min-height: 100vh;
  padding: 28px;
  background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
  font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  color: var(--text-dark);
  font-size: 15px;
}

.panel-layout { display: grid; grid-template-columns: 300px 1fr 260px; gap: 20px; align-items: start; }
.profile-card, .panel-block, .overview-card, .panel-header { background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 4px 14px rgba(16,24,40,0.04); border: 1px solid #eef2f7; }

.profile-head { display: flex; gap: 14px; align-items: center; }
.avatar { width: 56px; height: 56px; border-radius: 50%; background: #111827; color: #fff; display: grid; place-items: center; font-weight: 700; font-size: 18px; }
.label { font-size: 12px; color: #6b7280; }
.profile-meta { margin: 12px 0; display: grid; gap: 6px; font-size: 14px; color: rgba(66,33,11,0.9); }

.action-btn, .link-btn {
  border: 0; border-radius: 10px; background: var(--color-royal-blue); color: #fff; cursor: pointer; box-shadow: 0 8px 24px rgba(224,88,24,0.08);
}
.action-btn:hover, .link-btn:hover { filter: brightness(0.98); }

/* Make primary action full-width in profile card and give consistent spacing */
.profile-card .action-btn {
  display: block;
  width: 100%;
  padding: 10px 14px;
  margin-top: 12px;
}

/* Quick links: stack buttons with consistent gaps and padding */
.side-col .panel-block .link-btn {
  display: block;
  width: 100%;
  text-align: left;
  padding: 8px 12px;
  margin-bottom: 10px;
  background: linear-gradient(180deg, var(--color-royal-blue), #e05818);
  box-shadow: 0 8px 20px rgba(224,88,24,0.08);
}
.side-col .panel-block .link-btn:last-child { margin-bottom: 0; }

.main-col { display: grid; gap: 18px; }
.overview-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.overview-card { display: flex; flex-direction: column; gap: 8px; padding: 16px; }
.overview-card .k { color: rgba(66,33,11,0.9); font-size: 13px; }
.overview-card strong { font-size: 24px; color: var(--text-dark); }

.side-col { display: grid; gap: 14px; align-content: start; }
.panel-block ul { margin: 0; padding-left: 18px; }
.panel-block li { margin: 8px 0; color: rgba(66,33,11,0.9); }
.panel-header h1 { margin: 0 0 6px; font-size: 34px; letter-spacing: -0.5px; color: var(--text-dark); }
.panel-header p { margin: 0; color: rgba(66,33,11,0.9); }

.logout-btn { border: 0; border-radius: 999px; padding: 8px 12px; background: var(--alert); color: #fff; cursor: pointer; margin-top: 8px; box-shadow: 0 6px 18px rgba(239,68,68,0.08); }

.logout-confirm-backdrop {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); display: flex; align-items: center; justify-content: center; z-index: 9999;
}
.logout-confirm-box { width: min(92vw, 420px); background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 12px 40px rgba(16,24,40,0.12); }
.logout-confirm-box h3 { margin: 0 0 8px; font-size: 18px; }
.logout-confirm-box p { margin: 0 0 14px; color: #64748b; }
.logout-actions { display: flex; gap: 10px; justify-content: flex-end; }
.btn-cancel, .btn-confirm { border: 0; border-radius: 999px; padding: 6px 14px; font-size: 0.88rem; cursor: pointer; }
.btn-cancel { background: rgba(16,24,40,0.04); color: var(--text-primary); }
.btn-confirm { background: var(--alert); color: #ffffff; }

.fade-enter-active, .fade-leave-active { transition: opacity .18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 1100px) {
  .panel-layout { grid-template-columns: 1fr; }
  .overview-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

</style>
