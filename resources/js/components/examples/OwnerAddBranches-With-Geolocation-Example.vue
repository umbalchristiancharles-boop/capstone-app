// Example: Integrating GeolocationMap into OwnerAddBranches.vue
// Location: resources/js/components/examples/OwnerAddBranches-With-Geolocation-Example.vue

<template>
  <div class="add-branch-container">
    <h2>{{ showAddBranchForm ? 'Add New Branch' : 'Manage Branches' }}</h2>

    <!-- Branch Form -->
    <form v-if="showAddBranchForm" @submit.prevent="submitBranch" class="branch-form">
      <div class="form-grid">
        <!-- Branch Code -->
        <div class="form-group">
          <label class="form-label">Branch Code *</label>
          <input 
            v-model="branchForm.code" 
            type="text" 
            class="form-input" 
            placeholder="e.g., QC001"
            required 
          />
        </div>

        <!-- Branch Name -->
        <div class="form-group">
          <label class="form-label">Branch Name *</label>
          <input 
            v-model="branchForm.name" 
            type="text" 
            class="form-input"
            placeholder="e.g., Quezon City Branch"
            required 
          />
        </div>

        <!-- Budget -->
        <div class="form-group">
          <label class="form-label">Initial Budget (PHP)</label>
          <input 
            v-model.number="branchForm.budget" 
            type="number"
            class="form-input"
            min="100000"
            max="1000000"
            step="1000"
          />
        </div>

        <!-- ✨ NEW: Address with Geolocation Component ✨ -->
        <div class="form-group full-span">
          <label class="form-label">Address & Location *</label>
          <AddressCascaderWithMap
            :initialAddress="{
              region: branchForm.region,
              province: branchForm.province,
              city: branchForm.city,
              barangay: branchForm.barangay
            }"
            :initialLocation="{
              lat: branchForm.latitude,
              lng: branchForm.longitude
            }"
            :showSaveButton="true"
            @saved="onAddressSaved"
            @update:address="onAddressUpdate"
            @update:location="onLocationUpdate"
          />
        </div>

        <!-- Saved Address Display -->
        <div v-if="addressSaved" class="form-group full-span">
          <div class="address-card">
            <div class="address-card-header">
              <div class="address-info">
                <strong>📍 Location Saved</strong>
                <div v-if="branchForm.latitude && branchForm.longitude" class="location-coords">
                  Coordinates: {{ branchForm.latitude?.toFixed(6) }}, {{ branchForm.longitude?.toFixed(6) }}
                </div>
                <div class="full-address">{{ savedAddress }}</div>
              </div>
              <button type="button" @click="editBranchAddress" class="btn btn-secondary">
                Edit
              </button>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="form-group full-span action-buttons">
          <button type="submit" class="btn btn-primary">Create Branch</button>
          <button type="button" @click="cancelAddBranch" class="btn btn-secondary">
            Cancel
          </button>
        </div>
      </div>

      <!-- Messages -->
      <div v-if="formError" class="alert alert-error">{{ formError }}</div>
      <div v-if="formSuccess" class="alert alert-success">{{ formSuccess }}</div>
    </form>

    <!-- Branches List -->
    <div v-if="!showAddBranchForm" class="branches-list">
      <button @click="addNewBranch" class="btn btn-primary">+ Add New Branch</button>

      <!-- Branch Cards with Map Display -->
      <div class="branches-grid">
        <div v-for="branch in branches" :key="branch.id" class="branch-card">
          <h3>{{ branch.name }}</h3>
          <p><strong>Code:</strong> {{ branch.code }}</p>
          <p><strong>Address:</strong> {{ branch.address }}</p>
          
          <!-- ✨ NEW: Display Location on Map -->
          <div v-if="branch.latitude && branch.longitude" class="mini-map">
            <GeoLocationMapView
              :branch-name="branch.name"
              :latitude="branch.latitude"
              :longitude="branch.longitude"
              view-mode="readonly"
            />
          </div>

          <div class="branch-actions">
            <button @click="editBranch(branch)" class="btn btn-primary btn-sm">Edit</button>
            <button @click="deleteBranch(branch.id)" class="btn btn-danger btn-sm">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AddressCascaderWithMap from '../AddressCascaderWithMap.vue'
import GeoLocationMapView from '../GeoLocationMapView.vue' // View-only map component
import axios from 'axios'

