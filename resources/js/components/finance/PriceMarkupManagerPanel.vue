<template>
  <div class="price-markup-manager-section">
    <!-- Financial Charts Section -->
    <div class="financial-charts-section">
      <div class="charts-container">
        <!-- Income Chart -->
        <div class="financial-chart-card">
          <div class="card-header">
            <h3 class="card-title">Income Report</h3>
            <span class="refresh-btn" @click="fetchFinancialData" :class="{ loading: isLoadingCharts }">↻</span>
          </div>
          <div v-if="isLoadingCharts" class="chart-loading-container">
            <div class="loading-spinner"></div>
          </div>
          <div v-else class="chart-wrapper">
            <Chart type="bar" :data="incomeChartData" :options="chartOptions" />
            <div class="chart-info">
              <p class="chart-value">₱{{ formatCurrency(financialData.totalRevenue) }}</p>
              <p class="chart-label">Total Income</p>
            </div>
          </div>
        </div>

        <!-- Expenses Chart -->
        <div class="financial-chart-card">
          <div class="card-header">
            <h3 class="card-title">Expenses Report</h3>
            <span class="refresh-btn" @click="fetchFinancialData" :class="{ loading: isLoadingCharts }">↻</span>
          </div>
          <div v-if="isLoadingCharts" class="chart-loading-container">
            <div class="loading-spinner"></div>
          </div>
          <div v-else class="chart-wrapper">
            <Chart type="bar" :data="expensesChartData" :options="chartOptions" />
            <div class="chart-info">
              <p class="chart-value">₱{{ formatCurrency(financialData.totalExpenses) }}</p>
              <p class="chart-label">Total Expenses</p>
            </div>
          </div>
        </div>

        <!-- Profit Chart -->
        <div class="financial-chart-card">
          <div class="card-header">
            <h3 class="card-title">Profit Report</h3>
            <span class="refresh-btn" @click="fetchFinancialData" :class="{ loading: isLoadingCharts }">↻</span>
          </div>
          <div v-if="isLoadingCharts" class="chart-loading-container">
            <div class="loading-spinner"></div>
          </div>
          <div v-else class="chart-wrapper">
            <Chart type="bar" :data="profitChartData" :options="chartOptions" />
            <div class="chart-info">
              <p class="chart-value" :class="{ 'profit-positive': financialData.netProfit >= 0, 'profit-negative': financialData.netProfit < 0 }">
                ₱{{ formatCurrency(financialData.netProfit) }}
              </p>
              <p class="chart-label">Net Profit</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Current Percentage Display & Request Form / Approval Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
      <!-- Current Markup Display -->
      <div class="price-markup-card current-markup">
        <div class="card-header">
          <h3 class="card-title">Current Price Markup</h3>
          <span class="refresh-btn" @click="fetchCurrentPercentage" :class="{ loading: isLoadingCurrent }">↻</span>
        </div>
        <div v-if="isLoadingCurrent" class="chart-loading-container">
          <div class="loading-spinner"></div>
        </div>
        <div v-else class="chart-wrapper">
          <div class="chart-canvas-wrap">
            <Chart type="doughnut" :data="markupChartData" :options="markupChartOptions" />
            <div class="chart-center-overlay">
              <p class="center-value" style="color: #8B5CF6;">{{ currentPercentage }}%</p>
            </div>
          </div>
          <div class="chart-info">
            <p class="chart-label">Markup Multiplier: ×{{ currentMultiplier }}</p>
          </div>
        </div>
      </div>

      <!-- Request Form (For Branch Finance Managers) OR Approval Section (For Main Branch Finance Manager) -->
      <div v-if="props.isMainBranchFinance">
        <!-- Show Approval Section Instead -->
        <PriceMarkupMainFinancePanel :branchId="null" />
      </div>
      <div v-else class="price-markup-card request-form">
        <div class="card-header">
          <h3 class="card-title">Request Percentage Change</h3>
        </div>

        <!-- Show Pending Request Alert if Exists -->
        <div v-if="pendingRequest" class="alert alert-warning">
          <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div>
            <strong>Pending Request</strong>
            <p>{{ pendingRequest.current_percentage }}% → {{ pendingRequest.requested_percentage }}%</p>
            <p class="text-sm">Status: <span :class="['status-badge', getStatusClass(pendingRequest)]">{{ formatStatus(pendingRequest) }}</span></p>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitRequest" class="form-group">
          <div class="form-field">
            <label for="requested_pct">Requested Percentage <span class="required">*</span></label>
            <div class="input-with-unit">
              <input
                id="requested_pct"
                v-model.number="form.requested_percentage"
                type="number"
                min="1"
                max="100"
                step="0.01"
                placeholder="e.g., 25.00"
                required
                @input="updateMultiplierPreview"
                :disabled="isSubmitting || !!pendingRequest"
              />
              <span class="unit-suffix">%</span>
            </div>
            <p v-if="form.requested_percentage" class="preview-text">
              Multiplier: ×{{ (1 + form.requested_percentage / 100).toFixed(2) }}
            </p>
            <p v-if="form.requested_percentage === currentPercentage" class="error-text">
              ⚠ This is the same as current percentage
            </p>
          </div>

          <div class="form-field">
            <label for="reason">Reason for Change</label>
            <textarea
              id="reason"
              v-model="form.reason"
              placeholder="e.g., Increased operational costs, market adjustment..."
              rows="4"
              :disabled="isSubmitting || !!pendingRequest"
            ></textarea>
            <p class="help-text">Optional, but helps with approval process</p>
          </div>

          <div class="form-actions">
            <button
              type="submit"
              class="btn btn-primary"
              :disabled="isSubmitting || !isFormValid || !!pendingRequest"
            >
              <span v-if="isSubmitting" class="btn-loading">
                <span class="spinner"></span> Submitting...
              </span>
              <span v-else>Submit Request for Approval</span>
            </button>
          </div>

          <div v-if="formError" class="alert alert-error">
            <strong>Error:</strong> {{ formError }}
          </div>
        </form>
      </div>
    </div>

    <!-- Success Message -->
    <transition name="fade">
      <div v-if="showSuccessMessage" class="alert alert-success">
        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <div>
          <strong>Request Submitted!</strong>
          <p>Your percentage change request has been submitted for approval.</p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { Chart } from 'vue-chartjs'
