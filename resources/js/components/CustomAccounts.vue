<template>
  <section class="custom-accounts-panel panel-block">
    <div class="panel-header custom-accounts-panel__header">
      <div>
        <h2>Custom Accounts</h2>
        <p>Choose an account and control which features appear in its custom sidebar.</p>
      </div>
      <button type="button" class="btn-primary" @click="loadAccounts" :disabled="loading">Refresh</button>
    </div>

    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
    <div v-if="loading" class="custom-accounts-state">Loading accounts...</div>
    <div v-else-if="accounts.length === 0" class="custom-accounts-state">No accounts available.</div>

    <div v-else class="custom-accounts-layout">
      <div class="custom-account-list">
        <button
          v-for="account in accounts"
          :key="account.id"
          type="button"
          class="custom-account-list__item"
          :class="{ 'is-selected': selectedAccount?.id === account.id }"
          @click="selectAccount(account)"
        >
          <strong>{{ account.full_name || account.username }}</strong>
          <span>{{ account.role }} · {{ account.branch_name || 'Unassigned' }}</span>
        </button>
      </div>

      <form v-if="selectedAccount" class="custom-account-editor" @submit.prevent="savePermissions">
        <div class="custom-account-editor__heading">
          <div>
            <h3>{{ selectedAccount.full_name || selectedAccount.username }}</h3>
            <p>{{ selectedAccount.email || 'No email address' }}</p>
          </div>
          <label class="toggle-inline">
            <input v-model="isCustom" type="checkbox">
            <span>Custom sidebar account</span>
          </label>
        </div>

        <div class="custom-account-form-grid">
          <label class="custom-account-field">
            <span>Account role</span>
            <select v-model="selectedRole" required :disabled="isCustom">
              <option v-if="isCustom" value="CUSTOM">Custom</option>
              <option v-for="role in roleOptions" :key="role.value" :value="role.value">{{ role.label }}</option>
            </select>
          </label>
          <label class="custom-account-field">
            <span>Department</span>
            <select v-model="selectedDepartment" :required="!isCustom" :disabled="isCustom">
              <option v-for="department in availableDepartments" :key="department.value" :value="department.value">{{ department.label }}</option>
            </select>
          </label>
        </div>

        <p class="custom-account-editor__hint">
          Turn on the custom sidebar only when this account should use selected feature access. Otherwise, the selected normal role and department determine its regular panel.
        </p>

        <fieldset v-if="isCustom">
          <legend>Custom sidebar features</legend>
          <div class="custom-account-check-grid">
            <label v-for="module in moduleOptions" :key="module.key" class="custom-account-check">
              <input v-model="selectedModules" type="checkbox" :value="module.key">
              <span>{{ module.label }}</span>
            </label>
          </div>
        </fieldset>

        <div class="custom-account-editor__footer">
          <span v-if="saveMessage" class="custom-account-message">{{ saveMessage }}</span>
          <button type="submit" class="btn-primary" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save permissions' }}
          </button>
        </div>
      </form>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

const accounts = ref([])
const moduleOptions = ref([])
const selectedAccount = ref(null)
const selectedModules = ref([])
const selectedRole = ref('')
const selectedDepartment = ref('')
const isCustom = ref(false)
const roleOptions = ref([])
const departmentOptions = ref([])
const availableDepartments = computed(() => {
  const departmentsByRole = {
    STAFF: ['CASHIER', 'INVENTORY', 'KITCHEN'],
    MANAGER: ['FINANCE', 'PROCUREMENT', 'LOGISTICS', 'HR'],
  }
  const allowed = departmentsByRole[selectedRole.value] || []
  return departmentOptions.value.filter(department => allowed.includes(department.value))
})
const loading = ref(false)
const saving = ref(false)
const errorMessage = ref('')
const saveMessage = ref('')

watch(isCustom, (enabled) => {
  if (enabled) {
    selectedRole.value = 'CUSTOM'
    selectedDepartment.value = ''
  } else if (selectedRole.value === 'CUSTOM') {
    selectedRole.value = 'STAFF'
    selectedDepartment.value = availableDepartments.value[0]?.value || ''
  }
})

watch(selectedRole, (role) => {
  if (!availableDepartments.value.some(department => department.value === selectedDepartment.value)) {
    selectedDepartment.value = ''
  }
})

