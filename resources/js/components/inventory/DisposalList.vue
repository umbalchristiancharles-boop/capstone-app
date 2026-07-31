<template>
  <OwnerPanelLayout
    :userProfile="staffProfile"
    :panelTitle="'Product Disposal List'"
    panelDescription="Track and manage expired product disposals"
    :enableProfileUpdate="true"
    :showProfileColumn="false"
    :ownerTwoColumnLayout="true"
    @logout="logout"
  >
    <template #headerActions>
      <div class="header-profile-wrapper" ref="headerProfileWrapper" style="margin: 0 0 12px;">
        <button class="header-profile-btn" @click.stop="toggleProfileMenu" type="button">
          <div class="header-avatar">
            <div v-if="staffProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url(' + staffProfile.avatarUrl + ')' }"></div>
            <div v-else class="header-avatar-initials">{{ staffProfile.fullName ? (staffProfile.fullName.charAt(0) || 'U') : 'U' }}</div>
          </div>
          <div class="header-name">{{ ((staffProfile.fullName || staffProfile.full_name) || ((staffProfile.role || 'STAFF') + (staffProfile.branch_name ? ' - ' + staffProfile.branch_name : (staffProfile.branch ? ' - ' + staffProfile.branch : '')) )).toUpperCase() }}</div>
        </button>
        <input id="staff-avatar-input" type="file" accept="image/*" @change="onAvatarChange" style="display: none" />

        <!-- Profile dropdown -->
        <div v-if="showProfileMenu" class="profile-dropdown" ref="profileDropdown" @click.stop>
          <button class="dropdown-item" @click="openInfoFromMenu">Info</button>
          <button class="dropdown-item" @click="openLogoutFromMenu">Logout</button>
        </div>
      </div>
    </template>

    <template #main>
      <!-- Summary Stats -->
      <div class="disposal-stats-grid">
        <div class="disposal-stat-card disposal-stat-card--total">
          <div class="disposal-stat-icon">📊</div>
          <div class="disposal-stat-content">
            <span class="disposal-stat-label">Total Disposals</span>
            <span class="disposal-stat-value">{{ disposalStats.total }}</span>
          </div>
        </div>
        <div class="disposal-stat-card disposal-stat-card--pending">
          <div class="disposal-stat-icon">⏳</div>
          <div class="disposal-stat-content">
            <span class="disposal-stat-label">Pending Review</span>
            <span class="disposal-stat-value">{{ disposalStats.pending }}</span>
          </div>
        </div>
        <div class="disposal-stat-card disposal-stat-card--resolved">
          <div class="disposal-stat-icon">✅</div>
          <div class="disposal-stat-content">
            <span class="disposal-stat-label">Resolved</span>
            <span class="disposal-stat-value">{{ disposalStats.resolved }}</span>
          </div>
        </div>
        <div class="disposal-stat-card disposal-stat-card--quantity">
          <div class="disposal-stat-icon">📦</div>
          <div class="disposal-stat-content">
            <span class="disposal-stat-label">Total Units Disposed</span>
            <span class="disposal-stat-value">{{ disposalStats.totalQuantity }}</span>
          </div>
        </div>
      </div>

      <!-- Filters and Actions -->
      <div class="disposal-controls">
        <div class="disposal-filters">
          <input
            v-model="searchQuery"
            @input="filterDisposals"
            type="text"
            placeholder="Search by product name or SKU..."
            class="disposal-search"
          />
          <select v-model="statusFilter" @change="filterDisposals" class="disposal-filter">
            <option value="all">All Status</option>
            <option value="pending">Pending Review</option>
            <option value="reviewed">Reviewed</option>
            <option value="resolved">Resolved</option>
          </select>
          <input
            v-model="dateFrom"
            @change="filterDisposals"
            type="date"
            class="disposal-date-filter"
            placeholder="From"
          />
          <input
            v-model="dateTo"
            @change="filterDisposals"
            type="date"
            class="disposal-date-filter"
            placeholder="To"
          />
        </div>
        <div class="disposal-actions">
          <button @click="exportDisposals" class="btn btn-light">
            📥 Export CSV
          </button>
          <button @click="refreshDisposals" class="btn btn-primary" :disabled="isLoading">
            🔄 Refresh
          </button>
        </div>
      </div>

      <!-- Disposal List Table -->
      <div class="disposal-table-container">
        <div v-if="isLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading disposal records...</p>
        </div>
        <div v-else-if="disposalError" class="error-container">
          <p class="error-message">{{ disposalError }}</p>
          <button @click="refreshDisposals" class="btn-retry">Retry</button>
        </div>
        <div v-else-if="filteredDisposals.length === 0" class="empty-container">
          <p>No disposal records found.</p>
        </div>
        <table v-else class="disposal-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Product Name</th>
              <th>SKU</th>
              <th>Quantity</th>
              <th>Reported By</th>
              <th>Notes</th>
              <th>Image</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="disposal in paginatedDisposals" :key="disposal.id" class="disposal-row">
              <td class="disposal-date">{{ formatDate(disposal.created_at) }}</td>
              <td class="disposal-product">{{ disposal.product_name }}</td>
              <td class="disposal-sku">{{ disposal.product_sku || 'N/A' }}</td>
              <td class="disposal-quantity">{{ disposal.quantity }}</td>
              <td class="disposal-reporter">{{ disposal.reported_by }}</td>
              <td class="disposal-notes">
                <span v-if="disposal.notes" class="notes-text" :title="disposal.notes">
                  {{ truncateText(disposal.notes, 50) }}
                </span>
                <span v-else class="no-notes">-</span>
              </td>
              <td class="disposal-image">
                <img
                  v-if="disposal.image_path"
                  :src="disposal.image_path"
                  :alt="disposal.product_name"
                  class="disposal-thumb"
                  @click="viewImage(disposal.image_path)"
                />
                <span v-else class="no-image">No image</span>
              </td>
              <td class="disposal-status">
                <span :class="['status-badge', getStatusClass(disposal.status)]">
                  {{ getStatusLabel(disposal.status) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="filteredDisposals.length > perPage" class="disposal-pagination">
        <button
          class="page-btn"
          :disabled="currentPage === 1"
          @click="currentPage = 1"
        >
          « First
        </button>
        <button
          class="page-btn"
          :disabled="currentPage === 1"
          @click="currentPage--"
        >
          ‹ Prev
        </button>
        <span class="page-info">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button
          class="page-btn"
          :disabled="currentPage === totalPages"
          @click="currentPage++"
        >
          Next ›
        </button>
        <button
          class="page-btn"
          :disabled="currentPage === totalPages"
          @click="currentPage = totalPages"
        >
          Last »
        </button>
        <select v-model.number="perPage" class="per-page-select">
          <option :value="10">10 / page</option>
          <option :value="25">25 / page</option>
          <option :value="50">50 / page</option>
        </select>
      </div>

      <!-- Image Viewer Modal -->
      <transition name="fade">
        <div v-if="showImageModal" class="image-modal-backdrop" @click.self="closeImageModal">
          <div class="image-modal">
            <button @click="closeImageModal" class="image-modal-close">×</button>
            <img :src="selectedImage" alt="Disposal evidence" class="image-modal-img" />
          </div>
        </div>
      </transition>
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import OwnerPanelLayout from '../OwnerPanelLayout.vue';
import { showToast } from '../toastStore';

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

const showProfileMenu = ref(false);
const headerProfileWrapper = ref(null);
const profileDropdown = ref(null);

// Disposal data
const disposals = ref([]);
const filteredDisposals = ref([]);
const isLoading = ref(false);
const disposalError = ref('');

// Filters
const searchQuery = ref('');
const statusFilter = ref('all');
const dateFrom = ref('');
const dateTo = ref('');

// Pagination
const currentPage = ref(1);
const perPage = ref(25);

// Image modal
const showImageModal = ref(false);
const selectedImage = ref('');

// Stats
const disposalStats = ref({
  total: 0,
  pending: 0,
  reviewed: 0,
  resolved: 0,
  totalQuantity: 0
});

// Computed
const totalPages = computed(() => {
  return Math.max(1, Math.ceil(filteredDisposals.value.length / perPage.value));
});

const paginatedDisposals = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  return filteredDisposals.value.slice(start, end);
});

