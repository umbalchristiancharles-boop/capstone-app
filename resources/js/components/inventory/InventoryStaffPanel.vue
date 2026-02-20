<template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page">
      <section class="admin-layout">
        <!-- LEFT: PROFILE SIDEBAR -->
        <aside class="admin-profile-column">
          <div v-if="!isProfileLoading" class="admin-card admin-card--stacked">
            <div class="admin-card__header admin-card__header--stacked">
              <label class="admin-avatar admin-avatar--photo avatar-upload" for="avatar-input">
                <img v-if="staffProfile.avatarUrl" :src="staffProfile.avatarUrl" alt="Profile picture" class="avatar-img" />
                <div v-else class="avatar-placeholder"><span class="avatar-initials">ST</span></div>
                <div class="avatar-overlay"><span class="avatar-change-text">Change Photo</span></div>
              </label>
              <div class="admin-header-text admin-admin-header-text--center">
                <div class="admin-label">Account</div>
                <div class="admin-name">{{ staffProfile.fullName || 'Staff' }}</div>
                <div class="admin-role">{{ staffProfile.role || 'STAFF' }}</div>
              </div>
              <input id="avatar-input" type="file" accept="image/*" @change="onAvatarChange" style="display: none" />
            </div>
            <div class="admin-card__body admin-card__body--stacked">
              <div class="admin-id-block admin-id-block--center">
                <span class="admin-id-label">Account I.D: </span>
                <span class="admin-id-value">&nbsp;{{ staffProfile.accountId || 'st0001' }}</span>
              </div>
              <button class="admin-info-btn admin-info-btn--center" @click="openInfoModal">Info</button>
            </div>
            <div class="admin-card__footer admin-card__footer--stacked">
              <div class="admin-actions-row">
                <button class="logout-btn logout-btn--center" @click="showLogoutConfirm = true">Logout</button>
              </div>
            </div>
          </div>
        </aside>
        <!-- MIDDLE: MAIN CONTENT -->
        <main class="admin-main">
          <header class="admin-main-header">
            <div class="admin-main-header-top">
              <div>
                <h1>Product List</h1>
                <p>All products assigned to inventory staff.</p>
              </div>
            </div>
          </header>
          <section class="overview-grid">
            <div v-if="!products.length">No products found.</div>
            <ul v-else>
              <li v-for="prod in products" :key="prod.id">{{ prod.name }} - Stock: {{ prod.stock }}</li>
            </ul>
          </section>
        </main>
      </section>

      <!-- INFO MODAL -->
      <transition name="fade">
        <div v-if="showInfoModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Staff Information</h3>
            <p class="info-sub">Personal details for this staff can be updated from this panel.</p>
            <div class="info-grid">
              <div class="info-row"><span class="info-label">Full name</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.fullName }}</span>
                <input v-else v-model="staffProfile.fullName" class="info-input" type="text" />
              </div>
              <div class="info-row"><span class="info-label">Role</span><span class="info-value">{{ staffProfile.role }}</span></div>
              <div class="info-row"><span class="info-label">Username</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.username }}</span>
                <input v-else v-model="staffProfile.username" class="info-input" type="text" placeholder="Enter username" />
              </div>
              <div class="info-row"><span class="info-label">Email</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.email }}</span>
                <input v-else v-model="staffProfile.email" class="info-input" type="email" />
              </div>
              <div class="info-row"><span class="info-label">Contact</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.contact }}</span>
                <input v-else v-model="staffProfile.contact" class="info-input" type="text" />
              </div>
              <!-- Password fields - only shown when editing -->
              <template v-if="isEditingInfo">
                <div class="info-row info-row--password">
                  <span class="info-label">New Password</span>
                  <input v-model="staffProfile.password" class="info-input" type="password" placeholder="Leave blank to keep current" />
                </div>
                <div class="info-row info-row--password">
                  <span class="info-label">Confirm Password</span>
                  <input v-model="staffProfile.password_confirmation" class="info-input" type="password" placeholder="Re-enter new password" />
                </div>
              </template>
            </div>
            <div v-if="profileError" class="info-error">{{ profileError }}</div>
            <div v-if="profileSuccess" class="info-success">{{ profileSuccess }}</div>
            <div class="info-actions">
              <button class="btn-outline" @click="handleInfoClose">{{ isEditingInfo ? 'Cancel' : 'Close' }}</button>
              <button class="btn-primary" @click="isEditingInfo ? saveStaffInfo() : (isEditingInfo = true)" :disabled="isSavingProfile">
                {{ isEditingInfo ? (isSavingProfile ? 'Saving...' : 'Save changes') : 'Edit information' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- LOGOUT CONFIRM MODAL -->
      <transition name="fade">
        <div v-if="showLogoutConfirm" class="info-backdrop">
          <div class="info-modal">
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to logout?</p>
            <div class="info-actions">
              <button class="btn-outline" @click="showLogoutConfirm = false">Cancel</button>
              <button class="btn-primary" @click="logout" :disabled="isLoggingOut">{{ isLoggingOut ? 'Logging out...' : 'Logout' }}</button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const staffProfile = ref({
  avatarUrl: '',
  fullName: '',
  role: '',
  username: '',
  email: '',
  contact: '',
  accountId: '',
  password: '',
  password_confirmation: ''
});
const isProfileLoading = ref(true);
const isEditingInfo = ref(false);
const isSavingProfile = ref(false);
const showInfoModal = ref(false);
const profileError = ref('');
const profileSuccess = ref('');
const showLogoutConfirm = ref(false);
const isLoggingOut = ref(false);

// Products prop (with default)
defineProps({
  products: {
    type: Array,
    default: () => []
  }
});

onMounted(async () => {
  isProfileLoading.value = true;
  try {
    const res = await axios.get('/api/me', { withCredentials: true });
    if (res.data && res.data.ok && res.data.user) {
      const u = res.data.user;
      staffProfile.value = {
        avatarUrl: u.avatar_url || '',
        fullName: u.full_name || '',
        role: u.role || '',
        username: u.username || '',
        email: u.email || '',
        contact: u.contact || '',
        accountId: u.account_id || '',
        password: '',
        password_confirmation: ''
      };
    }
  } catch (e) {
    profileError.value = 'Failed to load profile info.';
  } finally {
    isProfileLoading.value = false;
  }
});

function openInfoModal() {
  showInfoModal.value = true;
  isEditingInfo.value = false;
  profileError.value = '';
  profileSuccess.value = '';
}

function handleInfoClose() {
  if (isEditingInfo.value) {
    isEditingInfo.value = false;
    profileError.value = '';
    profileSuccess.value = '';
  } else {
    showInfoModal.value = false;
  }
}

async function saveStaffInfo() {
  isSavingProfile.value = true;
  profileError.value = '';
  profileSuccess.value = '';
  // TODO: Replace with real API call
  setTimeout(() => {
    isSavingProfile.value = false;
    isEditingInfo.value = false;
    profileSuccess.value = 'Profile updated!';
  }, 1000);
}

function onAvatarChange(e) {
  // TODO: Implement avatar upload
  profileError.value = 'Avatar upload not implemented.';
}

async function logout() {
  if (isLoggingOut.value) return;
  isLoggingOut.value = true;
  try {
    await axios.post('/api/logout', {}, { withCredentials: true });
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  // Optional: show overlay (if you have one)
  showLogoutConfirm.value = false;
  setTimeout(() => {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    try { window.location.replace('/') } catch (e) { router.push('/').catch(() => {}) }
  }, 600);
}
</script>
