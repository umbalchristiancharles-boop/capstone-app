<template>
  <div class="geolocation-container">
    <!-- Loading Skeleton -->
    <div v-if="isMapLoading" class="map-loading">
      <div class="skeleton-pulse"></div>
      <p>Loading map...</p>
    </div>

    <div v-show="!isMapLoading" class="map-controls">
      <div class="search-control">
        <input
          v-model="searchQuery"
          @input="onSearchInput"
          placeholder="Search for address or place (e.g. Quezon City, SM Mall)"
          class="search-input"
          aria-label="Search location"
        />
        <ul v-if="searchResults.length" class="search-results">
          <li v-for="(r, idx) in searchResults" :key="idx" @click="selectSearchResult(r)" class="search-result-item">
            <div class="result-title">{{ r.display_name }}</div>
            <div class="result-sub">{{ formatResultSub(r) }}</div>
          </li>
        </ul>
      </div>
      <button type="button" @click="getCurrentLocation" class="btn btn-primary btn-sm" :disabled="isLocating">
        <span v-if="isLocating">⏳ Detecting...</span>
        <span v-else>📍 Use My Current Location</span>
      </button>
      <button type="button" @click="resetMap" class="btn btn-secondary btn-sm">↺ Reset</button>
      <button type="button" @click="saveLocation" class="btn btn-success btn-sm" :disabled="!selectedLocation.lat || !selectedLocation.lng">💾 Save Location</button>
    </div>

    <!-- Map Container -->
    <div ref="mapContainer" class="map-wrapper" :class="{ 'loading': isMapLoading }"></div>

    <!-- Coordinates Display -->
    <div v-show="!isMapLoading && selectedLocation.lat && selectedLocation.lng" class="coordinates-display">
      <div class="coord-item">
        <span class="coord-label">Latitude:</span>
        <span class="coord-value">{{ selectedLocation.lat?.toFixed(8) }}</span>
      </div>
      <div class="coord-item">
        <span class="coord-label">Longitude:</span>
        <span class="coord-value">{{ selectedLocation.lng?.toFixed(8) }}</span>
      </div>
    </div>

    <!-- Info Message -->
    <div v-show="!isMapLoading" class="info-message">
      📌 Click map to pin location
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import L from 'leaflet'
import cities from '../../../cities.json'
import barangays from '../../../barangays.json'
import 'leaflet/dist/leaflet.css'

// Cache marker icon setup
let iconSetupDone = false

// Philippine cities to region mapping (for reverse geocoding fallback)
const cityToRegionMap = {
  'manila': 'National Capital Region (NCR)',
  'makati': 'National Capital Region (NCR)',
  'quezon city': 'National Capital Region (NCR)',
  'pasay': 'National Capital Region (NCR)',
  'taguig': 'National Capital Region (NCR)',
  'paranaque': 'National Capital Region (NCR)',
  'caloocan': 'National Capital Region (NCR)',
  'cebu': 'Region VII (Central Visayas)',
  'davao': 'Region XI (Davao Region)',
  'cagayan de oro': 'Region X (Northern Mindanao)',
  'iloilo': 'Region VI (Western Visayas)',
  'bacolod': 'Region VI (Western Visayas)',
  'zamboanga': 'Region IX (Zamboanga Peninsula)',
  'baguio': 'Cordillera Administrative Region (CAR)',
  'los baños': 'Region IV-A (CALABARZON)',
  'laguna': 'Region IV-A (CALABARZON)',
  'cavite': 'Region IV-A (CALABARZON)',
  'batangas': 'Region IV-A (CALABARZON)',
  'rizal': 'Region IV-A (CALABARZON)',
  'quezon': 'Region IV-A (CALABARZON)',
}

