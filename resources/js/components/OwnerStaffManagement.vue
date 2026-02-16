<template>
  <div class="staff-management-page">
    <div class="staff-header">
      <h1>Staff Management (Owner)</h1>
      <div class="header-actions">
        <input v-model="searchQuery" type="text" placeholder="Search staff..." class="search-input" />
        <button @click="refreshStaff" class="btn-primary">Refresh</button>
        <button @click="openAddModal" class="btn-success">+ Add Person</button>
      </div>
    </div>

    <div v-if="isLoading" class="loading-state">Loading...</div>
    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

    <div v-if="!isLoading && flattenedStaff.length">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Branch</th>
            <th>Role</th>
            <th>Department</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in filteredStaff" :key="m.id">
            <td>{{ m.full_name || m.username }}</td>
            <td>{{ m.username }}</td>
            <td>{{ m.email }}</td>
            <td>{{ m.branch_name || '—' }}</td>
            <td>{{ m.role }}</td>
            <td>{{ m.department || '-' }}</td>
            <td>
              <button @click="edit(m)" class="btn-sm btn-info">Edit</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-container">
          <div class="modal-header">
            <h3>{{ isEdit ? 'Edit Person' : 'Add Person' }}</h3>
            <button @click="closeModal">×</button>
          </div>

          <div class="modal-body">
            <div class="form-grid">
              <div class="form-group">
                <label>Username</label>
                <input v-model="form.username" type="text" />
              </div>
              <div class="form-group">
                <label>Full Name</label>
                <input v-model="form.fullName" type="text" />
              </div>
              <div class="form-group">
                <label>Email</label>
                <input v-model="form.email" type="email" />
              </div>
              <div class="form-group">
                <label>Phone</label>
                <input v-model="form.phone" type="text" />
              </div>

              <div class="form-group">
                <label>Branch</label>
                <select v-model="form.branchId">
                  <option value="">-- Select Branch --</option>
                  <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
              </div>

              <div class="form-group">
                <label>Type</label>
                <select v-model="form.personType">
                  <option value="STAFF">Staff</option>
                  <option value="MANAGER">Manager</option>
                </select>
              </div>

              <div class="form-group" v-if="form.personType === 'MANAGER'">
                <label>Manager Role</label>
                <select v-model="form.managerRole">
                  <option value="HR">HR</option>
                  <option value="FINANCE">Finance</option>
                  <option value="LOGISTICS">Logistics</option>
                  <option value="INVENTORY">Inventory</option>
                </select>
              </div>

              <div class="form-group" v-if="form.personType === 'STAFF'">
                <label>Staff Role</label>
                <select v-model="form.staffRole">
                  <option value="LOGISTICS">Logistics</option>
                  <option value="INVENTORY">Inventory</option>
                  <option value="CASHIER">Cashier</option>
                </select>
              </div>

              <div class="form-group">
                <label>Password (new users)</label>
                <input v-model="form.password" type="password" placeholder="optional — will default" />
              </div>

              <!-- Document uploads (backend expects these for create) -->
              <div class="form-group" v-for="doc in documentKeys" :key="doc">
                <label>{{ docLabels[doc].label }}</label>
                <input type="file" @change="onFileChange(doc, $event)" />
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-secondary" @click="closeModal">Cancel</button>
            <button class="btn-primary" @click="submit">{{ isEdit ? 'Update' : 'Create' }}</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'OwnerStaffManagement',
  data() {
    return {
      isLoading: false,
      errorMessage: '',
      searchQuery: '',
      branches: [],
      dataRaw: [],
      showModal: false,
      isEdit: false,
      form: {
        id: '',
        username: '',
        fullName: '',
        email: '',
        phone: '',
        branchId: '',
        personType: 'STAFF',
        managerRole: 'HR',
        staffRole: 'LOGISTICS',
        password: '',
      },
      documentFiles: {},
      documentKeys: ['resume','government_id','psa_birth_certificate','nbi_clearance','police_clearance','medical_certificate','drug_test_result','sss_id','philhealth_id','pagibig_mdf','tin_id','diploma_transcript'],
      docLabels: {
        resume: { label: 'Resume or Biodata' },
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
    }
  },
  computed: {
    flattenedStaff() {
      // flatten branches into simple list
      const list = []
      this.dataRaw.forEach(branch => {
        const branchName = branch.branch_name || ''
        if (branch.branch_manager) list.push({ ...branch.branch_manager, branch_name: branchName })
        if (Array.isArray(branch.hr)) branch.hr.forEach(h => list.push({ ...h, branch_name: branchName }))
        if (Array.isArray(branch.staff)) branch.staff.forEach(s => list.push({ ...s, branch_name: branchName }))
      })
      return list
    },
    filteredStaff() {
      if (!this.searchQuery) return this.flattenedStaff
      const q = this.searchQuery.toLowerCase()
      return this.flattenedStaff.filter(m => (m.full_name || m.username || '').toLowerCase().includes(q) || (m.email || '').toLowerCase().includes(q))
    }
  },
  mounted() {
    this.loadBranches()
    this.loadStaff()
  },
  methods: {
    async loadBranches() {
      try {
        const res = await axios.get('/api/admin/branches')
        if (res.data && res.data.success) {
          this.branches = res.data.data || []
        }
      } catch (e) {
        // ignore
      }
    },
    async loadStaff() {
      this.isLoading = true
      try {
        const res = await axios.get('/api/admin/staff')
        if (res.data && res.data.success) {
          this.dataRaw = res.data.data || []
        } else {
          this.dataRaw = []
        }
      } catch (e) {
        this.errorMessage = 'Failed to load staff data'
      } finally {
        this.isLoading = false
      }
    },
    refreshStaff() { this.loadStaff() },
    openAddModal() {
      this.isEdit = false
      this.resetForm()
      this.showModal = true
    },
    closeModal() { this.showModal = false },
    edit(m) {
      this.isEdit = true
      this.form.id = m.id
      this.form.username = m.username
      this.form.fullName = m.full_name || ''
      this.form.email = m.email || ''
      this.form.phone = m.phone_number || ''
      this.form.branchId = m.branch_id || ''
      // infer type
      if (m.role === 'HR' || m.role === 'BRANCH_MANAGER') {
        this.form.personType = 'MANAGER'
        this.form.managerRole = m.role === 'HR' ? 'HR' : (m.department || 'FINANCE')
      } else {
        this.form.personType = 'STAFF'
        this.form.staffRole = m.department || 'LOGISTICS'
      }
      this.showModal = true
    },
    resetForm() {
      this.form = { id: '', username: '', fullName: '', email: '', phone: '', branchId: '', personType: 'STAFF', managerRole: 'HR', staffRole: 'LOGISTICS', password: '' }
      this.documentFiles = {}
    },
    onFileChange(key, ev) {
      const f = ev.target.files && ev.target.files[0]
      if (f) this.documentFiles[key] = f
    },
    async submit() {
      try {
        const isCreate = !this.isEdit
        const url = isCreate ? '/api/admin/staff' : `/api/admin/staff/${this.form.id}`
        const method = isCreate ? 'post' : 'put'

        // Map role according to selections
        let role = 'STAFF'
        if (this.form.personType === 'MANAGER') {
          if (this.form.managerRole === 'HR') role = 'HR'
          else role = 'BRANCH_MANAGER'
        } else {
          role = 'STAFF'
        }

        if (isCreate) {
          const fd = new FormData()
          fd.append('username', this.form.username)
          fd.append('email', this.form.email)
          fd.append('fullName', this.form.fullName)
          fd.append('phone', this.form.phone)
          fd.append('branchId', this.form.branchId)
          fd.append('role', role)
          fd.append('password', this.form.password || 'ChikinTayo_2526')
          // attach documents
          this.documentKeys.forEach(k => {
            const f = this.documentFiles[k]
            if (f) fd.append(k, f)
          })

          const res = await axios({ method, url, data: fd, headers: { 'Content-Type': 'multipart/form-data' }, withCredentials: true })
          if (res.data && res.data.success) {
            this.closeModal()
            this.loadStaff()
          } else {
            this.errorMessage = res.data.message || 'Failed to create user'
          }
        } else {
          // update: send JSON with normalized fields
          const payload = {
            username: this.form.username,
            email: this.form.email,
            fullName: this.form.fullName,
            phone: this.form.phone,
            branchId: this.form.branchId,
            role: role,
            isActive: true,
            password: this.form.password || ''
          }
          const res = await axios.put(url, payload, { withCredentials: true })
          if (res.data && res.data.success) {
            this.closeModal()
            this.loadStaff()
          } else {
            this.errorMessage = res.data.message || 'Failed to update'
          }
        }
      } catch (e) {
        if (e.response && e.response.data && e.response.data.errors) {
          const errors = Object.values(e.response.data.errors).flat().join(', ')
          this.errorMessage = errors
        } else {
          this.errorMessage = e.response?.data?.message || 'An error occurred'
        }
      }
    }
  }
}
</script>

<style scoped>
.staff-management-page { padding: 1.5rem }
.staff-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem }
.header-actions { display:flex; gap:0.5rem; align-items:center }
.search-input { padding:6px }
.staff-table { width:100%; border-collapse:collapse }
.staff-table th, .staff-table td { border:1px solid #eee; padding:8px }
.modal-overlay { position:fixed; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.4) }
.modal-container { background:white; width:800px; max-height:90vh; overflow:auto; border-radius:8px; padding:1rem }
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px }
.form-group { display:flex; flex-direction:column }
.modal-header { display:flex; justify-content:space-between; align-items:center }
.modal-footer { display:flex; justify-content:flex-end; gap:8px; margin-top:1rem }
.btn-primary { background:#ff6b1c; color:white; padding:8px 12px; border:none }
.btn-success { background:#28a745; color:white; padding:8px 12px; border:none }
.btn-secondary { background:#6c757d; color:white; padding:8px 12px; border:none }
.btn-sm { padding:4px 8px }
</style>