// Methods
async function fetchDisposals() {
  isLoading.value = true;
  disposalError.value = '';

  try {
    console.log('[DisposalList] Fetching expired products...');
    const res = await axios.get('/api/staff/inventory/expired-products', { withCredentials: true });
    console.log('[DisposalList] API Response:', res.data);
    
    if (res.data && res.data.ok) {
      const data = res.data.data || [];
      console.log('[DisposalList] Received disposals:', data.length, 'records');
      disposals.value = data;
      filteredDisposals.value = disposals.value;
      calculateStats();
      console.log('[DisposalList] Stats calculated:', disposalStats.value);
    } else {
      console.warn('[DisposalList] API returned ok=false or no data');
      disposals.value = [];
      filteredDisposals.value = [];
    }
  } catch (e) {
    console.error('[DisposalList] Failed to fetch disposals:', e);
    console.error('[DisposalList] Error details:', e.response?.data || e.message);
    disposalError.value = 'Failed to load disposal records: ' + (e.response?.data?.message || e.message);
    disposals.value = [];
    filteredDisposals.value = [];
  } finally {
    isLoading.value = false;
  }
}

function calculateStats() {
  const stats = {
    total: disposals.value.length,
    pending: 0,
    reviewed: 0,
    resolved: 0,
    totalQuantity: 0
  };

  disposals.value.forEach(disposal => {
    stats.totalQuantity += disposal.quantity || 0;
    
    const status = (disposal.status || '').toLowerCase();
    if (status === 'pending') stats.pending++;
    else if (status === 'reviewed') stats.reviewed++;
    else if (status === 'resolved') stats.resolved++;
  });

  disposalStats.value = stats;
}