function selectAccount(account) {
  selectedAccount.value = account
  selectedModules.value = [...(account.permissions?.modules || [])]
  selectedRole.value = account.role === 'CUSTOM' || selectedModules.value.length > 0
    ? 'CUSTOM'
    : (['STAFF', 'MANAGER'].includes(account.role) ? account.role : 'STAFF')
  selectedDepartment.value = account.department || ''
  isCustom.value = selectedRole.value === 'CUSTOM'
  saveMessage.value = ''
}

async function loadAccounts() {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await axios.get('/api/admin/custom-accounts', { withCredentials: true })
    accounts.value = response.data.accounts || []
    moduleOptions.value = response.data.modules || []
    roleOptions.value = (response.data.roles || [])
      .filter(role => ['STAFF', 'MANAGER'].includes(role))
      .map(role => ({
      value: role,
      label: role.charAt(0) + role.slice(1).toLowerCase(),
    }))
    departmentOptions.value = (response.data.departments || []).map(department => ({
      value: department,
      label: department.charAt(0) + department.slice(1).toLowerCase(),
    }))
    if (selectedAccount.value) {
      const refreshed = accounts.value.find(account => account.id === selectedAccount.value.id)
      if (refreshed) selectAccount(refreshed)
    } else if (accounts.value.length) {
      selectAccount(accounts.value[0])
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Unable to load accounts.'
  } finally {
    loading.value = false
  }
}

async function savePermissions() {
  if (!selectedAccount.value) return
  saving.value = true
  saveMessage.value = ''
  try {
    await axios.put(`/api/admin/custom-accounts/${selectedAccount.value.id}`, {
      is_custom: isCustom.value,
      role: selectedRole.value,
      department: selectedDepartment.value || null,
      modules: selectedModules.value,
    }, { withCredentials: true })
    saveMessage.value = 'Permissions saved.'
    await loadAccounts()
  } catch (error) {
    saveMessage.value = error.response?.data?.message || 'Unable to save permissions.'
  } finally {
    saving.value = false
  }
}

onMounted(loadAccounts)
</script>

<style scoped>
.custom-accounts-panel { padding: 1.25rem; }
.custom-accounts-panel__header { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; }
.custom-accounts-panel__header p, .custom-account-editor p { margin: .25rem 0 0; color: #6b7280; }
.custom-accounts-layout { display: grid; grid-template-columns: minmax(220px, .8fr) minmax(320px, 1.5fr); gap: 1rem; margin-top: 1rem; }
.custom-account-list { display: grid; gap: .5rem; align-content: start; }
.custom-account-list__item { border: 1px solid #e5e7eb; background: #fff; border-radius: .5rem; padding: .75rem; text-align: left; cursor: pointer; }
.custom-account-list__item.is-selected { border-color: #b45309; box-shadow: 0 0 0 2px #fde68a; }
.custom-account-list__item span { display: block; margin-top: .25rem; color: #6b7280; font-size: .8rem; }
.custom-account-editor { border: 1px solid #e5e7eb; border-radius: .5rem; padding: 1rem; }
.custom-account-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; margin-top: 1rem; }
.custom-account-field { display: grid; gap: .35rem; color: #374151; font-weight: 600; font-size: .9rem; }
.custom-account-field select { border: 1px solid #d1d5db; border-radius: .35rem; padding: .55rem .65rem; background: #fff; color: #111827; }
.custom-account-editor__heading, .custom-account-editor__footer { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
.custom-account-editor__hint { margin: .75rem 0; padding: .65rem .8rem; background: #fffbeb; border: 1px solid #fde68a; border-radius: .35rem; color: #92400e !important; font-size: .9rem; }
.custom-account-check-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: .5rem; margin: .75rem 0 1.25rem; }
.custom-account-check { display: flex; gap: .5rem; align-items: center; padding: .45rem; border: 1px solid #e5e7eb; border-radius: .35rem; }
legend { font-weight: 700; margin-top: 1rem; }
.custom-account-message { color: #166534; font-size: .9rem; }
.custom-accounts-state { padding: 2rem 0; color: #6b7280; }
@media (max-width: 800px) { .custom-accounts-layout { grid-template-columns: 1fr; } .custom-account-form-grid { grid-template-columns: 1fr; } }
</style>
