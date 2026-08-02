<template>
  <div class="main-branch-page">
    <div class="page-toolbar">
      <button class="back-btn" aria-label="Back" @click="goBack">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M15 18l-6-6 6-6" stroke="#111827" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <section class="panel-layout">
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

        <!-- Customer Reports / CRM Section -->
        <section class="panel-block" style="margin-top: 24px;">
          <div class="reports-header">
            <h3 style="margin: 0 0 16px 0;">Customer Reports / CRM</h3>
            <div class="reports-stats">
              <div class="stat-card">
                <span class="stat-label">Total</span>
                <span class="stat-value">{{ reportStats.total }}</span>
              </div>
              <div class="stat-card stat-pending">
                <span class="stat-label">Pending</span>
                <span class="stat-value">{{ reportStats.pending }}</span>
              </div>
              <div class="stat-card stat-progress">
                <span class="stat-label">In Progress</span>
                <span class="stat-value">{{ reportStats.in_progress }}</span>
              </div>
              <div class="stat-card stat-resolved">
                <span class="stat-label">Resolved</span>
                <span class="stat-value">{{ reportStats.resolved }}</span>
              </div>
            </div>
          </div>

          <div class="reports-controls">
            <div class="filter-group">
              <select v-model="reportFilterStatus" @change="loadReports" class="filter-select">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
              </select>

              <input
                v-model="reportSearchQuery"
                @input="debouncedReportSearch"
                type="text"
                placeholder="Search reports..."
                class="search-input"
              />
            </div>

            <button @click="loadReports" class="refresh-btn" :disabled="reportsLoading">
              {{ reportsLoading ? 'Loading...' : 'Refresh' }}
            </button>
          </div>

          <div v-if="reportsLoading && reports.length === 0" class="loading-state">
            <p>Loading reports...</p>
          </div>

          <div v-else-if="reports.length === 0" class="empty-state">
            <p>No customer reports found</p>
          </div>

          <div v-else class="reports-table">
            <div class="table-header">
              <span>Customer</span>
              <span>Subject</span>
              <span>Status</span>
              <span>Date</span>
              <span>Actions</span>
            </div>

            <div
              v-for="report in reports"
              :key="report.id"
              class="table-row"
              :class="{ 'row-expanded': expandedReport === report.id }"
            >
              <div class="row-main" @click="toggleExpandReport(report.id)">
                <span class="customer-info">
                  <div class="customer-name">{{ report.customer_name }}</div>
                  <div class="customer-email">{{ report.customer_email }}</div>
                </span>
                <span class="subject-text">{{ report.subject }}</span>
                <span>
                  <span class="badge" :class="getStatusClass(report.status)">
                    {{ formatStatus(report.status) }}
                  </span>
                </span>
                <span class="date-text">{{ formatDate(report.created_at) }}</span>
                <span class="actions-cell" @click.stop>
                  <button @click="openEditReportModal(report)" class="action-btn" title="Edit">
                    ✏️
                  </button>
                  <button @click="deleteReport(report)" class="action-btn action-btn--danger" title="Delete">
                    🗑️
                  </button>
                </span>
              </div>

              <div v-if="expandedReport === report.id" class="row-details">
                <div class="details-grid">
                  <div class="detail-item">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ report.customer_phone || 'N/A' }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Assigned To:</span>
                    <span class="detail-value">{{ report.assigned_to ? report.assigned_to.full_name : 'Unassigned' }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Resolved At:</span>
                    <span class="detail-value">{{ report.resolved_at ? formatDate(report.resolved_at) : 'N/A' }}</span>
                  </div>
                </div>
                <div class="message-section">
                  <span class="detail-label">Message:</span>
                  <p class="message-text">{{ report.message }}</p>
                </div>
                <div v-if="report.admin_notes" class="notes-section">
                  <span class="detail-label">Admin Notes:</span>
                  <p class="notes-text">{{ report.admin_notes }}</p>
                </div>
              </div>
            </div>
          </div>
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
                    <span v-if="comment.is_hidden" class="hidden-badge">Hidden</span>
                  <button
                    class="flag-btn"
                    @click="flagComment(comment.id)"
                    title="Flag this comment"
                  >🚩</button>
                  <button
                    class="delete-btn"
                    @click="deleteComment(comment.id)"
                    title="Delete this comment"
                  >🗑️</button>
                    <button v-if="comment.is_hidden" class="unhide-btn" @click="unhideComment(comment.id)" title="Unhide this comment">🔓</button>
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

      <!-- Edit Report Modal -->
      <transition name="fade">
        <div v-if="showEditReportModal" class="modal-backdrop" @click.self="closeEditReportModal">
          <div class="modal">
            <div class="modal-header">
              <h3>Update Report</h3>
              <button class="modal-close" @click="closeEditReportModal">✕</button>
            </div>

            <div class="modal-body">
              <div class="form-group" v-if="editingReport.message">
                <label>Customer Concern:</label>
                <div class="customer-concern-display">
                  {{ editingReport.message }}
                </div>
              </div>

              <div class="form-group" v-if="editingReport.customer_email">
                <label>Customer Email: <strong>{{ editingReport.customer_email }}</strong></label>
              </div>

              <!-- Email Communications History -->
              <div class="email-history-section">
                <div class="email-history-header">
                  <span class="detail-label">Email Communications:</span>
                  <button @click="toggleEmailHistory(editingReport.id)" class="btn-toggle-email">
                    {{ expandedEmailHistory === editingReport.id ? 'Hide' : 'Show' }} Email History
                  </button>
                </div>

                <div v-if="expandedEmailHistory === editingReport.id" class="email-history-content">
                  <div v-if="loadingEmails[editingReport.id]" class="loading-state">
                    <p>Loading emails...</p>
                  </div>
                  <div v-else-if="getEmailHistory(editingReport.id).length === 0" class="empty-state">
                    <p>No email communications yet</p>
                  </div>
                  <div v-else class="email-list">
                    <div v-for="email in getEmailHistory(editingReport.id)" :key="email.id" class="email-item" :class="email.direction">
                      <div class="email-header">
                        <div class="email-direction-badge">
                          {{ email.direction === 'outbound' ? '📤 Outbound' : '📥 Inbound' }}
                        </div>
                        <div class="email-status-badge" :class="'status--' + email.status">
                          {{ email.status }}
                        </div>
                        <span class="email-date">{{ formatDate(email.created_at) }}</span>
                      </div>
                      <div class="email-subject">
                        <strong>Subject:</strong> {{ email.subject }}
                      </div>
                      <div class="email-participants">
                        <div><strong>From:</strong> {{ email.sender_name }} ({{ email.sender_email }})</div>
                        <div><strong>To:</strong> {{ email.recipient_name || email.recipient_email }} ({{ email.recipient_email }})</div>
                      </div>
                      <div class="email-message">
                        {{ email.message }}
                      </div>
                      <div v-if="email.error_message" class="email-error">
                        <strong>Error:</strong> {{ email.error_message }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn-cancel" @click="closeEditReportModal">Cancel</button>
              <button
                v-if="editingReport.customer_email"
                class="btn-email"
                @click="openEmailModal"
                :disabled="sendingEmail"
                title="Send email to customer"
              >
                📧 Send Email
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Email Modal -->
      <transition name="fade">
        <div v-if="showEmailModal" class="modal-backdrop" @click.self="closeEmailModal">
          <div class="modal">
            <div class="modal-header">
              <h3>Send Email to Customer</h3>
              <button class="modal-close" @click="closeEmailModal">✕</button>
            </div>

            <div class="modal-body">
              <div class="form-group">
                <label>To:</label>
                <input
                  type="email"
                  :value="editingReport.customer_email"
                  class="form-input"
                  disabled
                />
              </div>

              <div class="form-group">
                <label>Subject *</label>
                <input
                  v-model="emailForm.subject"
                  type="text"
                  class="form-input"
                  placeholder="Enter email subject"
                  required
                />
              </div>

              <div class="form-group">
                <label>Message *</label>
                <textarea
                  v-model="emailForm.message"
                  rows="8"
                  class="form-input"
                  placeholder="Enter your message to the customer..."
                  required
                ></textarea>
              </div>

              <!-- Email Communications History -->
              <div class="email-history-section">
                <div class="email-history-header">
                  <span class="detail-label">Email Communications:</span>
                  <button @click="toggleEmailHistory(editingReport.id)" class="btn-toggle-email">
                    {{ expandedEmailHistory === editingReport.id ? 'Hide' : 'Show' }} Email History
                  </button>
                </div>

                <div v-if="expandedEmailHistory === editingReport.id" class="email-history-content">
                  <div v-if="loadingEmails[editingReport.id]" class="loading-state">
                    <p>Loading emails...</p>
                  </div>
                  <div v-else-if="getEmailHistory(editingReport.id).length === 0" class="empty-state">
                    <p>No email communications yet</p>
                  </div>
                  <div v-else class="email-list">
                    <div v-for="email in getEmailHistory(editingReport.id)" :key="email.id" class="email-item" :class="email.direction">
                      <div class="email-header">
                        <div class="email-direction-badge">
                          {{ email.direction === 'outbound' ? '📤 Outbound' : '📥 Inbound' }}
                        </div>
                        <div class="email-status-badge" :class="'status--' + email.status">
                          {{ email.status }}
                        </div>
                        <span class="email-date">{{ formatDate(email.created_at) }}</span>
                      </div>
                      <div class="email-subject">
                        <strong>Subject:</strong> {{ email.subject }}
                      </div>
                      <div class="email-participants">
                        <div><strong>From:</strong> {{ email.sender_name }} ({{ email.sender_email }})</div>
                        <div><strong>To:</strong> {{ email.recipient_name || email.recipient_email }} ({{ email.recipient_email }})</div>
                      </div>
                      <div class="email-message">
                        {{ email.message }}
                      </div>
                      <div v-if="email.error_message" class="email-error">
                        <strong>Error:</strong> {{ email.error_message }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Automatic Response Preview -->
              <div v-if="isFirstEmail" class="auto-response-preview">
                <div class="auto-response-header">
                  <span class="auto-response-icon">📧</span>
                  <strong>Automatic Acknowledgment Will Be Sent First</strong>
                </div>
                <div class="auto-response-content">
                  <p class="auto-response-label">The customer will receive this automatic response before your message:</p>
                  <div class="auto-response-box">
                    <div class="auto-response-subject">
                      <strong>Subject:</strong> Re: {{ editingReport.subject || emailForm.subject }}
                    </div>
                    <div class="auto-response-message">
                      <strong>Message:</strong><br />
                      Dear {{ editingReport.customer_name }},<br /><br />
                      Thank you for reaching out to us. We have received your message and our team is reviewing it.<br /><br />
                      We will get back to you as soon as possible.<br /><br />
                      Best regards,<br />
                      Customer Support Team
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn-cancel" @click="closeEmailModal">Cancel</button>
              <button class="btn-primary" @click="sendEmail" :disabled="sendingEmail">
                {{ sendingEmail ? 'Sending...' : 'Send Email' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

    </section>
  </div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

function goBack() {
  try { router.back() } catch (e) { try { router.push('/main-branch/admin') } catch (e) {} }
}
const profile = ref({})
const comments = ref([])
const isLoading = ref(true)
const searchQuery = ref('')
const selectedRating = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Customer Reports state
const reports = ref([])
const reportStats = ref({
  total: 0,
  pending: 0,
  in_progress: 0,
  resolved: 0,
  closed: 0,
})
const reportFilterStatus = ref('all')
const reportSearchQuery = ref('')
const reportsLoading = ref(false)
const expandedReport = ref(null)
const showEditReportModal = ref(false)
const showEmailModal = ref(false)
const editingReport = ref({
  id: null,
  status: 'pending',
  subject: '',
  message: '',
  customer_email: '',
})
const staffList = ref([])
const sendingEmail = ref(false)
const emailForm = ref({
  subject: '',
  message: '',
})
const expandedEmailHistory = ref(null)
const emailHistoryCache = ref({})
const loadingEmails = ref({})
const isFirstEmail = ref(false)
const checkingFirstEmail = ref(false)

let reportSearchTimeout = null

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

async function flagComment(commentId) {
  try {
    // Ask for reason using dropdown with common reasons
    let reason = ''
    if (window.swalSelectPrompt) {
      const flaggingReasons = {
        '': 'No reason (skip)',
        'inappropriate_content': 'Inappropriate content',
        'spam': 'Spam',
        'offensive_language': 'Offensive language',
        'misinformation': 'Misinformation',
        'harassment': 'Harassment',
        'fake_review': 'Fake review',
        'off_topic': 'Off-topic'
      }
      try { reason = await window.swalSelectPrompt('Select reason for flagging this comment', 'Flag Comment', flaggingReasons) } catch (e) { reason = '' }
    } else {
      try { reason = window.prompt('Optional reason for flagging this comment') || '' } catch (e) { reason = '' }
    }
    // Prompt for an email address to notify in case the comment author is a guest
    let notifyEmail = ''
    if (window.swalPrompt) {
      try { notifyEmail = await window.swalPrompt('Recipient email (leave blank to use commenter account email)') } catch (e) { notifyEmail = '' }
    } else {
      try { notifyEmail = window.prompt('Recipient email (optional)') || '' } catch (e) { notifyEmail = '' }
    }

    const ok = await (window.swalConfirm ? window.swalConfirm('Flag this comment? This will notify the user.', 'Confirm flag') : Promise.resolve(true))
    if (!ok) return

    await axios.post(`/api/product-comments/${commentId}/flag`, { reason, email: notifyEmail || undefined }, { withCredentials: true })

    // Refresh comments to reflect any flag count changes
    await loadComments()

    if (window.swalAlert) {
      await window.swalAlert('Comment flagged and user notified', 'Success', 'success')
    }
  } catch (error) {
    console.error('Failed to flag comment:', error)
    if (window.swalAlert) {
      await window.swalAlert('Failed to flag comment. Please try again.', 'Error', 'error')
    }
  }
}

async function unhideComment(commentId) {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('Unhide this comment?', 'Confirm') : Promise.resolve(true))
    if (!ok) return
    await axios.post(`/api/product-comments/${commentId}/unhide`, {}, { withCredentials: true })
    await loadComments()
    if (window.swalAlert) await window.swalAlert('Comment unhidden', 'Success', 'success')
  } catch (e) {
    console.error('Failed to unhide comment:', e)
    if (window.swalAlert) await window.swalAlert('Failed to unhide comment. Please try again.', 'Error', 'error')
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

// Customer Reports Functions
function debouncedReportSearch() {
  clearTimeout(reportSearchTimeout)
  reportSearchTimeout = setTimeout(() => {
    loadReports()
  }, 500)
}

async function loadReportStats() {
  try {
    const response = await axios.get('/api/customer-reports/stats', { withCredentials: true })
    if (response.data.ok) {
      reportStats.value = response.data.stats
    }
  } catch (error) {
    console.error('Error loading report stats:', error)
  }
}

async function loadReports() {
  reportsLoading.value = true
  try {
    const params = {}
    if (reportFilterStatus.value !== 'all') {
      params.status = reportFilterStatus.value
    }
    if (reportSearchQuery.value.trim()) {
      params.search = reportSearchQuery.value.trim()
    }

    const response = await axios.get('/api/customer-reports', { params, withCredentials: true })
    if (response.data.ok) {
      reports.value = response.data.reports.data || []
    }
  } catch (error) {
    console.error('Error loading reports:', error)
    if (window.swalAlert) {
      await window.swalAlert('Failed to load reports. Please try again.', 'Error', 'error')
    }
  } finally {
    reportsLoading.value = false
  }
}

async function loadStaffList() {
  try {
    const response = await axios.get('/api/admin/staff', { withCredentials: true })
    if (response.data) {
      staffList.value = response.data
    }
  } catch (error) {
    console.error('Error loading staff:', error)
  }
}

function toggleExpandReport(id) {
  expandedReport.value = expandedReport.value === id ? null : id
}

function openEditReportModal(report) {
  editingReport.value = {
    id: report.id,
    status: report.status,
    subject: report.subject || '',
    message: report.message || '',
    customer_email: report.customer_email || '',
  }
  showEditReportModal.value = true
}

function closeEditReportModal() {
  showEditReportModal.value = false
  editingReport.value = {
    id: null,
    status: 'pending',
    message: '',
    customer_email: '',
  }
}

async function openEmailModal() {
  emailForm.value = {
    subject: editingReport.value.subject || '',
    message: '',
  }

  // Check if this is the first email for this report
  checkingFirstEmail.value = true
  try {
    const response = await axios.get(`/api/customer-reports/${editingReport.value.id}/emails`, { withCredentials: true })
    if (response.data.ok) {
      const emails = response.data.emails || []
      const hasOutboundEmails = emails.some(email => email.direction === 'outbound')
      isFirstEmail.value = !hasOutboundEmails
    } else {
      isFirstEmail.value = false
    }
  } catch (error) {
    console.error('Error checking email history:', error)
    isFirstEmail.value = false
  } finally {
    checkingFirstEmail.value = false
  }

  showEmailModal.value = true
}

function closeEmailModal() {
  showEmailModal.value = false
  emailForm.value = {
    subject: '',
    message: '',
  }
}

async function sendEmail() {
  // For first emails, only subject is required (message is optional since auto-acknowledgment is sent)
  // For subsequent emails, both subject and message are required
  if (!emailForm.value.subject.trim()) {
    if (window.swalAlert) {
      await window.swalAlert('Please fill in the subject', 'Error', 'error')
    }
    return
  }

  if (!isFirstEmail.value && !emailForm.value.message.trim()) {
    if (window.swalAlert) {
      await window.swalAlert('Please fill in the message', 'Error', 'error')
    }
    return
  }

  sendingEmail.value = true
  try {
    const response = await axios.post(`/api/customer-reports/${editingReport.value.id}/send-email`, {
      subject: emailForm.value.subject,
      message: emailForm.value.message,
    }, { withCredentials: true })

    if (response.data.ok) {
      // Automatically update status to "in_progress" after sending email
      await updateReportStatusToInProgress()

      let successMessage = response.data.message || 'Email sent successfully'

      // Show additional notification if automatic acknowledgment was sent
      if (response.data.is_first_email && window.swalAlert) {
        await window.swalAlert(
          'First email sent! An automatic acknowledgment has been sent to the customer notifying them that their message has been received.',
          'Automatic Acknowledgment Sent',
          'info'
        )
      } else if (window.swalAlert) {
        await window.swalAlert(successMessage, 'Success', 'success')
      }

      closeEmailModal()
      await loadReports()
      await loadReportStats()
    } else {
      if (window.swalAlert) {
        await window.swalAlert(response.data.message || 'Failed to send email', 'Error', 'error')
      }
    }
  } catch (error) {
    console.error('Error sending email:', error)
    if (window.swalAlert) {
      await window.swalAlert(error.response?.data?.message || 'Failed to send email. Please try again.', 'Error', 'error')
    }
  } finally {
    sendingEmail.value = false
  }
}

async function updateReportStatusToInProgress() {
  try {
    const response = await axios.put(`/api/customer-reports/${editingReport.value.id}`, {
      status: 'in_progress',
    }, { withCredentials: true })

    if (response.data.ok) {
      // Update local editing report status
      editingReport.value.status = 'in_progress'
    }
  } catch (error) {
    console.error('Error updating report status:', error)
  }
}



function getEmailHistory(reportId) {
  return emailHistoryCache.value[reportId] || []
}

async function toggleEmailHistory(reportId) {
  if (expandedEmailHistory.value === reportId) {
    expandedEmailHistory.value = null
    return
  }

  expandedEmailHistory.value = reportId

  // If we haven't loaded emails for this report yet, load them
  if (!emailHistoryCache.value[reportId]) {
    await loadEmailHistory(reportId)
  }
}

async function loadEmailHistory(reportId) {
  loadingEmails.value[reportId] = true
  try {
    const response = await axios.get(`/api/customer-reports/${reportId}/emails`, { withCredentials: true })
    if (response.data.ok) {
      emailHistoryCache.value[reportId] = response.data.emails || []
    }
  } catch (error) {
    console.error('Error loading email history:', error)
    emailHistoryCache.value[reportId] = []
  } finally {
    loadingEmails.value[reportId] = false
  }
}

async function deleteReport(report) {
  const confirmed = await (window.swalConfirm ? window.swalConfirm(`Are you sure you want to delete this report from ${report.customer_name}?`, 'Delete Report') : Promise.resolve(false))
  if (!confirmed) return

  try {
    const response = await axios.delete(`/api/customer-reports/${report.id}`, { withCredentials: true })
    if (response.data.ok) {
      if (window.swalAlert) {
        await window.swalAlert('Report deleted successfully', 'Success', 'success')
      }
      await loadReports()
      await loadReportStats()
    }
  } catch (error) {
    console.error('Error deleting report:', error)
    if (window.swalAlert) {
      await window.swalAlert('Failed to delete report. Please try again.', 'Error', 'error')
    }
  }
}

function getStatusClass(status) {
  const classes = {
    pending: 'badge--warning',
    in_progress: 'badge--info',
    resolved: 'badge--success',
    closed: 'badge--secondary',
  }
  return classes[status] || 'badge--secondary'
}

function formatStatus(status) {
  const labels = {
    pending: 'Pending',
    in_progress: 'In Progress',
    resolved: 'Resolved',
    closed: 'Closed',
  }
  return labels[status] || status
}

onMounted(async () => {
  await loadProfile()
  await loadComments()
  await loadReportStats()
  await loadReports()
  await loadStaffList()
})
</script>

<style scoped>
.main-branch-page {
  min-height: 100vh;
  padding: 28px;
  background: #f3f4f7;
  font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  color: var(--text-dark);
  font-size: 15px;
}

/* Back button placement inside content flow */
.page-toolbar {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 22px;
}

.back-btn {
  position: static;
  min-width: 44px;
  height: 44px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: 999px;
  padding: 0 12px;
  gap: 6px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
  cursor: pointer;
  transition: transform 0.18s ease, border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
}
.back-btn:hover {
  transform: translateY(-1px);
  border-color: #9ca3af;
  background: #f8fafc;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.1);
}
.back-btn:focus-visible {
  outline: 2px solid transparent;
  outline-offset: 3px;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.18), 0 14px 30px rgba(15, 23, 42, 0.08);
}
.back-btn svg {
  display: block;
  width: 18px;
  height: 18px;
}

.panel-layout { display: grid; grid-template-columns: 1fr; gap: 20px; align-items: start; }
.profile-card, .panel-block, .overview-card, .panel-header { background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 4px 14px rgba(16,24,40,0.04); border: 1px solid #eef2f7; }

.profile-head { display: flex; gap: 14px; align-items: center; }
.avatar { width: 56px; height: 56px; border-radius: 50%; background: #111827; color: #fff; display: grid; place-items: center; font-weight: 700; font-size: 18px; }
.label { font-size: 12px; color: #6b7280; }
.profile-meta { margin: 12px 0; display: grid; gap: 6px; font-size: 14px; color: rgba(66,33,11,0.9); }

.overview-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.overview-card { display: flex; flex-direction: column; gap: 8px; padding: 20px; background: #ffffff; border: 1px solid #e5e7eb; }
.overview-card .k { color: #6b7280; font-size: 13px; text-transform: uppercase; letter-spacing: 0.06em; }
.overview-card strong { font-size: 26px; color: #111827; }

.panel-header h1 { margin: 0 0 6px; font-size: 34px; letter-spacing: -0.5px; color: var(--text-dark); }
.panel-header p { margin: 0; color: rgba(66,33,11,0.75); }

.main-col { display: grid; gap: 18px; }
.side-col { display: grid; gap: 14px; align-content: start; }

.panel-block ul { margin: 0; padding-left: 18px; }
.panel-block li { margin: 8px 0; color: rgba(66,33,11,0.9); }

.hidden-badge {
  display: inline-block;
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fcd34d;
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 12px;
  margin-left: 8px;
}

.unhide-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  margin-left: 6px;
}

.link-btn {
  border: 0; border-radius: 10px; background: var(--color-royal-blue); color: #fff; cursor: pointer;
  box-shadow: 0 8px 24px rgba(224,88,24,0.08);
  /* default: full-width blocks in side panels */
  display: block; width: 100%; text-align: left;
  padding: 8px 12px; margin-bottom: 10px;
}
.link-btn:hover { filter: brightness(0.98); }
.link-btn:last-of-type { margin-bottom: 0; }


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



.fade-enter-active, .fade-leave-active { transition: opacity .18s ease; }

/* Reports Section */
.reports-header { margin-bottom: 20px; }
.reports-header h3 { font-size: 1.25rem; font-weight: 600; color: #1F2937; }
.reports-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-top: 12px; }
.stat-card { background: white; padding: 16px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); text-align: center; }
.stat-label { display: block; font-size: 0.85rem; color: #6B7280; margin-bottom: 4px; }
.stat-value { display: block; font-size: 1.5rem; font-weight: 700; color: #1F2937; }
.stat-pending .stat-value { color: #F59E0B; }
.stat-progress .stat-value { color: #3B82F6; }
.stat-resolved .stat-value { color: #10B981; }

.reports-controls { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
.filter-group { display: flex; gap: 12px; flex: 1; flex-wrap: wrap; }
.filter-select, .search-input { padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 12px; font-size: 0.95rem; font-family: inherit; background: #fff; }
.filter-select { min-width: 170px; }
.search-input { flex: 1; max-width: 360px; }
.refresh-btn { padding: 12px 22px; background: #f3f4f6; color: #1f2937; border: 1px solid #d1d5db; border-radius: 10px; font-weight: 700; cursor: pointer; transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.06); }
.refresh-btn:hover:not(:disabled) { background: #e5e7eb; border-color: #cbd5e1; transform: translateY(-1px); }
.refresh-btn:disabled { opacity: 0.6; cursor: not-allowed; background: #f8fafc; border-color: #e5e7eb; box-shadow: none; color: #6b7280; }

.reports-table-wrapper { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); overflow: hidden; }
.reports-table { width: 100%; }
.table-header { display: grid; grid-template-columns: 2fr 2fr 1fr 1.5fr 1fr; gap: 16px; padding: 18px 20px; background: #ffffff; border-bottom: 1px solid #E5E7EB; font-weight: 600; color: #374151; font-size: 0.9rem; }
.table-row { border-bottom: 1px solid #E5E7EB; transition: background 0.2s; }
.table-row:hover { background: #F9FAFB; }
.row-main { display: grid; grid-template-columns: 2fr 2fr 1fr 1.5fr 1fr; gap: 16px; padding: 16px 20px; align-items: center; cursor: pointer; }
.row-details { padding: 20px; background: #F9FAFB; border-top: 1px solid #E5E7EB; }
.details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 16px; }
.detail-item { display: flex; gap: 8px; }
.detail-label { font-weight: 600; color: #374151; font-size: 0.9rem; }
.detail-value { color: #6B7280; font-size: 0.9rem; }
.message-section, .notes-section { margin-bottom: 12px; }
.message-section .detail-label, .notes-section .detail-label { display: block; margin-bottom: 6px; }
.message-text, .notes-text { margin: 0; padding: 12px; background: white; border-radius: 6px; color: #4B5563; line-height: 1.6; font-size: 0.95rem; }
.notes-text { background: #FEF3C7; border-left: 3px solid #F59E0B; }
.customer-concern-display { padding: 12px; background: #F0F9FF; border: 1px solid #BAE6FD; border-radius: 6px; color: #0369A1; line-height: 1.6; font-size: 0.95rem; }
.customer-info { display: flex; flex-direction: column; gap: 2px; }
.customer-name { font-weight: 600; color: #1F2937; }
.customer-email { font-size: 0.85rem; color: #6B7280; }
.subject-text { color: #374151; font-size: 0.95rem; }
.date-text { color: #6B7280; font-size: 0.85rem; }
.actions-cell { display: flex; gap: 8px; }
.action-btn { background: none; border: none; font-size: 1.1rem; cursor: pointer; padding: 4px 8px; border-radius: 4px; transition: all 0.2s; }
.action-btn:hover { background: #F3F4F6; }
.action-btn--danger:hover { background: #FEE2E2; }
.badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 600; }
.badge--warning { background: #FEF3C7; color: #92400E; }
.badge--info { background: #DBEAFE; color: #1E40AF; }
.badge--success { background: #D1FAE5; color: #065F46; }
.badge--secondary { background: #E5E7EB; color: #374151; }

/* Modal */
.modal-backdrop { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 24px; }
.modal { background: white; border-radius: 12px; max-width: 800px; width: 100%; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #E5E7EB; flex-shrink: 0; }
.modal-header h3 { margin: 0; font-size: 1.25rem; color: #1F2937; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6B7280; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; }
.modal-close:hover { background: #F3F4F6; }
.modal-body { padding: 24px; overflow-y: auto; flex: 1; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; font-size: 0.95rem; }
.form-input { width: 100%; padding: 10px 14px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 0.95rem; font-family: inherit; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: #0066FF; box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1); }
textarea.form-input { resize: vertical; min-height: 100px; }
.modal-footer { display: flex; gap: 12px; justify-content: flex-end; padding: 16px 24px; border-top: 1px solid #E5E7EB; flex-shrink: 0; }
.btn-cancel { padding: 10px 20px; background: #F3F4F6; color: #6B7280; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-cancel:hover { background: #E5E7EB; }
.btn-email { padding: 10px 20px; background: #10B981; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-email:hover:not(:disabled) { background: #059669; }
.btn-email:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-primary { padding: 10px 20px; background: #0066FF; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-primary:hover:not(:disabled) { background: #0057e6; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
@media (max-width: 1024px) {
  .overview-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .comments-header { flex-direction: column; align-items: stretch; }
  .comment-filters { flex-direction: column; }
  .search-input { width: 100%; }
  .rating-filter { width: 100%; }
  .table-header, .row-main { grid-template-columns: 2fr 2fr 1fr 1fr; }
  .table-header span:nth-child(4), .row-main span:nth-child(4) { display: none; }
}

@media (max-width: 768px) {
  .reports-stats { grid-template-columns: repeat(2, 1fr); }
  .filter-group { flex-direction: column; }
  .search-input { max-width: none; }
  .table-header, .row-main { grid-template-columns: 1fr; gap: 8px; }
  .table-header { display: none; }
  .row-main { display: flex; flex-direction: column; align-items: flex-start; gap: 8px; }
  .actions-cell { width: 100%; justify-content: flex-end; }
}

/* Modal Form Layout */
.modal-body .form-group {
  margin-bottom: 20px;
}

.modal-body .form-group:last-child {
  margin-bottom: 0;
}

.customer-concern-display {
  padding: 12px;
  background: #F0F9FF;
  border: 1px solid #BAE6FD;
  border-radius: 6px;
  color: #0369A1;
  line-height: 1.6;
  font-size: 0.95rem;
  max-height: 150px;
  overflow-y: auto;
}

/* Email History Styles */
.email-history-section {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 2px solid #E5E7EB;
}

.email-history-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}

.email-history-header .detail-label {
  font-size: 1rem;
  font-weight: 700;
  color: #1F2937;
}

.btn-toggle-email {
  padding: 10px 20px;
  background: #8B5CF6;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.95rem;
  white-space: nowrap;
  box-shadow: 0 2px 4px rgba(139, 92, 246, 0.2);
}

.btn-toggle-email:hover {
  background: #7C3AED;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);
}

.email-history-content {
  margin-top: 20px;
  padding-right: 0;
}

.email-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.email-item {
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  transition: all 0.2s;
  min-height: auto;
  height: auto;
  max-width: 100%;
  box-sizing: border-box;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.email-item:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

.email-item.outbound {
  background: #F0FDF4;
  border-left: 5px solid #10B981;
}

.email-item.inbound {
  background: #FEF3C7;
  border-left: 5px solid #F59E0B;
}

.email-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 10px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.email-direction-badge {
  padding: 6px 14px;
  border-radius: 14px;
  font-size: 0.9rem;
  font-weight: 600;
  background: #10B981;
  color: white;
  box-shadow: 0 1px 2px rgba(16, 185, 129, 0.2);
}

.email-item.inbound .email-direction-badge {
  background: #F59E0B;
  box-shadow: 0 1px 2px rgba(245, 158, 11, 0.2);
}

.email-status-badge {
  padding: 6px 14px;
  border-radius: 14px;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: capitalize;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.email-status-badge.status--sent {
  background: #D1FAE5;
  color: #065F46;
}

.email-status-badge.status--failed {
  background: #FEE2E2;
  color: #991B1B;
}

.email-status-badge.status--pending {
  background: #FEF3C7;
  color: #92400E;
}

.email-date {
  font-size: 0.9rem;
  color: #6B7280;
  font-weight: 500;
}

.email-subject {
  margin-bottom: 16px;
  padding: 12px 16px;
  background: white;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  color: #1F2937;
  border-left: 3px solid #0066FF;
}

.email-participants {
  margin-bottom: 16px;
  padding: 14px 16px;
  background: white;
  border-radius: 8px;
  font-size: 0.95rem;
  line-height: 1.7;
  color: #4B5563;
}

.email-participants div {
  margin-bottom: 6px;
}

.email-participants div:last-child {
  margin-bottom: 0;
}

.email-participants strong {
  color: #1F2937;
  font-weight: 600;
}

.email-message {
  padding: 16px;
  background: white;
  border-radius: 8px;
  color: #374151;
  line-height: 1.7;
  font-size: 0.95rem;
  white-space: pre-wrap;
  word-break: break-word;
  border: 1px solid #F3F4F6;
}

.email-error {
  margin-top: 16px;
  padding: 12px 16px;
  background: #FEE2E2;
  border: 1px solid #FECACA;
  border-radius: 8px;
  color: #991B1B;
  font-size: 0.95rem;
  font-weight: 500;
}

/* Automatic Response Preview */
.auto-response-preview {
  margin-top: 24px;
  padding: 20px;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border: 2px solid #10b981;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}

.auto-response-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 14px;
  padding-bottom: 12px;
  border-bottom: 1px solid #10b981;
}

.auto-response-icon {
  font-size: 1.5rem;
}

.auto-response-header strong {
  color: #065f46;
  font-size: 1rem;
}

.auto-response-content {
  margin-top: 12px;
}

.auto-response-label {
  color: #374151;
  font-size: 0.9rem;
  margin-bottom: 12px;
  font-weight: 500;
}

.auto-response-box {
  background: white;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.auto-response-subject {
  padding-bottom: 12px;
  margin-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
  color: #374151;
  font-size: 0.95rem;
}

.auto-response-message {
  color: #4b5563;
  font-size: 0.95rem;
  line-height: 1.7;
  white-space: pre-line;
}

</style>
