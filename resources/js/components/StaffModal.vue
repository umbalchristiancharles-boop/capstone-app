<template>
  <div v-if="show" class="modal-backdrop" @click.self="closeModal">
    <div class="modal">
      <div class="modal-card">
        <form @submit.prevent="submitForm">
          <div class="modal-header">
            <h2>{{ isEdit ? 'Edit Staff Member' : 'Add New Staff Member' }}</h2>
            <button type="button" @click="closeModal" class="close-button">&times;</button>
          </div>

          <!-- Basic Info Form Grid -->
          <div class="form-grid">

            <!-- Username (optional for both custom and non-custom accounts) -->
            <div class="form-group">
              <label for="username" class="form-label">Username (Optional)</label>
              <input
                v-model="form.username"
                id="username"
                class="form-input"
                :class="{ 'read-only': isEdit }"
                :placeholder="!isEdit ? 'Leave blank to auto-generate' : ''"
                :disabled="isEdit"
              />
              <div class="form-hint">Leave blank to auto-generate a username from the staff name. You can also manually enter one.</div>
            </div>

            <!-- Full Name -->
            <div class="form-group">
              <label for="full_name" class="form-label">Full Name *</label>
              <input
                v-model="form.full_name"
                id="full_name"
                class="form-input"
                placeholder="Enter full name"
                required
              />
            </div>

            <!-- Email (both for sending credentials and saving to profile) -->
            <div class="form-group" v-if="!isEdit && !form.custom_account">
              <label for="email" class="form-label">Staff Email Address *</label>
              <input
                v-model="form.email"
                id="email"
                type="email"
                class="form-input"
                placeholder="staff@example.com"
                required
              />
              <div class="form-hint">Account credentials and password will be sent to this email, and it will be saved to their profile.</div>
            </div>

            <!-- Phone Number & Password (side by side) -->
            <div class="form-group">
              <label for="phone_number" class="form-label">Phone Number</label>
              <input
                v-model="form.phone_number"
                id="phone_number"
                type="tel"
                class="form-input"
                placeholder="Enter phone number"
              />
            </div>
            <div class="form-group password-group" v-if="!isEdit && !form.custom_account">
              <label class="form-label">Password <span style="font-weight:400">*</span></label>

              <div class="password-display-container">
                <!-- Password Display Card -->
                <div class="password-display-card">
                  <div class="password-display-label">Randomized Password:</div>
                  <div class="password-display-value">
                    <span class="password-text" v-if="generatedPassword">{{ generatedPassword }}</span>
                    <span class="password-text" v-else style="color:#999;">Will be generated and sent via email</span>
                    <button v-if="generatedPassword" type="button" class="btn btn-primary btn-copy" @click="copyGeneratedPassword">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                      </svg>
                      Copy Password
                    </button>
                  </div>
                  <div class="form-hint">A random password will be automatically generated and sent to the email address specified above. Staff member will be required to change it upon first login.</div>
                </div>

                <!-- Loading state -->
                <div v-if="fetchingDefaultPassword" class="password-loading">
                  <span style="color:#6b7280; font-size:0.9rem;">Loading default password...</span>
                </div>
              </div>
            </div>

            <!-- Current Address -->
            <div class="form-group full-span">
              <label for="address" class="form-label">Current Address & Location</label>

              <div v-if="addressSaved" class="address-card">
                <div class="address-card-header">
                  <div style="flex:1">
                    <strong>Current Address</strong>
                    <div v-if="form.latitude && form.longitude" style="font-size:0.85rem; color:#6b7280; margin-top:0.25rem">
                      📍 {{ form.latitude?.toFixed(6) }}, {{ form.longitude?.toFixed(6) }}
                    </div>
                  </div>
                  <button type="button" class="btn btn-secondary" @click="editSavedAddress">Edit</button>
                </div>
                <div class="address-card-body">{{ savedAddress }}</div>
              </div>

              <div v-else>
                <AddressCascaderWithMap
                  :initialAddress="{ region: form.region, province: form.province, city: form.city, barangay: form.barangay }"
                  :initialLocation="{ lat: form.latitude, lng: form.longitude }"
                  @save:location="onAddressSaved"
                  @update:location="onLocationUpdate"
                />
              </div>
            </div>

            <!-- Role/Department (restricted list) -->
            <div class="form-group">
              <label for="roleDepartment" class="form-label">Role / Department *</label>
              <select v-model="form.roleDepartment" id="roleDepartment" class="form-input" :required="!isEdit">
                <option value="">-- Select Role / Department --</option>
                <option value="MANAGER logistics">Logistics Manager</option>
                <option value="MANAGER finance">Finance Manager</option>
                <option value="MANAGER procurement">Procurement Manager</option>
                <option value="STAFF cashier">Cashier Staff</option>
                <option value="STAFF inventory">Inventory Staff</option>
                <option value="CUSTOM">Custom Account</option>
              </select>
            </div>

            <!-- Custom Account Type (visible when CUSTOM selected) -->
            <div class="form-group full-span" v-if="form.roleDepartment === 'CUSTOM'">
              <label class="form-label">Account Types</label>
              <div class="account-types-grid">
                <label v-for="type in accountTypeOptions" :key="type.value" style="display:flex;align-items:center;gap:8px;padding:8px;cursor:pointer">
                  <input type="checkbox" :value="type.value" v-model="form.custom_account_types" />
                  <span>{{ type.label }}</span>
                </label>
              </div>
              <div class="form-hint">Select one or more account types to grant access to</div>
            </div>

            <!-- Custom Account Permissions (visible when CUSTOM selected) -->
            <div class="form-group full-span" v-if="form.roleDepartment === 'CUSTOM'">
              <div class="custom-account-card">
                <div class="custom-account-header">
                  <strong>Custom Account Permissions</strong>
                  <label style="display:inline-flex;align-items:center;gap:8px">
                    <input type="checkbox" v-model="form.enable_custom_permissions" />
                    <span style="font-weight:600">Enable custom permissions</span>
                  </label>
                </div>
                <div class="custom-account-body" v-if="form.enable_custom_permissions">
                  <div class="permission-grid">
                    <div v-for="module in permissionTemplates" :key="module.key" class="permission-card">
                      <div class="permission-card-header">
                        <label style="display:flex;align-items:center;gap:8px">
                          <input type="checkbox" v-model="form.custom_permissions.modules[module.key]" />
                          <span style="font-weight:700; text-transform:capitalize">{{ module.label }}</span>
                        </label>
                      </div>
                      <!-- Panel-level only: functions removed per request -->
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Branch Selection -->
            <div class="form-group">
              <label for="branch_id" class="form-label">Branch *</label>
              <select v-model="form.branch_id" id="branch_id" class="form-input" :required="!isEdit" :disabled="branchReadOnly || isEdit">
                <option value="">-- Select Branch --</option>
                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
              </select>
              <div class="form-hint" v-if="branchReadOnly">Branch is locked to your assigned branch and cannot be changed.</div>
            </div>

            <!-- Password (Edit mode - optional) -->
            <div class="form-group password-group" v-if="isEdit && !form.password">
              <label for="password" class="form-label">New Password (leave blank to keep current)</label>
              <div class="password-input-wrapper">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  class="form-input"
                  placeholder="Enter new password (min 8 characters)"
                />
                <button type="button" class="password-toggle" @click="toggleShowPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'">
                  <span v-if="showPassword">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24">
                      <path stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.06 10.06 0 0 1 12 20c-5.05 0-9.29-3.81-10-8 .23-1.44.8-2.79 1.67-3.93M6.12 6.12A9.98 9.98 0 0 1 12 4c5.05 0 9.29 3.81 10 8-.23 1.44-.8 2.79-1.67 3.93M1 1l22 22M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-5.12"/>
                    </svg>
                  </span>
                  <span v-else>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24">
                      <ellipse cx="12" cy="12" rx="10" ry="8" stroke="#888" stroke-width="2"/>
                      <circle cx="12" cy="12" r="3" stroke="#888" stroke-width="2"/>
                    </svg>
                  </span>
                </button>
              </div>
            </div>

            <!-- Reset Password to Default (Edit mode) -->
            <div class="form-group full-span" v-if="isEdit">
              <div class="reset-password-section">
                <button type="button" class="btn btn-reset-default" @click="resetPasswordToDefault" :disabled="isResetting">
                  {{ isResetting ? 'Resetting...' : '🔑 Reset Password to Default' }}
                </button>
                <span class="reset-hint">Resets password to the system default. Staff will need to change it on next login.</span>
                <div v-if="resetSuccessMsg" class="reset-success">{{ resetSuccessMsg }}</div>
                <div v-if="resetErrorMsg" class="reset-error">{{ resetErrorMsg }}</div>
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
              <div v-if="isEdit && changedFields.length > 0" class="changes-summary">
                <strong>Changes:</strong> {{ changedFields.length }} — {{ changedFields.join(', ') }}
              </div>
            </div>
            <button type="button" @click="closeModal" class="btn btn-secondary" :disabled="isSubmitting">
              Cancel
            </button>
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
import AddressCascaderWithMap from './AddressCascaderWithMap.vue'