import PriceMarkupMainFinancePanel from './PriceMarkupMainFinancePanel.vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Colors
} from 'chart.js'

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Colors
)

const props = defineProps({
  branchId: {
    type: Number,
    required: true
  },
  isMainBranchFinance: {
    type: Boolean,
    default: false
  }
})

console.log('[PriceMarkupManagerPanel] Props received:', {
  branchId: props.branchId,
  isMainBranchFinance: props.isMainBranchFinance
})

// State - Financial Data
const financialData = ref({
  totalRevenue: 0,
  totalExpenses: 0,
  netProfit: 0,
  pendingApprovals: 0,
  totalOrders: 0
})

const isLoadingCharts = ref(false)

// Chart Options
const chartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  indexAxis: 'y',
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          return '₱' + context.parsed.x.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        }
      }
    }
  },
  scales: {
    x: {
      beginAtZero: true,
      ticks: {
        callback: function(value) {
          return '₱' + (value / 1000).toFixed(0) + 'k'
        }
      }
    }
  }
}

// Chart Data
const incomeChartData = computed(() => ({
  labels: ['Income'],
  datasets: [
    {
      label: 'Total Income',
      data: [financialData.value.totalRevenue],
      backgroundColor: '#10B981',
      borderColor: '#059669',
      borderWidth: 2,
      borderRadius: 8,
      barThickness: 60
    }
  ]
}))

