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
            :disabled="processingId === application.id || !isInterviewTimeReached(application)"
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
          <dt>Ready to Work Date</dt><dd>{{ formatDate(selectedApplication.available_start_date) }}</dd>
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

function isInterviewTimeReached(application) {
  if (!application?.interview_date || !application?.interview_time) return false
  const interviewAt = new Date(`${application.interview_date}T${application.interview_time}`)
  return !Number.isNaN(interviewAt.getTime()) && Date.now() >= interviewAt.getTime()
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
.account-applications {
  min-width: 0;
  padding: clamp(16px, 2vw, 24px);
  border: 1px solid #eadfd7;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 8px 24px rgba(17, 24, 39, 0.045);
  color: #1f2937;
}

.account-applications__header {
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
  padding: 14px 0 16px;
  border-bottom: 1px solid #edf0f3;
}

.account-applications__header::before {
  content: '';
  position: absolute;
  inset: 0 0 auto;
  height: 3px;
  background: linear-gradient(90deg, #f97316 0%, #fb923c 68%, #fbbf24 100%);
}

.account-applications__eyebrow {
  display: inline-block;
  margin-bottom: 6px;
  color: #c25a12;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.account-applications h2 {
  margin: 0;
  color: #172337;
  font-size: clamp(21px, 2vw, 25px);
  font-weight: 750;
  letter-spacing: -0.025em;
  line-height: 1.25;
}

.account-applications__header p {
  margin: 7px 0 0;
  color: #64748b;
  font-size: 13px;
  line-height: 1.5;
}

.account-applications__refresh,
.button {
  display: inline-flex;
  min-height: 38px;
  align-items: center;
  justify-content: center;
  border: 1px solid transparent;
  border-radius: 9px;
  padding: 9px 14px;
  font: inherit;
  font-size: 12px;
  font-weight: 700;
  line-height: 1.2;
  cursor: pointer;
  transition: background-color 160ms ease, border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
}

.account-applications__refresh {
  flex: 0 0 auto;
  margin-top: 1px;
  background: #172337;
  color: #fff;
}

.account-applications__refresh:hover:not(:disabled),
.button:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 5px 12px rgba(23, 35, 55, 0.12);
}

.account-applications__refresh:focus-visible,
.button:focus-visible,
.account-application-modal__close:focus-visible {
  outline: 3px solid rgba(194, 90, 18, 0.28);
  outline-offset: 2px;
}

.account-applications__refresh:disabled,
.button:disabled {
  cursor: not-allowed;
  opacity: 0.58;
}

.account-applications__list {
  display: grid;
  gap: 12px;
  margin-top: 14px;
}

.account-application-card {
  min-width: 0;
  padding: 17px 18px;
  border: 1px solid #eadfd7;
  border-radius: 12px;
  background: #fffaf6;
  transition: border-color 160ms ease, box-shadow 160ms ease;
}

.account-application-card:hover {
  border-color: #e5c7b2;
  box-shadow: 0 5px 16px rgba(23, 35, 55, 0.055);
}

.account-application-card__top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.account-application-card__top > div {
  min-width: 0;
}

.account-application-card h3 {
  margin: 0;
  color: #172337;
  font-size: 16px;
  font-weight: 750;
  line-height: 1.35;
  overflow-wrap: anywhere;
}

.account-application-card p {
  margin: 4px 0 0;
  color: #756b66;
  font-size: 13px;
  line-height: 1.5;
}

.account-application-card p span {
  padding: 0 3px;
  color: #b9a79a;
}

.account-application-card__status {
  flex: 0 0 auto;
  align-self: flex-start;
  max-width: 100%;
  padding: 6px 10px;
  border: 1px solid #f4dfc9;
  border-radius: 999px;
  background: #fff0df;
  color: #9a4b12;
  font-size: 11px;
  font-weight: 750;
  line-height: 1.25;
  text-align: center;
}

.account-application-card__details {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px 18px;
  margin: 15px 0 0;
  padding: 12px 0;
  border-top: 1px solid #efe5dd;
  border-bottom: 1px solid #efe5dd;
  color: #526174;
  font-size: 12px;
  line-height: 1.5;
}

.account-application-card__details span {
  min-width: 0;
  overflow-wrap: anywhere;
}

.account-application-card__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
}

.button--secondary {
  background: #e8eef5;
  color: #25364d;
}

.button--secondary:hover:not(:disabled) {
  background: #dce5ef;
}

.button--primary {
  background: #172337;
  color: #fff;
}