// Philippine provinces to region mapping
const provinceToRegionMap = {
  'abra': 'Cordillera Administrative Region (CAR)',
  'apayao': 'Cordillera Administrative Region (CAR)',
  'benguet': 'Cordillera Administrative Region (CAR)',
  'ifugao': 'Cordillera Administrative Region (CAR)',
  'kalinga': 'Cordillera Administrative Region (CAR)',
  'mountain province': 'Cordillera Administrative Region (CAR)',
  'quirino': 'Region II (Cagayan Valley)',
  'isabela': 'Region II (Cagayan Valley)',
  'nueva vizcaya': 'Region II (Cagayan Valley)',
  'cagayan': 'Region II (Cagayan Valley)',
  'batanes': 'Region II (Cagayan Valley)',
  'ilocos norte': 'Region I (Ilocos Region)',
  'ilocos sur': 'Region I (Ilocos Region)',
  'la union': 'Region I (Ilocos Region)',
  'pangasinan': 'Region I (Ilocos Region)',
  'nueva ecija': 'Region III (Central Luzon)',
  'bulacan': 'Region III (Central Luzon)',
  'pampanga': 'Region III (Central Luzon)',
  'tarlac': 'Region III (Central Luzon)',
  'zambales': 'Region III (Central Luzon)',
  'bataan': 'Region III (Central Luzon)',
  'cavite': 'Region IV-A (CALABARZON)',
  'laguna': 'Region IV-A (CALABARZON)',
  'quezon': 'Region IV-A (CALABARZON)',
  'rizal': 'Region IV-A (CALABARZON)',
  'batangas': 'Region IV-A (CALABARZON)',
  'marinduque': 'MIMAROPA Region',
  'mindoro occidental': 'MIMAROPA Region',
  'mindoro oriental': 'MIMAROPA Region',
  'palawan': 'MIMAROPA Region',
  'romblon': 'MIMAROPA Region',
  'albay': 'Region V (Bicol Region)',
  'camarines norte': 'Region V (Bicol Region)',
  'camarines sur': 'Region V (Bicol Region)',
  'catanduanes': 'Region V (Bicol Region)',
  'masbate': 'Region V (Bicol Region)',
  'sorsogon': 'Region V (Bicol Region)',
  'aklan': 'Region VI (Western Visayas)',
  'antique': 'Region VI (Western Visayas)',
  'capiz': 'Region VI (Western Visayas)',
  'iloilo': 'Region VI (Western Visayas)',
  'negros occidental': 'Region VI (Western Visayas)',
  'bohol': 'Region VII (Central Visayas)',
  'cebu': 'Region VII (Central Visayas)',
  'negros oriental': 'Region VII (Central Visayas)',
  'siquijor': 'Region VII (Central Visayas)',
  'eastern samar': 'Region VIII (Eastern Visayas)',
  'guimaras': 'Region VIII (Eastern Visayas)',
  'leyte': 'Region VIII (Eastern Visayas)',
  'northern samar': 'Region VIII (Eastern Visayas)',
  'samar': 'Region VIII (Eastern Visayas)',
  'southern leyte': 'Region VIII (Eastern Visayas)',
  'western samar': 'Region VIII (Eastern Visayas)',
  'basilan': 'Region IX (Zamboanga Peninsula)',
  'zamboanga del norte': 'Region IX (Zamboanga Peninsula)',
  'zamboanga del sur': 'Region IX (Zamboanga Peninsula)',
  'zamboanga sibugay': 'Region IX (Zamboanga Peninsula)',
  'bukidnon': 'Region X (Northern Mindanao)',
  'camiguin': 'Region X (Northern Mindanao)',
  'lanao del norte': 'Region X (Northern Mindanao)',
  'misamis occidental': 'Region X (Northern Mindanao)',
  'misamis oriental': 'Region X (Northern Mindanao)',
  'davao de oro': 'Region XI (Davao Region)',
  'davao del norte': 'Region XI (Davao Region)',
  'davao del sur': 'Region XI (Davao Region)',
  'davao oriental': 'Region XI (Davao Region)',
  'davao occidental': 'Region XI (Davao Region)',
  'cotabato': 'Region XII (SOCCSKSARGEN)',
  'cotabato city': 'Region XII (SOCCSKSARGEN)',
  'north cotabato': 'Region XII (SOCCSKSARGEN)',
  'sarangani': 'Region XII (SOCCSKSARGEN)',
  'south cotabato': 'Region XII (SOCCSKSARGEN)',
  'sultan kudarat': 'Region XII (SOCCSKSARGEN)',
  'agusan del norte': 'Region XIII (Caraga)',
  'agusan del sur': 'Region XIII (Caraga)',
  'dinagat islands': 'Region XIII (Caraga)',
  'surigao del norte': 'Region XIII (Caraga)',
  'surigao del sur': 'Region XIII (Caraga)',
}

const props = defineProps({
  initialLocation: {
    type: Object,
    default: () => ({
      lat: 14.5994,
      lng: 120.9842
    })
  },
  initialAddress: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:location', 'update:address', 'save:location'])

const mapContainer = ref(null)
const map = ref(null)
const marker = ref(null)
const selectedLocation = ref({
  lat: 14.5994,
  lng: 120.9842,
  address: ''
})
const isLocating = ref(false)
const isMapLoading = ref(true)
let tileLayer = null

// Search / forward geocoding state
const searchQuery = ref('')
const searchResults = ref([])
const isSearching = ref(false)
let searchTimeout = null

// Fast marker icon setup (only once)
function setupMarkerIcons() {
  if (iconSetupDone) return
  iconSetupDone = true
  
  delete L.Icon.Default.prototype._getIconUrl
  L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.3.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.3.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.3.1/images/marker-shadow.png'
  })
}

