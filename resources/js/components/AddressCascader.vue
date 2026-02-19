<template>
  <div class="address-cascader">
    <div class="form-row">
      <label>Region *</label>
      <select v-model="address.state.selectedRegion">
        <option value="">-- Select Region --</option>
        <optgroup v-for="group in regionGroups" :label="group.group" :key="group.group">
          <option v-for="r in group.regions" :key="r" :value="r">{{ r }}</option>
        </optgroup>
      </select>
      <div v-if="address.state.errors.region" class="error">{{ address.state.errors.region }}</div>
    </div>

    <div class="form-row">
      <label>Province *</label>
      <select v-model="address.state.selectedProvince" :disabled="!address.state.selectedRegion">
        <option value="">-- Select Province --</option>
        <template v-if="provinces && provinces.length">
          <option v-for="p in provinces" :key="p" :value="p">{{ p }}</option>
        </template>
        <option v-else disabled value="">No provinces available</option>
      </select>
      <div v-if="address.state.errors.province" class="error">{{ address.state.errors.province }}</div>
    </div>

    <div class="form-row">
      <label>City / Municipality *</label>
      <select v-model="address.state.selectedCity" :disabled="!address.state.selectedProvince">
        <option value="">-- Select City / Municipality --</option>
        <template v-if="cities && cities.length">
          <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
        </template>
        <option v-else disabled value="">No cities available</option>
      </select>
      <div v-if="address.state.errors.city" class="error">{{ address.state.errors.city }}</div>
    </div>

    <div class="form-row">
      <label>Barangay *</label>
      <select v-model="address.state.selectedBarangay" :disabled="!address.state.selectedCity">
        <option value="">-- Select Barangay --</option>
        <template v-if="barangays && barangays.length">
          <option v-for="b in barangays" :key="b" :value="b">{{ b }}</option>
        </template>
        <option v-else disabled value="">No barangays available</option>
      </select>
      <div v-if="address.state.errors.barangay" class="error">{{ address.state.errors.barangay }}</div>
    </div>

    <div class="actions" v-if="props.showSaveButton">
      <button type="button" @click="onSave">Save Address</button>
    </div>
  </div>
</template>

<script setup>
import { watch, defineProps, defineEmits } from 'vue'
import useAddress from '../composables/useAddress'

const props = defineProps({
  initialAddress: {
    type: Object,
    default: () => ({})
  },
  showSaveButton: {
    type: Boolean,
    default: true
  }
})
const emit = defineEmits(['update:address', 'saved'])

const address = useAddress(props.initialAddress || {})

const regionGroups = [
  {
    group: 'LUZON',
    regions: [
      'NCR (National Capital Region)',
      'CAR (Cordillera Administrative Region)',
      'Region I – Ilocos Region',
      'Region II – Cagayan Valley',
      'Region III – Central Luzon',
      'Region IV-A – CALABARZON',
      'Region IV-B – MIMAROPA',
      'Region V – Bicol Region'
    ]
  },
  {
    group: 'VISAYAS',
    regions: [
      'Region VI – Western Visayas',
      'Region VII – Central Visayas',
      'Region VIII – Eastern Visayas'
    ]
  },
  {
    group: 'MINDANAO',
    regions: [
      'Region IX – Zamboanga Peninsula',
      'Region X – Northern Mindanao',
      'Region XI – Davao Region',
      'Region XII – SOCCSKSARGEN',
      'Region XIII – Caraga',
      'BARMM – Bangsamoro Autonomous Region'
    ]
  }
]

const { provinces, cities, barangays } = address

// Emit updates whenever selections change
watch(
  () => ({
    region: address.state.selectedRegion,
    province: address.state.selectedProvince,
    city: address.state.selectedCity,
    barangay: address.state.selectedBarangay
  }),
  (val) => {
    emit('update:address', val)
  },
  { deep: true }
)

function onSave() {
  if (!address.validate()) return
  const payload = {
    region: address.state.selectedRegion,
    province: address.state.selectedProvince,
    city: address.state.selectedCity,
    barangay: address.state.selectedBarangay
  }
  emit('saved', payload)
}
</script>

<style scoped>
.form-row {
  margin-bottom: 0.75rem;
  display: flex;
  flex-direction: column;
}
.form-row label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.35rem;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}
.form-row select {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  background: #ffffff;
  color: #374151;
  font-size: 0.9rem;
  font-family: inherit;
}
.form-row select:disabled {
  background: #f3f4f6;
  color: #9ca3af;
}
.error { color: #b91c1c; font-size: 0.85rem; margin-top: 0.25rem }
.actions { margin-top: 1rem }
</style>
