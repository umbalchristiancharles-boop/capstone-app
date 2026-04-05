<template>
  <div class="main-branch-page">
    <button class="back-btn" aria-label="Back" @click="goBack">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M15 18l-6-6 6-6" stroke="#111827" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
    <section class="panel-layout">
      <aside class="profile-col">
        <div class="profile-card">
          <div class="profile-head">
            <div class="avatar">CRM</div>
            <div>
              <div class="label">Main Branch Account</div>
              <h2>{{ profile.full_name || 'CRM Officer' }}</h2>
              <p>CUSTOMER RELATIONS</p>
            </div>
          </div>
          <div class="profile-meta">
            <div><strong>Username:</strong> {{ profile.username || 'crm_main_branch' }}</div>
            <div><strong>Branch:</strong> Main Branch (HQ)</div>
          </div>
        </div>
      </aside>

      <main class="main-col">
        <header class="panel-header">
          <h1>Customer Relationship Management Dashboard</h1>
          <p>View and manage all customer comments and feedback</p>
        </header>

        <section class="overview-grid">
          <article class="overview-card"><span class="k">Total Comments</span><strong>{{ totalComments }}</strong></article>
          <article class="overview-card"><span class="k">Avg Rating</span><strong>{{ averageRating }}/5</strong></article>
          <article class="overview-card"><span class="k">Recent Comments</span><strong>{{ recentCommentsCount }}</strong></article>
          <article class="overview-card"><span class="k">Unique Customers</span><strong>{{ uniqueCustomers }}</strong></article>
        </section>

        <section class="panel-block">
          <h3>Customer Feedback Overview</h3>
          <p>Monitor and respond to all customer comments about your products. High engagement with comments helps improve customer satisfaction and product quality.</p>
        </section>

        <!-- Comments List -->
        <section class="panel-block" style="margin-top: 24px;">
          <div class="comments-header">
            <h3 style="margin: 0;">All Customer Comments</h3>
            <div class="comment-filters">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search comments..."
                class="search-input"
              />
              <select v-model="selectedRating" class="rating-filter">
                <option value="">All Ratings</option>
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
                <option value="2">2 Stars</option>
                <option value="1">1 Star</option>
              </select>
            </div>
          </div>

          <div v-if="isLoading" class="loading-state">
            <p>Loading comments...</p>
          </div>

          <div v-else-if="filteredComments.length === 0" class="empty-state">
            <p>No comments found</p>
          </div>

          <div v-else class="comments-list">
            <article v-for="comment in paginatedComments" :key="comment.id" class="comment-card">
              <div class="comment-header">
                <div class="comment-meta">
                  <strong class="comment-author">{{ comment.author }}</strong>
                  <span class="comment-date">{{ formatDate(comment.created_at) }}</span>
                </div>
                <div class="comment-actions">
                  <div class="comment-rating">
                    <span class="rating-stars" :data-rating="comment.rating">
                      {{ '★'.repeat(comment.rating) }}{{ '☆'.repeat(5 - comment.rating) }}
                    </span>
                  </div>
                  <button
                    class="delete-btn"
                    @click="deleteComment(comment.id)"
                    title="Delete this comment"
                  >🗑️</button>
                </div>
              </div>

              <div class="comment-product" v-if="comment.product">
                <strong>Product:</strong> {{ comment.product.name }}
              </div>

              <div class="comment-text">
                {{ comment.text }}
              </div>

              <!-- Replies -->
              <div v-if="comment.replies && comment.replies.length > 0" class="comment-replies">
                <div v-for="reply in comment.replies" :key="reply.id" class="reply-item">
                  <div class="reply-content">
                    <strong>{{ reply.author }}</strong>
                    <span class="reply-date">{{ formatDate(reply.created_at) }}</span>
                  </div>
                  <p>{{ reply.text }}</p>
                </div>
              </div>
            </article>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="pagination">
              <button
                :disabled="currentPage === 1"
                @click="currentPage--"
                class="pagination-btn"
              >
                Previous
              </button>
              <span class="pagination-info">
                Page {{ currentPage }} of {{ totalPages }}
              </span>
              <button
                :disabled="currentPage === totalPages"
                @click="currentPage++"
                class="pagination-btn"
              >
                Next
              </button>
            </div>
          </div>
        </section>
      </main>

      <aside class="side-col">
        <section class="panel-block">
          <h3>Quick Links</h3>
          <button class="link-btn" @click="refreshComments" style="background: linear-gradient(180deg, #10b981, #059669);">Refresh Comments</button>
          <button class="logout-btn" @click.prevent="askLogout">Logout</button>
        </section>
      </aside>
    </section>

    <transition name="fade">
      <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
        <div class="logout-confirm-box">
          <h3>Logout from CRM Panel?</h3>
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
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

