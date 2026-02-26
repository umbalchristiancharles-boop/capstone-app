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
              <div v-if="!isEdit" class="form-group password-group">
                <label for="password" class="form-label">Password <span style="font-weight:400">*</span></label>
                <div style="display:flex; gap:0.75rem; align-items:flex-start; flex-wrap:wrap;">
                  <!-- password display (read-only) + toggle -->
                  <div style="display:flex; gap:0.5rem; align-items:center; flex:1; min-width:220px;">
                    <input
                      :value="defaultPassword"
                      :type="showPassword ? 'text' : 'password'"
                      id="password"
                      class="form-input read-only"
                      readonly
                      autocomplete="new-password"
                      style="flex:1; background-color: #f3f4f6;"
                    />
                    <button type="button" class="password-toggle" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'" style="height:40px;">
                      <span v-if="showPassword">
                        <!-- Eye-off SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24">
                          <path stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.06 10.06 0 0 1 12 20c-5.05 0-9.29-3.81-10-8 .23-1.44.8-2.79 1.67-3.93M6.12 6.12A9.98 9.98 0 0 1 12 4c5.05 0 9.29 3.81 10 8-.23 1.44-.8 2.79-1.67 3.93M1 1l22 22M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-5.12"/>
                        </svg>
                      </span>
                      <span v-else>
                        <!-- Eye SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24">
                          <ellipse cx="12" cy="12" rx="10" ry="8" stroke="#888" stroke-width="2"/>
                          <circle cx="12" cy="12" r="3" stroke="#888" stroke-width="2"/>
                        </svg>
                      </span>
                    </button>
                  </div>
                  <!-- copy button -->
                  <div style="display:flex; gap:0.5rem; align-items:center;">
                    <button type="button" class="btn btn-primary" @click="copyDefaultToClipboard" style="padding:0.4rem 0.6rem;">Copy</button>
                  </div>
                  <div class="small-hint" style="color:#6b7280;font-size:0.9rem; flex-basis:100%;">Default password is automatically set for new staff.</div>
                </div>
              </div>
              <div v-else-if="isEdit" class="form-group">
                <label for="password" class="form-label">New Password (optional)</label>
                <input
                  v-model="form.password"
                  type="password"
                  id="password"
                  class="form-input"
                  placeholder="••••••••"
                  autocomplete="new-password"
                />
              </div>
              <div v-if="isEdit" class="form-group">
                <label class="form-label">Role *</label>
                <div class="form-input" style="background-color: #f3f4f6; padding: 0.5rem; border-radius: 8px; display: flex; align-items: center;">
                  {{ (form.role === 'BRANCH_MANAGER' || form.role === 'MANAGER') ? 'Manager' : (form.role === 'STAFF' ? 'Staff' : (form.role === 'HR' ? 'HR' : form.role)) }}
                </div>
              </div>

              <!-- Department -->
              <div class="form-group" v-if="isEdit || currentUserRole === 'OWNER'">
                <label for="department" class="form-label">Department</label>
                <select
                  v-model="form.department"
                  id="department"
                  class="form-input"
                >
                  <option value="">-- Select Department (optional) --</option>
                  <option v-for="d in departmentOptions" :key="d.value" :value="d.value">{{ d.label }}</option>
                </select>
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
                required
              ></textarea>
            </div>

            <!-- Province -->
            <div class="form-group">
              <label for="province" class="form-label">Province</label>
              <select v-model="form.province" id="province" class="form-input" @change="onProvinceChange">
                <option value="">-- Select Province --</option>
                <option v-for="p in provinces" :key="p.id || p.code || p.name" :value="p.id || p.code || p.name">{{ p.name || p.label || p.province || p }}</option>
              </select>
            </div>

            <!-- City -->
            <div class="form-group">
              <label for="city" class="form-label">City / Municipality</label>
              <select v-model="form.city" id="city" class="form-input" @change="onCityChange">
                <option value="">-- Select City / Municipality --</option>
                <option v-for="c in cities" :key="c.id || c.code || c.name" :value="c.id || c.code || c.name">{{ c.name || c.label || c.city || c }}</option>
              </select>
            </div>

            <!-- Barangay -->
            <div class="form-group">
              <label for="barangay" class="form-label">Barangay</label>
              <select v-model="form.barangay" id="barangay" class="form-input">
                <option value="">-- Select Barangay --</option>
                <option v-for="b in barangays" :key="b.id || b.code || b.name" :value="b.id || b.code || b.name">{{ b.name || b.label || b.barangay || b }}</option>
              </select>
            </div>

            <!-- Document uploads removed for admin create flow -->

            <!-- Documents (Edit Only - View/Download/Delete/Upload) -->
            <div v-if="isEdit && documents" class="documents-section">
              <h4 class="documents-title">Staff Documents</h4>
              <div class="documents-grid">
                <div 
                  v-for="(doc, docType) in getDocumentLabels()"
                  :key="docType"
                  class="document-item"
                >
                  <label class="doc-label">{{ doc.label }}</label>
                  <div class="doc-controls">
                    <div v-if="documents[docType]" class="doc-exists">
                      <button 
                        type="button"
                        @click="downloadDocument(docType)"
                        class="btn-doc-action btn-download"
                        title="Download"
                      >
                        ⬇️ Download
                      </button>
                      <button
                        type="button"
                        @click="deleteDocument(docType)"
                        class="btn-doc-action btn-delete-doc"
                        :disabled="deletingDocs.includes(docType)"
                        title="Delete"
                      >
                        {{ deletingDocs.includes(docType) ? '❌ Deleting...' : '🗑️ Delete' }}
                      </button>
                    </div>
                    <input
                      type="file"
                      @change="(e) => handleDocumentUpload(docType, e)"
                      accept=".jpg,.jpeg,.png,.webp,.pdf"
                      class="doc-upload-input"
                      :key="`upload-${docType}`"
                    />
                  </div>
                </div>
              </div>
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
              v-if="isEdit"
              type="button"
              class="btn btn-warning"
              :disabled="isResetting"
              @click="resetPassword"
            >
              {{ isResetting ? 'Resetting...' : 'Reset Password' }}
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
    },
    roleOptions() {
      // For this deployment we only allow creating Owner accounts from admin staff management.
      // Return only the OWNER role option when creating accounts (non-edit mode).
      return [
        { value: 'OWNER', label: 'Owner' },
      ]
    }
    ,
    departmentOptions() {
      return [
        { value: 'HR', label: 'HR' },
        { value: 'FINANCE', label: 'Finance' },
        { value: 'INVENTORY', label: 'Inventory' },
        { value: 'LOGISTICS', label: 'Logistics' },
        { value: 'CASHIER', label: 'Cashier' }
      ]
    }
  },
  emits: ['close', 'success'],
  data() {
    return {
      showPassword: false,
      defaultPassword: 'Chikintayo_123', // Replace with dynamic fetch if needed
      form: {
        id: '',
        username: '',
        email: '',
        fullName: '',
        password: '',
        phone: '',
            branchId: '',
        role:  '',
        department: '',
        address: '',
        province: '',
        city: '',
        barangay: '',
        isActive: true
      },
      provinces: [],
      cities: [],
      barangays: [],
      // documentFiles removed for admin create flow — attachments handled only in edit mode
      // branches removed — owner creation does not need branches
      errorMessage: '',
      successMessage: '',
      currentUserRole: sessionStorage.getItem('user_role') || null,
      isSubmitting: false,
      isResetting: false,
      documents: null,
      deletingDocs: [],
      uploadingDocs: {},
    }
  },
  watch: {
    show:  {
      immediate: true,
      async handler(newVal) {
        if (newVal) {

          if (this.isEdit && this.staff) {
            // Populate form with existing staff data; accept multiple field shapes
            const existingBranchId = this.staff.branch_id || (this.staff.branch && this.staff.branch.id) || this.staff.branchId || ''
            this.form = {
              id: this.staff.id || this.staff.user_id || this.staff.userId || '',
              username: this.staff.username || '',
              email: this.staff.email || '',
              fullName: this.staff.full_name || '',
              password:  '',
              phone: this.staff.phone_number || '',
              branchId: existingBranchId,
              role: this.staff.role || '',
              department: this.staff.department || this.staff.dept || '',
              address: this.staff.address || '',
              isActive: this.staff.is_active !== undefined ? Boolean(this.staff.is_active) : true
            }
            this.documents = this.staff.documents || {}
            this.form.province = this.staff.province || ''
            this.form.city = this.staff.city || ''
            this.form.barangay = this.staff.barangay || ''
            if (this.form.province) this.loadCities(this.form.province)
            if (this.form.city) this.loadBarangays(this.form.city)
          } else {
            // Reset form for new staff
            this.form = {
              id: '',
              username: '',
              email: '',
              fullName:  '',
              password: '',
              phone: '',
                branchId: '',
                role: 'OWNER',
                department: '',
              address: '',
              province: '',
              city: '',
              barangay: '',
              isActive: true
            }
            // If manager mode, default branch to manager's branch id (if provided)
            if (this.branchManagerMode && this.branchForManager) {
              this.form.branchId = this.branchForManager
            }

            // load provinces for dropdowns
            this.loadProvinces()
          }

          this.errorMessage = ''
        }
      }
    }
  },
  methods: {
    copyDefaultToClipboard() {
      if (!this.defaultPassword) return
      try {
        navigator.clipboard?.writeText(this.defaultPassword)
      } catch (e) {}
    },
    // document attachments are not sent during admin create
    buildCreateFormData() {
      // return plain object to be sent as JSON for admin create
      return {
        username: this.form.username,
        email: this.form.email,
        fullName: this.form.fullName,
        phone: this.form.phone || '',
        address: this.form.address || '',
        branchId: this.form.branchId || '',
        role: this.form.role || 'OWNER',
        department: this.form.department || null,
        password: this.defaultPassword,
        province: this.form.province || '',
        city: this.form.city || '',
        barangay: this.form.barangay || ''
      }
    },

    // loadBranches removed — branches are not required for Owner creation

    async submitForm() {
      this.isSubmitting = true
      this.errorMessage = ''

      // Require address fields
      if (!this.form.address || !this.form.province || !this.form.city || !this.form.barangay) {
        this.errorMessage = 'Please provide Address, Province, City, and Barangay.'
        this.isSubmitting = false
        return
      }

      // Ensure CSRF cookie/header are set before submitting
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        if (csrfToken) {
          axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken
        }
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true }).catch(() => {})

        // helper to read cookie
        function getCookie(name) {
          const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)'))
          return match ? match[2] : null
        }
        const xsrf = getCookie('XSRF-TOKEN')
        if (xsrf) {
          try {
            axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrf)
          } catch (e) {
            axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrf
          }
        }
      } catch (e) {
        console.warn('Could not initialize CSRF cookie before submit', e)
      }

      let url, method
      try {
        if (this.isEdit) {
          url = `/api/admin/staff/${this.form.id}`
          method = 'PUT'
        } else {
          url = '/api/admin/staff'
          method = 'POST'
        }

        let payload = this.form
        let headers

        if (!this.isEdit) {
          // send JSON for admin create (no files attached here)
          payload = this.buildCreateFormData()
          headers = undefined
        }

        // Send form data
        const res = await axios({ method, url, data: payload, headers })

        if (res.data.success) {
          this.$emit('success', res.data)
          this.closeModal()
        } else {
          this.errorMessage = res.data.message || 'An error occurred.'
        }
      } catch (e) {
        console.error('Submit error:', e)

        // If we got a 419 due to stale CSRF, try to refresh the CSRF cookie and retry once
        if (e.response && e.response.status === 419) {
          try {
            await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
            const csrfToken2 = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            if (csrfToken2) axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken2
            const xsrf2 = (document.cookie.match(new RegExp('(^|; )' + 'XSRF-TOKEN' + '=([^;]*)')) || [])[2]
            if (xsrf2) {
              try { axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrf2) } catch (_) { axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrf2 }
            }

            // retry the original request once
            const retryPayload = this.isEdit ? this.form : this.buildCreateFormData()
            const retryHeaders = this.isEdit ? undefined : undefined
            const retryRes = await axios({ method, url, data: retryPayload, headers: retryHeaders })
            if (retryRes.data && retryRes.data.success) {
              this.$emit('success', retryRes.data)
              this.closeModal()
              return
            }
          } catch (retryErr) {
            console.error('Retry after CSRF refresh failed', retryErr)
            // Show a visible error to the user so they can manually reload
            try { console.log('document.cookie:', document.cookie) } catch (e) {}
            try { console.log('axios.defaults.headers.common:', axios.defaults.headers.common) } catch (e) {}
            // Try to resync by performing a one-time reload (avoid loops)
            try { sessionStorage.setItem('appReloaded', '1') } catch (e) {}
            try { sessionStorage.setItem('preReloadPath', window.location.pathname) } catch (e) {}
            try { window.location.reload() } catch (e) {}
          }
        }

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

    async loadProvinces() {
      const endpoints = ['/api/locations/provinces', '/api/provinces']
      for (const url of endpoints) {
        try {
          const res = await axios.get(url, { withCredentials: true })
          if (!res || !res.data) continue
          if (res.data.success && Array.isArray(res.data.data)) {
            this.provinces = res.data.data
            return
          }
          if (Array.isArray(res.data)) {
            this.provinces = res.data
            return
          }
        } catch (e) { continue }
      }
      this.provinces = []
    },
    async loadCities(provinceValue) {
      if (!provinceValue) { this.cities = []; return }
      const endpoints = [`/api/locations/cities?province=${encodeURIComponent(provinceValue)}`, `/api/cities?province=${encodeURIComponent(provinceValue)}`]
      for (const url of endpoints) {
        try {
          const res = await axios.get(url, { withCredentials: true })
          if (!res || !res.data) continue
          if (res.data.success && Array.isArray(res.data.data)) {
            this.cities = res.data.data
            return
          }
          if (Array.isArray(res.data)) {
            this.cities = res.data
            return
          }
        } catch (e) { continue }
      }
      this.cities = []
    },
    async loadBarangays(cityValue) {
      if (!cityValue) { this.barangays = []; return }
      const endpoints = [`/api/locations/barangays?city=${encodeURIComponent(cityValue)}`, `/api/barangays?city=${encodeURIComponent(cityValue)}`]
      for (const url of endpoints) {
        try {
          const res = await axios.get(url, { withCredentials: true })
          if (!res || !res.data) continue
          if (res.data.success && Array.isArray(res.data.data)) {
            this.barangays = res.data.data
            return
          }
          if (Array.isArray(res.data)) {
            this.barangays = res.data
            return
          }
        } catch (e) { continue }
      }
      this.barangays = []
    },
    onProvinceChange() {
      this.form.city = ''
      this.form.barangay = ''
      this.cities = []
      this.barangays = []
      this.loadCities(this.form.province)
    },
    onCityChange() {
      this.form.barangay = ''
      this.barangays = []
      this.loadBarangays(this.form.city)
    },

    closeModal() {
      this.errorMessage = ''
      this.$emit('close')
    },

    async resetPassword() {
      if (!this.isEdit || !this.form.id) return
      if (!confirm(`Reset password for "${this.form.username || this.form.fullName}" to default?`)) return
      this.isResetting = true
      this.errorMessage = ''
      this.successMessage = ''
      try {
        // ensure CSRF
        try {
          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          if (csrfToken) axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken
          await axios.get('/sanctum/csrf-cookie', { withCredentials: true }).catch(() => {})
          const match = document.cookie.match(new RegExp('(^|; )' + 'XSRF-TOKEN' + '=([^;]*)'))
          const xsrf = match ? match[2] : null
          if (xsrf) {
            try { axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrf) } catch (e) { axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrf }
          }
        } catch (e) {
          console.warn('Failed to refresh CSRF before reset', e)
        }

        const res = await axios.post(`/api/admin/staff/${this.form.id}/reset-password`, {}, { withCredentials: true })
        if (res.data && res.data.success) {
          this.successMessage = res.data.message || 'Password reset to default.'
          // Optionally show default password (be careful in production)
          if (res.data.defaultPassword) {
            this.successMessage += ' Default password: ' + res.data.defaultPassword
          }
          this.$emit('success', { reset: true, id: this.form.id })
        } else {
          this.errorMessage = res.data?.message || 'Failed to reset password.'
        }
      } catch (e) {
        this.errorMessage = e.response?.data?.message || 'Failed to reset password.'
      } finally {
        this.isResetting = false
      }
    },

    getDocumentLabels() {
      return {
        government_id: { label: 'Valid Government-issued ID' },
        psa_birth_certificate: { label: 'PSA Birth Certificate' },
        nbi_clearance: { label: 'NBI Clearance' },
        police_clearance: { label: 'Police Clearance' },
        medical_certificate: { label: 'Medical Certificate / Health Clearance' },
        drug_test_result: { label: 'Drug Test Result' },
        sss_id: { label: 'SSS Number / SSS ID' },
        philhealth_id: { label: 'PhilHealth Number / ID' },
        pagibig_mdf: { label: 'Pag-IBIG Number / MDF' },
        tin_id: { label: 'TIN (Tax Identification Number)' },
        diploma_transcript: { label: 'Diploma / Transcript / Certificate of Enrollment' },
      }
    },

    downloadDocument(docType) {
      const url = `/api/admin/staff/${this.form.id}/document/${docType}`
      window.open(url, '_blank')
    },

    async deleteDocument(docType) {
      if (!confirm(`Delete this ${this.getDocumentLabels()[docType].label}?`)) {
        return
      }

      this.deletingDocs.push(docType)

      try {
        const res = await axios.delete(`/api/admin/staff/${this.form.id}/document/${docType}`, {
          withCredentials: true
        })

        if (res.data.success) {
          this.documents[docType] = null
          this.$forceUpdate()
        } else {
          this.errorMessage = res.data.message || 'Failed to delete document'
        }
      } catch (e) {
        console.error('Delete error:', e)
        this.errorMessage = e.response?.data?.message || 'Failed to delete document'
      } finally {
        this.deletingDocs = this.deletingDocs.filter(d => d !== docType)
      }
    },

    async handleDocumentUpload(docType, event) {
      const file = event?.target?.files?.[0]
      if (!file) return

      this.uploadingDocs[docType] = true

      try {
        const formData = new FormData()
        formData.append('file', file)

        const res = await axios.post(`/api/admin/staff/${this.form.id}/document/${docType}`, formData, {
          withCredentials: true,
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        if (res.data.success) {
          if (this.documents) {
            this.documents[docType] = {
              path: res.data.path,
              url: `/api/admin/staff/${this.form.id}/document/${docType}`
            }
          }
          this.$forceUpdate()
        } else {
          this.errorMessage = res.data.message || 'Failed to upload document'
        }
      } catch (e) {
        console.error('Upload error:', e)
        this.errorMessage = e.response?.data?.message || 'Failed to upload document'
      } finally {
        delete this.uploadingDocs[docType]
      }
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
  text-transform: uppercase;
  letter-spacing: 0.5px;
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
</style>
