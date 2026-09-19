<template>
  <section class="account-applications">
    <header class="account-applications__header">
      <div>
        <span class="account-applications__eyebrow">Recruitment</span>
        <h2>Account Applications</h2>
        <p>Review applicants for the account positions broadcast by Main Branch.</p>
      </div>
      <button type="button" class="account-applications__refresh" @click="loadApplications" :disabled="loading">
        {{ loading ? 'Loading...' : 'Refresh' }}
      </button>
    </header>

    <div v-if="error" class="account-applications__error">{{ error }}</div>
    <div v-else-if="loading" class="account-applications__empty">Loading applications...</div>
    <div v-else-if="applications.length === 0" class="account-applications__empty">No account applications found.</div>

    <div v-else class="account-applications__list">
      <article v-for="application in applications" :key="application.id" class="account-application-card">
        <div class="account-application-card__top">
          <div>
            <h3>{{ application.applicant_full_name }}</h3>
            <p>{{ application.job_title || 'Account position' }} <span>•</span> {{ application.department || 'CUSTOM' }}</p>
          </div>
          <span class="account-application-card__status">{{ application.status || 'Submitted' }}</span>
        </div>

        <div class="account-application-card__details">
          <span>{{ application.applicant_email }}</span>
          <span>{{ application.applicant_phone || 'No phone provided' }}</span>
          <span>Applied {{ formatDate(application.created_at) }}</span>
        </div>

        <div class="account-application-card__actions">
          <button type="button" class="button button--secondary" @click="viewDetails(application)">View Details</button>
          <button
            v-if="!isReady(application.status) && !isPassed(application.status) && !isNotPassed(application.status)"
            type="button"
            class="button button--primary"
            @click="openInterviewSchedule(application)"
            :disabled="processingId === application.id"
          >
            {{ processingId === application.id ? 'Sending...' : 'Ready for Interview' }}
          </button>
          <button
            v-if="isReady(application.status)"
            type="button"
            class="button button--success"
            @click="markPassed(application)"
            :disabled="processingId === application.id"
          >
            Mark as Passed
          </button>
          <button
            v-if="isReady(application.status)"
            type="button"
            class="button button--danger"
            @click="markNotPassed(application)"
            :disabled="processingId === application.id"
          >
            Mark as Not Passed
          </button>
        </div>
      </article>
    </div>

    <div v-if="showInterviewModal" class="account-application-modal" @click.self="closeInterviewSchedule">
      <div class="account-application-modal__body interview-modal">
        <button type="button" class="account-application-modal__close" @click="closeInterviewSchedule">×</button>
        <h3>Schedule Interview</h3>
        <p class="interview-modal__subtitle">Select date and time for the interview with {{ interviewApplication?.applicant_full_name }}</p>

        <div class="interview-form">
          <label>
            <span>Interview Date *</span>
            <input v-model="interviewSchedule.date" type="date" :min="getMinDate" required>
          </label>
          <label>
            <span>Interview Time *</span>
            <input v-model="interviewSchedule.time" type="time" required>
          </label>
          <label>
            <span>Additional Notes (Optional)</span>
            <textarea v-model="interviewSchedule.notes" rows="3" placeholder="e.g., Bring portfolio, interview with HR Manager, etc."></textarea>
          </label>
        </div>

        <div v-if="interviewSchedule.date && interviewSchedule.time" class="interview-summary">
          <strong>Interview Scheduled:</strong>
          <span>{{ formatInterviewDate(interviewSchedule.date) }} at {{ formatInterviewTime(interviewSchedule.time) }}</span>
        </div>

        <div class="interview-modal__footer">
          <button type="button" class="button button--secondary" @click="closeInterviewSchedule">Cancel</button>
          <button type="button" class="button button--primary" @click="sendInterview" :disabled="!isInterviewScheduleValid || processingId === interviewApplication?.id">
            {{ processingId === interviewApplication?.id ? 'Sending...' : 'Send Interview Email' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="selectedApplication" class="account-application-modal" @click.self="selectedApplication = null">
      <div class="account-application-modal__body">
        <button type="button" class="account-application-modal__close" @click="selectedApplication = null">×</button>
        <h3>{{ selectedApplication.applicant_full_name }}</h3>
        <dl>
          <dt>Email</dt><dd>{{ selectedApplication.applicant_email || '-' }}</dd>
          <dt>Phone</dt><dd>{{ selectedApplication.applicant_phone || '-' }}</dd>
          <dt>Address</dt><dd>{{ selectedApplication.applicant_address || '-' }}</dd>
          <dt>Position</dt><dd>{{ selectedApplication.job_title || '-' }}</dd>
          <dt>Experience</dt><dd>{{ selectedApplication.years_of_experience || 0 }} years</dd>
          <dt>Education</dt><dd>{{ selectedApplication.education || '-' }}</dd>
          <dt>Cover Letter</dt><dd>{{ selectedApplication.cover_letter || '-' }}</dd>
        </dl>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const applications = ref([])
const loading = ref(false)
const error = ref('')
const processingId = ref(null)
const selectedApplication = ref(null)
const showInterviewModal = ref(false)
const interviewApplication = ref(null)
const interviewSchedule = ref({ date: '', time: '', notes: '' })

function formatDate(value) {
  if (!value) return 'N/A'
  return new Date(value).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
}

function normalizedStatus(status) {
  return String(status || '').toLowerCase()
}

function isReady(status) {
  return normalizedStatus(status).includes('ready for interview')
}

function isPassed(status) {
  return normalizedStatus(status).includes('passed')
}

function isNotPassed(status) {
  return normalizedStatus(status).includes('not passed')
}

const getMinDate = new Date().toISOString().split('T')[0]

const isInterviewScheduleValid = computed(() => Boolean(interviewSchedule.value.date && interviewSchedule.value.time))

function formatInterviewDate(value) {
  return new Date(`${value}T00:00:00`).toLocaleDateString('en-US', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
  })
}

function formatInterviewTime(value) {
  const [hours, minutes] = value.split(':')
  const date = new Date()
  date.setHours(Number(hours), Number(minutes), 0, 0)
  return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
}

function viewDetails(application) {
  selectedApplication.value = application
}

async function loadApplications() {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get('/api/hr/positions/applications', { withCredentials: true })
    applications.value = response.data?.ok ? (response.data.applications || []) : []
    if (!response.data?.ok) error.value = response.data?.message || 'Failed to load applications.'
  } catch (exception) {
    error.value = exception.response?.data?.message || 'Failed to load applications.'
  } finally {
    loading.value = false
  }
}

