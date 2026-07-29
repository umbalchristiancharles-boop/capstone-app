<template>
  <div class="customer-reports-panel">
    <div class="reports-header">
      <h2>Customer Reports / CRM</h2>
      <div class="reports-stats">
        <div class="stat-card">
          <span class="stat-label">Total</span>
          <span class="stat-value">{{ stats.total }}</span>
        </div>
        <div class="stat-card stat-pending">
          <span class="stat-label">Pending</span>
          <span class="stat-value">{{ stats.pending }}</span>
        </div>
        <div class="stat-card stat-progress">
          <span class="stat-label">In Progress</span>
          <span class="stat-value">{{ stats.in_progress }}</span>
        </div>
        <div class="stat-card stat-resolved">
          <span class="stat-label">Resolved</span>
          <span class="stat-value">{{ stats.resolved }}</span>
        </div>
      </div>
    </div>

    <div class="reports-controls">
      <div class="filter-group">
        <select v-model="filterStatus" @change="loadReports" class="filter-select">
          <option value="all">All Status</option>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="resolved">Resolved</option>
          <option value="closed">Closed</option>
        </select>

        <input
          v-model="searchQuery"
          @input="debouncedSearch"
          type="text"
          placeholder="Search reports..."
          class="search-input"
        />
      </div>

      <button @click="loadReports" class="refresh-btn" :disabled="loading">
        {{ loading ? 'Loading...' : 'Refresh' }}
      </button>
    </div>

    <div class="reports-table-wrapper">
      <div v-if="loading && reports.length === 0" class="loading-state">
        Loading reports...
      </div>

      <div v-else-if="reports.length === 0" class="empty-state">
        <div class="empty-icon">📭</div>
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
          <div class="row-main" @click="toggleExpand(report.id)">
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
              <button @click="openEditModal(report)" class="action-btn" title="Edit">
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
    </div>

    <!-- Edit Modal -->
    <transition name="fade">
      <div v-if="showEditModal" class="modal-backdrop" @click.self="closeEditModal">
        <div class="modal">
          <div class="modal-header">
            <h3>Update Report</h3>
            <button class="modal-close" @click="closeEditModal">✕</button>
          </div>

          <div class="modal-body">
            <div class="form-group">
              <label>Status</label>
              <select v-model="editingReport.status" class="form-input">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
              </select>
            </div>

            <div class="form-group">
              <label>Assign To</label>
              <select v-model="editingReport.assigned_to" class="form-input">
                <option :value="null">Unassigned</option>
                <option v-for="user in staffList" :key="user.id" :value="user.id">
                  {{ user.full_name }} ({{ user.role }})
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Admin Notes</label>
              <textarea
                v-model="editingReport.admin_notes"
                rows="4"
                class="form-input"
                placeholder="Add internal notes..."
              ></textarea>
            </div>

            <div v-if="editingReport.customer_email" class="form-group">
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
            <button class="btn-cancel" @click="closeEditModal">Cancel</button>
            <button 
              v-if="editingReport.customer_email" 
              class="btn-email" 
              @click="openEmailModal"
              :disabled="updating"
              title="Send email to customer"
            >
              📧 Send Email
            </button>
            <button class="btn-primary" @click="updateReport" :disabled="updating">
              {{ updating ? 'Saving...' : 'Save Changes' }}
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(false)
const updating = ref(false)
const reports = ref([])
const stats = ref({
  total: 0,
  pending: 0,
  in_progress: 0,
  resolved: 0,
  closed: 0,
})
const filterStatus = ref('all')
const searchQuery = ref('')
const expandedReport = ref(null)
const showEditModal = ref(false)
const editingReport = ref({
  id: null,
  status: 'pending',
  subject: '',
  message: '',
  customer_email: '',
  admin_notes: '',
  assigned_to: null,
})
const staffList = ref([])

// Email history state
const expandedEmailHistory = ref(null)
const emailHistoryCache = ref({})
const loadingEmails = ref({})
const showEmailModal = ref(false)
const emailForm = ref({
  subject: '',
  message: '',
})
const sendingEmail = ref(false)
const isFirstEmail = ref(false)
const checkingFirstEmail = ref(false)