function filterDisposals() {
  currentPage.value = 1;
  
  let filtered = [...disposals.value];

  // Search filter
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim();
    filtered = filtered.filter(d => 
      (d.product_name && d.product_name.toLowerCase().includes(query)) ||
      (d.product_sku && d.product_sku.toLowerCase().includes(query))
    );
  }

  // Status filter
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(d => d.status === statusFilter.value);
  }

  // Date filters
  if (dateFrom.value) {
    const fromDate = new Date(dateFrom.value);
    filtered = filtered.filter(d => new Date(d.created_at) >= fromDate);
  }

  if (dateTo.value) {
    const toDate = new Date(dateTo.value);
    toDate.setHours(23, 59, 59, 999);
    filtered = filtered.filter(d => new Date(d.created_at) <= toDate);
  }

  filteredDisposals.value = filtered;
}

function refreshDisposals() {
  fetchDisposals();
}

function exportDisposals() {
  const headers = ['Date', 'Product Name', 'SKU', 'Quantity', 'Reported By', 'Notes', 'Status'];
  const rows = [headers.join(',')];

  filteredDisposals.value.forEach(d => {
    const row = [
      formatDate(d.created_at),
      `"${(d.product_name || '').replace(/"/g, '""')}"`,
      d.product_sku || 'N/A',
      d.quantity,
      `"${(d.reported_by || '').replace(/"/g, '""')}"`,
      `"${(d.notes || '').replace(/"/g, '""')}"`,
      d.status || 'Unknown'
    ];
    rows.push(row.join(','));
  });

  const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `disposal-list-${new Date().toISOString().split('T')[0]}.csv`;
  a.click();
  URL.revokeObjectURL(url);

  showToast('Disposal list exported successfully', 'success');
}

function viewImage(imagePath) {
  if (imagePath) {
    selectedImage.value = imagePath;
    showImageModal.value = true;
  }
}

function closeImageModal() {
  showImageModal.value = false;
  selectedImage.value = '';
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  try {
    return new Date(dateStr).toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (e) {
    return dateStr;
  }
}

function truncateText(text, maxLength) {
  if (!text) return '';
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
}

function getStatusLabel(status) {
  const labels = {
    'pending': 'Pending Review',
    'reviewed': 'Reviewed',
    'resolved': 'Resolved'
  };
  return labels[status] || status || 'Unknown';
}

function getStatusClass(status) {
  const classes = {
    'pending': 'status-pending',
    'reviewed': 'status-reviewed',
    'resolved': 'status-resolved'
  };
  return classes[status] || 'status-unknown';
}

// Profile functions
function toggleProfileMenu() {
  showProfileMenu.value = !showProfileMenu.value;
}

function openInfoFromMenu() {
  showProfileMenu.value = false;
  // TODO: Open info modal
}

function openLogoutFromMenu() {
  showProfileMenu.value = false;
  logout();
}

async function logout() {
  try {
    await axios.post('/api/logout', {}, { withCredentials: true });
    localStorage.clear();
    sessionStorage.clear();
    window.location.replace('/staff-landing');
  } catch (e) {
    console.error('Logout failed:', e);
  }
}

async function onAvatarChange(event) {
  const file = event.target.files[0];
  if (!file) return;

  try {
    const formData = new FormData();
    formData.append('avatar', file);

    const res = await axios.post('/api/staff/inventory/avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      withCredentials: true
    });

    if (res.data && res.data.ok) {
      staffProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now();
      showToast('Profile picture updated successfully!', 'success');
    }
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to upload profile picture', 'error');
  }
}

// Load profile on mount
onMounted(async () => {
  try {
    const res = await axios.get('/api/me', { withCredentials: true });
    if (res.data && res.data.ok && res.data.user) {
      const u = res.data.user;
      staffProfile.value = {
        avatarUrl: u.avatar_url || '',
        fullName: u.full_name || u.fullName || '',
        role: u.role || '',
        username: u.username || '',
        email: u.email || '',
        contact: u.contact || '',
        accountId: u.account_id || '',
        branch_name: u.branch_name || (u.branch && (u.branch.name || u.branch.branch_name)) || '',
        password: '',
        password_confirmation: ''
      };
    }
  } catch (e) {
    console.error('Failed to load profile:', e);
  }

  await fetchDisposals();
});
</script>

