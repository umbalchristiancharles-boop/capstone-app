<template>
  <div :class="['staff-management-page', { 'main-branch-theme': (isMainBranch || isFromSuperAdmin), 'from-superadmin': isFromSuperAdmin }]">
    <!-- Back button: goes to Owner or Super Admin depending on role -->
    <button @click="handleBack" class="btn-secondary back-to-dashboard-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      {{ backLabel }}
    </button>

    <!-- Header -->
    <div class="staff-header">
      <h1 class="owner-staff-title">Branch Management</h1>
      <div class="header-actions">
        <button @click="refreshBranches" class="btn-primary">Refresh</button>
        <button @click="openAddBranchForm" class="btn-success">+ Add Branch</button>
      </div>
    </div>

    <div v-if="showAddBranchForm" class="modal-backdrop" @click.self="closeAddBranch">
      <div class="modal">
        <div class="modal-card">
          <form @submit.prevent="submitBranch" data-no-overlay="1">
            <div class="modal-header">
              <h2>Add New Branch</h2>
              <button type="button" @click="closeAddBranch" class="close-button">&times;</button>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Branch Code *</label>
                <input v-model="branchForm.code" class="form-input" placeholder="e.g. BR001" required />
              </div>

              <div class="form-group">
                <label class="form-label">Branch Name *</label>
                <input v-model="branchForm.name" class="form-input" placeholder="e.g. Quezon City Branch" required />
              </div>

              <div class="form-group full-span">
                <label class="form-label">Address</label>

                <!-- Saved Address Card -->
                <div v-if="addressSaved" class="address-saved-card">
                  <div class="address-card-header">
                    <div class="address-card-content">
                      <strong class="address-label">Location:</strong>
                      <span class="address-text">{{ savedAddress }}</span>
                    </div>
                    <div class="address-card-actions">
                      <button type="button" class="btn btn-secondary btn-sm" @click="editBranchAddress">Edit</button>
                    </div>
                  </div>
                </div>

                <!-- Address Input Form -->
                <div v-else class="address-input-section">
                  <textarea v-model="branchForm.address" rows="2" class="form-input" placeholder="House number, street, subdivision" :required="!savedAddress"></textarea>

                  <!-- Address Cascader (Region → Province → City → Barangay) -->
                  <div style="margin-top:0.5rem;">
                    <AddressCascader
                      :initialAddress="{ region: branchForm.region, province: branchForm.province, city: branchForm.city, barangay: branchForm.barangay }"
                      :showSaveButton="false"
                      @update:address="onAddressUpdate"
                    />
                  </div>

                  <div style="margin-top:0.5rem; display:flex; gap:0.5rem;">
                    <button type="button" class="btn btn-primary" @click="saveBranchAddress">Save Location</button>
                    <button type="button" class="btn btn-secondary" @click="clearBranchAddress">Clear</button>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Initial Budget (PHP)</label>
                <input v-model.number="branchForm.budget" type="number" min="100000" max="1000000" step="1000" class="form-input" />
                <small class="budget-helper">Min 100,000 - Max 1,000,000</small>
              </div>
            </div>

            <div class="default-accounts-info">
              <h3>Default Accounts</h3>
              <p class="info-sub">Select which accounts to create automatically for this branch.</p>
              <div class="default-account-list">
                <div
                  v-for="account in accountOptions"
                  :key="account.key"
                  class="default-account-item"
                >
                  <input
                    :id="`account-${account.key}`"
                    v-model="branchForm.selectedAccounts[account.key]"
                    type="checkbox"
                    class="account-checkbox"
                    :disabled="account.key === 'admin'"
                  />
                  <div class="account-details">
                    <label :for="`account-${account.key}`" class="account-checkbox-label">
                      {{ account.label }}
                      <span v-if="account.key === 'admin'" class="checkbox-helper">(required)</span>
                    </label>
                    <div class="account-info">
                      <span class="account-role-badge" :class="account.badgeClass">{{ account.badgeText }}</span>
                      <span class="account-username">
                        Username:
                        <strong>{{ account.prefix }}_{{ codeSlugPreview }}</strong>
                      </span>
                      <span class="account-password">
                        Password:
                        <strong>{{ showPassword ? defaultPassword : maskedPassword }}</strong>
                        <button
                          type="button"
                          class="btn btn-secondary password-toggle-btn"
                          @click="toggleShowPassword"
                        >
                          {{ showPassword ? 'Hide' : 'Show' }}
                        </button>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="custom-account-card">
              <div class="custom-account-header">
                <div>
                  <h3>Custom Account (role: CUSTOM)</h3>
                  <p class="info-sub">Select which panels this account can access. A CUSTOM account is created only if at least one panel is selected.</p>
                </div>
                <label class="toggle">
                  <input type="checkbox" v-model="branchForm.customAccount.enabled" />
                  <span>Create custom account</span>
                </label>
              </div>

              <div v-if="branchForm.customAccount.enabled" class="custom-account-body">
                <div class="form-grid compact">
                  <div class="form-group">
                    <label class="form-label">Username</label>
                    <input v-model="branchForm.customAccount.username" class="form-input" :placeholder="`custom_${codeSlugPreview}`" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input v-model="branchForm.customAccount.fullName" class="form-input" :placeholder="`Custom Account - ${branchForm.name || branchForm.code}`" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Password</label>
                    <input v-model="branchForm.customAccount.password" class="form-input" :placeholder="defaultPassword" />
                  </div>
                </div>

                <div class="permission-grid">
                  <div v-for="module in permissionTemplates" :key="module.key" class="permission-card">
                    <div class="permission-card-header">
                      <label style="display:flex;align-items:center;gap:8px">
                        <input type="checkbox" v-model="branchForm.customAccount.permissions.modules[module.key]" />
                        <span style="font-weight:700; text-transform:capitalize">{{ module.label }}</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Error / Success -->
            <div v-if="formError" class="error-message">{{ formError }}</div>
            <div v-if="formSuccess" class="success-message">{{ formSuccess }}</div>

            <div class="modal-footer">
              <button type="button" @click="closeAddBranch" class="btn btn-secondary" :disabled="isSubmitting">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                {{ isSubmitting ? 'Creating...' : 'Create Branch' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <p>Loading branches...</p>
    </div>

    <!-- Error State -->
    <div v-if="errorMessage" class="alert alert-danger">
      {{ errorMessage }}
    </div>

    <!-- Summary -->
    <div v-if="!loading" class="summary-card">
      <h3 class="owner-staff-total">Total Branches: {{ branches.length }}</h3>
    </div>

    <!-- Branches Table -->
    <div v-if="!loading && branches.length > 0">
      <div class="branch-group">
        <div class="staff-table-wrapper">
          <table class="staff-table">
            <thead>
              <tr>
                <th>Code</th>
                <th>Branch Name</th>
                <th>Budget</th>
                <th>Address</th>
                <th>Admin Account</th>
                <th>HR Manager Account</th>
                <th>Finance Manager Account</th>
                <th>Procurement Manager Account</th>
                <th>Logistics Manager Account</th>
                <th>Status</th>
                <th>Staff Count</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="branch in branches" :key="branch.id">
                <td><strong>{{ branch.code }}</strong></td>
                <td>
                  {{ branch.name }}
                  <span v-if="branch.is_main_branch" class="account-chip" style="margin-left: 8px; background: #fff3cd; color: #7c2d12; border-color: #f59e0b;">
                    HQ Protected
                  </span>
                </td>
                <td>{{ formatCurrency(branch.budget || 0) }}</td>
                <td>{{ branch.address || '-' }}</td>
                <td>
                  <span v-if="branch.admin_user" class="account-chip admin-chip">
                    {{ branch.admin_user.username }}
                    <span v-if="branch.admin_user.is_active" class="chip-dot active"></span>
                    <span v-else class="chip-dot inactive"></span>
                  </span>
                  <span v-else class="text-muted">—</span>
                </td>
                <td>
                  <span v-if="branch.hr_manager" class="account-chip hr-chip">
                    {{ branch.hr_manager.username }}
                    <span v-if="branch.hr_manager.is_active" class="chip-dot active"></span>
                    <span v-else class="chip-dot inactive"></span>
                  </span>
                  <span v-else class="text-muted">—</span>
                </td>
                <td>
                  <span v-if="branch.finance_manager" class="account-chip finance-chip">
                    {{ branch.finance_manager.username }}
                    <span v-if="branch.finance_manager.is_active" class="chip-dot active"></span>
                    <span v-else class="chip-dot inactive"></span>
                  </span>
                  <span v-else class="text-muted">—</span>
                </td>
                <td>
                  <span v-if="branch.procurement_manager" class="account-chip procurement-chip">
                    {{ branch.procurement_manager.username }}
                    <span v-if="branch.procurement_manager.is_active" class="chip-dot active"></span>
                    <span v-else class="chip-dot inactive"></span>
                  </span>
                  <span v-else class="text-muted">—</span>
                </td>
                <td>
                  <span v-if="branch.logistics_manager" class="account-chip logistics-chip">
                    {{ branch.logistics_manager.username }}
                    <span v-if="branch.logistics_manager.is_active" class="chip-dot active"></span>
                    <span v-else class="chip-dot inactive"></span>
                  </span>
                  <span v-else class="text-muted">—</span>
                </td>
                <td>
                  <span v-if="branch.approval_status === 'pending'" class="badge badge-pending">
                    Pending Owner Approval
                  </span>
                  <span v-else-if="branch.approval_status === 'rejected'" class="badge badge-rejected">
                    Rejected
                  </span>
                  <span v-else :class="['badge', branch.is_active ? 'badge-online' : 'badge-offline']">
                    {{ branch.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>{{ branch.staff_count || 0 }}</td>
                <td>
                  <button
                    v-if="branch.can_delete !== false && !branch.is_main_branch && branch.approval_status !== 'pending'"
                    :class="['btn-status', branch.is_active ? 'btn-deactivate' : 'btn-reactivate']"
                    @click="branch.is_active ? confirmDeactivateBranch(branch) : confirmReactivateBranch(branch)"
                    :disabled="isDeleting"
                    :title="branch.is_active ? 'Deactivate this branch' : 'Reactivate this branch'"
                  >
                    <svg v-if="branch.is_active" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="1"></circle>
                      <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m4.12 4.12l4.24 4.24M1 12h6m6 0h6M4.22 19.78l4.24-4.24m4.12-4.12l4.24-4.24"></path>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                      <polyline points="9 9 12 15 22 4"></polyline>
                    </svg>
                    {{ branch.is_active ? 'Deactivate' : 'Reactivate' }}
                  </button>
                  <span v-else class="btn-status btn-protected" :title="branch.is_main_branch ? 'Main branch is protected' : 'Not available'">
                    {{ branch.is_main_branch ? 'Protected' : (branch.approval_status === 'pending' ? 'Pending' : '—') }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && branches.length === 0" class="empty-state">
      <p>No branches found. Click "+ Add Branch" to create one.</p>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'
import { useTheme } from '../composables/useTheme'
import AddressCascader from './AddressCascader.vue'

const router = useRouter()
const route = useRoute()

// Detect if page is opened under main-branch routes (more robust)
const isMainBranch = computed(() => {
  try {
    const p = String(route.path || '')
    const full = String(route.fullPath || '')
    const winPath = (typeof window !== 'undefined' && window.location && window.location.pathname) ? String(window.location.pathname) : ''
    // match when path starts with or contains main-branch in any of the route/location values
    return p.startsWith('/main-branch') || full.includes('/main-branch') || winPath.includes('/main-branch') || p.includes('main-branch')
  } catch (e) { return false }
})

// Detect when opened from Super Admin panel via ?from=superadmin
const isFromSuperAdmin = computed(() => {
  try { return String(route.query?.from || '') === 'superadmin' }
  catch (e) { return false }
})

const backTarget = computed(() => {
  // If navigated from Super Admin, return there; otherwise default to main branch admin
  return (route.query && route.query.from === 'superadmin') ? '/super-admin-panel' : '/main-branch/admin'
})

const backLabel = computed(() => route.query && route.query.from === 'superadmin' ? 'Back to Super Admin' : 'Back to Main Branch')

function handleBack() {
  try {
    // Prefer router navigation so SPA state is preserved
    router.push(backTarget.value)
  } catch (e) {
    try { window.location.href = backTarget.value } catch (_) {}
  }
}

// State
const loading = ref(false)
const errorMessage = ref('')
const branches = ref([])
const defaultPassword = ref('Chikintayo_123')

const accountOptions = [
  { key: 'admin', label: 'Admin Account', badgeText: 'ADMIN', badgeClass: 'admin-badge', prefix: 'admin' },
  { key: 'hr', label: 'HR Manager Account', badgeText: 'HR MANAGER', badgeClass: 'hr-badge', prefix: 'hr' },
  { key: 'finance', label: 'Finance Manager Account', badgeText: 'FINANCE MANAGER', badgeClass: 'finance-badge', prefix: 'finance' },
  { key: 'procurement', label: 'Procurement Manager Account', badgeText: 'PROCUREMENT MANAGER', badgeClass: 'procurement-badge', prefix: 'procurement' },
  { key: 'logistics', label: 'Logistics Manager Account', badgeText: 'LOGISTICS MANAGER', badgeClass: 'logistics-badge', prefix: 'logistics' },
]

// Modules and granular functions available for custom accounts
const permissionTemplates = [
  { key: 'admin', label: 'Admin', functions: [
    { key: 'admin.users', label: 'User Management' },
    { key: 'admin.branches', label: 'Branch Settings' },
    { key: 'admin.settings', label: 'System Settings' },
  ]},
  { key: 'finance', label: 'Finance', functions: [
    { key: 'finance.dashboard', label: 'Dashboard & KPIs' },
    { key: 'finance.budget', label: 'Branch Budgets' },
    { key: 'finance.reports', label: 'Reports' },
    { key: 'finance.expenses', label: 'Expenses' },
  ]},
  { key: 'logistics', label: 'Logistics', functions: [
    { key: 'logistics.dispatch', label: 'Dispatch / Delivery' },
    { key: 'logistics.receiving', label: 'Receiving' },
    { key: 'logistics.transfers', label: 'Transfers' },
  ]},
  { key: 'inventory', label: 'Inventory', functions: [
    { key: 'inventory.products', label: 'Products' },
    { key: 'inventory.counts', label: 'Stock Counts' },
    { key: 'inventory.adjustments', label: 'Adjustments' },
  ]},
  { key: 'procurement', label: 'Procurement', functions: [
    { key: 'procurement.purchase_orders', label: 'Purchase Orders' },
    { key: 'procurement.suppliers', label: 'Suppliers' },
    { key: 'procurement.approvals', label: 'Approvals' },
  ]},
  { key: 'kitchen', label: 'Kitchen Staff', functions: [
    { key: 'kitchen.orders', label: 'Orders Queue' },
    { key: 'kitchen.production', label: 'Production' },
    { key: 'kitchen.waste', label: 'Waste / Spoilage' },
  ]},
  { key: 'cashier', label: 'Cashier', functions: [
    { key: 'cashier.pos', label: 'POS' },
    { key: 'cashier.refunds', label: 'Refunds / Voids' },
    { key: 'cashier.shifts', label: 'Shift Closure' },
  ]},
  { key: 'hr', label: 'HR', functions: [
    { key: 'hr.attendance', label: 'Attendance' },
    { key: 'hr.scheduling', label: 'Scheduling' },
    { key: 'hr.payroll', label: 'Payroll Export' },
  ]},
  { key: 'reports', label: 'Reports', functions: [
    { key: 'reports.sales', label: 'Sales Reports' },
    { key: 'reports.inventory', label: 'Inventory Reports' },
    { key: 'reports.finance', label: 'Finance Reports' },
  ]},
]

function buildPermissionState() {
  const modules = {}
  const functions = {}
  permissionTemplates.forEach(mod => {
    modules[mod.key] = false
    mod.functions.forEach(fn => { functions[fn.key] = false })
  })
  return { modules, functions }
}

const defaultAccountSelection = () => ({
  admin: true,
  hr: true,
  finance: true,
  procurement: true,
  logistics: true,
})

const getInitialBranchForm = () => ({
  code: '',
  name: '',
  address: '',
  region: '',
  province: '',
  city: '',
  barangay: '',
  budget: 100000,
  selectedAccounts: defaultAccountSelection(),
  customAccount: {
    enabled: false,
    username: '',
    fullName: '',
    password: '',
    permissions: buildPermissionState(),
  },
})

// Show/hide password toggle for default password preview
const showPassword = ref(false)
const maskedPassword = computed(() => {
  try {
    return '*'.repeat(Math.max(6, (defaultPassword.value || '').length))
  } catch (e) { return '******' }
})

function toggleShowPassword() {
  showPassword.value = !showPassword.value
}

// Add Branch Form
const showAddBranchForm = ref(false)
const isSubmitting = ref(false)
const formError = ref('')
const formSuccess = ref('')
const branchForm = ref(getInitialBranchForm())
const codeSlugPreview = computed(() => branchForm.value.code ? branchForm.value.code.toLowerCase() : 'branchcode')
const savedAddress = ref('')
const addressSaved = ref(false)

const isDeleting = ref(false)

async function confirmDeactivateBranch(branch) {
  try {
    if (branch.can_delete === false || branch.is_main_branch) {
      formError.value = 'Main Branch (HQ) is protected and cannot be deactivated.'
      setTimeout(() => { formError.value = '' }, 2200)
      return
    }

    const count = branch.staff_count || 0
    const ok = await window.swalConfirm(`Deactivate branch "${branch.name}"? This will deactivate the branch and disable access for all ${count} account(s) in it.`)
    if (!ok) return
    await deactivateBranch(branch.id)
  } catch (e) {
    console.error(e)
  }
}

async function deactivateBranch(branchId) {
  isDeleting.value = true
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const res = await axios.patch(`/api/superadmin/branches/${branchId}/deactivate`, {}, { withCredentials: true })
    if (res.data && res.data.ok) {
      formSuccess.value = res.data.message || 'Branch deactivated.'
      await loadBranches()
      setTimeout(() => { formSuccess.value = '' }, 2000)
    } else {
      formError.value = res.data?.message || 'Failed to deactivate branch.'
    }
  } catch (e) {
    formError.value = e.response?.data?.message || 'Failed to deactivate branch.'
  } finally {
    isDeleting.value = false
  }
}

async function confirmReactivateBranch(branch) {
  try {
    if (branch.is_main_branch) {
      formError.value = 'Main Branch (HQ) is protected and cannot be managed.'
      setTimeout(() => { formError.value = '' }, 2200)
      return
    }

    const ok = await window.swalConfirm(`Reactivate branch "${branch.name}"? Accounts in this branch will be able to login again.`)
    if (!ok) return
    await reactivateBranch(branch.id)
  } catch (e) {
    console.error(e)
  }
}

async function reactivateBranch(branchId) {
  isDeleting.value = true
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const res = await axios.patch(`/api/superadmin/branches/${branchId}/reactivate`, {}, { withCredentials: true })
    if (res.data && res.data.ok) {
      formSuccess.value = res.data.message || 'Branch reactivated.'
      await loadBranches()
      setTimeout(() => { formSuccess.value = '' }, 2000)
    } else {
      formError.value = res.data?.message || 'Failed to reactivate branch.'
    }
  } catch (e) {
    formError.value = e.response?.data?.message || 'Failed to reactivate branch.'
  } finally {
    isDeleting.value = false
  }
}

async function loadBranches() {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await axios.get('/api/superadmin/branches', { withCredentials: true })
    if (res.data && res.data.ok) {
      branches.value = res.data.branches || []
    } else if (Array.isArray(res.data)) {
      branches.value = res.data
    } else {
      errorMessage.value = res.data?.message || 'Failed to load branches'
    }
  } catch (e) {
    if (e.response?.status === 401) { router.push('/staff-landing'); return }
    errorMessage.value = 'Error loading branches.'
  } finally {
    loading.value = false
  }
}

async function loadDefaultPassword() {
  // Only owners or admins are allowed to fetch this value from the server.
  try {
    const u = JSON.parse(localStorage.getItem('user') || 'null')
    const role = (u && u.role) ? String(u.role).toLowerCase() : ''
    if (!['owner', 'admin'].includes(role)) {
      // Skip fetching for other roles (e.g. superadmin) to avoid 403 responses
      return
    }

    const res = await axios.get('/api/admin/config/default-password', { withCredentials: true })
    if (res.data && res.data.success && res.data.default_password) {
      defaultPassword.value = res.data.default_password
    }
  } catch (e) {
    // keep fallback
  }
}

function refreshBranches() {
  loadBranches()
}

function closeAddBranch() {
  showAddBranchForm.value = false
  formError.value = ''
  formSuccess.value = ''
  branchForm.value = getInitialBranchForm()
}

function suggestBranchCode(name) {
  try {
    if (!name) return 'BR' + String(Date.now()).slice(-6)
    // create a short slug from name
    const slug = name.replace(/[^a-zA-Z0-9]/g, '').toLowerCase().slice(0, 8)
    if (!slug) return 'BR' + String(Date.now()).slice(-6)
    return slug.toUpperCase()
  } catch (e) { return 'BR' + String(Date.now()).slice(-6) }
}

function openAddBranchForm() {
  // Prefill suggested code based on current name (if any) or timestamp
  branchForm.value = getInitialBranchForm()
  branchForm.value.code = suggestBranchCode(branchForm.value.name)
  branchForm.value.budget = 100000
  formError.value = ''
  formSuccess.value = ''
  savedAddress.value = ''
  addressSaved.value = false
  showAddBranchForm.value = true
}

function onAddressUpdate(address) {
  branchForm.value.region = address.region || ''
  branchForm.value.province = address.province || ''
  branchForm.value.city = address.city || ''
  branchForm.value.barangay = address.barangay || ''
}

function saveBranchAddress() {
  const parts = []
  if (branchForm.value.address && branchForm.value.address.trim() !== '') parts.push(branchForm.value.address.trim())
  if (branchForm.value.barangay) parts.push(branchForm.value.barangay)
  if (branchForm.value.city) parts.push(branchForm.value.city)
  if (branchForm.value.province) parts.push(branchForm.value.province)
  if (branchForm.value.region) parts.push(branchForm.value.region)
  savedAddress.value = parts.join(', ')
  addressSaved.value = true
}

function clearBranchAddress() {
  branchForm.value.address = ''
  branchForm.value.province = ''
  branchForm.value.city = ''
  branchForm.value.barangay = ''
  branchForm.value.region = ''
  savedAddress.value = ''
  addressSaved.value = false
}

function editBranchAddress() {
  addressSaved.value = false
}

async function submitBranch() {
  formError.value = ''
  formSuccess.value = ''

  if (!branchForm.value.code.trim()) {
    formError.value = 'Branch code is required.'
    return
  }

  // Auto-fill branch name with location + Chikintayo if left blank
  if (!branchForm.value.name.trim()) {
    if (savedAddress.value) {
      branchForm.value.name = savedAddress.value + ' - Chikintayo'
    } else if (branchForm.value.address.trim()) {
      branchForm.value.name = branchForm.value.address.trim() + ' - Chikintayo'
    } else {
      formError.value = 'Branch name or address is required.'
      return
    }
  }

  const budgetValue = Number(branchForm.value.budget)
  if (!Number.isFinite(budgetValue) || budgetValue < 100000 || budgetValue > 1000000) {
    formError.value = 'Initial budget must be between 100,000 and 1,000,000.'
    return
  }

  isSubmitting.value = true
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

    let customAccountPayload = null
    if (branchForm.value.customAccount.enabled) {
      const selectedModules = permissionTemplates
        .filter(m => branchForm.value.customAccount.permissions.modules[m.key])
        .map(m => m.key)

      if (selectedModules.length > 0) {
        const username = (branchForm.value.customAccount.username || '').trim() || `custom_${codeSlugPreview.value}`
        const fullName = (branchForm.value.customAccount.fullName || '').trim() || `Custom Account - ${branchForm.value.name || branchForm.value.code}`
        const password = (branchForm.value.customAccount.password || '').trim() || defaultPassword.value
        customAccountPayload = {
          username,
          full_name: fullName,
          password,
          modules: selectedModules,
        }
      }
    }

    const res = await axios.post('/api/superadmin/branches', {
      code: branchForm.value.code.trim(),
      name: branchForm.value.name.trim(),
      address: savedAddress.value || branchForm.value.address.trim(),
      region: branchForm.value.region || '',
      province: branchForm.value.province || '',
      city: branchForm.value.city || '',
      barangay: branchForm.value.barangay || '',
      budget: Number(branchForm.value.budget) || 100000,
      accounts: branchForm.value.selectedAccounts,
      custom_account: customAccountPayload,
    }, { withCredentials: true })

    if (res.data && res.data.ok) {
      formSuccess.value = res.data.message || 'Branch created successfully with default accounts!'
      await loadBranches()
      setTimeout(() => {
        closeAddBranch()
      }, 1500)
    } else {
      formError.value = res.data?.message || 'Failed to create branch.'
    }
  } catch (e) {
    if (e.response?.data?.message) {
      formError.value = e.response.data.message
    } else {
      formError.value = 'Failed to create branch. Please try again.'
    }
  } finally {
    isSubmitting.value = false
  }
}

function enforceLightMode() {
  try {
    document.documentElement.classList.remove('dark-mode')
    document.documentElement.classList.add('light-mode')
    document.body.classList.remove('dark-mode')
    document.body.classList.add('light-mode')
    document.documentElement.removeAttribute('data-superadmin-theme')
  } catch (e) {}
}

onMounted(async () => {
  if (isFromSuperAdmin.value) {
    try { const { initializeTheme } = useTheme(); initializeTheme() } catch (e) {}
  } else {
    enforceLightMode()
  }
  await Promise.all([loadBranches(), loadDefaultPassword()])
})

function formatCurrency(amount) {
  try {
    const val = Number(amount) || 0
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(val)
  } catch (e) {
    return 'PHP ' + (amount || 0)
  }
}
</script>

<style scoped>
.staff-management-page {
  padding: 2rem;
  /* Match Super Admin panel gradient */
  background: linear-gradient(180deg, #FF9A4A 0%, #FF6A3D 100%);
  min-height: 100vh;
}

.staff-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  background: rgba(255,255,255,0.12);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}

.staff-header h1 { margin: 0; color: #000; font-size: 2.5rem; font-weight: 700; }
.staff-management-page:not(.main-branch-theme) .owner-staff-title { color: #ffffff; }
.header-actions { display: flex; gap: 1rem; align-items: center; }

.btn-primary, .btn-success, .btn-secondary { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; transition: all 0.25s ease; }
/* Primary matches Super Admin primary-action (blue gradient) */
.btn-primary { background: linear-gradient(135deg, #2b8aef, #1a6ed8); color: #fff; }
.btn-primary:hover { background: linear-gradient(135deg, #1a6ed8, #1557b0); transform: translateY(-1px); }
.btn-success { background: #28a745; color: #fff; }
.btn-success:hover { background: #218838; }
.btn-secondary { background: #6c757d; color: #fff; }
.btn-secondary:hover { background: #5a6268; }

.back-to-dashboard-btn { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; padding: 0.6rem 1.2rem; border: none; border-radius: 6px; cursor: pointer; }

.summary-card { background: rgba(255,255,255,0.14); padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
.owner-staff-total { color: #fff; margin: 0; font-size: 1.2rem; }

.loading-state, .empty-state { text-align: center; padding: 3rem; color: #fff; font-size: 1.1rem; }
.alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }

.branch-group { margin-bottom: 2rem; }
.staff-table-wrapper { overflow-x: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.staff-table { width: 100%; border-collapse: collapse; background: #fff; }
.staff-table thead { background: linear-gradient(135deg, #FF9A4A, #FF6A3D); }
.staff-table th { padding: 0.85rem 1rem; text-align: left; color: #fff; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
.staff-table td { padding: 0.75rem 1rem; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; color: #333; }
.staff-table tbody tr:hover { background: #fff8f0; }

.text-muted { color: #999; font-style: italic; }

/* Account chips */
.account-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
.admin-chip { background: #e3f2fd; color: #1565c0; }
.hr-chip { background: #e8f5e9; color: #2e7d32; }
.finance-chip { background: #fef3c7; color: #b45309; }
.logistics-chip { background: #e0e7ff; color: #4338ca; }
.procurement-chip { background: #fff7ed; color: #92400e; }
.chip-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.chip-dot.active { background: #28a745; }
.chip-dot.inactive { background: #dc3545; }

.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
.badge-online { background: #d4edda; color: #155724; }
.badge-offline { background: #f8d7da; color: #721c24; }
.badge-pending { background: #fff3cd; color: #7c2d12; }
.badge-rejected { background: #fee2e2; color: #991b1b; }

/* Modal */
.modal-backdrop { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(4px); }
.modal { background: #fff; border-radius: 12px; width: 700px; max-width: 96vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,0.25); animation: modalSlideIn 0.3s ease-out; }
@keyframes modalSlideIn { from { opacity: 0; transform: translateY(-50px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
.modal-card { padding: 0; }
.modal-header { background: linear-gradient(135deg, #ff9a56, #ff8c5f); color: white; padding: 1.5rem 2rem; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; }
.modal-header h2 { margin: 0; font-size: 1.5rem; font-weight: 700; }
.close-button { background: rgba(255,255,255,0.2); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.close-button:hover { background: rgba(255,255,255,0.3); transform: scale(1.1); }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; padding: 2rem; }
.form-group { display: flex; flex-direction: column; }
.form-group.full-span { grid-column: 1 / -1; }
.form-label { font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px; }
.form-input { padding: 0.875rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.875rem; transition: all 0.2s ease; background: white; color: #374151; min-height: 48px; box-sizing: border-box; font-family: inherit; }
.form-input:focus { outline: none; border-color: #ff7e5f; box-shadow: 0 0 0 3px rgba(255,126,95,0.1); }
textarea.form-input { resize: vertical; }
.budget-helper { color: #6b7280; font-size: 0.78rem; margin-top: 6px; }

.default-accounts-info { padding: 1.5rem 2rem; border-top: 1px solid #e5e7eb; background: #fafbfc; }
.default-accounts-info h3 { margin: 0 0 0.25rem 0; font-size: 1rem; color: #374151; }
.info-sub { margin: 0 0 1rem 0; color: #6b7280; font-size: 0.85rem; }
.default-account-list { display: flex; flex-direction: column; gap: 0.75rem; }
.default-account-item { display: flex; align-items: flex-start; gap: 1rem; padding: 0.75rem 1rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; }
.account-role-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.admin-badge { background: #dbeafe; color: #1d4ed8; }
.hr-badge { background: #dcfce7; color: #166534; }
.finance-badge { background: #fef3c7; color: #b45309; }
.logistics-badge { background: #e0e7ff; color: #4338ca; }
.procurement-badge { background: #fff7ed; color: #92400e; }
.account-details { display: flex; flex-direction: column; gap: 6px; font-size: 0.85rem; color: #4b5563; }
.account-checkbox { width: 18px; height: 18px; margin-top: 4px; accent-color: #ff8c42; cursor: pointer; }
.account-checkbox-label { font-weight: 700; color: #374151; cursor: pointer; }
.checkbox-helper { margin-left: 6px; font-weight: 600; color: #6b7280; font-size: 0.8rem; }
.account-info { display: flex; flex-direction: column; gap: 6px; }
.account-username, .account-password { display: inline-flex; align-items: center; gap: 6px; }
.password-toggle-btn { margin-left: 8px; padding: 4px 8px; font-size: 0.78rem; }

.custom-account-card { margin: 0 0 1.5rem 0; padding: 1.25rem 2rem; border-top: 1px solid #e5e7eb; background: #fffefb; }
.custom-account-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
.toggle { display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #374151; }
.custom-account-body { margin-top: 1rem; display: flex; flex-direction: column; gap: 1rem; }
.form-grid.compact { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; padding: 0; }
.permission-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; }
.permission-card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 0.75rem; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
.permission-card-header { margin-bottom: 0.5rem; font-weight: 700; color: #111827; }
.module-label { margin-left: 0.4rem; }
.permission-functions { display: flex; flex-direction: column; gap: 0.4rem; }
.function-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: #374151; }

.error-message { margin: 0 2rem 1rem; padding: 0.75rem 1rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; color: #dc2626; font-size: 0.9rem; }
.success-message { margin: 0 2rem 1rem; padding: 0.75rem 1rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; color: #16a34a; font-size: 0.9rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.5rem 2rem; border-top: 1px solid #e5e7eb; }
.btn { padding: 0.6rem 1.5rem; border: none; border-radius: 6px; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.btn.btn-primary { background: linear-gradient(135deg, #ff9a56, #ff8c5f); color: #fff; }
.btn.btn-primary:hover { background: linear-gradient(135deg, #ff8c42, #ff7e3a); }
.btn.btn-secondary { background: #e5e7eb; color: #374151; }
.btn.btn-secondary:hover { background: #d1d5db; }
.btn.btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* Status buttons */
.btn-status { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem; border: none; border-radius: 6px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.25s ease; text-transform: capitalize; }
.btn-deactivate { background: linear-gradient(135deg, #f87171, #ef4444); color: #fff; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25); }
.btn-deactivate:hover { background: linear-gradient(135deg, #ef4444, #dc2626); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35); }
.btn-deactivate:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
.btn-reactivate { background: linear-gradient(135deg, #4ade80, #22c55e); color: #fff; box-shadow: 0 2px 8px rgba(34, 197, 94, 0.25); }
.btn-reactivate:hover { background: linear-gradient(135deg, #22c55e, #16a34a); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35); }
.btn-reactivate:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
.btn-protected { background: #d1d5db; color: #6b7280; cursor: not-allowed; font-weight: 600; }

/* Address Card Styles */
.address-saved-card {
  margin-top: 0.5rem;
  padding: 1rem;
  background: linear-gradient(135deg, #f0f9ff 0%, #e6f7ff 100%);
  border: 2px solid #bfdbfe;
  border-radius: 8px;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.address-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.address-card-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.address-label {
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #1e40af;
  letter-spacing: 0.5px;
}

.address-text {
  font-size: 1rem;
  color: #1f2937;
  font-weight: 500;
  word-break: break-word;
}

.address-card-actions {
  display: flex;
  gap: 0.5rem;
}

.address-input-section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* Main Branch theme (based on StaffIndex) */
.staff-management-page.main-branch-theme,
.staff-management-page.from-superadmin {
  /* Use cleaner, pale background with consistent typography */
  background: linear-gradient(180deg, rgba(255,154,74,0.08) 0%, rgba(255,106,61,0.06) 100%);
  font-family: 'Inter', 'Poppins', sans-serif;
}

.staff-management-page.main-branch-theme .owner-staff-title,
.staff-management-page.from-superadmin .owner-staff-title {
  font-family: 'Inter', 'Poppins', sans-serif;
  font-weight: 800;
  letter-spacing: -0.5px;
  line-height: 1.1;
  /* slightly smaller per request */
  font-size: clamp(1.6rem, 2.4vw, 2.2rem);
  padding-bottom: 16px;
  color: var(--text-dark, #1f2937);
}

/* When opened from Super Admin keep Super Admin color scheme but use smaller title */
.staff-management-page.from-superadmin .owner-staff-title {
  font-size: clamp(1.6rem, 2.4vw, 2.2rem);
  line-height: 1.1;
  padding-bottom: 16px;
}

.staff-management-page.main-branch-theme .owner-staff-total,
.staff-management-page.from-superadmin .owner-staff-total {
  color: rgba(31,41,55,0.95);
  font-size: 1.05rem;
}

.staff-management-page.main-branch-theme .staff-header,
.staff-management-page.from-superadmin .staff-header {
  color: var(--text-dark, #1f2937);
}

.staff-management-page.main-branch-theme .header-actions .btn-primary,
.staff-management-page.main-branch-theme .header-actions .btn-success,
.staff-management-page.main-branch-theme .header-actions .btn-secondary,
.staff-management-page.from-superadmin .header-actions .btn-primary,
.staff-management-page.from-superadmin .header-actions .btn-success,
.staff-management-page.from-superadmin .header-actions .btn-secondary {
  font-family: 'Inter', 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 0.98rem;
}

.staff-management-page.main-branch-theme .header-actions .btn-primary,
.staff-management-page.from-superadmin .header-actions .btn-primary {
  /* ensure Refresh button matches StaffIndex look */
  background: #fffefb;
  color: var(--text-dark, #1f2937);
  padding: 10px 18px;
  border-radius: 999px;
  box-shadow: 0 8px 18px rgba(0,0,0,0.08);
}

.staff-management-page.main-branch-theme .header-actions .btn-success,
.staff-management-page.from-superadmin .header-actions .btn-success {
  background: #28a745;
  color: #fff;
  padding: 10px 16px;
  border-radius: 8px;
}

.staff-management-page.main-branch-theme .staff-table thead,
.staff-management-page.from-superadmin .staff-table thead {
  /* make header light with dark text like StaffIndex */
  background: var(--dirty-white, #fbfdfe);
  color: var(--text-dark, #42210b);
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

.staff-management-page.main-branch-theme .btn-primary,
.staff-management-page.from-superadmin .btn-primary {
  /* make primary buttons like StaffIndex .btn-login (light background, dark text) */
  background: #fffefb; /* dirty-white */
  color: #1f2937;
  border-radius: 999px;
  padding: 10px 18px;
  box-shadow: 0 8px 18px rgba(0,0,0,0.08);
  font-weight: 700;
}

.staff-management-page.main-branch-theme .btn-primary:hover,
.staff-management-page.from-superadmin .btn-primary:hover {
  transform: translateY(-2px);
}

.staff-management-page.main-branch-theme .btn-secondary,
.staff-management-page.from-superadmin .btn-secondary {
  background: transparent;
  border: 1px solid rgba(31,41,55,0.08);
  color: #1f2937;
}

.staff-management-page.main-branch-theme .btn-secondary:hover,
.staff-management-page.from-superadmin .btn-secondary:hover {
  background: rgba(255,255,255,0.06);
}

.staff-management-page.main-branch-theme .staff-table thead th,
.staff-management-page.from-superadmin .staff-table thead th {
  color: var(--text-dark, #42210b);
  font-weight: 700;
  padding: 12px 16px;
  font-size: 14px;
}

.staff-management-page.main-branch-theme .staff-table td,
.staff-management-page.from-superadmin .staff-table td {
  color: var(--text-dark, #42210b);
  font-size: 14px;
  padding: 12px 16px;
}

.staff-management-page.main-branch-theme .staff-table,
.staff-management-page.from-superadmin .staff-table {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
}
</style>