function openInterviewSchedule(application) {
  interviewApplication.value = application
  interviewSchedule.value = { date: '', time: '', notes: '' }
  showInterviewModal.value = true
}

function closeInterviewSchedule() {
  showInterviewModal.value = false
  interviewApplication.value = null
  interviewSchedule.value = { date: '', time: '', notes: '' }
}

async function sendInterview() {
  if (!interviewApplication.value || !isInterviewScheduleValid.value) return
  await processApplication(interviewApplication.value, `/api/hr/positions/applications/${interviewApplication.value.id}/send-interview-email`, {
    interview_date: interviewSchedule.value.date,
    interview_time: interviewSchedule.value.time,
    notes: interviewSchedule.value.notes,
  }, 'Ready for Interview')
  closeInterviewSchedule()
}

async function markPassed(application) {
  const result = await Swal.fire({
    title: 'Mark applicant as passed?',
    text: application.applicant_full_name,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Mark as Passed',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#16834b',
  })
  if (!result.isConfirmed) return
  await processApplication(application, `/api/hr/positions/applications/${application.id}/mark-as-passed`, {}, 'Passed - Ready for Hiring')
}

async function markNotPassed(application) {
  const result = await Swal.fire({
    title: 'Mark applicant as not passed?',
    text: application.applicant_full_name,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Mark as Not Passed',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#b42318',
  })
  if (!result.isConfirmed) return
  await processApplication(application, `/api/hr/positions/applications/${application.id}/mark-as-not-passed`, {}, 'Not Passed')
}