<style scoped>
.disposal-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.disposal-stat-card {
  background: white;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 12px;
}

.disposal-stat-icon {
  font-size: 2rem;
}

.disposal-stat-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.disposal-stat-label {
  font-size: 0.85rem;
  color: #666;
  font-weight: 500;
}

.disposal-stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #333;
}

.disposal-stat-card--total {
  border-left: 4px solid #3b82f6;
}

.disposal-stat-card--pending {
  border-left: 4px solid #f59e0b;
}

.disposal-stat-card--resolved {
  border-left: 4px solid #10b981;
}

.disposal-stat-card--quantity {
  border-left: 4px solid #8b5cf6;
}

.disposal-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.disposal-filters {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  flex: 1;
}

.disposal-search {
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  min-width: 250px;
  font-size: 0.9rem;
}

.disposal-filter,
.disposal-date-filter {
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.9rem;
  background: white;
}

.disposal-actions {
  display: flex;
  gap: 8px;
}

.disposal-table-container {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow-x: auto;
}

.disposal-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.disposal-table thead {
  background: #f8fafc;
}

.disposal-table thead th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
}

.disposal-table tbody td {
  padding: 12px;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: middle;
}

.disposal-row:hover {
  background: #f9fafb;
}

.disposal-product {
  font-weight: 600;
  color: #1f2937;
}

.disposal-sku {
  font-family: monospace;
  color: #6b7280;
  font-size: 0.85rem;
}

.disposal-quantity {
  font-weight: 600;
  color: #dc2626;
}

.disposal-notes {
  max-width: 200px;
}

.notes-text {
  cursor: help;
}

.no-notes,
.no-image {
  color: #9ca3af;
  font-style: italic;
  font-size: 0.85rem;
}

.disposal-thumb {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid #e5e7eb;
}

.disposal-thumb:hover {
  border-color: #3b82f6;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.8rem;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fbbf24;
}

.status-reviewed {
  background: #dbeafe;
  color: #1e40af;
  border: 1px solid #3b82f6;
}

.status-resolved {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #10b981;
}

.status-unknown {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.disposal-pagination {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 8px;
  margin-top: 16px;
  padding: 12px;
  background: white;
  border-radius: 8px;
}

.page-btn {
  padding: 6px 12px;
  border: 1px solid #e5e7eb;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  margin: 0 8px;
  color: #6b7280;
  font-size: 0.9rem;
}

.per-page-select {
  padding: 6px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.85rem;
  background: white;
}

/* Image Modal */
.image-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 20px;
}

.image-modal {
  position: relative;
  max-width: 90%;
  max-height: 90%;
}

.image-modal-close {
  position: absolute;
  top: -40px;
  right: 0;
  background: white;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  font-size: 1.5rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-modal-img {
  max-width: 100%;
  max-height: 85vh;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

/* Loading and Error States */
.loading-container,
.error-container,
.empty-container {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}

.loading-spinner {
  border: 4px solid #f3f4f6;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.btn-retry {
  padding: 8px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 12px;
}

.btn-retry:hover {
  background: #2563eb;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .disposal-controls {
    flex-direction: column;
    align-items: stretch;
  }

  .disposal-filters {
    flex-direction: column;
  }

  .disposal-search {
    min-width: 100%;
  }

  .disposal-table {
    font-size: 0.8rem;
  }

  .disposal-table thead th,
  .disposal-table tbody td {
    padding: 8px;
  }
}
</style>

<style>
/* Global styles for buttons */
.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s ease;
}

.btn-light {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-light:hover {
  background: #e5e7eb;
}

.btn-primary {
  background: linear-gradient(180deg, #ff8a4b, #ff7043);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(180deg, #ff7043, #ff6b3d);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Header profile styles */
.header-profile-wrapper {
  position: relative;
  display: inline-block;
}

.header-profile-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  background: white;
  border-radius: 8px;
  cursor: pointer;
}

.header-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
}

.header-avatar-img {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
}

.header-avatar-initials {
  font-weight: 700;
  color: #374151;
  font-size: 0.9rem;
}

.header-name {
  font-size: 0.8rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 320px;
}

.profile-dropdown {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
  padding: 6px;
  z-index: 1200;
  min-width: 140px;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 8px 12px;
  text-align: left;
  border: none;
  background: transparent;
  cursor: pointer;
  border-radius: 6px;
}

.dropdown-item:hover {
  background: #f5f5f5;
}
</style>