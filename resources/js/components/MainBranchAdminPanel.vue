<template>
  <div class="main-branch-admin-panel">
    <OwnerPanelLayout
      :userProfile="userProfile"
      :panelTitle="'Main Branch Administration'"
      :panelDescription="'Main Branch management and configuration'"
      :fullWidth="true"
      :enableProfileUpdate="true"
      :canEditProfile="false"
      :canChangePassword="true"
      :showProfileColumn="false"
      :showAnnouncements="false"
      @logout="askLogout"
      @profile-updated="onProfileUpdated"
    >
      <template #main>
        <div class="crm-button-row">
          <button class="crm-btn" @click.stop="goToCRM">Open CRM Panel</button>
        </div>

        <section class="kpi-row">
          <div class="kpi-card">
            <div class="kpi-title">Total Comments</div>
            <div class="kpi-value">{{ totalComments }}</div>
          </div>
          <div class="kpi-card">
            <div class="kpi-title">Avg Rating</div>
            <div class="kpi-value">{{ avgRating }}</div>
          </div>
          <div class="kpi-card">
            <div class="kpi-title">Recent Comments</div>
            <div class="kpi-value">{{ (comments && comments.length) || 0 }}</div>
          </div>
          <div class="kpi-card">
            <div class="kpi-title">Unique Customers</div>
            <div class="kpi-value">{{ uniqueCustomers }}</div>
          </div>
        </section>

        <section class="overview-box">
          <h3>Customer Feedback Overview</h3>
          <p>Monitor and respond to all customer comments about your products. High engagement with comments helps improve customer satisfaction and product quality.</p>
        </section>

        <section class="comments-panel">
          <div class="comments-header">
            <h4>All Customer Comments</h4>
            <div class="comments-controls">
              <input class="search-input" placeholder="Search comments..." />
              <select class="rating-select">
                <option>All Ratings</option>
              </select>
              <button class="refresh-comments" @click.prevent="loadComments(1000)">Refresh Comments</button>
            </div>
          </div>

          <div class="branch-reports">
            <div class="branch-title">Branch Financial Reports
              <div class="branch-sub">View branch KPIs and recent transactions</div>
            </div>
            <div class="branch-actions">
              <select class="branch-select"><option>All Branches</option></select>
              <button class="refresh-finance">Refresh Finance</button>
            </div>
          </div>

          <div class="financial-overview">
            <div class="financial-header">Financial Overview</div>
            <div class="financial-chart">[Revenue vs Expenses chart placeholder]</div>
          </div>

          <div class="comments-list-section">
            <div v-if="commentsLoading">Loading comments...</div>
            <div v-else-if="commentsError">Failed to load comments.</div>
            <div v-else>
              <div v-if="comments && comments.length">
                <div class="comment-item" v-for="c in comments.slice(0, 50)" :key="c.id">
                  <div class="comment-meta">
                    <strong>{{ c.user?.full_name || c.author || 'Customer' }}</strong>
                    <span class="comment-product"> — {{ c.product?.name || 'Product' }}</span>
                    <span class="comment-date">{{ new Date(c.created_at).toLocaleString() }}</span>
                  </div>
                  <div class="comment-body">{{ c.text }}</div>
                  <div class="comment-rating">Rating: {{ c.rating || '—' }}</div>
                </div>
              </div>
              <div v-else class="empty-text">No comments found.</div>
            </div>

            <!-- debug block removed -->
          </div>
        </section>
      </template>

      <template #headerActions>
        <div class="header-profile-wrapper" @click.stop>
          <button class="header-profile-btn" @click="toggleProfileDropdown">
            <div class="header-avatar">
              <div v-if="userProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url('+userProfile.avatarUrl+')' }"></div>
              <div v-else class="header-avatar-initials">{{ (userProfile.fullName || 'A').charAt(0) }}</div>
            </div>
            <div class="header-name">{{ ((userProfile.fullName || userProfile.full_name) || 'ADMIN').toUpperCase() }}</div>
          </button>
          <div v-if="profileDropdownVisible" class="header-profile-dropdown" @click.stop>
            <button class="dropdown-item" @click="openInfoFromHeader">Info</button>
            <button class="dropdown-item" @click="triggerLogoutFromHeader">Logout</button>
          </div>
        </div>
      </template>
    </OwnerPanelLayout>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'

const userProfile = ref({})
const profileDropdownVisible = ref(false)
const router = useRouter()

// Comments + KPI state
const comments = ref([])
const commentsMeta = ref({})
const commentsLoading = ref(false)
const commentsError = ref(null)

const totalComments = computed(() => {
  return commentsMeta.value.total ?? (Array.isArray(comments.value) ? comments.value.length : 0)
})

const avgRating = computed(() => {
  const list = comments.value || []
  if (!list.length) return '0/5'
  const sum = list.reduce((s, c) => s + (c.rating || 0), 0)
  const avg = (sum / list.length) || 0
  return `${avg.toFixed(1)}/5`
})

const uniqueCustomers = computed(() => {
  const set = new Set((comments.value || []).map(c => (c.user && c.user.id) || c.author || null).filter(Boolean))
  return set.size
})

function toggleProfileDropdown() {
  profileDropdownVisible.value = !profileDropdownVisible.value
}

function closeProfileDropdown() {
  profileDropdownVisible.value = false
}

function openInfoFromHeader() {
  closeProfileDropdown()
  try {
    window.dispatchEvent(new Event('open-owner-info'))
  } catch (e) {}
}