function goBack() {
  try { router.back() } catch (e) { try { router.push('/main-branch/admin') } catch (e) {} }
}
const profile = ref({})
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const comments = ref([])
const isLoading = ref(true)
const searchQuery = ref('')
const selectedRating = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)


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


function formatDate(dateStr) {
  try {
    const date = new Date(dateStr)
    return date.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
  } catch (e) {
    return dateStr
  }
}

async function loadProfile() {
  try {
    const res = await axios.get('/api/me', { withCredentials: true })
    if (res.data?.ok) profile.value = res.data.user || {}
  } catch (e) {}
}

async function loadComments() {
  isLoading.value = true
  try {
    const res = await axios.get('/api/product-comments/all', {
      params: {
        per_page: 1000 // Get all comments at once for easier filtering
      },
      withCredentials: true
    })
    if (res.data?.data) {
      comments.value = res.data.data
    } else if (Array.isArray(res.data)) {
      comments.value = res.data
    }
  } catch (e) {
    console.error('Failed to load comments:', e)
  } finally {
    isLoading.value = false
  }
}

async function refreshComments() {
  currentPage.value = 1
  await loadComments()
}

async function deleteComment(commentId) {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('Are you sure you want to delete this comment? This action cannot be undone.', 'Delete Comment') : Promise.resolve(false))
    if (!ok) return

    await axios.delete(`/api/product-comments/${commentId}`, { withCredentials: true })

    // Remove comment from local state
    comments.value = comments.value.filter(c => c.id !== commentId)

    // Show success message
    if (window.swalAlert) {
      await window.swalAlert('Comment deleted successfully', 'Success', 'success')
    }
  } catch (error) {
    console.error('Failed to delete comment:', error)
    if (window.swalAlert) {
      await window.swalAlert('Failed to delete comment. Please try again.', 'Error', 'error')
    }
  }
}

const filteredComments = computed(() => {
  let filtered = comments.value

  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(c =>
      (c.author && c.author.toLowerCase().includes(query)) ||
      (c.text && c.text.toLowerCase().includes(query)) ||
      (c.product && c.product.name && c.product.name.toLowerCase().includes(query))
    )
  }

  // Filter by rating
  if (selectedRating.value) {
    filtered = filtered.filter(c => c.rating === parseInt(selectedRating.value))
  }

  return filtered
})

const paginatedComments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredComments.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredComments.value.length / itemsPerPage.value) || 1
})

const totalComments = computed(() => comments.value.length)

const averageRating = computed(() => {
  if (comments.value.length === 0) return '0'
  const sum = comments.value.reduce((acc, c) => acc + (c.rating || 0), 0)
  return (sum / comments.value.length).toFixed(1)
})

const recentCommentsCount = computed(() => {
  const oneWeekAgo = new Date()
  oneWeekAgo.setDate(oneWeekAgo.getDate() - 7)
  return comments.value.filter(c => new Date(c.created_at) > oneWeekAgo).length
})

const uniqueCustomers = computed(() => {
  const authors = new Set(comments.value.map(c => c.author))
  return authors.size
})

onMounted(async () => {
  await loadProfile()
  await loadComments()
})
</script>

<style scoped>
.main-branch-page {
  min-height: 100vh;
  padding: 28px;
  background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
  font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  color: var(--text-dark);
  font-size: 15px;
}

/* Back button top-left */
.back-btn {
  position: absolute;
  top: 18px;
  left: 18px;
  width: 38px;
  height: 38px;
  display: grid;
  place-items: center;
  background: #fff;
  border: 1px solid #eef2f7;
  border-radius: 8px;
  box-shadow: 0 6px 14px rgba(16,24,40,0.06);
  cursor: pointer;
  z-index: 40;
}
.back-btn:hover { transform: translateY(-2px); }
.back-btn svg { display:block }

