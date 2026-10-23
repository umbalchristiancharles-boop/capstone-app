<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    panelEyebrow="EMPLOYEE PORTAL"
    panelTitle="My Payroll"
    panelDescription="Review your payslips and confirm when you have received your salary."
    :enableProfileUpdate="true"
    :showProfileColumn="false"
    :ownerTwoColumnLayout="false"
    @profile-updated="userProfile = { ...userProfile, ...$event }"
  >
    <template #headerLeft>
      <button type="button" class="employee-payroll__back" @click="goBack">Back to dashboard</button>
    </template>
    <template #main>
      <section class="employee-payroll">
        <header class="employee-payroll__header">
          <div>
            <h2>My Payslips</h2>
            <p>Review your payslip and confirm it after Finance records your payment.</p>
          </div>
          <button type="button" @click="loadPayroll" :disabled="loading">{{ loading ? 'Loading...' : 'Refresh' }}</button>
        </header>

        <p v-if="errorMessage" class="employee-payroll__error">{{ errorMessage }}</p>
        <p v-if="loading" class="employee-payroll__empty">Loading payroll...</p>
        <p v-else-if="payrolls.length === 0" class="employee-payroll__empty">No payroll records are available yet.</p>
        <div v-else class="employee-payroll__list">
          <article
            v-for="payroll in payrolls"
            :key="payroll.id"
            class="employee-payroll__card"
            :class="{ 'employee-payroll__card--awaiting-confirmation': payroll.status === 'paid' && !payroll.employee_confirmed_at }"
          >
            <div class="employee-payroll__card-heading">
              <div>
                <h3>{{ payroll.payroll_type === 'mid_month' ? 'Mid-Month' : 'End of Month' }} Payroll</h3>
                <p>{{ formatPeriod(payroll.pay_period_start, payroll.pay_period_end) }}</p>
              </div>
              <span :class="['employee-payroll__badge', payroll.status === 'paid' ? 'employee-payroll__badge--paid' : '']">
                {{ payroll.status === 'rejected' ? 'Rejected' : (payroll.status === 'paid' ? (payroll.employee_confirmed_at ? 'Receipt Confirmed' : 'Awaiting Your Confirmation') : (payroll.hr_confirmed_at ? 'Awaiting Finance' : 'Awaiting HR')) }}
              </span>
            </div>
            <div class="employee-payroll__amount">
              <span>Net salary</span>
              <strong>₱{{ formatAmount(payroll.net_salary) }}</strong>
            </div>
            <div class="employee-payroll__details">
              <span>Pay date: {{ formatDate(payroll.pay_date) }}</span>
              <span>Days worked: {{ payroll.days_worked }}</span>
              <span>Overtime: {{ payroll.total_overtime_hours }} hrs</span>
              <span>Late deductions: ₱{{ formatAmount(payroll.late_deductions) }}</span>
            </div>
            <div class="employee-payroll__actions">
              <button v-if="payroll.status === 'paid'" type="button" class="employee-payroll__secondary" @click="printPayslip(payroll)">Print Payslip</button>
              <button
                v-if="payroll.status === 'paid' && !payroll.employee_confirmed_at"
                type="button"
                class="employee-payroll__confirm"
                :disabled="confirmingId === payroll.id"
                @click="confirmReceipt(payroll)"
              >{{ confirmingId === payroll.id ? 'Confirming...' : 'Confirm Payslip' }}</button>
              <span v-if="payroll.employee_confirmed_at" class="employee-payroll__confirmed">
                Confirmed {{ formatDate(payroll.employee_confirmed_at) }}
              </span>
            </div>
          </article>
        </div>
      </section>
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import axios from 'axios'
import OwnerPanelLayout from './OwnerPanelLayout.vue'

const userProfile = ref({})
const payrolls = ref([])
const loading = ref(false)
const confirmingId = ref(null)
const errorMessage = ref('')

onMounted(async () => {
  window.addEventListener('payroll-notification-selected', handlePayrollNotification)
  try {
    const response = await axios.get('/api/staff/profile', { withCredentials: true })
    userProfile.value = response.data.user || {}
  } catch (error) {
    console.error('Unable to load employee profile:', error)
  }
  await loadPayroll()
})

onUnmounted(() => {
  window.removeEventListener('payroll-notification-selected', handlePayrollNotification)
})

function handlePayrollNotification() {
  loadPayroll().then(() => {
    document.querySelector('.employee-payroll__card--awaiting-confirmation')?.scrollIntoView({
      behavior: 'smooth',
      block: 'center',
    })
  })
}

async function loadPayroll() {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await axios.get('/api/payroll/my', { withCredentials: true })
    if (!response.data?.ok) throw new Error(response.data?.message || 'Unable to load payroll')
    payrolls.value = response.data.data || []
  } catch (error) {
    console.error('Unable to load employee payroll:', error)
    errorMessage.value = error.response?.data?.message || error.message || 'Unable to load payroll.'
  } finally {
    loading.value = false
  }
}

function goBack() {
  if (window.history.length > 1) {
    window.history.back()
    return
  }
  window.location.assign('/staff-panel')
}