export default {
  name: 'StaffModal',
  components: { AddressCascaderWithMap },
  props: {
    show: Boolean,
    staff: Object,
    isEdit: Boolean,
    preSelectedBranchId: {
      type: [Number, String],
      default: null
    }
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
        custom_account: false,
        custom_department: '',
        custom_account_types: [],
        enable_custom_permissions: false,
        custom_permissions: null,
        roleDepartment: '',
        branch_id: '',
        address: '',
        region: '',
        province: '',
        city: '',
        barangay: '',
        latitude: null,
        longitude: null,
      },
      branchReadOnly: false,
      branches: [],
      // Address UI state
      addressSaved: false,
      savedAddress: '',
      showPassword: false,
      // default password fetched from server (OWNER only)
      fetchedDefaultPassword: null,
      fetchingDefaultPassword: false,
      showDefaultPassword: false,
      // generated password from server response (for randomized password)
      generatedPassword: null,
      errorMessage: '',
      successMessage: '',
      isSubmitting: false,
      isResetting: false,
      resetSuccessMsg: '',
      resetErrorMsg: '',
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
      permissionTemplates: [
        { key: "dashboard", label: "Dashboard", category: "view" },
        { key: "analytics", label: "Analytics", category: "view" },
        { key: "orders", label: "Orders", category: "view" },
        { key: "inventory", label: "Inventory", category: "view" },
        { key: "procurement", label: "Procurement", category: "view" },
        { key: "budgets", label: "Budgets", category: "view" },
        { key: "logistics", label: "Logistics", category: "view" },
        { key: "suppliers", label: "Suppliers", category: "view" },
        { key: "suppliers_auditing", label: "Suppliers - Auditing", category: "view" },
        { key: "staff", label: "Staff", category: "view" },
        { key: "customers", label: "Customers", category: "view" },
        { key: "settlements", label: "Settlements", category: "view" },
        { key: "announcements", label: "Announcements", category: "view" },
      ],
      accountTypeOptions: [
        { value: 'admin', label: 'Admin' },
        { value: 'finance', label: 'Finance' },
        { value: 'hr', label: 'HR' },
        { value: 'logistics', label: 'Logistics' },
        { value: 'inventory', label: 'Inventory' },
        { value: 'procurement', label: 'Procurement' },
        { value: 'kitchen', label: 'Kitchen' },
        { value: 'cashier', label: 'Cashier' },
        { value: 'reports', label: 'Reports' },
      ],
    }
  },
  mounted() {
    this.loadBranches()
    if (this.isEdit && this.staff) {
      this.$nextTick(() => {
        if (this.form.province) this.loadCities(this.form.province)
        if (this.form.city) this.loadBarangays(this.form.city)
      })
    }
  },
  methods: {
    async loadBranches() {
      const endpoints = ['/api/admin/branches', '/api/owner/branches', '/api/branches']
      // Determine enforced branch id: prefer explicit prop, fallback to localStorage.user.branch_id for HR users
      let enforcedBranchId = this.preSelectedBranchId || ''
      if ((!enforcedBranchId || String(enforcedBranchId) === '') && this.isHrUser) {
        try {
          const stored = JSON.parse(localStorage.getItem('user') || 'null') || null
          if (stored && stored.branch_id) enforcedBranchId = stored.branch_id
        } catch (e) { enforcedBranchId = enforcedBranchId }
      }

      for (const url of endpoints) {
        try {
          const res = await axios.get(url, { withCredentials: true })
          if (!res || !res.data) continue

          if (res.data.success && Array.isArray(res.data.data) && res.data.data.length > 0) {
            this.branches = res.data.data
            // If this modal was opened for HR (or preSelectedBranchId provided), restrict branches to enforcedBranchId and make readonly
            if (enforcedBranchId && (this.isHrUser || this.preSelectedBranchId)) {
              this.branches = this.branches.filter(b => String(b.id) === String(enforcedBranchId))
              this.form.branch_id = enforcedBranchId
              this.branchReadOnly = true
            }
            return
          }
          if (Array.isArray(res.data) && res.data.length > 0) {
            this.branches = res.data
            if (enforcedBranchId && (this.isHrUser || this.preSelectedBranchId)) {
              this.branches = this.branches.filter(b => String(b.id) === String(enforcedBranchId))
              this.form.branch_id = enforcedBranchId
              this.branchReadOnly = true
            }
            return
          }
          if (res.data.data && Array.isArray(res.data.data) && res.data.data.length > 0) {
            this.branches = res.data.data
            if (enforcedBranchId && (this.isHrUser || this.preSelectedBranchId)) {
              this.branches = this.branches.filter(b => String(b.id) === String(enforcedBranchId))
              this.form.branch_id = enforcedBranchId
              this.branchReadOnly = true
            }
            return
          }
        } catch (e) {
          continue
        }
      }
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
      try {
        const keys = Object.keys(this.locationMap || {})
        this.provinces = keys.map(k => ({ name: k }))
      } catch (e) {
        this.provinces = []
      }
    },

    async loadCities(provinceValue) {
      if (!provinceValue) {
        this.cities = []
        return
      }
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
      if (!cityValue) {
        this.barangays = []
        return
      }
      try {
        const prov = Object.keys(this.locationMap || {}).find(p =>
          Object.prototype.hasOwnProperty.call(this.locationMap[p].cities, cityValue)
        )
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

    clearAddress() {
      this.form.address = ''
      this.form.province = ''
      this.form.city = ''
      this.form.barangay = ''
      this.form.region = ''
    },

    onAddressUpdate(address) {
      this.form.region = address.region || ''
      this.form.province = address.province || ''
      this.form.city = address.city || ''
      this.form.barangay = address.barangay || ''
    },

    saveAddress() {
      const parts = []
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

    onAddressSaved(payload) {
      // Extract address components from the new payload format
      const addressComponents = payload.addressComponents || {}
      
      // Update address components
      this.form.region = addressComponents.region || ''
      this.form.province = addressComponents.province || ''
      this.form.city = addressComponents.city || ''
      this.form.barangay = addressComponents.barangay || ''
      
      // Update geolocation coordinates
      this.form.latitude = payload.lat
      this.form.longitude = payload.lng
      
      // Generate saved address display
      const parts = []
      if (payload.address && payload.address.trim() !== '') parts.push(payload.address.trim())
      if (addressComponents.barangay) parts.push(addressComponents.barangay)
      if (addressComponents.city) parts.push(addressComponents.city)
      if (addressComponents.province) parts.push(addressComponents.province)
      if (addressComponents.region) parts.push(addressComponents.region)
      this.savedAddress = parts.join(', ')
      this.addressSaved = true
    },

    onLocationUpdate(location) {
      this.form.latitude = location.lat
      this.form.longitude = location.lng
    },

    reconstructRoleDepartment(role, department) {
      if (!role) return ''
      // Normalize legacy BRANCH_MANAGER to MANAGER for option matching
      let normalizedRole = String(role)
      if (normalizedRole.toUpperCase() === 'BRANCH_MANAGER') normalizedRole = 'MANAGER'
      // Ensure department token matches option values (lowercase)
      if (!department) return normalizedRole
      return `${normalizedRole} ${String(department).toLowerCase()}`
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
      formData.append('latitude', this.form.latitude || '')
      formData.append('longitude', this.form.longitude || '')
      formData.append('password', this.form.password)
      formData.append('role', role)
      if (department !== null && department !== undefined && department !== '') {
        formData.append('department', department)
      }
      formData.append('branchId', this.form.branch_id || '')
      
      // Handle custom account
      if (this.form.custom_account) {
        formData.append('custom_account', '1')
        if (this.form.custom_account_types && this.form.custom_account_types.length > 0) {
          formData.append('custom_account_types', JSON.stringify(this.form.custom_account_types))
        }
        if (this.form.enable_custom_permissions && this.form.custom_permissions) {
          formData.append('custom_permissions', JSON.stringify(this.form.custom_permissions))
        }
      } else {
        formData.append('custom_account', '0')
        formData.append('notification_email', this.form.email)
      }

      return formData
    },

    buildPermissionState() {
      const state = {}
      this.permissionTemplates.forEach((module) => {
        state[module.key] = false
      })
      return { modules: state }
    },


    async submitForm() {
      this.errorMessage = ''

      // Common validation
      if (!this.form.full_name || this.form.full_name.trim() === '') {
        this.errorMessage = 'Full name is required'
        return
      }

      if (!this.isEdit) {
        // Create mode validation
        if (this.form.custom_account) {
          // Custom account mode: username required
          if (!this.form.username || this.form.username.trim() === '') {
            this.errorMessage = 'Username is required for custom account'
            return
          }
        } else {
          // Randomized password mode: email required
          if (!this.form.email || this.form.email.trim() === '') {
            this.errorMessage = 'Staff email address is required'
            return
          }
        }
        
        if (!this.form.roleDepartment) {
          this.errorMessage = 'Please select role and department'
          return
        }
        if (!this.form.branch_id) {
          this.errorMessage = 'Please select a Branch'
          return
        }
        // Require coordinates (latitude/longitude) from map pinning
        if (!this.form.latitude || !this.form.longitude) {
          this.errorMessage = 'Please pin your location on the map'
          return
        }
        // Password is optional in create mode. Backend will set a default password if left blank.
      }

      // Parse roleDepartment
      let parsedRole = null
      let parsedDepartment = null
      if (this.form.roleDepartment) {
        const parts = this.form.roleDepartment.split(' ')
        parsedRole = parts[0]
        parsedDepartment = parts.slice(1).join(' ') || null
        if (parsedRole) parsedRole = parsedRole.toUpperCase()
        if (parsedDepartment) parsedDepartment = parsedDepartment.toUpperCase()
      }

      this.isSubmitting = true
      try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
        let res

        // Use the correct API endpoint based on user role
        const apiBaseUrl = this.staffApiBaseUrl

        if (this.isEdit) {
          // Edit mode
          const payload = {
            username: this.form.username || this.staff.username || '',
            email: this.form.email || this.staff.email || '',
            fullName: this.form.full_name || this.staff.full_name || '',
            phone: this.form.phone_number !== undefined ? this.form.phone_number : (this.staff.phone_number || ''),
            address: this.form.address || this.staff.address || '',
            region: this.form.region || this.staff.region || '',
            province: this.form.province || this.staff.province || '',
            city: this.form.city || this.staff.city || '',
            barangay: this.form.barangay || this.staff.barangay || '',
            latitude: this.form.latitude || this.staff.latitude || null,
            longitude: this.form.longitude || this.staff.longitude || null,
            branchId: this.form.branch_id || this.staff.branch_id || '',
            isActive: this.staff?.is_active ? 1 : 0,
          }

          if (parsedRole && parsedDepartment) {
            payload.role = parsedRole
            payload.department = parsedDepartment
          } else if (this.staff?.role) {
            payload.role = this.staff.role
            payload.department = this.staff.department
          }

          if (this.form.password && this.form.password.trim() !== '') {
            payload.password = this.form.password
          }

          res = await axios.put(`${apiBaseUrl}/staff/${this.staff.id}`, payload, { withCredentials: true })
        } else {
          // Create mode - build form data based on user role
          if (this.isManagerHrUser) {
            // Manager/HR endpoint uses JSON format
            const payload = {
              username: this.form.username,
              email: this.form.email,
              fullName: this.form.full_name,
              phone: this.form.phone_number || '',
              address: this.form.address || '',
              region: this.form.region || '',
              province: this.form.province || '',
              city: this.form.city || '',
              barangay: this.form.barangay || '',
              latitude: this.form.latitude || null,
              longitude: this.form.longitude || null,
            }
            
            // Add custom account or notification email field based on mode
            if (this.form.custom_account) {
              payload.custom_account = 1
              payload.custom_account_types = this.form.custom_account_types
              if (this.form.enable_custom_permissions && this.form.custom_permissions) {
                payload.custom_permissions = this.form.custom_permissions
              }
            } else {
              payload.custom_account = 0
              payload.notification_email = this.form.email
            }
            
            if (parsedRole) payload.role = parsedRole
            if (parsedDepartment) payload.department = parsedDepartment
            res = await axios.post(`${apiBaseUrl}/staff`, payload, {
              withCredentials: true,
              headers: { 'Content-Type': 'application/json' }
            })
          } else {
            // Admin endpoint uses FormData for full staff creation with documents
            const formData = this.buildCreateFormData(parsedRole, parsedDepartment)
            res = await axios.post(`${apiBaseUrl}/staff`, formData, {
              withCredentials: true,
              headers: { 'Content-Type': 'multipart/form-data' }
            })
          }
        }

        if (res.data?.success !== false) {
          // Store generated password if returned by server
          if (res.data.password) {
            this.generatedPassword = res.data.password
          }
          this.$emit('success', res.data)
          this.closeModal()
        } else {
          this.errorMessage = res.data?.message || 'Failed to save staff member'
        }
      } catch (error) {
        console.error('Submit error:', error)
        if (error.response?.data) {
          const serverMsg = error.response.data.message || error.response.data.error || JSON.stringify(error.response.data)
          this.errorMessage = `Failed to save: ${serverMsg}`
        } else {
          this.errorMessage = 'Failed to save staff member. Please try again.'
        }
      } finally {
        this.isSubmitting = false
      }
    },

    closeModal() {
      this.errorMessage = ''
      this.resetSuccessMsg = ''
      this.resetErrorMsg = ''
      this.generatedPassword = null
      this.form.notification_email = ''
      this.$emit('close')
    },

    async resetPasswordToDefault() {
      if (!this.staff || !this.staff.id) return
      if (!confirm('Reset this staff member\'s password to the system default?')) return
      this.isResetting = true
      this.resetSuccessMsg = ''
      this.resetErrorMsg = ''
      try {
        const res = await axios.post(`/api/admin/staff/${this.staff.id}/reset-password`, {}, { withCredentials: true })
        if (res.data && res.data.success) {
          this.resetSuccessMsg = 'Password reset to default: ' + (res.data.defaultPassword || 'Chikintayo_123')
        } else {
          this.resetErrorMsg = res.data?.message || 'Failed to reset password.'
        }
      } catch (e) {
        this.resetErrorMsg = e?.response?.data?.message || 'Failed to reset password.'
      } finally {
        this.isResetting = false
      }
    }
    ,
    async fetchDefaultPassword() {
      // Only try to fetch default password for OWNER, ADMIN, or SUPER_ADMIN users
      // Skip for other roles to avoid 403 errors
      const userRole = window.userRole || '';
      if (userRole !== 'OWNER' && userRole !== 'ADMIN' && userRole !== 'SUPER_ADMIN' && userRole !== 'SUPERADMIN') {
        // Fallback to hardcoded default for display purposes
        this.fetchedDefaultPassword = 'Chikintayo_123';
        return;
      }

      if (this.fetchingDefaultPassword) return
      this.fetchingDefaultPassword = true
      try {
        const res = await axios.get('/api/admin/config/default-password', { withCredentials: true })
        if (res.data && res.data.success && res.data.default_password) {
          this.fetchedDefaultPassword = res.data.default_password
        } else {
          // Fallback to hardcoded default
          this.fetchedDefaultPassword = 'Chikintayo_123'
        }
      } catch (e) {
        // Fallback to hardcoded default on error - default password is optional feature
        this.fetchedDefaultPassword = 'Chikintayo_123'
      } finally {
        this.fetchingDefaultPassword = false
      }
    },

    copyDefaultToClipboard() {
      const passwordToCopy = this.fetchedDefaultPassword || this.defaultPasswordValue
      if (!passwordToCopy) return
      try {
        navigator.clipboard?.writeText(passwordToCopy)
        // Show visual feedback
        alert('Password copied to clipboard: ' + passwordToCopy)
      } catch (e) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea')
        textArea.value = passwordToCopy
        document.body.appendChild(textArea)
        textArea.select()
        document.execCommand('copy')
        document.body.removeChild(textArea)
        alert('Password copied to clipboard: ' + passwordToCopy)
      }
    },

    copyGeneratedPassword() {
      const passwordToCopy = this.generatedPassword
      if (!passwordToCopy) return
      try {
        navigator.clipboard?.writeText(passwordToCopy)
        // Show visual feedback
        alert('Password copied to clipboard: ' + passwordToCopy)
      } catch (e) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea')
        textArea.value = passwordToCopy
        document.body.appendChild(textArea)
        textArea.select()
        document.execCommand('copy')
        document.body.removeChild(textArea)
        alert('Password copied to clipboard: ' + passwordToCopy)
      }
    },

    async refreshCsrfToken() {
      try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        if (csrfToken) {
          axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken
        }
      } catch (e) {
        console.warn('Failed to refresh CSRF token', e)
      }
    }
  },

  computed: {
    // Determine the correct API endpoint for staff management based on user role
    // BRANCH_MANAGER, MANAGER, HR should use /api/manager/hr/staff
    // OWNER, ADMIN, SUPER_ADMIN should use /api/admin/staff
    staffApiBaseUrl() {
      const userRole = window.userRole || ''
      const role = userRole.toUpperCase()

      if (role === 'BRANCH_MANAGER' || role === 'MANAGER' || role === 'HR') {
        return '/api/manager/hr'
      }
      return '/api/admin'
    },

    // Check if user can use the manager HR endpoint for create/update
    isManagerHrUser() {
      const userRole = window.userRole || ''
      const role = userRole.toUpperCase()
      return role === 'BRANCH_MANAGER' || role === 'MANAGER' || role === 'HR'
    },
    isHrUser() {
      // Treat explicit HR role, or a Manager whose department is HR, as HR user
      try {
        const stored = JSON.parse(localStorage.getItem('user') || 'null') || null
        const role = (stored && stored.role) ? String(stored.role).toUpperCase() : (window.userRole || '').toUpperCase()
        const dept = (stored && stored.department) ? String(stored.department).toUpperCase() : ''
        if (role === 'HR') return true
        if (role === 'MANAGER' && dept === 'HR') return true
        if (role === 'BRANCH_MANAGER' && dept === 'HR') return true
        return false
      } catch (e) {
        const userRole = window.userRole || ''
        return String(userRole).toUpperCase() === 'HR'
      }
    },

    // Reset password always uses admin endpoint (only exists there)
    resetPasswordApiUrl() {
      return '/api/admin'
    },

    defaultPasswordValue() {
      return 'Chikintayo_123'
    },
    changedFields() {
      if (!this.isEdit || !this.staff) return []
      const changes = []

      if (this.form.full_name && this.form.full_name !== (this.staff.full_name || ''))
        changes.push('Full name')
      if (this.form.email && this.form.email !== (this.staff.email || ''))
        changes.push('Email')
      if (this.form.phone_number !== undefined && this.form.phone_number !== (this.staff.phone_number || ''))
        changes.push('Phone')
      if (this.form.address && this.form.address !== (this.staff.address || ''))
        changes.push('Address')
      if (this.form.region && this.form.region !== (this.staff.region || ''))
        changes.push('Region')
      if (this.form.province && this.form.province !== (this.staff.province || ''))
        changes.push('Province')
      if (this.form.city && this.form.city !== (this.staff.city || ''))
        changes.push('City')
      if (this.form.barangay && this.form.barangay !== (this.staff.barangay || ''))
        changes.push('Barangay')

      const currentRoleDept = this.reconstructRoleDepartment(this.staff?.role, this.staff?.department)
      if (this.form.roleDepartment && this.form.roleDepartment !== currentRoleDept)
        changes.push('Role/Department')

      if (this.form.branch_id && String(this.form.branch_id) !== String(this.staff.branch_id || ''))
        changes.push('Branch')

      if (this.form.password && this.form.password.trim() !== '')
        changes.push('Password')

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
            notification_email: '',
            full_name: newStaff.full_name || '',
            phone_number: newStaff.phone_number || '',
            password: '',
            custom_account: newStaff.custom_account == 1,
            custom_department: newStaff.custom_account == 1 ? (newStaff.department || '') : '',
            custom_account_types: [],
            enable_custom_permissions: newStaff.role === 'CUSTOM' && !!newStaff.custom_permissions,
            custom_permissions: newStaff.role === 'CUSTOM' && newStaff.custom_permissions 
              ? JSON.parse(newStaff.custom_permissions) 
              : this.buildPermissionState(),
            roleDepartment: reconstructedRoleDept,
            branch_id: newStaff.branch_id || '',
            address: newStaff.address || '',
            region: newStaff.region || '',
            province: newStaff.province || '',
            city: newStaff.city || '',
            barangay: newStaff.barangay || '',
            latitude: newStaff.latitude || null,
            longitude: newStaff.longitude || null,
          }

          if (this.form.region || this.form.province || this.form.city || this.form.barangay) {
            this.savedAddress = [this.form.address, this.form.barangay, this.form.city, this.form.province]
              .filter(Boolean).join(', ')
            this.addressSaved = true
          }
        } else {
          this.form = {
            username: '',
            email: '',
            notification_email: '',
            full_name: '',
            phone_number: '',
            password: '',
            custom_account: false,
            custom_department: '',
            custom_account_types: [],
            enable_custom_permissions: false,
            custom_permissions: null,
            roleDepartment: '',
            branch_id: '',
            address: '',
            province: '',
            city: '',
            barangay: '',
            region: '',
            latitude: null,
            longitude: null,
          }
          this.addressSaved = false
        }
        this.errorMessage = ''
      }
    },
    show(newVal) {
      if (!newVal) {
        this.closeModal()
      } else {
        // Refresh CSRF token when modal opens to avoid stale token issues
        this.refreshCsrfToken()

        // When opening the modal in create mode, ensure any previous `staff` prop
        // value does not leak into the form. Reset the form state for fresh create.
        if (!this.isEdit) {
          this.form = {
            username: '',
            email: '',
            notification_email: '',
            full_name: '',
            phone_number: '',
            password: '',
            custom_account: false,
            custom_department: '',
            custom_account_types: [],
            enable_custom_permissions: false,
            custom_permissions: null,
            roleDepartment: '',
            branch_id: this.preSelectedBranchId || '',
            address: '',
            province: '',
            city: '',
            barangay: '',
            region: '',
            latitude: null,
            longitude: null,
          }
          this.addressSaved = false
          this.errorMessage = ''
          // try to fetch default password for owners/admins (optional display)
          this.fetchDefaultPassword()
        }
      }
    },
    roleDepartment(newRole) {
      if (newRole === 'CUSTOM') {
        // Auto-initialize custom permissions when CUSTOM role selected
        if (!this.form.custom_permissions) {
          this.form.custom_permissions = this.buildPermissionState()
        }
      }
    },
  },
}
</script>