.panel-layout { display: grid; grid-template-columns: 300px 1fr 260px; gap: 20px; align-items: start; }
.profile-card, .panel-block, .overview-card, .panel-header { background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 4px 14px rgba(16,24,40,0.04); border: 1px solid #eef2f7; }

.profile-head { display: flex; gap: 14px; align-items: center; }
.avatar { width: 56px; height: 56px; border-radius: 50%; background: #111827; color: #fff; display: grid; place-items: center; font-weight: 700; font-size: 18px; }
.label { font-size: 12px; color: #6b7280; }
.profile-meta { margin: 12px 0; display: grid; gap: 6px; font-size: 14px; color: rgba(66,33,11,0.9); }

.overview-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.overview-card { display: flex; flex-direction: column; gap: 8px; padding: 16px; }
.overview-card .k { color: rgba(66,33,11,0.9); font-size: 13px; }
.overview-card strong { font-size: 24px; color: var(--text-dark); }

.panel-header h1 { margin: 0 0 6px; font-size: 34px; letter-spacing: -0.5px; color: var(--text-dark); }
.panel-header p { margin: 0; color: rgba(66,33,11,0.9); }

.main-col { display: grid; gap: 18px; }
.side-col { display: grid; gap: 14px; align-content: start; }

.panel-block ul { margin: 0; padding-left: 18px; }
.panel-block li { margin: 8px 0; color: rgba(66,33,11,0.9); }

.link-btn {
  border: 0; border-radius: 10px; background: var(--color-royal-blue); color: #fff; cursor: pointer;
  box-shadow: 0 8px 24px rgba(224,88,24,0.08);
  /* default: full-width blocks in side panels */
  display: block; width: 100%; text-align: left;
  padding: 8px 12px; margin-bottom: 10px;
}
.link-btn:hover { filter: brightness(0.98); }
.link-btn:last-of-type { margin-bottom: 0; }

.logout-btn { border: 0; border-radius: 999px; padding: 8px 12px; background: var(--alert); color: #fff; cursor: pointer; margin-top: 8px; box-shadow: 0 6px 18px rgba(239,68,68,0.08); width: 100%; }


/* Comments Section */
.comments-header { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-bottom: 16px; flex-wrap: wrap; }
.comment-filters { display: flex; gap: 10px; flex-wrap: wrap; }
.search-input, .rating-filter { padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; }
.search-input { flex: 1; min-width: 200px; }
.rating-filter { min-width: 120px; }

.loading-state, .empty-state { text-align: center; padding: 32px 16px; color: #6b7280; }

.comments-list { display: grid; gap: 14px; }
.comment-card {
  background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px;
  transition: all 0.24s ease;
}
.comment-card:hover { background: #ffffff; border-color: #d1d5db; box-shadow: 0 2px 8px rgba(16,24,40,0.06); }

.comment-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 10px; }
.comment-meta { display: flex; align-items: center; gap: 12px; }
.comment-author { font-size: 16px; color: var(--text-dark); }
.comment-date { font-size: 13px; color: #9ca3af; }
.comment-actions { display: flex; align-items: center; gap: 12px; }
.comment-rating { display: flex; align-items: center; }
.rating-stars { font-size: 14px; color: #fbbf24; }
.delete-btn {
  background: none; border: none; font-size: 14px; cursor: pointer; padding: 4px 6px;
  border-radius: 4px; transition: all 0.2s ease;
}
.delete-btn:hover { background: rgba(239, 68, 68, 0.1); transform: scale(1.1); }

.comment-product { font-size: 13px; color: #6b7280; margin-bottom: 8px; padding: 8px; background: #f3f4f6; border-radius: 6px; }
.comment-text { color: #374151; line-height: 1.5; margin-bottom: 12px; }

.comment-replies { margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb; background: #fafbfc; padding: 12px; border-radius: 6px; }
.reply-item { margin-bottom: 10px; }
.reply-content { display: flex; gap: 8px; font-size: 13px; margin-bottom: 4px; }
.reply-content strong { color: var(--text-dark); }
.reply-date { color: #9ca3af; }
.reply-item p { margin: 4px 0 0; color: #374151; font-size: 14px; line-height: 1.4; }

.pagination { display: flex; justify-content: center; align-items: center; gap: 12px; margin-top: 20px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
.pagination-btn { border: 1px solid #d1d5db; background: #fff; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 14px; }
.pagination-btn:hover:not(:disabled) { background: #f3f4f6; }
.pagination-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pagination-info { font-size: 14px; color: #6b7280; }

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

@media (max-width: 1024px) {
  .panel-layout { grid-template-columns: 1fr; }
  .overview-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .comments-header { flex-direction: column; align-items: stretch; }
  .comment-filters { flex-direction: column; }
  .search-input { width: 100%; }
  .rating-filter { width: 100%; }
}

</style>
