<template>
  <div v-if="show" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container">
      <div class="modal-card">
        <form @submit.prevent="submitForm">
          <!-- Modal Header -->
          <div class="modal-header">
            <h3 class="modal-title">
              {{ isEdit ? 'Edit Staff Account' : 'Add New Staff Member' }}
            </h3>
            <button type="button" @click="closeModal" class="close-button">✕</button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body">
            <div class="form-grid">
              <!-- Username (Create only) -->
              <div class="form-group" v-if="!isEdit">
                <label for="username" class="form-label">Username *</label>
                <input
                  v-model="form.username"
                  type="text"
                  id="username"
                  class="form-input"
                  placeholder="Enter username"
                  required
                />
              </div>
              <!-- Full Name -->
              <div class="form-group">
                <label for="fullName" class="form-label">Full Name *</label>
                <input
                  v-model="form.full_name"
                  type="text"
                  id="fullName"
                  class="form-input"
                  placeholder="Enter full name"
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
              <!-- Phone Number -->
              <div class="form-group">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input
                  v-model="form.phone_number"
                  type="text"
                  id="phone_number"
                  class="form-input"
                  placeholder="Enter phone number"
                />
              </div>
              <!-- Department -->
              <div class="form-group">
                <label for="department" class="form-label">Department</label>
                <select v-model="form.department" id="department" class="form-input">
                  <option value="">-- Select Department (optional) --</option>
                  <option value="HR">HR</option>
                  <option value="FINANCE">Finance</option>
                  <option value="INVENTORY">Inventory</option>
                  <option value="LOGISTICS">Logistics</option>
                  <option value="CASHIER">Cashier</option>
                </select>
              </div>
              <!-- Password (Create only) -->
              <div class="form-group password-group" v-if="!isEdit">
                <label for="password" class="form-label">Password *</label>
                <div class="password-input-wrapper">
                  <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    id="password"
                    class="form-input"
                    placeholder="Enter password (min 8 characters)"
                    required
                  />
                  <button type="button" class="password-toggle" @click="toggleShowPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'">
                    <span v-if="showPassword">
                      <!-- Eye-off SVG -->
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.06 10.06 0 0 1 12 20c-5.05 0-9.29-3.81-10-8 .23-1.44.8-2.79 1.67-3.93M6.12 6.12A9.98 9.98 0 0 1 12 4c5.05 0 9.29 3.81 10 8-.23 1.44-.8 2.79-1.67 3.93M1 1l22 22M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-5.12"/></svg>
                    </span>
                    <span v-else>
                      <!-- Eye SVG -->
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><ellipse cx="12" cy="12" rx="10" ry="8" stroke="#888" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="#888" stroke-width="2"/></svg>
                    </span>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <!-- Error Message -->
          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn btn-secondary" :disabled="isSubmitting">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
              {{ isSubmitting ? 'Saving...' : (isEdit ? 'Update Staff' : 'Add Staff') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'OwnerStaffModal',
  props: {
    show: Boolean,
    staff: Object,
    isEdit: Boolean
  },
  emits: ['close', 'success'],
  data() {
    return {
      form: {
        username: '',
        email: '',
        full_name: '',
        phone_number: '',
        password: '',
        department: '',
      },
      showPassword: false,
      errorMessage: '',
      isSubmitting: false
    }
  },
  methods: {
    toggleShowPassword() {
      this.showPassword = !this.showPassword
    },
    closeModal() {
      this.errorMessage = ''
      this.$emit('close')
    },
    async submitForm() {
      this.errorMessage = ''

      // Validate required fields
      if (!this.form.username || this.form.username.trim() === '') {
        this.errorMessage = 'Username is required'
        return
      }

      if (!this.form.full_name || this.form.full_name.trim() === '') {
        this.errorMessage = 'Full name is required'
        return
      }

      if (!this.form.email || this.form.email.trim() === '') {
        this.errorMessage = 'Email is required'
        return
      }

      // For new staff, password is required
      if (!this.isEdit) {
        if (!this.form.password || this.form.password.trim() === '') {
          this.errorMessage = 'Password is required'
          return
        }
      }

      this.isSubmitting = true

      try {
        let res

        if (this.isEdit) {
          // Update existing staff
          res = await axios.put(`/api/admin/staff/${this.staff.id}`, {
            full_name: this.form.full_name,
            email: this.form.email,
            phone_number: this.form.phone_number || '',
            department: this.form.department || '',
          }, {
            withCredentials: true
          })
        } else {
          // Create new staff - role should be 'staff' (Owner-specific logic: Owner should not be added as staff)
          res = await axios.post('/api/admin/staff', {
            username: this.form.username,
            email: this.form.email,
            full_name: this.form.full_name,
            phone_number: this.form.phone_number || '',
            password: this.form.password,
            department: this.form.department || '',
            role: 'staff', // Owner-specific logic: ensure staff role, not Owner
          }, {
            withCredentials: true
          })
        }

        if (res.data.success) {
          this.$emit('success')
        } else {
          this.errorMessage = res.data.message || 'Failed to save staff member'
        }
      } catch (error) {
        console.error('Submit error:', error)
        this.errorMessage = error.response?.data?.message || 'Failed to save staff member. Please try again.'
      } finally {
        this.isSubmitting = false
      }
    }
  },
  watch: {
    staff: {
      immediate: true,
      handler(newStaff) {
        if (this.isEdit && newStaff) {
          // Populate form for edit
          this.form = {
            username: newStaff.username || '',
            email: newStaff.email || '',
            full_name: newStaff.full_name || '',
            phone_number: newStaff.phone_number || '',
            password: '', // Don't prefill password
            department: newStaff.department || '',
          }
        } else {
          // Reset form for add
          this.form = {
            username: '',
            email: '',
            full_name: '',
            phone_number: '',
            password: 'Chikin_Tayo@2526',
            department: '',
          }
        }
      }
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
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

.read-only {
  background: #f9fafb;
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

.documents-section {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 2px solid #e5e7eb;
}

.documents-title {
  font-size: 1rem;
  font-weight: 700;
  color: #374151;
  margin-bottom: 1rem;
}

.documents-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.document-item {
  padding: 1rem;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.doc-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.doc-controls {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.doc-exists {
  display: flex;
  gap: 0.25rem;
  flex-wrap: wrap;
}

.btn-doc-action {
  padding: 0.5rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  flex: 1;
  min-width: 80px;
}

.btn-download {
  background: #3b82f6;
  color: white;
}

.btn-download:hover {
  background: #2563eb;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.btn-delete-doc {
  background: #ef4444;
  color: white;
}

.btn-delete-doc:hover:not(:disabled) {
  background: #dc2626;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}

.btn-delete-doc:disabled {
  background: #9ca3af;
  cursor: not-allowed;
  opacity: 0.6;
}

.doc-upload-input {
  padding: 0.5rem;
  font-size: 0.75rem;
  border: 1px dashed #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.doc-upload-input:hover {
  border-color: #ff7e5f;
  background: rgba(255, 126, 95, 0.05);
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

  .documents-grid {
    grid-template-columns: 1fr;
  }

  .btn-doc-action {
    min-width: auto;
  }

  .doc-exists {
    flex-direction: column;
  }
}
/* Password toggle styles */
.password-group .password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}
.password-group input.form-input {
  padding-right: 2.5rem;
}
.password-toggle {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 2rem;
  width: 2rem;
}
.password-toggle svg {
  display: block;
}
</style>