async function processApplication(application, url, payload, nextStatus) {
  processingId.value = application.id
  error.value = ''
  try {
    const response = await axios.post(url, payload, { withCredentials: true })
    if (!response.data?.ok) throw new Error(response.data?.message || 'Application update failed.')
    application.status = nextStatus
    await Swal.fire({
      title: 'Success',
      text: response.data.message || 'Application updated successfully.',
      icon: 'success',
      confirmButtonColor: '#16834b',
    })
  } catch (exception) {
    error.value = exception.response?.data?.message || exception.message || 'Application update failed.'
    await Swal.fire({
      title: 'Action failed',
      text: error.value,
      icon: 'error',
      confirmButtonColor: '#b42318',
    })
  } finally {
    processingId.value = null
  }
}

onMounted(loadApplications)
</script>

<style scoped>
.account-applications { padding: 18px; background: #fff; border-radius: 14px; box-shadow: 0 6px 12px rgba(17, 24, 39, 0.04); }
.account-applications__header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding-bottom: 14px; border-bottom: 1px solid #f0f2f5; }
.account-applications__eyebrow { color: #c25a12; font-size: 10px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.account-applications h2 { margin: 4px 0; color: #1f2937; font-size: 21px; }
.account-applications__header p { margin: 0; color: #64748b; font-size: 13px; }
.account-applications__refresh, .button { border: 0; border-radius: 8px; padding: 9px 12px; font-weight: 700; cursor: pointer; }
.account-applications__refresh { background: #172337; color: #fff; }
.account-applications__list { display: grid; gap: 12px; margin-top: 16px; }
.account-application-card { padding: 15px; border: 1px solid #eadfd7; border-radius: 12px; background: #fffaf6; }
.account-application-card__top { display: flex; justify-content: space-between; gap: 12px; }
.account-application-card h3 { margin: 0; color: #172337; font-size: 16px; }
.account-application-card p { margin: 4px 0 0; color: #756b66; font-size: 13px; }
.account-application-card__status { align-self: flex-start; padding: 5px 8px; border-radius: 999px; background: #fff0df; color: #9a4b12; font-size: 11px; font-weight: 700; }
.account-application-card__details { display: flex; flex-wrap: wrap; gap: 8px 18px; margin: 12px 0; color: #64748b; font-size: 12px; }
.account-application-card__actions { display: flex; flex-wrap: wrap; gap: 8px; }
.button--secondary { background: #e8eef5; color: #25364d; }
.button--primary { background: #172337; color: #fff; }
.button--success { background: #16834b; color: #fff; }
.button--danger { background: #b42318; color: #fff; }
.account-applications__empty, .account-applications__error { padding: 26px 0; color: #64748b; }
.account-applications__error { color: #b42318; }
.account-application-modal { position: fixed; inset: 0; z-index: 1000; display: grid; place-items: center; padding: 20px; background: rgba(15, 23, 42, .42); }
.account-application-modal__body { position: relative; width: min(650px, 100%); max-height: 85vh; overflow: auto; padding: 24px; border-radius: 14px; background: #fff; }
.account-application-modal__close { position: absolute; top: 10px; right: 14px; border: 0; background: transparent; color: #64748b; font-size: 24px; cursor: pointer; }
.account-application-modal dl { display: grid; grid-template-columns: 120px 1fr; gap: 10px; margin: 18px 0 0; font-size: 13px; }
.account-application-modal dt { color: #64748b; font-weight: 700; }
.account-application-modal dd { margin: 0; color: #1f2937; white-space: pre-wrap; }
.interview-modal { max-width: 560px; }
.interview-modal h3 { margin: 0; color: #1f2937; font-size: 21px; }
.interview-modal__subtitle { margin: 6px 0 20px; color: #64748b; font-size: 13px; }
.interview-form { display: grid; gap: 14px; }
.interview-form label { display: grid; gap: 6px; color: #475569; font-size: 12px; font-weight: 700; }
.interview-form input, .interview-form textarea { width: 100%; box-sizing: border-box; border: 1px solid #d9dee7; border-radius: 8px; padding: 10px; color: #1f2937; font: inherit; }
.interview-form textarea { resize: vertical; }
.interview-summary { display: grid; gap: 4px; margin-top: 16px; padding: 11px 12px; border-radius: 8px; background: #eef8f1; color: #166534; font-size: 13px; }
.interview-modal__footer { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }
.button:disabled { cursor: not-allowed; opacity: .55; }
</style>
