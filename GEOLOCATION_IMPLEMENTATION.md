# Geolocation Implementation Checklist

## ✅ Completed Setup

### 1. **Components Created**
- [x] `resources/js/components/GeolocationMap.vue` - Standalone map component
- [x] `resources/js/components/AddressCascaderWithMap.vue` - Address + Map combined component

### 2. **Dependencies Installed**
- [x] `leaflet` - Map library installed via npm

### 3. **Database**
- [x] Migration created: `database/migrations/2026_04_14_000000_add_geolocation_to_tables.php`
- [ ] **RUN MIGRATION**: `php artisan migrate`

### 4. **Models Updated**
- [x] `app/Models/User.php` - Added latitude/longitude fields
- [x] `app/Models/Branch.php` - Added latitude/longitude fields
- [x] `app/Models/CustomerAccount.php` - Added latitude/longitude fields

### 5. **Documentation**
- [x] `GEOLOCATION_GUIDE.md` - Complete integration guide

---

## 🚀 Next Steps to Get Started

### Step 1: Run Database Migration
```bash
php artisan migrate
```

### Step 2: Import Components in Your Views
Choose one or both:

**Option A: Use Standalone Map**
```vue
import GeolocationMap from '@/components/GeolocationMap.vue'
```

**Option B: Use Combined Address + Map** (RECOMMENDED)
```vue
import AddressCascaderWithMap from '@/components/AddressCascaderWithMap.vue'
```

### Step 3: Replace Existing Address Components

#### For Staff Modal (`resources/js/components/StaffModal.vue`):
```vue
<!-- OLD -->
<AddressCascader
  :initialAddress="{ region: form.region, ... }"
  @update:address="onAddressUpdate"
/>

<!-- NEW -->
<AddressCascaderWithMap
  :initialAddress="{ region: form.region, ... }"
  :initialLocation="{ lat: form.latitude, lng: form.longitude }"
  @saved="onAddressSaved"
/>
```

#### For Add Branches (`resources/js/components/OwnerAddBranches.vue`):
```vue
<!-- OLD -->
<AddressCascader
  :initialAddress="branchForm"
  @update:address="onAddressUpdate"
/>

<!-- NEW -->
<AddressCascaderWithMap
  :initialAddress="{ region: branchForm.region, ... }"
  :initialLocation="{ lat: branchForm.latitude, lng: branchForm.longitude }"
  @saved="onAddressSaved"
/>
```

### Step 4: Update Form Handlers
```javascript
function onAddressSaved(payload) {
  // Address components
  form.region = payload.address.region
  form.province = payload.address.province
  form.city = payload.address.city
  form.barangay = payload.address.barangay
  
  // Geolocation coordinates
  form.latitude = payload.location.lat
  form.longitude = payload.location.lng
}
```

### Step 5: Update API Endpoints
When saving, include coordinates:
```php
$user->update([
    'region' => $validated['region'],
    'province' => $validated['province'],
    'city' => $validated['city'],
    'barangay' => $validated['barangay'],
    'latitude' => $validated['latitude'],
    'longitude' => $validated['longitude'],
]);
```

---

## 📋 Feature Summary

### GeolocationMap.vue
| Feature | Status |
|---------|--------|
| Click on map to pin location | ✅ Ready |
| Use current GPS location | ✅ Ready |
| Display coordinates | ✅ Ready |
| Reset to defaults | ✅ Ready |
| Responsive design | ✅ Ready |
| Mobile support | ✅ Ready |

### AddressCascaderWithMap.vue
| Feature | Status |
|---------|--------|
| Hierarchical address selection | ✅ Ready |
| Integrated map component | ✅ Ready |
| Tab-based UI | ✅ Ready |
| Full address auto-generation | ✅ Ready |
| Location summary display | ✅ Ready |
| Combined save & emit | ✅ Ready |

---

## 🛠️ Common Integration Points

### 1. **StaffModal.vue** - Staff address & location
### 2. **OwnerAddBranches.vue** - Branch address & location
### 3. **Customer Registration** - Customer address & location
### 4. **Delivery Tracking** - Delivery address & real-time location

---

## 📱 Browser Requirements

- **Modern Browsers**: Chrome, Firefox, Safari, Edge
- **HTTPS Required**: Yes (except localhost)
- **Geolocation Permission**: User must grant access
- **JavaScript Enabled**: Required

---

## 🎯 Testing Checklist

- [ ] Database migration runs without errors
- [ ] Map component displays correctly
- [ ] Can click on map to set location
- [ ] "Use My Current Location" button works
- [ ] Coordinates display with 8 decimal precision
- [ ] Reset button returns to default location
- [ ] Address cascader selections work
- [ ] Form saves both address and coordinates
- [ ] Responsive design works on mobile
- [ ] No console errors

---

## 🔗 Quick Links

- **Geolocation Guide**: See `GEOLOCATION_GUIDE.md`
- **Component Docs**: See component file headers
- **Leaflet Docs**: https://leafletjs.com/reference.html
- **Geolocation API**: https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API

---

## ⚠️ Important Notes

1. **Migration**: Must run `php artisan migrate` before saving coordinates
2. **Leaflet CSS**: Ensure `import 'leaflet/dist/leaflet.css'` is in component
3. **CORS**: If using geocoding APIs, configure CORS appropriately
4. **Privacy**: Inform users about location collection
5. **Validation**: Validate latitude (-90 to 90) and longitude (-180 to 180)

---

**Status**: ✅ Ready for Integration
**Last Updated**: April 14, 2026