// State
const branches = ref([])
const showAddBranchForm = ref(false)
const addressSaved = ref(false)
const formError = ref('')
const formSuccess = ref('')

// Branch form
const branchForm = ref({
  code: '',
  name: '',
  budget: 100000,
  address: '',
  // Address components
  region: '',
  province: '',
  city: '',
  barangay: '',
  // ✨ NEW: Geolocation coordinates
  latitude: null,
  longitude: null
})

// Computed saved address string
const savedAddress = computed(() => {
  const parts = []
  if (branchForm.value.address?.trim()) {
    parts.push(branchForm.value.address.trim())
  }
  if (branchForm.value.barangay) parts.push(branchForm.value.barangay)
  if (branchForm.value.city) parts.push(branchForm.value.city)
  if (branchForm.value.province) parts.push(branchForm.value.province)
  if (branchForm.value.region) parts.push(branchForm.value.region)
  return parts.join(', ')
})

// ✨ NEW: Handle saved address & location
function onAddressSaved(payload) {
  // Update form with address components
  branchForm.value.region = payload.address.region
  branchForm.value.province = payload.address.province
  branchForm.value.city = payload.address.city
  branchForm.value.barangay = payload.address.barangay
  
  // Update form with geolocation coordinates
  branchForm.value.latitude = payload.location.lat
  branchForm.value.longitude = payload.location.lng
  
  addressSaved.value = true
  
  console.log('Branch address and location saved:', {
    address: payload.address,
    location: payload.location
  })
}

// Handle address updates (optional)
function onAddressUpdate(address) {
  branchForm.value.region = address.region || branchForm.value.region
  branchForm.value.province = address.province || branchForm.value.province
  branchForm.value.city = address.city || branchForm.value.city
  branchForm.value.barangay = address.barangay || branchForm.value.barangay
}

// Handle location updates (optional)
function onLocationUpdate(location) {
  branchForm.value.latitude = location.lat
  branchForm.value.longitude = location.lng
}

// Submit branch form
async function submitBranch() {
  formError.value = ''
  formSuccess.value = ''

  // Validation
  if (!branchForm.value.code.trim()) {
    formError.value = 'Branch code is required.'
    return
  }

  if (!addressSaved.value) {
    formError.value = 'Please select and save an address with location.'
    return
  }

  if (!branchForm.value.latitude || !branchForm.value.longitude) {
    formError.value = 'Location coordinates are required. Please pin location on the map.'
    return
  }

  // Auto-fill branch name if empty
  if (!branchForm.value.name.trim()) {
    branchForm.value.name = savedAddress.value + ' - Chikintayo'
  }

  try {
    const payload = {
      code: branchForm.value.code,
      name: branchForm.value.name,
      address: branchForm.value.address,
      region: branchForm.value.region,
      province: branchForm.value.province,
      city: branchForm.value.city,
      barangay: branchForm.value.barangay,
      budget: branchForm.value.budget,
      // ✨ NEW: Include geolocation in request
      latitude: branchForm.value.latitude,
      longitude: branchForm.value.longitude
    }

    const response = await axios.post('/api/branches', payload)

    if (response.data.ok) {
      formSuccess.value = 'Branch created successfully!'
      branches.value.push(response.data.branch)
      resetForm()
      setTimeout(() => {
        showAddBranchForm.value = false
      }, 1500)
    }
  } catch (error) {
    formError.value = error.response?.data?.message || 'Error creating branch'
  }
}

// Load branches
async function loadBranches() {
  try {
    const response = await axios.get('/api/branches')
    branches.value = response.data.branches || []
  } catch (error) {
    console.error('Error loading branches:', error)
  }
}

// Add new branch
function addNewBranch() {
  resetForm()
  showAddBranchForm.value = true
}

// Edit branch (not fully implemented in example)
function editBranch(branch) {
  branchForm.value = { ...branch }
  addressSaved.value = true
  showAddBranchForm.value = true
}

// Delete branch
async function deleteBranch(id) {
  if (!confirm('Are you sure you want to delete this branch?')) return

  try {
    await axios.delete(`/api/branches/${id}`)
    branches.value = branches.value.filter(b => b.id !== id)
  } catch (error) {
    alert('Error deleting branch')
  }
}

// Cancel adding branch
function cancelAddBranch() {
  resetForm()
  showAddBranchForm.value = false
}