async function triggerLogoutFromHeader() {
  closeProfileDropdown()
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('Logout from Main Branch Admin Panel?', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) {}
}

async function confirmLogout() {
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear() } catch (e) {}
  setTimeout(() => {
    try { window.location.replace('/') } catch (e) {}
  }, 600)
}

function askLogout() {
  try {
    window.swalConfirm('Logout from Main Branch Admin Panel?', 'Confirm logout').then(ok => {
      if (ok) confirmLogout()
    })
  } catch (e) {}
}

function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

async function loadComments(perPage = 200) {
  commentsLoading.value = true
  commentsError.value = null
  try {
    const res = await axios.get('/api/product-comments/all', { params: { per_page: perPage }, withCredentials: true })
    console.debug('[MainBranchAdminPanel] /api/product-comments/all response:', res && res.data)
    // response may be a paginator object
    if (res.data && Array.isArray(res.data.data)) {
      comments.value = res.data.data
      commentsMeta.value = {
        total: res.data.total,
        per_page: res.data.per_page,
        current_page: res.data.current_page,
      }
    } else if (Array.isArray(res.data)) {
      comments.value = res.data
      commentsMeta.value = { total: res.data.length }
    } else {
      comments.value = []
      commentsMeta.value = {}
    }
  } catch (e) {
    console.error('Failed to load comments:', e)
    commentsError.value = e
    comments.value = []
    commentsMeta.value = {}
  } finally {
    commentsLoading.value = false
  }
}

onMounted(() => {
  loadComments(1000)
})

function goToCRM() {
  try {
    router.push('/main-branch/crm')
  } catch (e) {
    try { window.location.href = '/main-branch/crm' } catch (e) {}
  }
}

// Close dropdown when clicking outside
window.addEventListener('click', () => {
  try { if (profileDropdownVisible.value) closeProfileDropdown() } catch (e) {}
})
</script>

<style scoped>
.main-branch-admin-panel {
  width: 100%;
}

.crm-btn {
  background: #10b981;
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}
.crm-btn:hover { opacity: 0.95 }

.crm-button-row {
  display: flex;
  justify-content: flex-start;
  margin: 18px 0;
}

.kpi-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin: 8px 0 20px 0;
}
.kpi-card {
  background: #fff;
  border-radius: 8px;
  padding: 18px;
  box-shadow: 0 6px 12px rgba(17,24,39,0.04);
}
.kpi-title { color: #6b6b6b; font-size: 13px }
.kpi-value { font-size: 20px; font-weight: 700; margin-top: 6px }

.overview-box {
  background: #fff;
  padding: 18px;
  border-radius: 8px;
  box-shadow: 0 6px 12px rgba(17,24,39,0.04);
  margin-bottom: 18px;
}
.overview-box h3 { margin: 0 0 6px 0 }
.overview-box p { margin: 0; color: #666 }

.comments-panel { margin-top: 8px }
.comments-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap }
.comments-controls { display:flex; gap:12px; align-items:center; flex-wrap:wrap }
.search-input { padding:8px 10px; border-radius:8px; border:1px solid #e6e6e6 }
.rating-select { padding:8px; border-radius:8px; border:1px solid #e6e6e6 }

.branch-reports { display:flex; justify-content:space-between; align-items:center; margin:12px 0 }
.branch-title { font-weight:700 }
.branch-sub { font-size:12px; color:#777 }
.branch-actions { display:flex; gap:12px; align-items:center; flex-wrap:nowrap }
.branch-select { padding:8px 12px; border-radius:8px; border:1px solid #e6e6e6; min-width:140px; height:40px }
.refresh-finance {
  background: linear-gradient(180deg, #ff8a42, #ff6a00);
  color: #fff;
  border: none;
  padding: 10px 16px;
  border-radius: 10px;
  font-weight: 700;
  box-shadow: 0 6px 18px rgba(255,106,0,0.14);
  cursor: pointer;
  transition: transform 120ms ease, box-shadow 120ms ease, opacity 120ms ease;
}
.refresh-finance:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(255,106,0,0.18); opacity: 0.98 }
.refresh-finance:active { transform: translateY(0); box-shadow: 0 6px 18px rgba(255,106,0,0.14) }

.financial-overview { background:#fff; border-radius:8px; padding:16px; box-shadow:0 6px 12px rgba(17,24,39,0.04) }
.financial-header { font-weight:700; margin-bottom:10px }
.financial-chart { background:#f6f8fa; height:200px; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#999 }

.comments-list-section { margin-top:16px }
.comment-item { background:#fff; padding:12px; border-radius:8px; box-shadow:0 4px 8px rgba(0,0,0,0.03); margin-bottom:10px }
.comment-meta { font-size:13px; color:#444; display:flex; gap:8px; align-items:center }
.comment-product { color:#777 }
.comment-date { margin-left:auto; color:#999; font-size:12px }
.comment-body { margin-top:8px; color:#333 }
.comment-rating { margin-top:8px; color:#555; font-weight:600 }
.empty-text { color:#777; padding:12px }

@media (max-width: 800px) {
  .branch-actions { flex-direction: column; align-items: stretch; gap:10px; flex-wrap:wrap }
  .branch-select { width: 100%; min-width: 0 }
  .refresh-finance { width: 100% }

  .comments-controls { flex-direction: column; align-items: stretch; gap:8px }
  .search-input, .rating-select { width: 100% }
}

/* Ensure refresh button matches select height on wide screens */
.refresh-finance { height: 40px; display: inline-flex; align-items: center; justify-content: center }
</style>