<style scoped>
/* Update: align StaffModal styles with HRStaffManagement color scheme and typography */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(4px);
}

.modal {
  background: #fff;
  border-radius: 12px;
  width: 1100px;
  max-width: 98vw;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px rgba(2,6,23,0.25);
  animation: modalSlideIn 0.28s ease-out;
  font-family: 'Inter', 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-30px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-card { padding: 0; }

.modal-header {
  background: #ffffff;
  color: var(--text-dark, #1f2937);
  padding: 1.25rem 1.75rem;
  border-radius: 12px 12px 0 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e6eefc;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f172a;
}

.close-button {
  background: #fff;
  color: #374151;
  border: 1px solid #e5e7eb;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 1.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
  flex-shrink: 0;
}

.close-button:hover { transform: scale(1.05); }

.form-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem 2rem;
  padding: 2rem 2rem 1.75rem 2rem;
  align-items: start;
}

.form-group { display: flex; flex-direction: column; width: 100%; }
.form-group.full-span { grid-column: 1 / -1; }

.form-label {
  font-size: 0.9rem;
  font-weight: 700;
  color: #374151;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-input {
  padding: 0.875rem;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  font-size: 0.95rem;
  font-family: inherit;
  transition: all 0.15s ease;
  background: white;
  color: #111827;
  min-height: 46px;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: #0066FF;
  box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.08);
}

