<template>
  <div v-if="show" class="modal-backdrop" @click.self="closeModal">
    <div class="modal">
      <div class="modal-card">
        <form @submit.prevent="submitForm">
          <div class="modal-header">
            <h2>{{ isViewOnly ? 'View Staff Member' : (isEdit ? 'Edit Staff Member' : 'Add New Staff Member') }}</h2>
            <button type="button" @click="closeModal" class="close-button">×</button>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Create custom account?</label>
              <div>
                <label style="display:inline-flex;align-items:center;gap:8px">
                  <input type="checkbox" v-model="form.custom_account" :disabled="isViewOnly" />
                  <span style="font-weight:600">Enable custom username/password</span>
                </label>
                <div class="form-hint">When disabled, the system will create account credentials automatically.</div>
              </div>
            </div>

            <div class="form-group" v-if="form.custom_account">
              <label for="username" class="form-label">Username {{ !isEdit ? '*' : '' }}</label>
              <input
                v-model="form.username"
                id="username"
                class="form-input"
                :class="{ 'read-only': isEdit || isViewOnly }"
                :placeholder="!isEdit ? 'Enter username' : ''"
                :required="form.custom_account && !isEdit"
                :disabled="isEdit || isViewOnly"
              />
            </div>

            <div class="form-group" v-else>
              <label class="form-label">Username</label>
              <input class="form-input" disabled placeholder="Will be auto-generated" />
            </div>

            <div class="form-group">
              <label for="full_name" class="form-label">Full name</label>
              <input v-model="form.full_name" id="full_name" class="form-input" :disabled="isViewOnly" />
            </div>

            <div class="form-group">
              <label for="email" class="form-label">Email</label>
              <input v-model="form.email" id="email" class="form-input" :disabled="isViewOnly" />
            </div>

            <div class="form-group">
              <label for="role" class="form-label">Role</label>
              <select v-model="form.role" id="role" class="form-input" :disabled="isViewOnly">
                <option value="">Select role</option>
                <option value="STAFF_INVENTORY">Inventory Staff</option>
                <option value="MANAGER_LOGISTICS">Logistics Manager</option>
                <option value="MANAGER_FINANCE">Finance Manager</option>
                <option value="MANAGER_PROCUREMENT">Procurement Manager</option>
                <option value="STAFF_CASHIER">Cashier Staff</option>
              </select>
            </div>

            <div class="form-group" v-if="form.custom_account && !isViewOnly">
              <label for="password" class="form-label">Password {{ !isEdit ? '*' : '' }}</label>
              <input v-model="form.password" id="password" class="form-input" type="password" autocomplete="current-password" :required="form.custom_account && !isEdit" />
            </div>
          </div>

          <!-- Debug: Show password info -->
          <div style="background: #f0f0f0; padding: 0.5rem; margin-bottom: 1rem; font-size: 0.75rem; border-radius: 4px; font-family: monospace;">
            isEdit: {{ isEdit }} | defaultPassword: {{ defaultPassword ? '✓ SET' : '✗ EMPTY' }} | branchName: {{ branchName }}
          </div>

          <!-- Default Password Section (Outside form-grid) -->
          <div v-if="isEdit && defaultPassword" class="default-password-section-wrapper">
            <div class="default-password-section">
              <label class="form-label">Default Branch Password</label>
              <div class="default-password-display">
                <div class="password-info">
                  <span class="password-label">Branch: <strong>{{ branchName }}</strong></span>
                  <span class="password-value">{{ defaultPassword }}</span>
                </div>
                <button 
                  type="button"
                  @click="resetToDefaultPassword"
                  :disabled="isResetting"
                  class="btn btn-secondary btn-reset-password"
                >
                  {{ isResetting ? 'Resetting...' : 'Reset to Default' }}
                </button>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <template v-if="isViewOnly">
              <button type="button" class="btn btn-primary" @click="closeModal">Close</button>
            </template>
            <template v-else>
              <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save</button>
            </template>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OwnerStaffModal',
  props: {
    show: { type: Boolean, default: false },
    isEdit: { type: Boolean, default: false },
    isViewOnly: { type: Boolean, default: false },
    staff: { type: Object, default: null },
    branchName: { type: String, default: '' },
    defaultPassword: { type: String, default: '' },
    editingStaffId: { type: [String, Number], default: null },
  },
  emits: ['close', 'success', 'resetPassword'],
  data() {
    return {
      form: {
        username: '',
        full_name: '',
        email: '',
        phone_number: '',
        password: '',
            role: '',
            custom_account: false,
        branch_id: '',
        address: '',
        province: '',
        city: '',
        barangay: '',
        region: '',
      },
      addressSaved: false,
      documentFiles: {},
      errorMessage: '',
      isSubmitting: false,
      isResetting: false,
    }
  },
  watch: {
      staff: {
        immediate: true,
        handler(newStaff) {
          console.log('OwnerStaffModal - staff prop changed:', newStaff)
          if (this.isEdit && newStaff) {
            this.form.username = newStaff.username || ''
            this.form.full_name = newStaff.full_name || ''
            this.form.email = newStaff.email || ''
            this.form.phone_number = newStaff.phone_number || ''
            this.form.branch_id = newStaff.branch_id || ''
            this.form.role = newStaff.role || ''
            this.form.address = newStaff.address || ''
            this.form.custom_account = !!(newStaff.username)
            // Don't populate password when editing (user can change if needed)
            this.form.password = ''
          } else {
            this.form.username = ''
            this.form.full_name = ''
            this.form.email = ''
            this.form.phone_number = ''
            this.form.branch_id = ''
            this.form.role = ''
            this.form.address = ''
            this.form.password = ''
            this.form.custom_account = false
          }
          this.errorMessage = ''
          this.documentFiles = {}
        }
      }
  },
  methods: {
    closeModal() {
      this.$emit('close')
    },
    submitForm() {
      // If view-only, do not submit; just close
      if (this.isViewOnly) {
        this.closeModal()
        return
      }

      // Validate minimal constraints
      if (this.form.custom_account && !this.isEdit) {
        if (!this.form.username || !this.form.password) {
          this.errorMessage = 'Username and password are required when creating a custom account.'
          return
        }
      }

      // Emit success with extra context (isEdit and staffId) so parent can call API
      const payload = {
        form: Object.assign({}, this.form),
        isEdit: this.isEdit,
        staffId: this.staff && this.staff.id ? this.staff.id : null
      }
      this.$emit('success', payload)
      this.closeModal()
    },
    async resetToDefaultPassword() {
      // Emit resetPassword event to parent component
      this.$emit('resetPassword', this.editingStaffId)
    }
  }
}
</script>