.button--primary:hover:not(:disabled),
.account-applications__refresh:hover:not(:disabled) {
  background: #263b58;
}

.button--success {
  background: #16834b;
  color: #fff;
}

.button--success:hover:not(:disabled) {
  background: #116c3d;
}

.button--danger {
  background: #b42318;
  color: #fff;
}

.button--danger:hover:not(:disabled) {
  background: #941d14;
}

.account-applications__empty,
.account-applications__error {
  margin-top: 16px;
  padding: 28px 18px;
  border: 1px dashed #d9dee7;
  border-radius: 11px;
  background: #fafbfc;
  color: #64748b;
  font-size: 13px;
  text-align: center;
}

.account-applications__error {
  border-color: #f0c8c4;
  background: #fff7f6;
  color: #b42318;
}

.account-application-modal {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: grid;
  place-items: center;
  overflow-y: auto;
  padding: 20px;
  background: rgba(15, 23, 42, 0.48);
  backdrop-filter: blur(3px);
}

.account-application-modal__body {
  position: relative;
  width: min(650px, 100%);
  max-height: min(85vh, 800px);
  overflow: auto;
  padding: 26px;
  border: 1px solid #eadfd7;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 24px 64px rgba(15, 23, 42, 0.22);
}

.account-application-modal__body > h3 {
  margin: 0;
  padding-right: 30px;
  color: #172337;
  font-size: 21px;
}

.account-application-modal__close {
  position: absolute;
  top: 12px;
  right: 14px;
  display: grid;
  width: 34px;
  height: 34px;
  place-items: center;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: #64748b;
  font-size: 24px;
  cursor: pointer;
}

.account-application-modal__close:hover {
  background: #f1f5f9;
  color: #172337;
}

.account-application-modal dl {
  display: grid;
  grid-template-columns: minmax(105px, 0.35fr) minmax(0, 1fr);
  gap: 0;
  margin: 18px 0 0;
  font-size: 13px;
}

.account-application-modal dt,
.account-application-modal dd {
  padding: 10px 0;
  border-top: 1px solid #edf0f3;
}

.account-application-modal dt {
  color: #64748b;
  font-weight: 700;
}

.account-application-modal dd {
  min-width: 0;
  margin: 0;
  color: #1f2937;
  overflow-wrap: anywhere;
  white-space: pre-wrap;
}

.interview-modal {
  max-width: 560px;
}

.interview-modal h3 {
  margin: 0;
  color: #1f2937;
  font-size: 21px;
}

.interview-modal__subtitle {
  margin: 6px 0 20px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.5;
}

.interview-form {
  display: grid;
  gap: 14px;
}

.interview-form label {
  display: grid;
  gap: 6px;
  color: #475569;
  font-size: 12px;
  font-weight: 700;
}

.interview-form input,
.interview-form textarea {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #d9dee7;
  border-radius: 8px;
  padding: 10px 11px;
  background: #fff;
  color: #1f2937;
  font: inherit;
}

.interview-form input:focus,
.interview-form textarea:focus {
  border-color: #c25a12;
  outline: 3px solid rgba(194, 90, 18, 0.13);
}

.interview-form textarea {
  resize: vertical;
}

.interview-summary {
  display: grid;
  gap: 4px;
  margin-top: 16px;
  padding: 12px;
  border: 1px solid #cfe7d5;
  border-radius: 9px;
  background: #eef8f1;
  color: #166534;
  font-size: 13px;
}

.interview-modal__footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 20px;
}

@media (max-width: 720px) {
  .account-application-card__details {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 520px) {
  .account-applications {
    padding: 15px;
    border-radius: 13px;
  }

  .account-applications__header {
    flex-direction: column;
    gap: 12px;
  }

  .account-applications__refresh {
    align-self: flex-start;
    min-height: 36px;
  }

  .account-application-card {
    padding: 14px;
  }

  .account-application-card__top {
    flex-direction: column;
    gap: 9px;
  }

  .account-application-card__details {
    grid-template-columns: minmax(0, 1fr);
    gap: 7px;
  }

  .account-application-card__actions {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
  }

  .account-application-card__actions .button {
    width: 100%;
  }

  .account-application-modal {
    padding: 12px;
  }

  .account-application-modal__body {
    padding: 22px 18px;
  }

  .account-application-modal dl {
    grid-template-columns: minmax(84px, 0.4fr) minmax(0, 1fr);
  }

  .interview-modal__footer {
    flex-direction: column-reverse;
  }

  .interview-modal__footer .button {
    width: 100%;
  }
}
</style>