function initializeMap() {
  if (!mapContainer.value) return
  
  setupMarkerIcons()
  
  const center = [selectedLocation.value?.lat || 14.5994, selectedLocation.value?.lng || 120.9842]

  // Create map with optimized settings
  map.value = L.map(mapContainer.value, {
    preferCanvas: true,
    zoomControl: true,
    attributionControl: false
  }).setView(center, 13)

  // Use OpenStreetMap tiles with better error handling
  tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    minZoom: 2,
    attribution: '',
    className: 'map-tiles'
  })
  
  tileLayer.on('tileerror', function() {
    console.warn('Tile loading error - checking connection')
  })
  
  tileLayer.addTo(map.value)

  // Fast click handler - no debouncing
  map.value.on('click', (e) => {
    const { lat, lng } = e.latlng
    selectedLocation.value = {
      lat,
      lng,
      address: 'Fetching...'
    }
    updateMarker(lat, lng)
    // Fetch address after updating marker
    reverseGeocode(lat, lng)
  })

  // Add initial marker if location is set
  if (selectedLocation.value?.lat && selectedLocation.value?.lng) {
    updateMarker(selectedLocation.value.lat, selectedLocation.value.lng)
  }

  // Ensure map viewport is properly sized
  setTimeout(() => {
    if (map.value) map.value.invalidateSize()
  }, 100)

  isMapLoading.value = false
}

function updateMarker(lat, lng) {
  if (!map.value) return
  
  // Remove old marker
  if (marker.value) {
    map.value.removeLayer(marker.value)
  }

  // Create new marker (fast)
  marker.value = L.marker([lat, lng], { 
    draggable: false,
    opacity: 0.9
  }).addTo(map.value)
  
  // Smooth pan to location
  map.value.panTo([lat, lng], { animate: true, duration: 0.5 })
}

// Reverse geocode coordinates to address
async function reverseGeocode(lat, lng) {
  try {
    const response = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
      {
        headers: { 'Accept-Language': 'en' }
      }
    )
    
    if (!response.ok) throw new Error('Geocoding failed')
    
    const data = await response.json()
    const address = data.address || {}
    
    console.log('Nominatim response address:', address)
    
    // Extract address components - For Philippines:
    // - "province" field in Nominatim = Region (e.g., "Metro Manila", "Cebu")
    // - If not available, use city to look up region
    // - Sometimes Nominatim returns the province name instead of region
    // - "city" = City/Municipality
    // - "barangay" = Barangay
    
    let region = address.province || address.state || ''
    const city = address.city || address.town || address.municipality || ''
    const barangay = address.barangay || address.suburb || address.village || ''
    
    // If region looks like it's actually a province name, map it to the full region
    if (region && !region.includes('Region') && !region.includes('National Capital')) {
      const provinceKey = region.toLowerCase().trim()
      const mappedRegion = provinceToRegionMap[provinceKey]
      if (mappedRegion) {
        console.log(`Mapped province "${region}" to region "${mappedRegion}"`)
        region = mappedRegion
      }
    }
    
    // If region is still empty, try to lookup from city using our mapping
    if (!region && city) {
      const cityKey = city.toLowerCase().trim()
      region = cityToRegionMap[cityKey] || ''
    }
    
    const province = address.province || '' // Usually empty for PH
    
    console.log('Extracted components:', { region, province, city, barangay })
    
    // Build formatted address string
    const addressString = [barangay, city, region].filter(Boolean).join(', ')
    selectedLocation.value.address = addressString
    
    // Emit location with address info
    emit('update:location', { 
      lat: parseFloat(lat.toFixed(8)), 
      lng: parseFloat(lng.toFixed(8)),
      address: addressString.trim(),
      addressComponents: {
        region: region,
        province: '', // Not used for Philippine cities (province = region)
        city: city,
        barangay: barangay
      }
    })
  } catch (error) {
    console.warn('Could not reverse geocode:', error)
    // Still emit location even if geocoding fails - with empty addressComponents
    emit('update:location', { 
      lat: parseFloat(lat.toFixed(8)), 
      lng: parseFloat(lng.toFixed(8)),
      addressComponents: {
        region: '',
        province: '',
        city: '',
        barangay: ''
      }
    })
  }
}