let searchTimeout = null

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadReports()
  }, 500)
}

async function loadStats() {
  try {
    const response = await axios.get('/api/customer-reports/stats')
    if (response.data.ok) {
      stats.value = response.data.stats
    }
  } catch (error) {
    console.error('Error loading stats:', error)
  }
}

async function loadReports() {
  loading.value = true
  try {
    const params = {}
    if (filterStatus.value !== 'all') {
      params.status = filterStatus.value
    }
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }

    const response = await axios.get('/api/customer-reports', { params })
    if (response.data.ok) {
      reports.value = response.data.reports.data || []
    }
  } catch (error) {
    console.error('Error loading reports:', error)
    alert('Failed to load reports. Please try again.')
  } finally {
    loading.value = false
  }
}

async function loadStaffList() {
  try {
    const response = await axios.get('/api/admin/staff')
    if (response.data) {
      staffList.value = response.data
    }
  } catch (error) {
    console.error('Error loading staff:', error)
  }
}

function toggleExpand(id) {
  expandedReport.value = expandedReport.value === id ? null : id
}

function openEditModal(report) {
  editingReport.value = {
    id: report.id,
    status: report.status,
    subject: report.subject || '',
    message: report.message || '',
    customer_email: report.customer_email || '',
    admin_notes: report.admin_notes || '',
    assigned_to: report.assigned_to?.id || null,
  }
  showEditModal.value = true
}

function closeEditModal() {
  showEditModal.value = false
  editingReport.value = {
    id: null,
    status: 'pending',
    subject: '',
    message: '',
    customer_email: '',
    admin_notes: '',
    assigned_to: null,
  }
  // Close email modal if open
  showEmailModal.value = false
  emailForm.value = { subject: '', message: '' }
}

async function updateReport() {
  updating.value = true
  try {
    const response = await axios.put(`/api/customer-reports/${editingReport.value.id}`, {
      status: editingReport.value.status,
      admin_notes: editingReport.value.admin_notes,
      assigned_to: editingReport.value.assigned_to,
    })

    if (response.data.ok) {
      alert('Report updated successfully')
      closeEditModal()
      loadReports()
      loadStats()
    }
  } catch (error) {
    console.error('Error updating report:', error)
    alert('Failed to update report. Please try again.')
  } finally {
    updating.value = false
  }
}