const expensesChartData = computed(() => ({
  labels: ['Expenses'],
  datasets: [
    {
      label: 'Total Expenses',
      data: [financialData.value.totalExpenses],
      backgroundColor: '#F59E0B',
      borderColor: '#D97706',
      borderWidth: 2,
      borderRadius: 8,
      barThickness: 60
    }
  ]
}))

const profitChartData = computed(() => ({
  labels: ['Profit'],
  datasets: [
    {
      label: 'Net Profit',
      data: [financialData.value.netProfit],
      backgroundColor: financialData.value.netProfit >= 0 ? '#3B82F6' : '#EF4444',
      borderColor: financialData.value.netProfit >= 0 ? '#1D4ED8' : '#DC2626',
      borderWidth: 2,
      borderRadius: 8,
      barThickness: 60
    }
  ]
}))

// Markup Percentage Chart Data
const markupChartData = computed(() => ({
  labels: ['Markup %', 'Remaining'],
  datasets: [
    {
      data: [currentPercentage.value, 100 - currentPercentage.value],
      backgroundColor: ['#8B5CF6', '#E5E7EB'],
      borderColor: ['#7C3AED', '#D1D5DB'],
      borderWidth: 2
    }
  ]
}))

const markupChartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          return context.label + ': ' + context.parsed + '%'
        }
      }
    }
  }
}

// State - Price Markup Data
const currentPercentage = ref(20.00)
const currentMultiplier = ref(1.20)
const pendingRequest = ref(null)
const isLoadingCurrent = ref(false)
const isSubmitting = ref(false)
const showSuccessMessage = ref(false)
const formError = ref('')

const form = ref({
  requested_percentage: null,
  reason: ''
})

// Computed
const isFormValid = computed(() => {
  return (
    form.value.requested_percentage &&
    form.value.requested_percentage > 0 &&
    form.value.requested_percentage <= 100 &&
    form.value.requested_percentage !== currentPercentage.value
  )
})