function getCurrentLocation() {
  if (!navigator.geolocation) {
    alert('Geolocation not supported')
    return
  }

  isLocating.value = true

  // Use max timeout of 5 seconds
  navigator.geolocation.getCurrentPosition(
    (position) => {
      const { latitude, longitude } = position.coords
      selectedLocation.value = {
        lat: latitude,
        lng: longitude,
        address: 'Fetching...'
      }
      if (map.value) {
        updateMarker(latitude, longitude)
      }
      // Reverse geocode the detected location
      reverseGeocode(latitude, longitude)
      isLocating.value = false
    },
    (error) => {
      console.error('Geolocation error:', error)
      alert('Location access denied or unavailable')
      isLocating.value = false
    },
    { 
      enableHighAccuracy: false,
      timeout: 5000,
      maximumAge: 300000  // Cache for 5 minutes
    }
  )
}

// --- Forward geocoding (Nominatim) for search box ---
function onSearchInput() {
  // debounce to avoid too many requests
  if (searchTimeout) clearTimeout(searchTimeout)
  if (!searchQuery.value || searchQuery.value.trim().length < 3) {
    searchResults.value = []
    return
  }
  searchTimeout = setTimeout(() => {
    fetchSearchResults(searchQuery.value.trim())
  }, 350)
}

async function fetchSearchResults(q) {
  isSearching.value = true
  searchResults.value = []
  try {
    // 1) Check local barangay/city datasets for strong matches
    const localCandidates = []
    const qLower = q.toLowerCase()

    // Barangay matches (prefix or exact)
    const barangayMatches = barangays.filter(b => b.name && b.name.toLowerCase().includes(qLower)).slice(0, 6)
    for (const b of barangayMatches) {
      // lookup city name for context
      const city = cities.find(c => c.code === b.city_code) || null
      const cityName = city ? city.name : ''
      localCandidates.push({ type: 'barangay', name: b.name, cityName })
    }

    // City matches
    const cityMatches = cities.filter(c => c.name && c.name.toLowerCase().includes(qLower)).slice(0, 6)
    for (const c of cityMatches) {
      localCandidates.push({ type: 'city', name: c.name, cityName: c.name })
    }

    const results = []

    // For each local candidate, perform a targeted search to get coordinates (limit 1)
    for (const cand of localCandidates.slice(0, 6)) {
      try {
        const queryText = cand.type === 'barangay' && cand.cityName ? `${cand.name}, ${cand.cityName}, Philippines` : `${cand.name}, Philippines`
        const url = `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1&q=${encodeURIComponent(queryText)}`
        const res = await fetch(url, { headers: { 'Accept-Language': 'en' } })
        if (!res.ok) continue
        const data = await res.json()
        if (Array.isArray(data) && data.length) results.push(data[0])
      } catch (e) {
        // ignore
      }
    }

    // 2) Fetch general search results and merge, avoiding duplicates
    try {
      const url = `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=8&q=${encodeURIComponent(q)}`
      const res = await fetch(url, { headers: { 'Accept-Language': 'en' } })
      if (res.ok) {
        const data = await res.json()
        if (Array.isArray(data)) {
          // merge while preventing duplicates by lat/lon
          const seen = new Set(results.map(r => `${r.lat}_${r.lon}`))
          for (const d of data) {
            const key = `${d.lat}_${d.lon}`
            if (!seen.has(key)) {
              results.push(d)
              seen.add(key)
            }
          }
        }
      }
    } catch (e) {
      console.warn('General search failed', e)
    }

    searchResults.value = results.slice(0, 8)
  } catch (e) {
    console.warn('Search error', e)
    searchResults.value = []
  } finally {
    isSearching.value = false
  }
}

function formatResultSub(r) {
  if (!r || !r.address) return ''
  const parts = []
  if (r.address.city) parts.push(r.address.city)
  if (r.address.state) parts.push(r.address.state)
  if (r.address.country) parts.push(r.address.country)
  return parts.join(', ')
}

function selectSearchResult(r) {
  if (!r) return
  const lat = parseFloat(r.lat)
  const lng = parseFloat(r.lon)
  // update selected location and marker
  selectedLocation.value = { lat, lng, address: r.display_name, addressComponents: {} }
  updateMarker(lat, lng)
  // emit a structured update including components where possible
  const addr = r.address || {}
  emit('update:location', {
    lat: parseFloat(lat.toFixed(8)),
    lng: parseFloat(lng.toFixed(8)),
    address: r.display_name,
    addressComponents: {
      region: addr.state || addr.county || addr.region || '',
      province: addr.state || '',
      city: addr.city || addr.town || addr.village || addr.municipality || '',
      barangay: addr.suburb || addr.neighbourhood || addr.village || ''
    }
  })
  // clear suggestions and query
  searchResults.value = []
  searchQuery.value = r.display_name
}