// Edit branch address
function editBranchAddress() {
  addressSaved.value = false
}

// Reset form
function resetForm() {
  branchForm.value = {
    code: '',
    name: '',
    budget: 100000,
    address: '',
    region: '',
    province: '',
    city: '',
    barangay: '',
    latitude: null,
    longitude: null
  }
  addressSaved.value = false
  formError.value = ''
  formSuccess.value = ''
}

// Load on mount
onMounted(() => {
  loadBranches()
})
</script>

<style scoped>
.add-branch-container {
  padding: 2rem;
  background: white;
  border-radius: 8px;
}

.branch-form {
  max-width: 1000px;
  margin: 0 auto;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-span {
  grid-column: 1 / -1;
}

.form-label {
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.form-input {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.95rem;
}

.form-input:focus {
  outline: none;
  border-color: #ff9a4a;
  box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.1);
}

.address-card {
  padding: 1rem;
  background: linear-gradient(135deg, #f0f9ff 0%, #e6f7ff 100%);
  border: 2px solid #bfdbfe;
  border-radius: 8px;
}

.address-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.address-info {
  flex: 1;
}

.address-info strong {
  display: block;
  color: #1e40af;
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.location-coords {
  font-size: 0.85rem;
  color: #1e40af;
  font-family: monospace;
  margin: 0.25rem 0;
}

.full-address {
  font-size: 0.95rem;
  color: #1f2937;
  margin-top: 0.5rem;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.alert {
  padding: 1rem;
  border-radius: 4px;
  margin-top: 1rem;
}

.alert-error {
  background-color: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.alert-success {
  background-color: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.95rem;
}

.btn-primary {
  background-color: #ff9a4a;
  color: white;
}

.btn-primary:hover {
  background-color: #ff6a3d;
}

.btn-secondary {
  background-color: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background-color: #d1d5db;
}

.btn-danger {
  background-color: #ef4444;
  color: white;
}

.btn-danger:hover {
  background-color: #dc2626;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
}

/* Branches List */
.branches-list {
  margin-top: 3rem;
}

.branches-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

.branch-card {
  padding: 1.5rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: box-shadow 0.2s;
}

.branch-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.branch-card h3 {
  margin: 0 0 1rem;
  color: #1f2937;
  font-size: 1.2rem;
}

.branch-card p {
  margin: 0.5rem 0;
  color: #6b7280;
  font-size: 0.95rem;
}

.mini-map {
  margin: 1rem 0;
  height: 250px;
  border-radius: 4px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.branch-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .branches-grid {
    grid-template-columns: 1fr;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }
}
</style>

<!-- ============================================
     BACKEND ENDPOINT EXAMPLE
     ============================================ -->

<!--
PHP Controller Example (app/Http/Controllers/BranchController.php):

public function store(Request $request)
{
    $validated = $request->validate([
        'code' => 'required|unique:branches|string|max:20',
        'name' => 'required|string|max:255',
        'address' => 'nullable|string',
        'region' => 'required|string',
        'province' => 'required|string',
        'city' => 'required|string',
        'barangay' => 'required|string',
        'budget' => 'nullable|numeric|min:0',
        'latitude' => 'required|numeric|between:-90,90',      // ✨ NEW
        'longitude' => 'required|numeric|between:-180,180',   // ✨ NEW
    ]);

    $branch = Branch::create([
        'code' => $validated['code'],
        'name' => $validated['name'],
        'address' => $validated['address'],
        'region' => $validated['region'],
        'province' => $validated['province'],
        'city' => $validated['city'],
        'barangay' => $validated['barangay'],
        'budget' => $validated['budget'] ?? 100000,
        'latitude' => $validated['latitude'],      // ✨ NEW
        'longitude' => $validated['longitude'],    // ✨ NEW
        'is_active' => true,
        'is_main_branch' => false
    ]);

    return response()->json([
        'ok' => true,
        'message' => 'Branch created successfully',
        'branch' => $branch
    ]);
}

public function index()
{
    $branches = Branch::get([
        'id',
        'code',
        'name',
        'address',
        'latitude',      // ✨ NEW
        'longitude',     // ✨ NEW
        'is_active',
        'budget',
        'created_at'
    ]);

    return response()->json([
        'ok' => true,
        'branches' => $branches
    ]);
}
-->
