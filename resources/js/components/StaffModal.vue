<template>
  <div v-if="show" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container">
      <div class="modal-card">
        <form @submit.prevent="submitForm">
          <!-- Modal Header -->
          <div class="modal-header">
            <h3 class="modal-title">
              {{ isEdit ? 'Edit Staff Account' : 'Create Staff Account' }}
            </h3>
            <button type="button" @click="closeModal" class="close-button">
              ✕
            </button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body">
            <div class="form-grid">
              <!-- Username -->
              <div class="form-group">
                <label for="username" class="form-label">Username *</label>
                <input
                  v-model="form.username"
                  type="text"
                  id="username"
                  :disabled="isEdit"
                  class="form-input"
                  placeholder="owner_admin"
                  required
                />
              </div>

              <!-- Email -->
              <div class="form-group">
                <label for="email" class="form-label">Email *</label>
                <input
                  v-model="form.email"
                  type="email"
                  id="email"
                  class="form-input"
                  placeholder="Enter email"
                  required
                />
              </div>

              <!-- Full Name -->
              <div class="form-group">
                <label for="fullName" class="form-label">Full Name *</label>
                <input
                  v-model="form.fullName"
                  type="text"
                  id="fullName"
                  class="form-input"
                  placeholder="Enter full name"
                  required
                />
              </div>

              <!-- Password -->
              <div class="form-group">
                <label for="password" class="form-label">Password {{ isEdit ? '' : '*' }}</label>
                <input
                  v-model="form.password"
                  type="password"
                  id="password"
                  class="form-input"
                  placeholder="••••••••"
                  :required="!isEdit"
                />
              </div>

              <!-- Phone -->
              <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <input
                  v-model="form.phone"
                  type="text"
                  id="phone"
                  class="form-input"
                  placeholder="+63 XXX XXX XXXX"
                />
              </div>

              <!-- Branch -->
              <div class="form-group" v-if="!branchManagerMode">
                <label for="branch" class="form-label">Branch <span v-if="!isEdit">*</span></label>
                <select
                  v-model="form.branchId"
                  id="branch"
                  class="form-input"
                  :required="!isEdit"
                >
                  <option v-if="!isEdit" value="" disabled>Select Branch</option>
                  <option
                        v-for="branch in branches"
                        :key="branch.id"
                        :value="branch.id"
                      >
                    {{ branch.name }}
                  </option>
                </select>
                <div v-if="isEdit" class="small-hint" style="margin-top:0.5rem; color:#6b7280; font-size:0.9rem;">
                  Current branch will be kept unless you choose another.
                </div>
              </div>
              <div class="form-group" v-else>
                <label class="form-label">Branch *</label>
                <div class="form-input" style="background-color: #f3f4f6; padding: 0.5rem; border-radius: 8px; display: flex; align-items: center;">
                  {{ managerBranchName || 'Staff Branch' }}
                </div>
              </div>

              <!-- Role -->
              <!-- When creating a staff account show the select; when editing, show read-only role text -->
              <div class="form-group" v-if="!branchManagerMode && !isEdit">
                <label for="role" class="form-label">Role *</label>
                <select
                  v-model="form.role"
                  id="role"
                  class="form-input"
                  required
                >
                  <option value="" disabled>Select Role</option>
                  <option value="BRANCH_MANAGER">Branch Manager</option>
                  <option value="STAFF">Staff</option>
                </select>
              </div>
              <div v-else-if="!branchManagerMode && isEdit" class="form-group">
                <label class="form-label">Role *</label>
                <div class="form-input" style="background-color: #f3f4f6; padding: 0.5rem; border-radius: 8px; display: flex; align-items: center;">
                  {{ form.role === 'BRANCH_MANAGER' ? 'Branch Manager' : (form.role === 'STAFF' ? 'Staff' : form.role) }}
                </div>
              </div>
              <div v-else class="form-group">
                <label class="form-label">Role *</label>
                <div class="form-input" style="background-color: #f3f4f6; padding: 0.5rem; border-radius: 8px; display: flex; align-items: center;">
                  Staff
                </div>
              </div>

              <!-- Status (Edit Only) -->
              <div v-if="isEdit" class="form-group">
                <label for="isActive" class="form-label">Status</label>
                <select
                  v-model="form.isActive"
                  id="isActive"
                  class="form-input"
                >
                  <option :value="true">Active</option>
                  <option :value="false">Inactive</option>
                </select>
              </div>
            </div>

            <!-- Address -->
            <div class="form-group">
              <label for="address" class="form-label">Address</label>
              <textarea
                v-model="form.address"
                id="address"
                rows="3"
                class="form-input"
                placeholder="Enter address"
              ></textarea>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="error-message">
              {{ errorMessage }}
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer">
            <button
              type="button"
              @click="closeModal"
              class="btn btn-secondary"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="btn btn-primary"
            >
              {{ isSubmitting ? 'Saving.. .' : (isEdit ? 'Update' :  'Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

// Always send CSRF token if present (for web.php routes)
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}
axios.defaults.withCredentials = true;

export default {
  name: 'StaffModal',
  props: {
    show: Boolean,
    staff: Object,
    isEdit: Boolean,
    branchManagerMode: {
      type: Boolean,
      default: false
    }
    ,
    branchForManager: {
      type: [String, Number],
      default: null
    }
  },
  computed: {
    managerBranchName() {
      const id = this.branchForManager || this.form.branchId
      if (!id || !this.branches) return null
      const b = this.branches.find(br => String(br.id) === String(id))
      return b ? b.name : null
    }
  },
  emits: ['close', 'success'],
  data() {
    return {
      form: {
        username: '',
        email: '',
        fullName: '',
        password: '',
        phone: '',
        branchId: '',
        role:  '',
        address: '',
        isActive: true
      },
      branches: [],
      errorMessage: '',
      isSubmitting: false,
    }
  },
  watch: {
    show:  {
      immediate: true,
      async handler(newVal) {
        if (newVal) {
          await this.loadBranches()

          if (this.isEdit && this.staff) {
            // Populate form with existing staff data; accept multiple field shapes
            const existingBranchId = this.staff.branch_id || (this.staff.branch && this.staff.branch.id) || this.staff.branchId || ''
            this.form = {
              username: this.staff.username || '',
              email: this.staff.email || '',
              fullName: this.staff.full_name || '',
              password:  '',
              phone: this.staff.phone_number || '',
              branchId: existingBranchId,
              role: this.staff.role || '',
              address: this.staff.address || '',
              isActive: this.staff.is_active !== undefined ? Boolean(this.staff.is_active) : true
            }
          } else {
            // Reset form for new staff
            this.form = {
              username: '',
              email: '',
              fullName:  '',
              password: '',
              phone: '',
              branchId: '',
              role: this.branchManagerMode ? 'STAFF' : '',
              address: '',
              isActive: true
            }
            // If manager mode, default branch to manager's branch id (if provided)
            if (this.branchManagerMode && this.branchForManager) {
              this.form.branchId = this.branchForManager
            }
          }

          this.errorMessage = ''
        }
      }
    }
  },
  methods: {
    async loadBranches() {
      try {
        const res = await axios.get('/api/admin/branches', {
          withCredentials: true,
        })

        console.log('Branches API response:', res.data)

        // Updated to match new API format
        if (res.data.success) {
          this.branches = res.data.data
          console.log('📊 Branches loaded:', this.branches.length)
        } else {
          console.error('Failed to load branches:', res.data.message)
        }
        } catch (e) {
        console.error('💥 Error loading branches:', e)
      }
    },

    async submitForm() {
      if (this.isSubmitting) return

      this.isSubmitting = true
      this.errorMessage = ''

      try {
        const url = this.isEdit
          ?  `/api/admin/staff/${this.staff.id}`
          : '/api/admin/staff'

        const method = this.isEdit ? 'put' : 'post'

        console.log('Submitting form:', this.form)

        // Map frontend form keys to backend expected keys.
        const payload = { ...this.form }
        // Ensure branchId is a number (not string)
        if (payload.branchId !== undefined && payload.branchId !== '') {
          payload.branch_id = Number(payload.branchId)
        } else if (this.isEdit) {
          // ensure we send existing branch id when editing (check multiple shapes)
          payload.branch_id = Number(this.staff?.branch_id || (this.staff?.branch && this.staff.branch.id) || this.staff?.branchId || '')
        }
        delete payload.branchId

        const res = await axios[method](url, payload, {
          withCredentials: true,
        })

        console.log('Submit response:', res.data)

        // Updated to match new API format
        if (res.data.success) {
          this.$emit('success', res.data.message || 'Success!')
          this.closeModal()
        } else {
          this.errorMessage = res.data.message || 'An error occurred.'
        }
      } catch (e) {
        console.error('Submit error:', e)

        if (e.response?.data?.errors) {
          // Laravel validation errors
          const errors = Object.values(e.response.data.errors).flat()
          this.errorMessage = errors.join(', ')
        } else if (e.response?.data?.message) {
          // API error message
          this.errorMessage = e.response.data.message
        } else {
          // Generic error
          this.errorMessage = 'An error occurred. Please try again.'
        }
      } finally {
        this.isSubmitting = false
      }
    },

    closeModal() {
      this.errorMessage = ''
      this.$emit('close')
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position:  fixed;
  inset: 0;
  z-index: 50;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-container {
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.95));
  border-radius: 16px;
  box-shadow:  0 20px 60px rgba(255, 126, 95, 0.25);
  overflow: hidden;
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  background: linear-gradient(135deg, #ff9a56, #ff8c5f);
  border-bottom: 2px solid rgba(255, 126, 95, 0.2);
}

.modal-title {
  font-size: 1.25rem;
  font-weight:  700;
  color: white;
  margin: 0;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.close-button {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-size: 1.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.close-button:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.modal-body {
  padding: 1.5rem;
}

.form-grid {
  display:  grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group:nth-child(n+5) {
  grid-column: span 1;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-input {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
  font-family: inherit;
  transition: all 0.2s ease;
  background: white;
  color: #374151;
}

.form-input:focus {
  outline: none;
  border-color: #ff7e5f;
  box-shadow: 0 0 0 3px rgba(255, 126, 95, 0.1);
}

.form-input:disabled {
  background: #f3f4f6;
  color: #9ca3af;
  cursor: not-allowed;
}

.form-input::placeholder {
  color: #d1d5db;
}

.form-group:nth-child(8) {
  grid-column: span 2;
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  padding: 1rem;
  border-radius:  8px;
  font-size: 0.875rem;
  font-weight: 600;
  border-left: 4px solid #dc2626;
  margin-top: 1rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(249, 250, 251, 0.5);
  border-top: 1px solid #e5e7eb;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-primary {
  background: linear-gradient(135deg, #ff9a56, #ff7e5f);
  color: white;
  box-shadow: 0 4px 12px rgba(255, 126, 95, 0.3);
}

btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #ff8c42, #ff6b47);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 126, 95, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background:  white;
  color: #374151;
  border: 2px solid #e5e7eb;
}

.btn-secondary:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

@media (max-width: 640px) {
  .modal-container {
    padding: 0.5rem;
  }

  .modal-header {
    padding: 1rem;
  }

  .modal-body {
    padding: 1rem;
  }

  .modal-footer {
    padding: 1rem;
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-group:nth-child(n+5) {
    grid-column: span 1;
  }
}
</style>