.form-input::placeholder { color: #c7ced6; }

.form-input:disabled, .read-only { background: #f8fafc; color: #9ca3af; cursor: not-allowed; }

select.form-input {
  padding-right: 2rem;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1.2em;
}

.address-card { padding: 1rem; border: 1px solid #E5E7EB; border-radius: 8px; background: #ffffff; }
.address-card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem }
.address-card-header strong { color:#0f172a; font-size:0.95rem }
.address-card-body { color:#4b5563; font-size:0.95rem; line-height:1.5 }

.documents-section { padding: 1.75rem; border-top: 1px solid #E5E7EB; }

.documents-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 1rem;
  padding-bottom: 0.25rem;
  border-bottom: 2px solid #0066FF;
}

.documents-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }

.error-message {
  background: rgba(239, 68, 68, 0.05);
  color: #dc2626;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  border-left: 4px solid #dc2626;
  margin: 0 1.5rem 1rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1rem 1.75rem;
  background: rgba(249,250,251,0.6);
  border-top: 1px solid #E5E7EB;
  border-radius: 0 0 12px 12px;
}

.btn {
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  border: none;
  transition: all 0.18s ease;
  text-transform: none;
  letter-spacing: 0.2px;
  min-height: 42px;
  box-sizing: border-box;
}

.btn:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-primary {
  background: #0066FF;
  color: white;
  box-shadow: 0 6px 14px rgba(59,130,246,0.12);
}

.btn-primary:hover:not(:disabled) {
  background: #3B82F6;
  transform: translateY(-2px);
}

.btn-secondary {
  background: #6c757d;
  color: #fff;
  border: none;
}

.btn-secondary:hover:not(:disabled) { background: #5a6268; }

/* Password toggle styles */
.password-group .password-input-wrapper { position: relative; display: flex; align-items: center; }
.password-group .form-input { padding-right: 3rem; }

.password-toggle { position: absolute; right: 0.875rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center; justify-content: center; height: 2rem; width: 2rem; }
.password-toggle svg { display: block; }

/* Password Display Styles - adapted to HR blue accent */
.password-display-container { display:flex; flex-direction:column; gap:0.75rem }
.password-display-card { background: linear-gradient(180deg, #f1f8ff 0%, #ffffff 100%); border: 1px solid #0066FF; border-radius: 10px; padding: 1.15rem; }
.password-display-label { font-size: 0.9rem; font-weight:700; color:#1E3A8A; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.5px }
.password-display-value { display:flex; align-items:center; gap:1rem; flex-wrap:wrap }
.password-text { font-family: 'Courier New', monospace; font-size:1.15rem; font-weight:700; color:#0f172a; background:#fff; padding:0.5rem 0.9rem; border-radius:6px; border:1px solid #e6eefc; letter-spacing:1px }
.btn-copy { display:inline-flex; align-items:center; gap:0.5rem; padding:0.5rem 0.9rem; font-size:0.9rem; background:#0066FF; color:#fff; border-radius:8px; border:none }
.btn-copy:hover { background:#3B82F6 }
.password-display-card .form-hint { margin-top:0.6rem; font-size:0.9rem; color:#6b7280 }
.password-loading { display:flex; align-items:center; padding:0.5rem }

.changes-summary { font-size:0.9rem; color:#6b7280; background: rgba(2,6,23,0.03); padding:0.5rem 1rem; border-radius:6px; border:1px solid rgba(2,6,23,0.04) }

/* Responsive Design */
@media (max-width: 768px) {
  .modal { width: 95vw; margin: 1rem; }
  .form-grid, .documents-grid { grid-template-columns: 1fr; gap: 1rem; padding: 1rem; }
}

.reset-password-section { display:flex; flex-wrap:wrap; align-items:center; gap:0.75rem; padding:1rem; background:#fff7ed; border:1px solid #fde68a; border-radius:8px }

.btn-reset-default { background:#dc2626; color:#fff; border:none; padding:0.5rem 1.25rem; border-radius:6px; font-size:0.9rem; font-weight:600; cursor:pointer }
.btn-reset-default:hover { background:#b91c1c }
.btn-reset-default:disabled { opacity:0.6; cursor:not-allowed }

.reset-hint { font-size:0.875rem; color:#374151 }
.reset-success { width:100%; padding:0.5rem 0.75rem; background:#dcfce7; border:1px solid #86efac; border-radius:6px; color:#166534; font-size:0.9rem }
.reset-error { width:100%; padding:0.5rem 0.75rem; background:#fef2f2; border:1px solid #fca5a5; border-radius:6px; color:#dc2626; font-size:0.9rem }

/* Custom Account Card Styles */
.custom-account-card { border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb; padding:1.25rem }
.custom-account-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; padding-bottom:0.75rem; border-bottom:2px solid #e5e7eb }
.custom-account-body { margin-top:1rem }
.permission-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:1rem }
.permission-card { padding:1rem; background:#ffffff; border:1px solid #e5e7eb; border-radius:6px; transition:all 0.2s ease }
.permission-card:hover { border-color:#0066FF; box-shadow:0 0 0 2px rgba(0, 102, 255, 0.05) }
.permission-card-header { display:flex; align-items:center; gap:0.75rem }
.permission-card-header label { display:flex; align-items:center; gap:0.5rem; margin:0; cursor:pointer; font-size:0.9rem }
.permission-card-header input[type="checkbox"] { margin:0 }

/* Account Types Grid */
.account-types-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:0.5rem; padding:1rem; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px }
@media (max-width: 768px) { .account-types-grid { grid-template-columns:repeat(2, 1fr) } }
.account-types-grid label { margin:0; padding:0.5rem; background:#ffffff; border:1px solid #e5e7eb; border-radius:4px; transition:all 0.2s ease; font-size:0.9rem }
.account-types-grid label:hover { border-color:#0066FF; background:#f0f7ff }
.account-types-grid label input[type="checkbox"] { margin:0; cursor:pointer }
</style>

