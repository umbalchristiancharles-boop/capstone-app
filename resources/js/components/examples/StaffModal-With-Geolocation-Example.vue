// Example: Integrating GeolocationMap into StaffModal.vue
// Location: resources/js/components/examples/StaffModal-With-Geolocation-Example.vue

<template>
  <div class="modal-content">
    <!-- Modal Header -->
    <h2>{{ isEdit ? 'Edit Staff' : 'Add New Staff' }}</h2>

    <!-- Form Fields -->
    <form @submit.prevent="submitForm">
      <!-- Basic Info -->
      <div class="form-group">
        <label>Full Name *</label>
        <input v-model="form.fullName" type="text" class="form-input" required />
      </div>

      <div class="form-group">
        <label>Email</label>
        <input v-model="form.email" type="email" class="form-input" />
      </div>

      <div class="form-group">
        <label>Phone Number</label>
        <input v-model="form.phone" type="tel" class="form-input" />
      </div>

      <!-- ✨ NEW: Address with Geolocation Component ✨ -->
      <div class="form-group full-span">
        <label class="form-label">Address & Location *</label>
        <AddressCascaderWithMap
          :initialAddress="{
            region: form.region,
            province: form.province,
            city: form.city,
            barangay: form.barangay
          }"
          :initialLocation="{
            lat: form.latitude,
            lng: form.longitude
          }"
          :showSaveButton="true"
          @saved="onAddressSaved"
          @update:address="onAddressUpdate"
          @update:location="onLocationUpdate"
        />
      </div>

      <!-- Display Current Location -->
      <div v-if="form.latitude && form.longitude" class="location-info">
        <p><strong>📍 Current Location:</strong></p>
        <p>Latitude: {{ form.latitude.toFixed(6) }}</p>
        <p>Longitude: {{ form.longitude.toFixed(6) }}</p>
        <p><strong>Full Address:</strong></p>
        <p>{{ fullAddressDisplay }}</p>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">
          {{ isEdit ? 'Update Staff' : 'Create Staff' }}
        </button>
        <button type="button" @click="closeModal" class="btn btn-secondary">
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import AddressCascaderWithMap from '../AddressCascaderWithMap.vue'
import axios from 'axios'

const props = defineProps({
  isEdit: Boolean,
  staffId: String
})

const emit = defineEmits(['close', 'saved'])

// Form state
const form = ref({
  fullName: '',
  email: '',
  phone: '',
  // Address components
  region: '',
  province: '',
  city: '',
  barangay: '',
  // Geolocation coordinates ← NEW
  latitude: null,
  longitude: null
})

// Computed full address display
const fullAddressDisplay = computed(() => {
  const parts = []
  if (form.value.barangay) parts.push(form.value.barangay)
  if (form.value.city) parts.push(form.value.city)
  if (form.value.province) parts.push(form.value.province)
  if (form.value.region) parts.push(form.value.region)
  return parts.join(', ') || 'Not set'
})

// ✨ NEW: Handle saved address & location
function onAddressSaved(payload) {
  // Update form with address components
  form.value.region = payload.address.region
  form.value.province = payload.address.province
  form.value.city = payload.address.city
  form.value.barangay = payload.address.barangay
  
  // Update form with geolocation coordinates
  form.value.latitude = payload.location.lat
  form.value.longitude = payload.location.lng
  
  console.log('Address and location saved:', {
    address: payload.address,
    location: payload.location
  })
}

// ✨ NEW: Handle address updates (optional - for real-time updates)
function onAddressUpdate(address) {
  form.value.region = address.region || form.value.region
  form.value.province = address.province || form.value.province
  form.value.city = address.city || form.value.city
  form.value.barangay = address.barangay || form.value.barangay
}

// ✨ NEW: Handle location updates (optional - for real-time updates)
function onLocationUpdate(location) {
  form.value.latitude = location.lat
  form.value.longitude = location.lng
}