function resetMap() {
  const defaultLat = 14.5994
  const defaultLng = 120.9842
  selectedLocation.value = {
    lat: defaultLat,
    lng: defaultLng,
    address: ''
  }
  if (map.value) {
    updateMarker(defaultLat, defaultLng)
  }
  emit('update:location', { lat: defaultLat, lng: defaultLng })
}

function saveLocation() {
  if (!selectedLocation.value.lat || !selectedLocation.value.lng) {
    alert('Please select a location first')
    return
  }
  emit('save:location', { 
    lat: selectedLocation.value.lat, 
    lng: selectedLocation.value.lng,
    address: selectedLocation.value.address,
    addressComponents: selectedLocation.value.addressComponents || {}
  })
}

// Watch for external changes
watch(() => props.initialLocation, (newLocation) => {
  if (newLocation && (newLocation.lat || newLocation.lng) && map.value) {
    selectedLocation.value = {
      lat: newLocation.lat || 14.5994,
      lng: newLocation.lng || 120.9842,
      address: props.initialAddress
    }
    updateMarker(selectedLocation.value.lat, selectedLocation.value.lng)
  }
}, { deep: true })

onMounted(() => {
  // Pre-set location for fast render
  if (props.initialLocation && (props.initialLocation.lat || props.initialLocation.lng)) {
    selectedLocation.value = {
      lat: props.initialLocation.lat || 14.5994,
      lng: props.initialLocation.lng || 120.9842,
      address: props.initialAddress
    }
  } else {
    selectedLocation.value = {
      lat: 14.5994,
      lng: 120.9842,
      address: props.initialAddress
    }
  }
  
  // Initialize map after component mounts
  initializeMap()
})
</script>

<style scoped>
.geolocation-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.map-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  gap: 1rem;
}

.skeleton-pulse {
  width: 100%;
  height: 400px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: pulse 1.5s infinite;
  border-radius: 8px;
}

@keyframes pulse {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.map-controls {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  position: relative;
  z-index: 1000; /* bring controls above the map */
}

.search-control {
  position: relative;
  min-width: 280px;
  max-width: 420px;
  z-index: 1001;
}

.search-input {
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  width: 100%;
  font-size: 0.9rem;
  box-sizing: border-box;
}

.search-results {
  position: absolute;
  left: 0;
  right: 0;
  top: calc(100% + 6px);
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  max-height: 240px;
  overflow: auto;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  z-index: 100000; /* ensure suggestions sit above Leaflet panes */
  padding: 6px 0;
}

.search-result-item {
  padding: 8px 12px;
  cursor: pointer;
}

.search-result-item:hover { background: #f3f4f6; }
.result-title { font-size: 0.9rem; font-weight: 600; color: #111827; }
.result-sub { font-size: 0.8rem; color: #6b7280; margin-top: 4px; }

.btn {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.15s ease;
  font-weight: 600;
  white-space: nowrap;
}

.btn-primary {
  background-color: #ff9a4a;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #ff6a3d;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(255, 106, 61, 0.3);
}

.btn-secondary {
  background-color: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover:not(:disabled) {
  background-color: #d1d5db;
  transform: translateY(-1px);
}

.btn-success {
  background-color: #10b981;
  color: white;
}

.btn-success:hover:not(:disabled) {
  background-color: #059669;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3);
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.8125rem;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.map-wrapper {
  width: 100%;
  height: 400px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  background: #f9fafb;
  transition: box-shadow 0.2s;
  position: relative;
  z-index: 1; /* keep map beneath controls/dropdown */
}

.map-wrapper.loading {
  opacity: 0.7;
  pointer-events: none;
}

.map-wrapper :deep(.map-tiles) {
  image-rendering: crisp-edges;
}

.coordinates-display {
  padding: 1rem;
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.coord-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.coord-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.coord-value {
  font-size: 0.95rem;
  color: #1f2937;
  font-family: 'Monaco', 'Courier New', monospace;
  font-weight: 600;
  word-break: break-all;
}

.info-message {
  padding: 0.75rem 1rem;
  background-color: #eff6ff;
  border-left: 4px solid #3b82f6;
  border-radius: 4px;
  color: #1e40af;
  font-size: 0.875rem;
  line-height: 1.4;
}

@media (max-width: 768px) {
  .map-wrapper {
    height: 300px;
  }

  .coordinates-display {
    grid-template-columns: 1fr;
  }

  .map-controls {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }
}
</style>
