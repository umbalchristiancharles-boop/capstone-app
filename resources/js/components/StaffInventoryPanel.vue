<template>
  <OwnerPanelLayout :userProfile="userProfile" :panelTitle="'Staff Inventory Panel'" :panelDescription="'Update stock and view product list.'" @logout="showLogoutConfirm = true">
    <template #main>
      <inventory-staff-panel :products="products" />
    </template>
    <template #profileFooter>
      <button class="admin-info-btn admin-info-btn--center" @click="showInfoModal = true">Info</button>
    </template>
  </OwnerPanelLayout>
  <StaffInfoModal :show="showInfoModal" :staff="userProfile" @close="showInfoModal = false" @updated="onProfileUpdated" />
  <transition name="fade">
    <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
      <div class="logout-confirm-box">
        <h3>Logout from Staff Inventory Panel?</h3>
        <p>This will end your current session for Chikin Tayo Staff.</p>
        <div class="logout-actions">
          <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
          <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
        </div>
      </div>
    </div>
  </transition>
  <transition name="fade">
    <div v-if="showOverlay" class="loading-overlay">
      <div class="logo-loading-box">
        <img :src="logoImg" alt="Chikin Tayo" class="logo-loading-img" />
        <p>{{ overlayText }}</p>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import InventoryStaffPanel from './inventory/InventoryStaffPanel.vue'
import StaffInfoModal from './StaffInfoModal.vue'
import axios from 'axios'
import '../css/adminpanel.css'

const userProfile = ref({})
const products = ref([])
const router = useRouter()

const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href
const showInfoModal = ref(false)

onMounted(async () => {
  const res = await axios.get('/api/staff/inventory/profile', { withCredentials: true })
  userProfile.value = res.data.user
  const prods = await axios.get('/api/staff/inventory/products', { withCredentials: true })
  products.value = prods.data
})

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try { try { localStorage.clear(); sessionStorage.clear(); } catch (e) {} window.location.replace('/logout') } catch (e) {}
  overlayText.value = 'Logging out...'
  try { if (window.pageBlur && typeof window.pageBlur.show === 'function') window.pageBlur.show() } catch (e) {}
  showOverlay.value = true
  showLogoutConfirm.value = false
  setTimeout(() => {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    try { window.location.replace('/') ; window.location.reload(); } catch (e) { router.push('/').catch(() => {}) }
  }, 600)
}

function onProfileUpdated(newData) {
  Object.assign(userProfile.value, newData)
}
</script>