// Methods - Financial Data
function formatCurrency(value) {
  return value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function fetchFinancialData() {
  isLoadingCharts.value = true
  try {
    const res = await axios.get('/api/manager/finance/dashboard', {
      params: { range: 'all' },
      withCredentials: true
    })
    if (res.data.ok) {
      financialData.value = {
        totalRevenue: res.data.totalRevenue || 0,
        totalExpenses: res.data.totalExpenses || 0,
        netProfit: res.data.netProfit || 0,
        pendingApprovals: res.data.pendingApprovals || 0,
        totalOrders: res.data.totalOrders || 0
      }
    }
  } catch (error) {
    console.error('Error fetching financial data:', error)
  } finally {
    isLoadingCharts.value = false
  }
}

// Methods - Price Markup
async function fetchCurrentPercentage() {
  if (!props.branchId || isNaN(props.branchId)) return

  isLoadingCurrent.value = true
  try {
    const res = await axios.get(`/api/price-markup/current/${props.branchId}`)
    if (res.data.ok) {
      currentPercentage.value = res.data.current_percentage
      currentMultiplier.value = res.data.multiplier
    }
  } catch (error) {
    console.error('Error fetching current percentage:', error)
  } finally {
    isLoadingCurrent.value = false
  }
}

async function fetchPendingRequest() {
  if (!props.branchId || isNaN(props.branchId)) return

  try {
    const res = await axios.get(`/api/price-markup/pending/${props.branchId}`)
    if (res.data.ok && res.data.requests.length > 0) {
      pendingRequest.value = res.data.requests[0]
    } else {
      pendingRequest.value = null
    }
  } catch (error) {
    console.error('Error fetching pending requests:', error)
  }
}

async function submitRequest() {
  if (!isFormValid.value) return

  isSubmitting.value = true
  formError.value = ''
  showSuccessMessage.value = false

  try {
    const res = await axios.post('/api/price-markup/request', {
      branch_id: props.branchId,
      requested_percentage: form.value.requested_percentage,
      reason: form.value.reason
    })

    if (res.data.ok) {
      form.value.requested_percentage = null
      form.value.reason = ''
      showSuccessMessage.value = true
      await fetchPendingRequest()

      setTimeout(() => {
        showSuccessMessage.value = false
      }, 5000)
    }
  } catch (error) {
    if (error.response?.data?.message) {
      formError.value = error.response.data.message
    } else {
      formError.value = 'Failed to submit request. Please try again.'
    }
  } finally {
    isSubmitting.value = false
  }
}

function updateMultiplierPreview() {
  // Computed automatically updates
}

function getStatusClass(request) {
  if (request.main_finance_approval === 'rejected' || request.owner_approval === 'rejected') {
    return 'status-rejected'
  }
  if (request.main_finance_approval === 'approved' && request.owner_approval === 'pending') {
    return 'status-pending'
  }
  return 'status-pending'
}

function formatStatus(request) {
  if (request.main_finance_approval === 'rejected') {
    return 'Rejected by Finance'
  }
  if (request.owner_approval === 'rejected') {
    return 'Rejected by Owner'
  }
  if (request.main_finance_approval === 'pending') {
    return 'Awaiting Finance Approval'
  }
  if (request.owner_approval === 'pending') {
    return 'Awaiting Owner Approval'
  }
  return 'Processing'
}

// Lifecycle
let financialInterval = null
let markupInterval = null

onMounted(() => {
  // Fetch financial data immediately
  fetchFinancialData()
  // Refresh financial data every 60 seconds
  financialInterval = setInterval(() => {
    fetchFinancialData()
  }, 60000)

  fetchCurrentPercentage()
  fetchPendingRequest()
  // Refresh markup data every 30 seconds
  markupInterval = setInterval(() => {
    fetchCurrentPercentage()
    fetchPendingRequest()
  }, 30000)
})

onUnmounted(() => {
  if (financialInterval) clearInterval(financialInterval)
  if (markupInterval) clearInterval(markupInterval)
})
</script>

<style scoped>
.price-markup-manager-section {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
  margin-bottom: 24px;
}

/* Financial Charts Section */
.financial-charts-section {
  width: 100%;
}

.charts-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-bottom: 24px;
}