<style scoped>
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
  z-index: 9999;
  backdrop-filter: blur(4px);
}

.modal {
  background: #fff;
  border-radius: 12px;
  width: 1000px;
  max-width: 98vw;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px rgba(0,0,0,0.25);
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from { opacity: 0; transform: translateY(-50px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-card { padding: 0; }

.modal-header {
  background: linear-gradient(135deg, #ff9a56, #ff8c5f);
  color: white;
  padding: 1.25rem 1.5rem;
  border-radius: 12px 12px 0 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.form-grid { display:grid; grid-template-columns: repeat(3,1fr); gap:1.25rem 1.5rem; padding:1.5rem }
.form-group { display:flex; flex-direction:column }
.form-label { font-size:0.85rem; font-weight:700; margin-bottom:0.5rem }
.form-input { padding:0.75rem; border:1px solid #e5e7eb; border-radius:8px }

.modal-footer { display:flex; justify-content:flex-end; gap:0.75rem; padding:1rem 1.25rem; border-top:1px solid #eee }
.btn { padding:0.6rem 1rem; border-radius:8px; font-weight:600 }
.btn-primary { background:#ff7e5f; color:#fff; border:none }
.btn-secondary { background:#fff; border:1px solid #e5e7eb }

/* Default Password Section */
.default-password-section-wrapper {
  padding: 1.5rem 1.5rem 0 1.5rem;
}

.default-password-section {
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px;
  color: white;
}

.default-password-display {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.password-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
}

.password-label {
  font-size: 0.85rem;
  opacity: 0.9;
}

.password-value {
  font-family: 'Courier New', monospace;
  font-size: 0.95rem;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.15);
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  word-break: break-all;
}

.btn-reset-password {
  background: white !important;
  color: #667eea !important;
  font-weight: 600 !important;
  border: none !important;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-reset-password:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.btn-reset-password:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>
