# Geolocation Integration Guide

## Overview
This guide explains how to integrate geolocation pinpointing with address selection in your Chikintayo application.

## Components Created

### 1. **GeolocationMap.vue**
Standalone map component with geolocation features.

**Features:**
- Interactive Leaflet map
- Click to pin location
- Get current device location
- Display latitude/longitude coordinates
- Reset map to default location

**Usage:**
```vue
<template>
  <GeolocationMap 
    :initialLocation="{ lat: 14.5994, lng: 120.9842 }"
    :initialAddress="'Quezon City, Metro Manila'"
    @update:location="handleLocationUpdate"
  />
</template>

<script setup>
import GeolocationMap from '@/components/GeolocationMap.vue'

function handleLocationUpdate(location) {
  console.log('Location updated:', location)
  // location = { lat: 14.5994, lng: 120.9842 }
}
</script>
```

### 2. **AddressCascaderWithMap.vue**
Enhanced address component combining cascading address selection with map.

**Features:**
- Tab-based interface (Address Selection | Map Pinning)
- Region → Province → City → Barangay selection
- Integrated map for location pinning
- Automatic full address display
- Saves both address and coordinates

**Usage:**
```vue
<template>
  <AddressCascaderWithMap 
    :initialAddress="{ region: 'NCR', province: 'Metro Manila', city: 'Quezon City' }"
    :initialLocation="{ lat: 14.5994, lng: 120.9842 }"
    :showSaveButton="true"
    @saved="handleSave"
    @update:address="handleAddressUpdate"
    @update:location="handleLocationUpdate"
  />
</template>

<script setup>
import AddressCascaderWithMap from '@/components/AddressCascaderWithMap.vue'

function handleSave(payload) {
  console.log('Saved data:', payload)
  // payload = {
  //   address: { region, province, city, barangay },
  //   location: { lat, lng }
  // }
}

function handleAddressUpdate(address) {
  console.log('Address updated:', address)
}

function handleLocationUpdate(location) {
  console.log('Location updated:', location)
}
</script>
```

## Database Schema

### Migration Applied
File: `database/migrations/2026_04_14_000000_add_geolocation_to_tables.php`

**Tables Updated:**
1. **users** - Added `latitude` (decimal 10,8), `longitude` (decimal 11,8)
2. **branches** - Added `latitude` (decimal 10,8), `longitude` (decimal 11,8)
3. **customer_accounts** - Added `latitude` (decimal 10,8), `longitude` (decimal 11,8)

**Run Migration:**
```bash
php artisan migrate
```

## Model Updates

### Updated Models
- `App\Models\User`
- `App\Models\Branch`
- `App\Models\CustomerAccount`

All models now include:
- `latitude` and `longitude` in `$fillable` array
- Float casting for coordinates in `$casts`

**Example:**
```php
$user = User::create([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'address' => '123 Main St, Quezon City',
    'latitude' => 14.5994,
    'longitude' => 120.9842,
    // ... other fields
]);
```

## Integration Examples

### Example 1: Update StaffModal Component
Replace or enhance existing AddressCascader with AddressCascaderWithMap:

```vue
<!-- In StaffModal.vue -->
<template>
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
  />
</template>

<script setup>
function onAddressSaved(payload) {
  form.region = payload.address.region
  form.province = payload.address.province
  form.city = payload.address.city
  form.barangay = payload.address.barangay
  form.latitude = payload.location.lat
  form.longitude = payload.location.lng
}
</script>
```

### Example 2: Update OwnerAddBranches Component
```vue
<!-- In OwnerAddBranches.vue -->
<template>
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
    @saved="onAddressSaved"
  />
</template>

<script setup>
function onAddressSaved(payload) {
  branchForm.region = payload.address.region
  branchForm.province = payload.address.province
  branchForm.city = payload.address.city
  branchForm.barangay = payload.address.barangay
  branchForm.latitude = payload.location.lat
  branchForm.longitude = payload.location.lng
}
</script>
```

### Example 3: Standalone Map for Displaying Locations
```vue
<template>
  <!-- View-only map showing stored location -->
  <div class="location-display">
    <h3>Branch Location</h3>
    <GeolocationMap 
      :initialLocation="{ 
        lat: branch.latitude, 
        lng: branch.longitude 
      }"
      :initialAddress="branch.address"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import GeolocationMap from '@/components/GeolocationMap.vue'

const branch = ref({
  address: 'Quezon City, Metro Manila',
  latitude: 14.5994,
  longitude: 120.9842
})
</script>
```

## API Endpoint Examples