async function confirmReceipt(payroll) {
  confirmingId.value = payroll.id
  errorMessage.value = ''
  try {
    const response = await axios.post(`/api/payroll/${payroll.id}/employee-confirm`, {}, { withCredentials: true })
    if (!response.data?.ok) throw new Error(response.data?.message || 'Unable to confirm receipt')
    await loadPayroll()
  } catch (error) {
    console.error('Unable to confirm payroll receipt:', error)
    errorMessage.value = error.response?.data?.message || error.message || 'Unable to confirm receipt.'
  } finally {
    confirmingId.value = null
  }
}

function printPayslip(payroll) {
  const popup = window.open('', '_blank')
  if (!popup) {
    errorMessage.value = 'Allow pop-ups to print your payslip.'
    return
  }
  popup.opener = null

  const safe = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[character])
  const amount = (value) => Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  const content = `
    <!doctype html><html><head><title>Payslip</title>
    <style>body{font:14px Arial,sans-serif;color:#222;margin:40px}h1,p{margin:0}.head{text-align:center;border-bottom:2px solid #222;padding-bottom:18px}.head p{font-weight:bold;color:#f97316}.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:24px 0}.item span{display:block;color:#666;font-size:12px;margin-bottom:4px}.breakdown{border-top:1px solid #ccc}.breakdown div{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #ddd}.total{font-weight:bold;font-size:17px}.foot{margin-top:32px;color:#666;font-size:12px}@media print{body{margin:15mm}}</style></head>
    <body><header class="head"><p>CHIKIN TAYO</p><h1>PAYSLIP</h1><span>Payment confirmed</span></header>
    <section class="grid">
      <div class="item"><span>Employee</span>${safe(userProfile.value.full_name || userProfile.value.fullName || 'Employee')}</div>
      <div class="item"><span>Branch</span>${safe(payroll.branch?.name || '—')}</div>
      <div class="item"><span>Pay period</span>${safe(formatPeriod(payroll.pay_period_start, payroll.pay_period_end))}</div>
      <div class="item"><span>Pay date</span>${safe(formatDate(payroll.pay_date))}</div>
    </section>
    <section class="breakdown">
      <div><span>Base salary</span><strong>₱${amount(payroll.base_salary)}</strong></div>
      <div><span>Overtime pay</span><strong>₱${amount(payroll.overtime_pay)}</strong></div>
      <div><span>Gross salary</span><strong>₱${amount(payroll.gross_salary)}</strong></div>
      <div><span>Late deductions</span><strong>- ₱${amount(payroll.late_deductions)}</strong></div>
      <div class="total"><span>Net salary paid</span><strong>₱${amount(payroll.net_salary)}</strong></div>
    </section>
    <footer class="foot">Confirmed by Finance: ${safe(payroll.finance_confirmer?.full_name || 'Finance')} ${safe(formatDate(payroll.confirmed_at))}</footer>
    <script>window.addEventListener('load',()=>window.print())<\/script></body></html>`
  popup.document.open()
  popup.document.write(content)
  popup.document.close()
}

function formatDate(value) {
  if (!value) return '—'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? '—' : date.toLocaleDateString('en-PH')
}

function formatPeriod(start, end) {
  return start && end ? `${formatDate(start)} - ${formatDate(end)}` : '—'
}

function formatAmount(value) {
  return Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

<style scoped>
.employee-payroll { padding: 1.25rem; background: #fff; border: 1px solid #eadfd6; border-radius: 14px; }
.employee-payroll__header, .employee-payroll__card-heading, .employee-payroll__amount, .employee-payroll__actions { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.employee-payroll__header { margin-bottom: 1rem; }
.employee-payroll__header h2, .employee-payroll__card h3 { margin: 0 0 .3rem; }
.employee-payroll__header p, .employee-payroll__card-heading p { margin: 0; color: #6b7280; }
.employee-payroll__header button, .employee-payroll__confirm, .employee-payroll__secondary { border: 0; border-radius: 8px; padding: .65rem .9rem; cursor: pointer; font-weight: 700; }
.employee-payroll__back { border: 0; padding: .5rem 0; background: transparent; color: #9a3412; cursor: pointer; font-weight: 700; }
.employee-payroll__header button, .employee-payroll__secondary { background: #e9eef5; color: #334155; }
.employee-payroll__list { display: grid; gap: .85rem; }
.employee-payroll__card { padding: 1rem; border: 1px solid #eee5dd; border-radius: 10px; }
.employee-payroll__badge { border-radius: 999px; padding: .35rem .65rem; background: #fff1cc; color: #805b00; font-size: .75rem; font-weight: 700; }
.employee-payroll__badge--paid { background: #dcfce7; color: #166534; }
.employee-payroll__amount { margin: 1rem 0; padding: .8rem; background: #fff8f1; border-radius: 8px; }
.employee-payroll__amount strong { font-size: 1.2rem; }
.employee-payroll__details { display: flex; flex-wrap: wrap; gap: .5rem 1.25rem; color: #64748b; font-size: .85rem; }
.employee-payroll__actions { justify-content: flex-start; margin-top: 1rem; }
.employee-payroll__confirm { background: #f97316; color: #fff; }
.employee-payroll__confirmed { color: #166534; font-weight: 700; font-size: .85rem; }
.employee-payroll__empty { padding: 1rem; color: #64748b; }
.employee-payroll__error { padding: .75rem; color: #991b1b; background: #fee2e2; border-radius: 8px; }
@media (max-width: 640px) { .employee-payroll__card-heading { align-items: flex-start; flex-direction: column; } }
</style>
