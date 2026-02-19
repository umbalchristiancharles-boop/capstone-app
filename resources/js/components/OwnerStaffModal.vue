<template>
  <div v-if="show" class="modal-backdrop" @click.self="closeModal">
    <div class="modal">
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

                <!-- Current Address / Address Cascader -->
                <div class="form-group full-span">
                  <label for="address" class="form-label">Current Address</label>

                  <div v-if="addressSaved" class="address-card">
                    <div class="address-card-header">
                      <strong>Current Address</strong>
                      <button type="button" class="btn btn-secondary" @click="editSavedAddress">Edit</button>
                    </div>
                    <div class="address-card-body">{{ savedAddress }}</div>
                  </div>

                  <div v-else>
                    <textarea
                      v-model="form.address"
                      id="address"
                      rows="2"
                      class="form-input"
                      placeholder="House number, street, subdivision"
                      :required="!isEdit"
                    ></textarea>

                    <!-- Address Cascader (Region → Province → City → Barangay) -->
                    <div style="margin-top:0.5rem;">
                      <AddressCascader :initialAddress="{ province: form.province, city: form.city, barangay: form.barangay }" :showSaveButton="false" @update:address="onAddressUpdate" />
                    </div>

                    <div style="margin-top:0.5rem; display:flex; gap:0.5rem;">
                      <button type="button" class="btn btn-primary" @click="saveAddress">Save Address</button>
                      <button type="button" class="btn btn-secondary" @click="() => { form.address=''; form.province=''; form.city=''; form.barangay=''; }">Clear</button>
                    </div>
                  </div>
                </div>
              <!-- Role/Department -->
              <div class="form-group">
                <label for="roleDepartment" class="form-label">Role / Department *</label>
                <select v-model="form.roleDepartment" id="roleDepartment" class="form-input" :required="!isEdit">
                  <option value="">-- Select Role / Department --</option>
                  <optgroup label="Managers">
                    <option value="BRANCH_MANAGER hr">Manager HR</option>
                    <option value="BRANCH_MANAGER finance">Manager Finance</option>
                    <option value="BRANCH_MANAGER inventory">Manager Inventory</option>
                    <option value="BRANCH_MANAGER logistics">Manager Logistics</option>
                  </optgroup>
                  <optgroup label="Staff">
                    <option value="STAFF cashier">Staff Cashier</option>
                    <option value="STAFF finance">Staff Finance</option>
                    <option value="STAFF inventory">Staff Inventory</option>
                  </optgroup>
                </select>
              </div>
              <!-- Branch Selection -->
              <div class="form-group">
                <label for="branch_id" class="form-label">Branch *</label>
                <select v-model="form.branch_id" id="branch_id" class="form-input" :required="!isEdit">
                  <option value="">-- Select Branch --</option>
                  <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
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
              <!-- Documents (Create Only) -->
              <div v-if="!isEdit" class="form-group full-span">
                <label class="form-label">Valid Government-issued ID *</label>
                <input
                  type="file"
                  class="form-input"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  required
                  @change="(e) => handleFileChange('government_id', e)"
                >
              </div>
              <div v-if="!isEdit" class="form-group full-span">
                <label class="form-label">PSA Birth Certificate *</label>
                <input
                  type="file"
                  class="form-input"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  required
                  @change="(e) => handleFileChange('psa_birth_certificate', e)"
                >
              </div>
              <div v-if="!isEdit" class="form-group full-span">
                <label class="form-label">NBI Clearance *</label>
                <input
                  type="file"
                  class="form-input"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  required
                  @change="(e) => handleFileChange('nbi_clearance', e)"
                >
              </div>
              <div v-if="!isEdit" class="form-group full-span">
                <label class="form-label">Police Clearance *</label>
                <input
                  type="file"
                  class="form-input"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  required
                  @change="(e) => handleFileChange('police_clearance', e)"
                >
              </div>
              <div v-if="!isEdit" class="form-group full-span">
                <label class="form-label">Medical Certificate / Health Clearance *</label>
                <input
                  type="file"
                  class="form-input"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  required
                  @change="(e) => handleFileChange('medical_certificate', e)"
                >
              </div>
              <div v-if="!isEdit" class="form-group full-span">
                <label class="form-label">Drug Test Result *</label>
                <input
                  type="file"
                  class="form-input"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  required
                  @change="(e) => handleFileChange('drug_test_result', e)"
                >
              </div>
              <div v-if="!isEdit" class="form-group full-span">
                <label class="form-label">SSS Number / SSS ID *</label>
                <input
                  type="file"
                  class="form-input"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  required
                  @change="(e) => handleFileChange('sss_id', e)"
                >
              </div>
              <div v-if="!isEdit" class="form-group full-span">
                <label class="form-label">PhilHealth Number / ID *</label>
                <input
                  type="file"
                  class="form-input"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  required
                  @change="(e) => handleFileChange('philhealth_id', e)"
                >
              </div>
              <div v-if="!isEdit" class="form-group full-span">
                <label class="form-label">Pag-IBIG Number / MDF *</label>
                <input
                  type="file"
                  class="form-input"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  required
                  @change="(e) => handleFileChange('pagibig_mdf', e)"
                >
              </div>
              <div v-if="!isEdit" class="form-group full-span">
                <label class="form-label">TIN (Tax Identification Number) *</label>
                <input
                  type="file"
                  class="form-input"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  required
                  @change="(e) => handleFileChange('tin_id', e)"
                >
              </div>
              <div v-if="!isEdit" class="form-group full-span">
                <label class="form-label">Diploma / Transcript / Certificate of Enrollment *</label>
                <input
                  type="file"
                  class="form-input"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  required
                  @change="(e) => handleFileChange('diploma_transcript', e)"
                >
              </div>
            </div>
          </div>
          <!-- Error Message -->
          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer">
            <div style="flex:1; display:flex; align-items:center; gap:12px;">
              <div v-if="isEdit" class="changes-summary">
                <strong>Changes:</strong>
                <span v-if="changedFields.length===0"> 0</span>
                <span v-else> {{ changedFields.length }} — {{ changedFields.join(', ') }}</span>
              </div>
            </div>
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
import AddressCascader from './AddressCascader.vue'