### Store User with Geolocation
```php
// In your controller
$validated = $request->validate([
    'full_name' => 'required|string',
    'address' => 'required|string',
    'latitude' => 'required|numeric|between:-90,90',
    'longitude' => 'required|numeric|between:-180,180',
    // ... other fields
]);

$user = User::create($validated);

return response()->json([
    'ok' => true,
    'user' => $user,
    'message' => 'User created with location'
]);
```

### Query by Geolocation Range
```php
// Find all branches within 5km radius
use Illuminate\Support\Facades\DB;

$latitude = 14.5994;
$longitude = 120.9842;
$radius = 5; // km

$branches = Branch::whereRaw(
    "ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) / 1000 <= ?",
    [$longitude, $latitude, $radius]
)->get();
```

## Installation & Setup

### 1. Install Dependencies
```bash
npm install leaflet
```

### 2. Run Migration
```bash
php artisan migrate
```

### 3. Import Components in Your Views

**In Vue setup (main.js or where you register global components):**
```javascript
import GeolocationMap from '@/components/GeolocationMap.vue'
import AddressCascaderWithMap from '@/components/AddressCascaderWithMap.vue'

// If registering globally:
app.component('GeolocationMap', GeolocationMap)
app.component('AddressCascaderWithMap', AddressCascaderWithMap)
```

### 4. Use in Your Components
See integration examples above.

## Features & Capabilities

### GeolocationMap Features
- ✅ **Click to Pin**: Click anywhere on the map to set a location
- ✅ **Current Location**: Auto-detect user's current GPS location
- ✅ **Reset**: Return to default view
- ✅ **Coordinates Display**: Show exact lat/lng values
- ✅ **Responsive Design**: Works on mobile and desktop
- ✅ **OpenStreetMap**: Uses free, open-source map provider

### AddressCascaderWithMap Features
- ✅ **Dual Tabs**: Switch between hierarchical address selection and map pinning
- ✅ **Full Address Display**: Automatically constructs address from selections
- ✅ **Location Summary**: Shows current coordinates and full address
- ✅ **Form Submission**: Saves both address components and geolocation
- ✅ **Validation**: Ensures all required address fields are selected
- ✅ **Responsive**: Mobile-friendly interface

## Map Library: Leaflet.js

The components use **Leaflet.js**, a popular open-source mapping library.

**Why Leaflet?**
- ✅ Free and open-source
- ✅ No API key required for basic maps
- ✅ Lightweight (~40KB gzipped)
- ✅ Excellent mobile support
- ✅ Easy to customize

**Map Provider:**
- Uses OpenStreetMap tiles (free, community-driven)
- No usage limits for reasonable traffic
- Works offline-capable

## Browser Compatibility

- **Geolocation API**: Requires HTTPS (or localhost for development)
- **Leaflet Maps**: Works in all modern browsers
- **Coordinate Precision**: 8 decimal places = ~1.11mm accuracy

## Troubleshooting

### Issue: "User denied geolocation" error
**Solution**: User needs to grant location permission in browser settings. Check browser console for permission prompts.

### Issue: Map not loading
**Solution**: 
- Ensure Leaflet CSS is imported: `import 'leaflet/dist/leaflet.css'`
- Check that `mapContainer` ref has sufficient height

### Issue: Coordinates not saving
**Solution**:
- Verify database migration ran: `php artisan migrate`
- Check model `$fillable` includes `latitude` and `longitude`
- Validate latitude is between -90 and 90, longitude between -180 and 180

### Issue: Browser blocking geolocation
**Solution**:
- Ensure site is on HTTPS (not required for localhost)
- Check browser permissions: Settings > Privacy > Site Settings > Location

## Privacy & Security Considerations

1. **User Consent**: Always ask for permission before accessing location
2. **HTTPS Only**: Geolocation requires secure context (HTTPS)
3. **Data Protection**: Latitude/longitude can identify individuals
4. **GDPR Compliance**: Ensure compliance with location data handling regulations
5. **Optional Field**: Make geolocation optional, not mandatory

## Future Enhancements

Possible additions:
- Address autocomplete via Geocoding API
- Distance calculation between locations
- Route mapping for logistics
- Real-time tracking for deliveries
- Heatmaps for order density
- Geofencing alerts

## Support & Documentation

- **Leaflet Docs**: https://leafletjs.com/reference.html
- **OpenStreetMap**: https://www.openstreetmap.org/
- **Geolocation API**: https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API

---

**Last Updated**: April 14, 2026
**Components**: GeolocationMap.vue, AddressCascaderWithMap.vue
**Migration**: 2026_04_14_000000_add_geolocation_to_tables.php