// Submit form (updated to include geolocation)
async function submitForm() {
  try {
    const payload = {
      full_name: form.value.fullName,
      email: form.value.email,
      phone_number: form.value.phone,
      region: form.value.region,
      province: form.value.province,
      city: form.value.city,
      barangay: form.value.barangay,
      // ✨ NEW: Include geolocation in request
      latitude: form.value.latitude,
      longitude: form.value.longitude
    }

    let response
    if (props.isEdit) {
      // PATCH request with geolocation
      response = await axios.patch(
        `/api/staff/${props.staffId}`,
        payload
      )
    } else {
      // POST request with geolocation
      response = await axios.post('/api/staff', payload)
    }

    if (response.data.ok) {
      emit('saved', response.data.staff)
      closeModal()
    }
  } catch (error) {
    console.error('Error saving staff:', error)
    alert(error.response?.data?.message || 'Error saving staff')
  }
}

function closeModal() {
  emit('close')
}

// Load existing staff data (if editing)
async function loadStaffData() {
  if (props.isEdit && props.staffId) {
    try {
      const response = await axios.get(`/api/staff/${props.staffId}`)
      const staff = response.data.staff
      
      // Load all fields including geolocation ← UPDATED
      form.value = {
        fullName: staff.full_name,
        email: staff.email,
        phone: staff.phone_number,
        region: staff.region,
        province: staff.province,
        city: staff.city,
        barangay: staff.barangay,
        latitude: staff.latitude,      // ✨ NEW
        longitude: staff.longitude      // ✨ NEW
      }
    } catch (error) {
      console.error('Error loading staff:', error)
    }
  }
}

// Load data on mount
onMounted(() => {
  loadStaffData()
})
</script>

<style scoped>
.modal-content {
  padding: 2rem;
  max-width: 900px;
  margin: 0 auto;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.form-input {
  width: 100%;
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

.form-group.full-span {
  grid-column: 1 / -1;
}

.location-info {
  padding: 1rem;
  background-color: #f0f9ff;
  border: 2px solid #bfdbfe;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-size: 0.95rem;
  line-height: 1.6;
  color: #1e40af;
}

.location-info p {
  margin: 0.5rem 0;
}

.location-info strong {
  color: #1e3a8a;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
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

@media (max-width: 768px) {
  .modal-content {
    padding: 1rem;
  }

  .form-actions {
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
PHP Controller Example (app/Http/Controllers/StaffController.php):

public function store(Request $request)
{
    $validated = $request->validate([
        'full_name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone_number' => 'nullable|string|max:20',
        'region' => 'required|string',
        'province' => 'required|string',
        'city' => 'required|string',
        'barangay' => 'required|string',
        'latitude' => 'required|numeric|between:-90,90',      // ✨ NEW
        'longitude' => 'required|numeric|between:-180,180',   // ✨ NEW
    ]);

    $staff = User::create([
        'full_name' => $validated['full_name'],
        'email' => $validated['email'],
        'phone_number' => $validated['phone_number'],
        'region' => $validated['region'],
        'province' => $validated['province'],
        'city' => $validated['city'],
        'barangay' => $validated['barangay'],
        'latitude' => $validated['latitude'],      // ✨ NEW
        'longitude' => $validated['longitude'],    // ✨ NEW
        'role' => 'STAFF'
    ]);

    return response()->json([
        'ok' => true,
        'message' => 'Staff created successfully',
        'staff' => $staff
    ]);
}

public function update(Request $request, $id)
{
    $validated = $request->validate([
        'full_name' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone_number' => 'nullable|string|max:20',
        'latitude' => 'nullable|numeric|between:-90,90',      // ✨ NEW
        'longitude' => 'nullable|numeric|between:-180,180',   // ✨ NEW
    ]);

    $staff = User::findOrFail($id);
    $staff->update($validated);

    return response()->json([
        'ok' => true,
        'message' => 'Staff updated successfully',
        'staff' => $staff
    ]);
}
-->