export default {
  name: 'OwnerStaffModal',
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
        full_name: '',
        phone_number: '',
        password: '',
        roleDepartment: '',
        branch_id: '',
        address: '',
        region: '',
        province: '',
        city: '',
        barangay: '',
      },
      provinces: [],
      cities: [],
      barangays: [],
      // Local fallback for provinces -> cities -> barangays when backend endpoints are not available
      locationMap: {
        "Metro Manila": {
          cities: {
            "Quezon City": ["Project 2", "Cubao"],
            "Manila": ["Tondo", "Binondo"]
          }
        },
        "Cebu": {
          cities: {
            "Cebu City": ["Poblacion", "Mabolo"],
            "Lapu-Lapu City": ["Poblacion", "Marigondon"]
          }
        },
        "Davao del Sur": {
          cities: {
            "Davao City": ["Buhangin", "Talomo"]
          }
        }
      },
      // documentFiles stores selected files for upload
      documentFiles: {},
      branches: [],
      // Address UI state
      addressSaved: false,
      savedAddress: '',
      showPassword: false,
      errorMessage: '',
      isSubmitting: false,
    }
  },
  components: { AddressCascader },
  mounted() {
    this.loadBranches()
    this.loadProvinces()
  },
  methods: {
    async loadBranches() {
      // Try multiple endpoints to support different user roles / backends.
      // 1) Admin endpoint (works for admins)
      // 2) Owner endpoint (some backends expose this for owners)
      // 3) Generic branches endpoint
      const endpoints = ['/api/admin/branches', '/api/owner/branches', '/api/branches']
      for (const url of endpoints) {
        try {
          const res = await axios.get(url, { withCredentials: true })
          if (!res || !res.data) continue

          // Normalize responses that return { success: true, data: [...] }
          if (res.data.success && Array.isArray(res.data.data) && res.data.data.length > 0) {
            this.branches = res.data.data
            return
          }

          // Responses that directly return an array (res.data = [...])
          if (Array.isArray(res.data) && res.data.length > 0) {
            this.branches = res.data
            return
          }

          // Some responses might be { data: [...] } without success flag
          if (res.data.data && Array.isArray(res.data.data) && res.data.data.length > 0) {
            this.branches = res.data.data
            return
          }
        } catch (e) {
          // try next endpoint
          continue
        }
      }

      // If none succeeded, leave branches empty so UI shows placeholder and user gets validation
      this.branches = []
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
      // Fallback to local locationMap if API did not return results
      try {
        const keys = Object.keys(this.locationMap || {})
        this.provinces = keys.map(k => ({ name: k }))
      } catch (e) {
        this.provinces = []
      }
    },
    async loadCities(provinceValue) {
      if (!provinceValue) { this.cities = []; return }
      // Local fallback: if province exists in locationMap, use it
      if (this.locationMap && this.locationMap[provinceValue]) {
        this.cities = Object.keys(this.locationMap[provinceValue].cities).map(name => ({ name }))
        return
      }
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
      // Local fallback: search locationMap for city and populate barangays
      try {
        const prov = Object.keys(this.locationMap || {}).find(p => Object.prototype.hasOwnProperty.call(this.locationMap[p].cities, cityValue))
        if (prov) {
          this.barangays = (this.locationMap[prov].cities[cityValue] || []).map(name => ({ name }))
          return
        }
      } catch (e) {}

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
      // reset dependent fields and load cities
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
    toggleShowPassword() {
      this.showPassword = !this.showPassword
    },
    buildCreateFormData(role, department) {
      const formData = new FormData()
      formData.append('username', this.form.username)
      formData.append('email', this.form.email)
      formData.append('fullName', this.form.full_name)
      formData.append('phone', this.form.phone_number || '')
      formData.append('address', this.form.address || '')
      formData.append('region', this.form.region || '')
      formData.append('province', this.form.province || '')
      formData.append('city', this.form.city || '')
      formData.append('barangay', this.form.barangay || '')
      formData.append('password', this.form.password)
      formData.append('role', role)
      formData.append('department', department)
      formData.append('branchId', this.form.branch_id || '')

      return formData
    },
    closeModal() {
      this.errorMessage = ''
      this.$emit('close')
    },
    reconstructRoleDepartment(role, department) {
      if (!role || !department) return ''
      return `${role} ${department}`
    },
    onAddressSaved(address) {
      // store to form and build display summary
      this.form.region = address.region || ''
      this.form.province = address.province || ''
      this.form.city = address.city || ''
      this.form.barangay = address.barangay || ''
      this.savedAddress = [this.form.address, this.form.barangay, this.form.city, this.form.province].filter(Boolean).join(', ')
      this.addressSaved = true
    },
    // handle address updates from AddressCascader
    onAddressUpdate(address) {
      this.form.region = address.region || ''
      this.form.province = address.province || ''
      this.form.city = address.city || ''
      this.form.barangay = address.barangay || ''
    },
    saveAddress() {
      const parts = []
      // ensure region is preserved if provided by the cascader
      this.form.region = this.form.region || ''
      if (this.form.address && this.form.address.trim() !== '') parts.push(this.form.address.trim())
      if (this.form.barangay) parts.push(this.form.barangay)
      if (this.form.city) parts.push(this.form.city)
      if (this.form.province) parts.push(this.form.province)
      this.savedAddress = parts.join(', ')
      this.addressSaved = true
    },
    editSavedAddress() {
      this.addressSaved = false
    },
    async submitForm() {
      this.errorMessage = ''
      // Validation differs for create vs edit
      if (!this.isEdit) {
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
        if (!this.form.roleDepartment) {
          this.errorMessage = 'Please select role and department'
          return
        }
        if (!this.form.branch_id) {
          this.errorMessage = 'Please select a Branch'
          return
        }
        if (!this.form.address || !this.form.region || !this.form.province || !this.form.city || !this.form.barangay) {
          this.errorMessage = 'Please provide Address, Region, Province, City, and Barangay.'
          return
        }
        if (!this.form.password || this.form.password.trim() === '') {
          this.errorMessage = 'Password is required'
          return
        }
      } else {
        // Edit: require at least one updatable field and basic full_name presence
        if (!this.form.full_name || this.form.full_name.trim() === '') {
          this.errorMessage = 'Full name is required'
          return
        }
      }
      // Parse roleDepartment if provided
      let parsedRole = null
      let parsedDepartment = null
      if (this.form.roleDepartment) {
        const parts = this.form.roleDepartment.split(' ')
        parsedRole = parts[0]
        parsedDepartment = parts.slice(1).join(' ') || null
        // Normalize to expected casing used by backend
        if (parsedRole) parsedRole = parsedRole.toUpperCase()
        if (parsedDepartment) parsedDepartment = parsedDepartment.toUpperCase()
        if (!parsedRole || !parsedDepartment) {
          this.errorMessage = 'Invalid role/department selection'
          return
        }
      }
      this.isSubmitting = true
      try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
        let res
        if (this.isEdit) {
          // Build a full payload for the server by merging existing staff values
          // with provided form values. Some backends expect complete payloads.
          // Build payload that matches backend validation keys
          const payload = {
            username: this.form.username || this.staff.username || '',
            email: this.form.email || this.staff.email || '',
            fullName: this.form.full_name || this.staff.full_name || '',
            phone: this.form.phone_number !== undefined ? this.form.phone_number : (this.staff.phone_number || ''),
            address: this.form.address || this.staff.address || '',
            // include location fields if available (backend will ignore extra keys)
            region: this.form.region || this.staff.region || '',
            province: this.form.province || this.staff.province || '',
            city: this.form.city || this.staff.city || '',
            barangay: this.form.barangay || this.staff.barangay || '',
            // backend accepts either branchId or branch_id; send camelCase to match SPA
            branchId: this.form.branch_id || this.staff.branch_id || '',
            // isActive is required by backend validation; send current staff active flag if present
            isActive: (this.form.is_active !== undefined) ? this.form.is_active : (this.staff?.is_active ? 1 : 0),
          }
          if (parsedRole && parsedDepartment) {
            payload.role = parsedRole
            payload.department = parsedDepartment
          } else if (this.staff && this.staff.role) {
            payload.role = this.staff.role
            payload.department = this.staff.department
          }
          if (this.form.password && this.form.password.trim() !== '') payload.password = this.form.password

          res = await axios.put(`/api/admin/staff/${this.staff.id}`, payload, { withCredentials: true })
        } else {
          const formData = this.buildCreateFormData(parsedRole, parsedDepartment)
          // attach any selected document files
          try {
            for (const k of Object.keys(this.documentFiles || {})) {
              const f = this.documentFiles[k]
              if (f) formData.append(k, f)
            }
          } catch (e) {}
          res = await axios.post('/api/admin/staff', formData, { withCredentials: true, headers: { 'Content-Type': 'multipart/form-data' } })
        }
        if (res.data.success) {
          this.$emit('success')
        } else {
          this.errorMessage = res.data.message || 'Failed to save staff member'
        }
      } catch (error) {
        // Log full server response for debugging
        console.error('Submit error:', error)
        if (error && error.response) {
          console.error('Server response:', error.response.data)
          const serverMsg = error.response.data && (error.response.data.message || error.response.data.error || JSON.stringify(error.response.data))
          this.errorMessage = `Failed to update account (${error.response.status}): ${serverMsg}`
        } else if (error && error.message) {
          this.errorMessage = `Failed to update account: ${error.message}`
        } else {
          this.errorMessage = 'Failed to update account. Please try again.'
        }
      } finally {
        this.isSubmitting = false
      }
    },
    handleFileChange(field, e) {
      try {
        const file = e.target && e.target.files ? e.target.files[0] : null
        if (!file) {
          this.documentFiles[field] = null
          return
        }
        // Vue 3: avoid this.$set (not available); assign directly
        this.documentFiles[field] = file
        // also store filename for display if needed
        this.form[`${field}_filename`] = file.name
      } catch (err) {
        console.error('File change handler error:', err)
      }
    }
  },
  computed: {
    changedFields() {
      if (!this.isEdit || !this.staff) return []
      const changes = []
      if (this.form.full_name && this.form.full_name !== (this.staff.full_name || '')) changes.push('Full name')
      if (this.form.email && this.form.email !== (this.staff.email || '')) changes.push('Email')
      if (this.form.phone_number !== undefined && this.form.phone_number !== (this.staff.phone_number || '')) changes.push('Phone')
      if (this.form.address && this.form.address !== (this.staff.address || '')) changes.push('Address')
      if (this.form.region && this.form.region !== (this.staff.region || '')) changes.push('Region')
      if (this.form.province && this.form.province !== (this.staff.province || '')) changes.push('Province')
      if (this.form.city && this.form.city !== (this.staff.city || '')) changes.push('City')
      if (this.form.barangay && this.form.barangay !== (this.staff.barangay || '')) changes.push('Barangay')
      const currentRoleDept = this.reconstructRoleDepartment(this.staff?.role, this.staff?.department)
      if (this.form.roleDepartment && this.form.roleDepartment !== currentRoleDept) changes.push('Role/Department')
      if (this.form.branch_id && String(this.form.branch_id) !== String(this.staff.branch_id || '')) changes.push('Branch')
      if (this.form.password && this.form.password.trim() !== '') changes.push('Password')
      // documents
      if (this.documentFiles && Object.keys(this.documentFiles).length > 0) {
        const anyFile = Object.values(this.documentFiles).some(f => !!f)
        if (anyFile) changes.push('Documents')
      }
      return changes
    }
  },
  watch: {
    staff: {
      immediate: true,
      handler(newStaff) {
        if (this.isEdit && newStaff) {
          const reconstructedRoleDept = this.reconstructRoleDepartment(newStaff.role, newStaff.department)
          this.form = {
            username: newStaff.username || '',
            email: newStaff.email || '',
            full_name: newStaff.full_name || '',
            phone_number: newStaff.phone_number || '',
            password: '',
            roleDepartment: reconstructedRoleDept,
            branch_id: newStaff.branch_id || '',
            address: newStaff.address || '',
            region: newStaff.region || '',
            province: newStaff.province || '',
            city: newStaff.city || '',
            barangay: newStaff.barangay || '',
          }
          // if there are values, try to load lists so selects populate
          if (this.form.province) this.loadCities(this.form.province)
          if (this.form.city) this.loadBarangays(this.form.city)
          // show summary card when editing existing staff with address
          if (this.form.region || this.form.province || this.form.city || this.form.barangay) {
            this.savedAddress = [this.form.address, this.form.barangay, this.form.city, this.form.province].filter(Boolean).join(', ')
            this.addressSaved = true
          }
        } else {
          this.form = {
            username: '',
            email: '',
            full_name: '',
            phone_number: '',
            password: 'Chikin_Tayo@2526',
            roleDepartment: '',
            branch_id: '',
            address: '',
            province: '',
            city: '',
            barangay: '',
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
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
  margin-bottom: 1rem;
  align-items: start;
}

/* ensure grid items stretch to fill available column space */
.form-grid {
  width: 100%;
  justify-items: stretch;
}

.read-only {
  background: #f9fafb;
}

.form-group {
  display: flex;
  flex-direction: column;
  width: 100%;
}

/* avoid using nth-child grid rules (v-if changes order) — use explicit .full-span instead */

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

.address-card {
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fafafc;
}
.address-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
}
.address-card-body {
  margin-top: 0.5rem;
  color: #374151;
}


.form-group select.form-input,
.form-group textarea.form-input {
  width: 100%;
}

.form-group.full-span {
  grid-column: 1 / -1;
}

/* increase spacing for save/clear buttons in address area */
.form-group.full-span .btn {
  margin-right: 0.5rem;
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

.changes-summary {
  font-size: 0.9rem;
  color: #374151;
}

.changes-summary strong {
  margin-right: 6px;
}
</style>
