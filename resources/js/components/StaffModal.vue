<template>
  <div v-if="show" class="modal-backdrop" @click.self="closeModal">
    <div class="modal-box">
      <!-- Header -->
      <div class="modal-header">
        <h3>{{ isEdit ?  'Edit Staff Account' : 'Create Staff Account' }}</h3>
        <button class="modal-close" @click="closeModal">×</button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <form @submit.prevent="submitForm">
          <!-- Username & Email -->
          <div class="form-row">
            <div class="form-group">
              <label>Username *</label>
              <input
                v-model="form.username"
                type="text"
                :disabled="isEdit"
                placeholder="owner_admin"
                required
              />
            </div>

            <div class="form-group">
              <label>Email *</label>
              <input
                v-model="form.email"
                type="email"
                placeholder="Enter email"
                required
              />
            </div>
          </div>

          <!-- Full Name & Password -->
          <div class="form-row">
            <div class="form-group">
              <label>Full Name *</label>
              <input
                v-model="form.fullName"
                type="text"
                placeholder="Enter full name"
                required
              />
            </div>

            <div class="form-group">
              <label>Password {{ isEdit ? '' : '*' }}</label>
              <input
                v-model="form.password"
                type="password"
                placeholder="••••••••"
                :required="!isEdit"
              />
            </div>
          </div>

          <!-- Phone & Branch -->
          <div class="form-row">
            <div class="form-group">
              <label>Phone Number</label>
              <input
                v-model="form.phone"
                type="text"
                placeholder="+63 XXX XXX XXXX"
              />
            </div>

            <div class="form-group">
              <label>Branch *</label>
              <select v-model="form.branchId" required>
                <option value="" disabled>Select Branch</option>
                <option
                  v-for="branch in branches"
                  :key="branch.id"
                  :value="branch.id"
                >
                  {{ branch.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Address -->
          <div class="form-group">
            <label>Address</label>
            <textarea
              v-model="form.address"
              placeholder="Enter address"
              rows="3"
            ></textarea>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="error-box">
            {{ errorMessage }}
          </div>

          <!-- Actions -->
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="closeModal">
              Cancel
            </button>
            <button type="submit" class="btn-create" :disabled="isSubmitting">
              {{ isSubmitting ? 'Saving...' : (isEdit ? 'Update' : 'Create') }}
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
  name: 'StaffModal',
  props: {
    show: Boolean,
    staff: Object,
    isEdit: Boolean,
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
        address: '',
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
            this.form = {
              username: this.staff.username,
              email: this.staff.email,
              fullName: this.staff.fullName,
              password: '',
              phone: this.staff.phone || '',
              branchId: this.staff.branchId,
              address: this.staff.address || '',
            }
          } else {
            this.form = {
              username: '',
              email: '',
              fullName:  '',
              password: '',
              phone: '',
              branchId: '',
              address: '',
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

        if (res.data.ok) {
          this.branches = res.data.branches
          console.log('📊 Branches count:', this.branches.length)
        } else {

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
          ? `/api/admin/staff/${this.staff.id}`
          : '/api/admin/staff'

        const method = this.isEdit ? 'put' : 'post'

        const res = await axios[method](url, this.form, {
          withCredentials: true,
        })

        if (res.data.ok) {
          this.$emit('success', res.data.message || 'Success!')
          this.closeModal()
        } else {
          this.errorMessage = res.data.message || 'An error occurred.'
        }
      } catch (e) {
        if (e.response?. data?.errors) {
          const errors = Object.values(e.response.data.errors).flat()
          this.errorMessage = errors.join(', ')
        } else {
          this.errorMessage = e.response?.data?.message || 'An error occurred.  Please try again.'
        }
      } finally {
        this. isSubmitting = false
      }
    },

    closeModal() {
      this.$emit('close')
    }
  }
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width:  100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-box {
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  background: linear-gradient(135deg, #ff8c42, #ff6b35);
  color: white;
  border-radius: 16px 16px 0 0;
}

.modal-header h3 {
  margin:  0;
  font-size: 1.5rem;
}

.modal-close {
  background: transparent;
  border: none;
  color: white;
  font-size: 2rem;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
}

.modal-body {
  padding: 2rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 1rem;
}

.form-group label {
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding:  0.75rem;
  border: 1px solid #ddd;
  border-radius:  8px;
  font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #ff8c42;
}

.error-box {
  background: #fee;
  color:  #c33;
  padding:  1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.btn-cancel,
.btn-create {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor:  pointer;
}

.btn-cancel {
  background: #e0e0e0;
  color: #333;
}

.btn-create {
  background: linear-gradient(135deg, #ff8c42, #ff6b35);
  color: white;
}

.btn-create:disabled {
  opacity:  0.5;
  cursor: not-allowed;
}
</style>
