<template>
  <div class="staff-management-page">
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
          <form @submit.prevent="submitBranch">
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
                <textarea v-model="branchForm.address" rows="2" class="form-input" placeholder="Branch address"></textarea>
              </div>

              <div class="form-group">
                <label class="form-label">Initial Budget (PHP)</label>
                <input v-model.number="branchForm.budget" type="number" min="0" step="1000" class="form-input" />
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
                  <p class="info-sub">Pick any modules and functions across panels. A CUSTOM account is created only if at least one permission is selected.</p>
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
                      <label>
                        <input type="checkbox" v-model="branchForm.customAccount.permissions.modules[module.key]" />
                        <span class="module-label">{{ module.label }}</span>
                      </label>
                    </div>
                    <div class="permission-functions">
                      <label
                        v-for="fn in module.functions"
                        :key="fn.key"
                        class="function-row"
                      >
                        <input
                          type="checkbox"
                          v-model="branchForm.customAccount.permissions.functions[fn.key]"
                        />
                        <span>{{ fn.label }}</span>
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
                  <span :class="['badge', branch.is_active ? 'badge-online' : 'badge-offline']">
                    {{ branch.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>{{ branch.staff_count || 0 }}</td>
                <td>
                  <button
                    class="btn btn-secondary"
                    @click="confirmDeleteBranch(branch)"
                    :disabled="isDeleting || branch.can_delete === false || branch.is_main_branch"
                  >
                    {{ branch.can_delete === false || branch.is_main_branch ? 'Protected' : 'Delete' }}
                  </button>
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

const router = useRouter()
const route = useRoute()

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

const isDeleting = ref(false)

async function confirmDeleteBranch(branch) {
  try {
    if (branch.can_delete === false || branch.is_main_branch) {
      formError.value = 'Main Branch (HQ) is protected and cannot be deleted.'
      setTimeout(() => { formError.value = '' }, 2200)
      return
    }

    const count = branch.staff_count || 0
    const ok = await window.swalConfirm(`Delete branch "${branch.name}" and all ${count} account(s) in it? This will permanently delete those accounts and the branch from the database. This cannot be undone.`)
    if (!ok) return
    await deleteBranch(branch.id)
  } catch (e) {
    console.error(e)
  }
}

async function deleteBranch(branchId) {
  isDeleting.value = true
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const res = await axios.delete(`/api/superadmin/branches/${branchId}`, { withCredentials: true })
    if (res.data && res.data.ok) {
      formSuccess.value = res.data.message || 'Branch deleted.'
      await loadBranches()
      setTimeout(() => { formSuccess.value = '' }, 2000)
    } else {
      formError.value = res.data?.message || 'Failed to delete branch.'
    }
  } catch (e) {
    formError.value = e.response?.data?.message || 'Failed to delete branch.'
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
  showAddBranchForm.value = true
}

async function submitBranch() {
  formError.value = ''
  formSuccess.value = ''

  if (!branchForm.value.code.trim() || !branchForm.value.name.trim()) {
    formError.value = 'Branch code and name are required.'
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

      const selectedFunctions = permissionTemplates.flatMap(m =>
        m.functions
          .filter(fn => branchForm.value.customAccount.permissions.functions[fn.key])
          .map(fn => fn.key)
      )

      if (selectedModules.length > 0 || selectedFunctions.length > 0) {
        const username = (branchForm.value.customAccount.username || '').trim() || `custom_${codeSlugPreview.value}`
        const fullName = (branchForm.value.customAccount.fullName || '').trim() || `Custom Account - ${branchForm.value.name || branchForm.value.code}`
        const password = (branchForm.value.customAccount.password || '').trim() || defaultPassword.value
        customAccountPayload = {
          username,
          full_name: fullName,
          password,
          modules: selectedModules,
          functions: selectedFunctions,
        }
      }
    }

    const res = await axios.post('/api/superadmin/branches', {
      code: branchForm.value.code.trim(),
      name: branchForm.value.name.trim(),
      address: branchForm.value.address.trim(),
      budget: Number(branchForm.value.budget) || 0,
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

onMounted(async () => {
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
  background: linear-gradient(180deg, #ff8c42 0%, #ff6b1c 100%);
  min-height: 100vh;
}

.staff-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  background: rgba(255,255,255,0.18);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.staff-header h1 { margin: 0; color: #000; font-size: 2.5rem; font-weight: 700; }
.owner-staff-title { color: #ffffff !important; }
.header-actions { display: flex; gap: 1rem; align-items: center; }

.btn-primary, .btn-success, .btn-secondary { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; transition: all 0.3s ease; }
.btn-primary { background: #ff9f43; color: #fff; }
.btn-primary:hover { background: #fabd83; }
.btn-success { background: #28a745; color: #fff; }
.btn-success:hover { background: #218838; }
.btn-secondary { background: #6c757d; color: #fff; }
.btn-secondary:hover { background: #5a6268; }

.back-to-dashboard-btn { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; padding: 0.6rem 1.2rem; border: none; border-radius: 6px; cursor: pointer; }

.summary-card { background: rgba(255,255,255,0.15); padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; }
.owner-staff-total { color: #fff; margin: 0; font-size: 1.2rem; }

.loading-state, .empty-state { text-align: center; padding: 3rem; color: #fff; font-size: 1.1rem; }
.alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }

.branch-group { margin-bottom: 2rem; }
.staff-table-wrapper { overflow-x: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.staff-table { width: 100%; border-collapse: collapse; background: #fff; }
.staff-table thead { background: linear-gradient(135deg, #ff9a56, #ff8c5f); }
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
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
