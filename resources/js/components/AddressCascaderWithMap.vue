<template>
  <div class="address-with-geolocation">
    <!-- Map Component -->
    <GeolocationMap 
      v-if="mapComponentReady"
      :initialLocation="currentLocation"
      :initialAddress="fullAddress"
      @update:location="onLocationUpdate"
      @save:location="onSaveLocation"
    />
    <div v-else class="map-loading-placeholder">
      <div class="skeleton-pulse"></div>
    </div>

    <!-- Current Location Display -->
    <div v-if="currentLocation && (currentLocation.lat || currentLocation.lng)" class="location-summary">
      <div class="summary-item">
        <strong>📌 Pinned Location:</strong>
        <span>{{ currentLocation.lat?.toFixed(6) }}, {{ currentLocation.lng?.toFixed(6) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import GeolocationMap from './GeolocationMap.vue'

const props = defineProps({
  initialLocation: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:location', 'save:location'])

const currentLocation = ref({
  lat: props.initialLocation?.lat || 14.5994,
  lng: props.initialLocation?.lng || 120.9842
})

// Map always ready
const mapComponentReady = ref(true)

const fullAddress = computed(() => '')

function onLocationUpdate(location) {
  console.log('� Location pinned:', location)
  currentLocation.value = location
  emit('update:location', location)
}
function onSaveLocation(location) {
  console.log('✓ Location saved:', location)
  emit('save:location', location)
}
</script>

<style scoped>
.address-with-geolocation {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.map-loading-placeholder {
  width: 100%;
  height: 300px;
  background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 50%, #f3f4f6 100%);
  background-size: 200% 100%;
  border-radius: 8px;
  animation: pulse 1.5s infinite;
}

.skeleton-pulse {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  animation: pulse 1.5s infinite;
  background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 50%, #f3f4f6 100%);
  background-size: 200% 100%;
}

@keyframes pulse {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.location-summary {
  padding: 1rem;
  background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
  border: 2px solid #bfdbfe;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.summary-item {
  display: flex;
  gap: 0.5rem;
  font-size: 0.95rem;
  line-height: 1.4;
}

.summary-item strong {
  color: #1e40af;
  flex-shrink: 0;
}

.summary-item span {
  color: #1f2937;
  font-family: 'Monaco', 'Courier New', monospace;
  word-break: break-word;
}
</style>
