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
.custom-accounts-panel {
  --custom-accounts-accent: #ea580c;
  --custom-accounts-border: #e2e8f0;
  --custom-accounts-muted: #64748b;
  box-sizing: border-box;
  padding: clamp(1rem, 2.5vw, 1.5rem);
  color: #0f172a;
}

.custom-accounts-panel *,
.custom-accounts-panel *::before,
.custom-accounts-panel *::after {
  box-sizing: border-box;
}

.custom-accounts-panel__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #edf0f4;
}

.custom-accounts-panel__header h2 {
  margin: 0;
  color: #0f172a;
  font-size: clamp(1.2rem, 2vw, 1.45rem);
  font-weight: 700;
  line-height: 1.3;
}

.custom-accounts-panel__header p,
.custom-account-editor p {
  margin: .35rem 0 0;
  color: var(--custom-accounts-muted);
  line-height: 1.5;
}

.custom-accounts-panel .btn-primary {
  flex: 0 0 auto;
  min-height: 2.5rem;
  padding: .55rem 1rem;
  border: 1px solid var(--custom-accounts-accent);
  border-radius: .6rem;
  background: var(--custom-accounts-accent);
  color: #fff;
  font: inherit;
  font-size: .9rem;
  font-weight: 650;
  line-height: 1.2;
  cursor: pointer;
  transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
}

.custom-accounts-panel .btn-primary:hover:not(:disabled) {
  border-color: #c2410c;
  background: #c2410c;
}

.custom-accounts-panel .btn-primary:focus-visible,
.custom-account-list__item:focus-visible,
.custom-account-field select:focus-visible,
.toggle-inline input:focus-visible,
.custom-account-check input:focus-visible {
  outline: 3px solid rgb(234 88 12 / 25%);
  outline-offset: 2px;
}

.custom-accounts-panel .btn-primary:disabled {
  cursor: wait;
  opacity: .65;
}

.custom-accounts-layout {
  display: grid;
  grid-template-columns: minmax(15rem, .75fr) minmax(0, 1.8fr);
  align-items: start;
  gap: 1.25rem;
  margin-top: 1.25rem;
}

.custom-account-list {
  display: grid;
  align-content: start;
  gap: .6rem;
  max-height: min(72vh, 52rem);
  overflow-y: auto;
  padding: .15rem .25rem .15rem .15rem;
}

.custom-account-list__item {
  display: block;
  width: 100%;
  min-width: 0;
  padding: .85rem .95rem;
  border: 1px solid var(--custom-accounts-border);
  border-radius: .7rem;
  background: #fff;
  color: #1e293b;
  text-align: left;
  overflow-wrap: anywhere;
  cursor: pointer;
  transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
}

.custom-account-list__item:hover {
  border-color: #fdba74;
  background: #fffaf5;
}

.custom-account-list__item.is-selected {
  border-color: var(--custom-accounts-accent);
  background: #fff7ed;
  box-shadow: 0 0 0 2px rgb(234 88 12 / 12%);
}

.custom-account-list__item strong {
  display: block;
  font-size: .94rem;
  font-weight: 650;
  line-height: 1.4;
}

.custom-account-list__item span {
  display: block;
  margin-top: .3rem;
  color: var(--custom-accounts-muted);
  font-size: .8rem;
  line-height: 1.4;
}

.custom-account-editor {
  min-width: 0;
  padding: clamp(1rem, 2vw, 1.5rem);
  border: 1px solid var(--custom-accounts-border);
  border-radius: .85rem;
  background: #fff;
  box-shadow: 0 4px 18px rgb(15 23 42 / 4%);
}

.custom-account-editor__heading,
.custom-account-editor__footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.custom-account-editor__heading {
  padding-bottom: 1rem;
  border-bottom: 1px solid #edf0f4;
}

.custom-account-editor__heading h3 {
  margin: 0;
  color: #0f172a;
  font-size: clamp(1.05rem, 1.8vw, 1.3rem);
  font-weight: 700;
  line-height: 1.35;
  overflow-wrap: anywhere;
}

.toggle-inline {
  display: inline-flex;
  flex: 0 0 auto;
  align-items: center;
  gap: .55rem;
  color: #334155;
  font-size: .9rem;
  font-weight: 600;
  cursor: pointer;
}

.toggle-inline input,
.custom-account-check input {
  width: 1rem;
  height: 1rem;
  flex: 0 0 auto;
  margin: 0;
  accent-color: var(--custom-accounts-accent);
}

.custom-account-form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
  margin-top: 1.1rem;
}

.custom-account-field {
  display: grid;
  min-width: 0;
  gap: .45rem;
  color: #334155;
  font-size: .88rem;
  font-weight: 650;
}

.custom-account-field select {
  width: 100%;
  min-width: 0;
  min-height: 2.75rem;
  padding: .6rem .75rem;
  border: 1px solid #cbd5e1;
  border-radius: .55rem;
  background: #fff;
  color: #0f172a;
  font: inherit;
  font-weight: 400;
}

.custom-account-field select:disabled {
  background: #f8fafc;
  color: #64748b;
  cursor: not-allowed;
}

.custom-account-editor__hint {
  margin: 1rem 0;
  padding: .8rem .95rem;
  border: 1px solid #fed7aa;
  border-radius: .6rem;
  background: #fff7ed;
  color: #9a3412 !important;
  font-size: .88rem;
  line-height: 1.55;
}

.custom-account-editor fieldset {
  min-width: 0;
  margin: 1.15rem 0 0;
  padding: .65rem .75rem .8rem;
  border: 1px solid var(--custom-accounts-border);
  border-radius: .7rem;
  background: #f8fafc;
}

.custom-account-editor legend {
  padding: 0 .4rem;
  color: #1e293b;
  font-size: .95rem;
  font-weight: 700;
}

.custom-account-check-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 11rem), 1fr));
  gap: .6rem;
  margin-top: .35rem;
}

.custom-account-check {
  display: flex;
  min-width: 0;
  min-height: 3rem;
  align-items: center;
  gap: .65rem;
  padding: .65rem .75rem;
  border: 1px solid #e2e8f0;
  border-radius: .55rem;
  background: #fff;
  color: #334155;
  font-size: .86rem;
  line-height: 1.4;
  cursor: pointer;
  transition: border-color .15s ease, background-color .15s ease;
}

.custom-account-check:hover {
  border-color: #fdba74;
  background: #fffaf5;
}

.custom-account-check span {
  min-width: 0;
  overflow-wrap: anywhere;
}

.custom-account-editor__footer {
  flex-wrap: wrap;
  margin-top: 1rem;
}

.custom-account-message {
  color: #166534;
  font-size: .9rem;
  font-weight: 600;
}

.custom-accounts-panel .alert {
  margin-top: 1rem;
}

.custom-accounts-state {
  padding: 2rem 1rem;
  color: var(--custom-accounts-muted);
  text-align: center;
}

@media (max-width: 960px) {
  .custom-accounts-layout {
    grid-template-columns: minmax(0, 1fr);
  }

  .custom-account-list {
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 15rem), 1fr));
    max-height: 19rem;
  }
}

@media (max-width: 600px) {
  .custom-accounts-panel__header,
  .custom-account-editor__heading {
    align-items: stretch;
    flex-direction: column;
  }

  .custom-accounts-panel__header {
    gap: .75rem;
  }

  .custom-accounts-panel__header .btn-primary {
    align-self: flex-start;
  }

  .custom-account-form-grid {
    grid-template-columns: minmax(0, 1fr);
  }

  .custom-account-editor__heading .toggle-inline {
    align-self: flex-start;
  }
}
</style>
