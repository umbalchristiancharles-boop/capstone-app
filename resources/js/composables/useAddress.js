import { reactive, computed, watch } from 'vue'

export default function useAddress(initial = {}) {
  const locationData = {
    /* LUZON */
    'NCR (National Capital Region)': {
      'Metro Manila': {
        'Quezon City': ['Project 2', 'Culiat'],
        'Manila': ['Tondo', 'Binondo']
      }
    },
    'CAR (Cordillera Administrative Region)': {
      'Benguet': {
        'Baguio City': ['Burnham', 'Military Cut-off']
      }
    },
    'Region I – Ilocos Region': {},
    'Region II – Cagayan Valley': {},
    'Region III – Central Luzon': {},
    'Region IV-A – CALABARZON': {
      'Laguna': {
        'Calamba City': ['Santo Tomas', 'Canlubang'],
        'San Pablo': ['Centro', 'San Jose']
      },
      'Cavite': {
        'Imus': ['Bucandala', 'Tanza']
      }
    },
    'Region IV-B – MIMAROPA': {},
    'Region V – Bicol Region': {},

    /* VISAYAS */
    'Region VI – Western Visayas': {
      'Iloilo': {
        'Iloilo City': ['Jaro', 'Molo']
      }
    },
    'Region VII – Central Visayas': {
      'Cebu': {
        'Cebu City': ['Poblacion', 'Mabolo'],
        'Lapu-Lapu City': ['Poblacion', 'Marigondon']
      }
    },
    'Region VIII – Eastern Visayas': {},

    /* MINDANAO */
    'Region IX – Zamboanga Peninsula': {},
    'Region X – Northern Mindanao': {},
    'Region XI – Davao Region': {
      'Davao del Sur': {
        'Davao City': ['Buhangin', 'Talomo']
      }
    },
    'Region XII – SOCCSKSARGEN': {},
    'Region XIII – Caraga': {},
    'BARMM – Bangsamoro Autonomous Region': {}
  }

  const state = reactive({
    selectedRegion: initial.region || '',
    selectedProvince: initial.province || '',
    selectedCity: initial.city || '',
    selectedBarangay: initial.barangay || '',
    errors: {}
  })

  const provinces = computed(() => {
    if (!state.selectedRegion) return []
    const regionObj = locationData[state.selectedRegion] || {}
    return Object.keys(regionObj)
  })

  const cities = computed(() => {
    if (!state.selectedRegion || !state.selectedProvince) return []
    const regionObj = locationData[state.selectedRegion] || {}
    const provinceObj = regionObj[state.selectedProvince] || {}
    return Object.keys(provinceObj)
  })

  const barangays = computed(() => {
    if (!state.selectedRegion || !state.selectedProvince || !state.selectedCity) return []
    const regionObj = locationData[state.selectedRegion] || {}
    const provinceObj = regionObj[state.selectedProvince] || {}
    return provinceObj[state.selectedCity] || []
  })

  watch(() => state.selectedRegion, () => {
    state.selectedProvince = ''
    state.selectedCity = ''
    state.selectedBarangay = ''
    delete state.errors.province
    delete state.errors.city
    delete state.errors.barangay
  })

  watch(() => state.selectedProvince, () => {
    state.selectedCity = ''
    state.selectedBarangay = ''
    delete state.errors.city
    delete state.errors.barangay
  })

  watch(() => state.selectedCity, () => {
    state.selectedBarangay = ''
    delete state.errors.barangay
  })

  function validate() {
    state.errors = {}
    if (!state.selectedRegion) state.errors.region = 'Region is required'
    if (!state.selectedProvince) state.errors.province = 'Province is required'
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
  }

  return {
    state,
    provinces,
    cities,
    barangays,
    validate,
    reset,
    locationData
  }
}