async function deleteReport(report) {
  if (!confirm(`Are you sure you want to delete this report from ${report.customer_name}?`)) {
    return
  }

  try {
    const response = await axios.delete(`/api/customer-reports/${report.id}`)
    if (response.data.ok) {
      alert('Report deleted successfully')
      loadReports()
      loadStats()
    }
  } catch (error) {
    console.error('Error deleting report:', error)
    alert('Failed to delete report. Please try again.')
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

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Email history functions
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
    const response = await axios.get(`/api/customer-reports/${reportId}/emails`)
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

function openEmailModal() {
  emailForm.value = {
    subject: editingReport.value.subject || '',
    message: '',
  }
  
  // Check if this is the first email for this report
  checkingFirstEmail.value = true
  axios.get(`/api/customer-reports/${editingReport.value.id}/emails`)
    .then(response => {
      if (response.data.ok) {
        const emails = response.data.emails || []
        const hasOutboundEmails = emails.some(email => email.direction === 'outbound')
        isFirstEmail.value = !hasOutboundEmails
      } else {
        isFirstEmail.value = false
      }
    })
    .catch(error => {
      console.error('Error checking email history:', error)
      isFirstEmail.value = false
    })
    .finally(() => {
      checkingFirstEmail.value = false
    })
  
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
  if (!emailForm.value.subject.trim() || !emailForm.value.message.trim()) {
    alert('Please fill in both subject and message')
    return
  }

  sendingEmail.value = true
  try {
    const response = await axios.post(`/api/customer-reports/${editingReport.value.id}/send-email`, {
      subject: emailForm.value.subject,
      message: emailForm.value.message,
    })

    if (response.data.ok) {
      alert(response.data.message || 'Email sent successfully')
      closeEmailModal()
      // Refresh email history if it was expanded
      if (expandedEmailHistory.value === editingReport.value.id) {
        await loadEmailHistory(editingReport.value.id)
      }
    } else {
      alert(response.data.message || 'Failed to send email')
    }
  } catch (error) {
    console.error('Error sending email:', error)
    alert(error.response?.data?.message || 'Failed to send email. Please try again.')
  } finally {
    sendingEmail.value = false
  }
}

onMounted(() => {
  loadStats()
  loadReports()
  loadStaffList()
})
</script>

<style scoped>
.customer-reports-panel {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.reports-header {
  margin-bottom: 24px;
}

.reports-header h2 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1F2937;
  margin: 0 0 16px 0;
}

.reports-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
}

.stat-card {
  background: white;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  text-align: center;
}

.stat-label {
  display: block;
  font-size: 0.85rem;
  color: #6B7280;
  margin-bottom: 4px;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1F2937;
}

.stat-pending .stat-value {
  color: #F59E0B;
}

.stat-progress .stat-value {
  color: #3B82F6;
}

.stat-resolved .stat-value {
  color: #10B981;
}

.reports-controls {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  gap: 12px;
  flex: 1;
}

.filter-select,
.search-input {
  padding: 10px 16px;
  border: 1px solid #D1D5DB;
  border-radius: 6px;
  font-size: 0.95rem;
  font-family: inherit;
}

.filter-select {
  min-width: 150px;
}

.search-input {
  flex: 1;
  max-width: 300px;
}

.refresh-btn {
  padding: 10px 20px;
  background: #0066FF;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.refresh-btn:hover:not(:disabled) {
  background: #0057e6;
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.reports-table-wrapper {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.loading-state,
.empty-state {
  padding: 60px 24px;
  text-align: center;
  color: #6B7280;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 12px;
}

.reports-table {
  width: 100%;
}

.table-header {
  display: grid;
  grid-template-columns: 2fr 2fr 1fr 1.5fr 1fr;
  gap: 16px;
  padding: 16px 20px;
  background: #F9FAFB;
  border-bottom: 1px solid #E5E7EB;
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.table-row {
  border-bottom: 1px solid #E5E7EB;
  transition: background 0.2s;
}

.table-row:hover {
  background: #F9FAFB;
}

.row-main {
  display: grid;
  grid-template-columns: 2fr 2fr 1fr 1.5fr 1fr;
  gap: 16px;
  padding: 16px 20px;
  align-items: center;
  cursor: pointer;
}

.row-details {
  padding: 20px;
  background: #F9FAFB;
  border-top: 1px solid #E5E7EB;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.detail-item {
  display: flex;
  gap: 8px;
}

.detail-label {
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.detail-value {
  color: #6B7280;
  font-size: 0.9rem;
}

.message-section,
.notes-section {
  margin-bottom: 12px;
}

.message-section .detail-label,
.notes-section .detail-label {
  display: block;
  margin-bottom: 6px;
}

.message-text,
.notes-text {
  margin: 0;
  padding: 12px;
  background: white;
  border-radius: 6px;
  color: #4B5563;
  line-height: 1.6;
  font-size: 0.95rem;
}

.notes-text {
  background: #FEF3C7;
  border-left: 3px solid #F59E0B;
}

.customer-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.customer-name {
  font-weight: 600;
  color: #1F2937;
}

.customer-email {
  font-size: 0.85rem;
  color: #6B7280;
}

.subject-text {
  color: #374151;
  font-size: 0.95rem;
}

.date-text {
  color: #6B7280;
  font-size: 0.85rem;
}

.actions-cell {
  display: flex;
  gap: 8px;
}

.action-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #F3F4F6;
}

.action-btn--danger:hover {
  background: #FEE2E2;
}

.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
}

.badge--warning {
  background: #FEF3C7;
  color: #92400E;
}

.badge--info {
  background: #DBEAFE;
  color: #1E40AF;
}

.badge--success {
  background: #D1FAE5;
  color: #065F46;
}

.badge--secondary {
  background: #E5E7EB;
  color: #374151;
}

/* Modal */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 24px;
}

.modal {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #E5E7EB;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #1F2937;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6B7280;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
}

.modal-close:hover {
  background: #F3F4F6;
}

.modal-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #374151;
  font-size: 0.95rem;
}

.form-input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #D1D5DB;
  border-radius: 6px;
  font-size: 0.95rem;
  font-family: inherit;
}