@media (max-width: 1400px) {
  .charts-container {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .charts-container {
    grid-template-columns: 1fr;
  }
}

.financial-chart-card {
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.chart-wrapper {
  display: flex;
  flex-direction: column;
  gap: 16px;
  height: 420px; /* increased to allow larger donut and center label */
}

.chart-wrapper > div:first-child {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chart-info {
  text-align: center;
  padding: 12px 0;
  border-top: 1px solid #F3F4F6;
}

.chart-value {
  font-size: 24px;
  font-weight: 700;
  margin: 0 0 4px 0;
  color: #1F2937;
}

.chart-value.profit-positive {
  color: #059669;
}

.chart-value.profit-negative {
  color: #DC2626;
}

.chart-label {
  font-size: 12px;
  color: #6B7280;
  margin: 0;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.chart-loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 420px;
}

/* Ensure Chart.js canvas scales responsively even when Chart sets inline sizes */
.chart-wrapper > div:first-child {
  flex: 1 1 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 0; /* allow shrinking in flex */
}
.chart-wrapper > div:first-child canvas,
.chart-wrapper > div:first-child canvas[data-v-06192ab6] {
  max-width: 100% !important;
  width: 100% !important;
  height: auto !important;
  display: block !important;
  box-sizing: border-box !important;
}

/* Also target canvas inside our canvas wrapper to ensure it never exceeds the wrapper */
.chart-canvas-wrap canvas,
.chart-canvas-wrap canvas[data-v-06192ab6] {
  max-width: 100% !important;
  width: 100% !important;
  height: auto !important;
  display: block !important;
}

@media (max-width: 520px) {
  .chart-wrapper { height: 300px !important; }
  .chart-loading-container { height: 300px !important; }
  .price-markup-card .chart-wrapper canvas { width: min(260px, 100%) !important; }
}

/* Make chart-info stick below the chart area and avoid overlap */
.chart-info {
  text-align: center;
  padding: 12px 0;
  border-top: 1px solid #F3F4F6;
  position: relative;
  z-index: 1;
}

/* Markup Section Styles */
.price-markup-card {
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  overflow: hidden; /* prevent chart from overflowing the card */
}

/* Center overlay inside donut chart */
.chart-canvas-wrap { position: relative; width: 100%; display:flex; align-items:center; justify-content:center; max-width:420px; margin:0 auto }
.chart-center-overlay { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 3; pointer-events: none; display:flex; align-items:center; justify-content:center }
.center-value { font-size: 28px; font-weight: 800; margin:0; }

.chart-info { margin-top: 8px }

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.card-title {
  font-size: 18px;
  font-weight: 600;
  color: #1F2937;
  margin: 0;
}

.refresh-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: #F3F4F6;
  border: 1px solid #D1D5DB;
  border-radius: 6px;
  cursor: pointer;
  font-size: 18px;
  transition: all 0.2s;
}

.refresh-btn:hover {
  background: #E5E7EB;
}

.refresh-btn.loading {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.current-info {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.percentage-display {
  display: flex;
  align-items: baseline;
  gap: 12px;
}

.percentage-value {
  font-size: 48px;
  font-weight: 700;
  color: #059669;
}

.multiplier-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #D1FAE5;
  color: #047857;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
}

.description {
  color: #6B7280;
  margin: 0;
}

.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 120px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #E5E7EB;
  border-top-color: #059669;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-field label {
  font-weight: 600;
  color: #1F2937;
  font-size: 14px;
}

.required {
  color: #DC2626;
}

.input-with-unit {
  position: relative;
  display: flex;
  align-items: center;
}

.input-with-unit input {
  width: 100%;
  padding: 10px 40px 10px 12px;
  border: 1px solid #D1D5DB;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.input-with-unit input:focus {
  outline: none;
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

.input-with-unit input:disabled {
  background: #F9FAFB;
  color: #9CA3AF;
  cursor: not-allowed;
}

.unit-suffix {
  position: absolute;
  right: 12px;
  color: #6B7280;
  font-weight: 600;
}

textarea {
  padding: 12px;
  border: 1px solid #D1D5DB;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
  resize: vertical;
  transition: border-color 0.2s;
}

textarea:focus {
  outline: none;
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

textarea:disabled {
  background: #F9FAFB;
  color: #9CA3AF;
  cursor: not-allowed;
}

.preview-text {
  font-size: 12px;
  color: #059669;
  font-weight: 500;
  margin: 0;
}

.error-text {
  font-size: 12px;
  color: #DC2626;
  margin: 0;
}

.help-text {
  font-size: 12px;
  color: #6B7280;
  margin: 0;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}

.btn {
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-primary {
  background: #059669;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #047857;
}

.btn-primary:disabled {
  background: #D1D5DB;
  cursor: not-allowed;
}

.btn-loading {
  display: flex;
  align-items: center;
  gap: 8px;
}

.spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.alert {
  padding: 16px;
  border-radius: 6px;
  display: flex;
  gap: 12px;
  margin: 0;
}

.alert-icon {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
}

.alert-warning {
  background: #FFFBEB;
  border: 1px solid #FCD34D;
  color: #92400E;
}

.alert-error {
  background: #FEE2E2;
  border: 1px solid #FECACA;
  color: #7F1D1D;
}

.alert-success {
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  color: #065F46;
}

.alert strong {
  display: block;
  font-weight: 600;
  margin-bottom: 4px;
}

.alert p {
  margin: 0;
  font-size: 14px;
}

.status-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-pending {
  background: #FEF08A;
  color: #92400E;
}

.status-rejected {
  background: #FECACA;
  color: #7F1D1D;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
