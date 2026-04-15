import { reactive, computed, watch, ref } from 'vue'
import axios from 'axios'

export default function useAddress(initial = {}) {
  const state = reactive({
    selectedRegion: initial.region || '',
    selectedProvince: initial.province || '',
    selectedCity: initial.city || '',
    selectedBarangay: initial.barangay || '',
    errors: {}
  })

  // Dynamically loaded data
  const provincesData = ref([])
  const citiesData = ref([])
  const barangaysData = ref([])
  const loading = ref(false)

  // Computed properties
  const provinces = computed(() => provincesData.value)
  const cities = computed(() => citiesData.value)
  const barangays = computed(() => barangaysData.value)

  // Load provinces when region changes
  watch(() => state.selectedRegion, async (newRegion) => {
    state.selectedProvince = ''
    state.selectedCity = ''
    state.selectedBarangay = ''
    provincesData.value = []
    citiesData.value = []
    barangaysData.value = []
    delete state.errors.province
    delete state.errors.city
    delete state.errors.barangay

    if (!newRegion) return

    try {
      loading.value = true
      const response = await axios.get(`/api/locations/provinces?region=${encodeURIComponent(newRegion)}`, {
        withCredentials: true
      })
      if (response.data && response.data.data) {
        provincesData.value = Array.isArray(response.data.data) ? response.data.data : []
      }
    } catch (error) {
      console.error('Error loading provinces:', error)
      provincesData.value = []
    } finally {
      loading.value = false
    }
  })

  // Load cities when province changes
  watch(() => state.selectedProvince, async (newProvince) => {
    state.selectedCity = ''
    state.selectedBarangay = ''
    citiesData.value = []
    barangaysData.value = []
    delete state.errors.city
    delete state.errors.barangay

    if (!newProvince || !state.selectedRegion) return

    try {
      loading.value = true
      const response = await axios.get(
        `/api/locations/cities?province=${encodeURIComponent(newProvince)}&region=${encodeURIComponent(state.selectedRegion)}`,
        { withCredentials: true }
      )
      if (response.data && response.data.data) {
        citiesData.value = Array.isArray(response.data.data) ? response.data.data : []
      }
    } catch (error) {
      console.error('Error loading cities:', error)
      citiesData.value = []
    } finally {
      loading.value = false
    }
  })

  // Load barangays when city changes
  watch(() => state.selectedCity, async (newCity) => {
    state.selectedBarangay = ''
    barangaysData.value = []
    delete state.errors.barangay

    if (!newCity) return

    try {
      loading.value = true
      const response = await axios.get(`/api/locations/barangays?city=${encodeURIComponent(newCity)}`, {
        withCredentials: true
      })
      if (response.data && response.data.data) {
        barangaysData.value = Array.isArray(response.data.data) ? response.data.data : []
      }
    } catch (error) {
      console.error('Error loading barangays:', error)
      barangaysData.value = []
    } finally {
      loading.value = false
    }
  })

  function validate() {
    state.errors = {}
    if (!state.selectedRegion) state.errors.region = 'Region is required'
    // Province is optional for Philippine addresses (skipped in auto-fill)
    if (!state.selectedCity) state.errors.city = 'City / Municipality is required'
    if (!state.selectedBarangay) state.errors.barangay = 'Barangay is required'
    return Object.keys(state.errors).length === 0
  }

  function reset() {
    state.selectedRegion = ''
    state.selectedProvince = ''
    state.selectedCity = ''
    state.selectedBarangay = ''
    state.errors = {}
    provincesData.value = []
    citiesData.value = []
    barangaysData.value = []
  }

  return {
    state,
    provinces,
    cities,
    barangays,
    validate,
    reset,
    loading
  }
}