.form-input:focus {
  outline: none;
  border-color: #0066FF;
  box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
}

textarea.form-input {
  resize: vertical;
  min-height: 100px;
}

.modal-footer {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding: 16px 24px;
  border-top: 1px solid #E5E7EB;
}

.btn-cancel {
  padding: 10px 20px;
  background: #F3F4F6;
  color: #6B7280;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #E5E7EB;
}

.btn-primary {
  padding: 10px 20px;
  background: #0066FF;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover:not(:disabled) {
  background: #0057e6;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-email {
  padding: 10px 20px;
  background: #10B981;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-email:hover:not(:disabled) {
  background: #059669;
}

.btn-email:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Email History Styles */
.email-history-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 2px solid #E5E7EB;
}

.email-history-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 12px;
}

.btn-toggle-email {
  padding: 8px 16px;
  background: #8B5CF6;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
  white-space: nowrap;
}

.btn-toggle-email:hover {
  background: #7C3AED;
  transform: translateY(-1px);
}

.email-history-content {
  margin-top: 16px;
  padding-right: 0;
}

.email-list {
  display: block;
}

.email-list > * {
  margin-bottom: 12px;
}

.email-list > *:last-child {
  margin-bottom: 0;
}

.email-item {
  padding: 16px;
  border-radius: 8px;
  border: 1px solid #E5E7EB;
  transition: all 0.2s;
  min-height: auto;
  height: auto;
  max-width: 100%;
  box-sizing: border-box;
}

.email-item:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.email-item.outbound {
  background: #F0FDF4;
  border-left: 4px solid #10B981;
}

.email-item.inbound {
  background: #FEF3C7;
  border-left: 4px solid #F59E0B;
}

.email-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  flex-wrap: wrap;
  gap: 8px;
}

.email-direction-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
  background: #10B981;
  color: white;
}

.email-item.inbound .email-direction-badge {
  background: #F59E0B;
}

.email-status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: capitalize;
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
  font-size: 0.85rem;
  color: #6B7280;
}

.email-subject {
  margin-bottom: 12px;
  padding: 8px 12px;
  background: white;
  border-radius: 6px;
  font-size: 0.95rem;
}

.email-participants {
  margin-bottom: 12px;
  padding: 10px 12px;
  background: white;
  border-radius: 6px;
  font-size: 0.9rem;
  line-height: 1.6;
}

.email-participants div {
  margin-bottom: 4px;
}

.email-message {
  padding: 12px;
  background: white;
  border-radius: 6px;
  color: #374151;
  line-height: 1.6;
  font-size: 0.95rem;
  white-space: pre-wrap;
  word-break: break-word;
}

.email-error {
  margin-top: 12px;
  padding: 10px 12px;
  background: #FEE2E2;
  border: 1px solid #FECACA;
  border-radius: 6px;
  color: #991B1B;
  font-size: 0.9rem;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 1024px) {
  .table-header,
  .row-main {
    grid-template-columns: 2fr 2fr 1fr 1fr;
  }

  .table-header span:nth-child(4),
  .row-main span:nth-child(4) {
    display: none;
  }
}

@media (max-width: 768px) {
  .reports-stats {
    grid-template-columns: repeat(2, 1fr);
  }

  .filter-group {
    flex-direction: column;
  }

  .search-input {
    max-width: none;
  }

  .table-header,
  .row-main {
    grid-template-columns: 1fr;
    gap: 8px;
  }

  .table-header {
    display: none;
  }

  .row-main {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .actions-cell {
    width: 100%;
    justify-content: flex-end;
  }
}
</style>