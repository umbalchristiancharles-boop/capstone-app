<template>
  <div class="inventory-page">
    <!-- Header -->
    <div class="inventory-header">
      <h1>Inventory Management</h1>
      <div class="header-actions">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search items..." 
          class="search-input"
        >
        <button @click="refreshInventory" class="btn-primary">Refresh</button>
        <button @click="showAddItemModal = true" class="btn-success">+ Add Item</button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-state">
      <p>Loading inventory...</p>
    </div>

    <!-- Error State -->
    <div v-if="errorMessage" class="alert alert-danger">
      {{ errorMessage }}
    </div>

    <!-- Summary Cards -->
    <div v-if="!isLoading && summary" class="summary-cards">
      <div class="card">
        <h3>Total Items</h3>
        <p class="number">{{ summary.total_items }}</p>
      </div>
      <div class="card">
        <h3>Low Stock</h3>
        <p class="number warning">{{ summary.low_stock }}</p>
      </div>
      <div class="card">
        <h3>Critical Stock</h3>
        <p class="number danger">{{ summary.critical_stock }}</p>
      </div>
    </div>

    <!-- Inventory Table -->
    <div v-if="!isLoading && filteredInventory.length > 0" class="inventory-table-wrapper">
      <table class="inventory-table">
        <thead>
          <tr>
            <th>Item Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Min Stock</th>
            <th>Status</th>
            <th>Last Updated</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in filteredInventory" :key="item.id" :class="'status-' + item.status">
            <td><strong>{{ item.item_name }}</strong></td>
            <td>{{ item.category }}</td>
            <td class="qty">{{ item.quantity }} {{ item.unit }}</td>
            <td>{{ item.min_stock }} {{ item.unit }}</td>
            <td>
              <span :class="'badge badge-' + item.status">
                {{ statusLabel(item.status) }}
              </span>
            </td>
            <td>{{ item.last_updated }}</td>
            <td class="actions">
              <button 
                @click="editItem(item)"
                class="btn-sm btn-info"
                title="Update Stock"
              >
                Update
              </button>
              <button 
                @click="recordDelivery(item)"
                class="btn-sm btn-warning"
                title="Record Delivery"
              >
                Delivery
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="!isLoading && filteredInventory.length === 0" class="empty-state">
      <p>No inventory items found</p>
    </div>

    <!-- Update Stock Modal -->
    <transition name="fade">
      <div v-if="showUpdateModal" class="modal-backdrop" @click="showUpdateModal = false">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h2>Update Stock - {{ editingItem.item_name }}</h2>
            <button @click="showUpdateModal = false" class="close-btn">×</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Current Quantity:</label>
              <input 
                type="number" 
                :value="editingItem.quantity" 
                disabled 
                class="form-input"
              >
            </div>
            <div class="form-group">
              <label>Action:</label>
              <select v-model="updateAction" class="form-input">
                <option value="add">Add Stock</option>
                <option value="set">Set Quantity</option>
                <option value="subtract">Remove Stock</option>
              </select>
            </div>
            <div class="form-group">
              <label>Quantity:</label>
              <input 
                v-model.number="updateQuantity" 
                type="number" 
                min="0"
                class="form-input"
                placeholder="Enter quantity"
              >
            </div>
            <div class="form-group">
              <label>Note (Optional):</label>
              <textarea 
                v-model="updateNote"
                class="form-input"
                rows="3"
                placeholder="Add a note about this update..."
              ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="showUpdateModal = false" class="btn-secondary">Cancel</button>
            <button @click="submitStockUpdate" class="btn-primary">Update Stock</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Record Delivery Modal -->
    <transition name="fade">
      <div v-if="showDeliveryModal" class="modal-backdrop" @click="showDeliveryModal = false">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h2>Record Delivery - {{ editingItem.item_name }}</h2>
            <button @click="showDeliveryModal = false" class="close-btn">×</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Quantity Received:</label>
              <input 
                v-model.number="deliveryQuantity"
                type="number"
                min="0"
                class="form-input"
                placeholder="Enter quantity received"
              >
            </div>
            <div class="form-group">
              <label>Supplier:</label>
              <input 
                v-model="deliverySupplier"
                type="text"
                class="form-input"
                placeholder="Supplier name"
              >
            </div>
            <div class="form-group">
              <label>Note:</label>
              <textarea 
                v-model="deliveryNote"
                class="form-input"
                rows="3"
                placeholder="Delivery notes..."
              ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="showDeliveryModal = false" class="btn-secondary">Cancel</button>
            <button @click="submitDelivery" class="btn-success">Record Delivery</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import '../css/adminpanel.css'

// State
const isLoading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')

// Inventory Data
const inventory = ref([])
const summary = ref(null)

// Update Modal
const showUpdateModal = ref(false)
const editingItem = ref(null)
const updateAction = ref('add')
const updateQuantity = ref(0)
const updateNote = ref('')

// Delivery Modal
const showDeliveryModal = ref(false)
const deliveryQuantity = ref(0)
const deliverySupplier = ref('')
const deliveryNote = ref('')

// Add Item Modal
const showAddItemModal = ref(false)

// Computed
const filteredInventory = computed(() => {
  if (!searchQuery.value.trim()) return inventory.value

  return inventory.value.filter(item =>
    item.item_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    item.category.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Methods
async function loadInventory() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const res = await axios.get('/api/manager/inventory', {
      withCredentials: true
    })

    if (res.data.success) {
      inventory.value = res.data.inventory
      summary.value = res.data.summary
    } else {
      errorMessage.value = res.data.message || 'Failed to load inventory'
    }
  } catch (error) {
    console.error('Inventory load error:', error)
    errorMessage.value = 'Error loading inventory. Please try again.'
  } finally {
    isLoading.value = false
  }
}

function refreshInventory() {
  loadInventory()
}

function editItem(item) {
  editingItem.value = { ...item }
  updateAction.value = 'add'
  updateQuantity.value = 0
  updateNote.value = ''
  showUpdateModal.value = true
}

async function submitStockUpdate() {
  if (!editingItem.value || updateQuantity.value === 0) {
    alert('Please enter a quantity')
    return
  }

  try {
    const res = await axios.put(`/api/manager/inventory/${editingItem.value.id}`, {
      quantity: updateQuantity.value,
      action: updateAction.value,
      note: updateNote.value
    }, {
      withCredentials: true
    })

    if (res.data.success) {
      showUpdateModal.value = false
      loadInventory()
      alert('Stock updated successfully!')
    }
  } catch (error) {
    console.error('Update error:', error)
    alert('Failed to update stock')
  }
}

function recordDelivery(item) {
  editingItem.value = { ...item }
  deliveryQuantity.value = 0
  deliverySupplier.value = ''
  deliveryNote.value = ''
  showDeliveryModal.value = true
}

async function submitDelivery() {
  if (!editingItem.value || deliveryQuantity.value === 0) {
    alert('Please enter a quantity')
    return
  }

  try {
    const res = await axios.post('/api/manager/inventory/delivery', {
      item_id: editingItem.value.id,
      quantity: deliveryQuantity.value,
      supplier: deliverySupplier.value,
      note: deliveryNote.value
    }, {
      withCredentials: true
    })

    if (res.data.success) {
      showDeliveryModal.value = false
      loadInventory()
      alert('Delivery recorded successfully!')
    }
  } catch (error) {
    console.error('Delivery error:', error)
    alert('Failed to record delivery')
  }
}

function statusLabel(status) {
  const labels = {
    'ok': 'OK',
    'low': 'Low Stock',
    'critical': 'Critical'
  }
  return labels[status] || status
}

onMounted(() => {
  loadInventory()
})
</script>

<style scoped>
.inventory-page {
  padding: 2rem;
  background: #f5f5f5;
  min-height: 100vh;
}

.inventory-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.inventory-header h1 {
  margin: 0;
  color: #333;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.search-input {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  width: 250px;
}

.search-input:focus {
  outline: none;
  border-color: #FF9A4A;
  box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.1);
}

.btn-primary, .btn-success, .btn-secondary, .btn-info, .btn-warning {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #FF9A4A;
  color: white;
}

.btn-primary:hover {
  background: #FF6A3D;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-success:hover {
  background: #218838;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #5a6268;
}

.btn-info {
  background: #17a2b8;
  color: white;
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
}

.btn-info:hover {
  background: #138496;
}

.btn-warning {
  background: #ffc107;
  color: #333;
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
}

.btn-warning:hover {
  background: #e0a800;
}

.btn-sm {
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  text-align: center;
}

.card h3 {
  margin: 0 0 0.5rem 0;
  color: #666;
  font-size: 0.9rem;
  text-transform: uppercase;
}

.card .number {
  margin: 0;
  font-size: 2rem;
  font-weight: bold;
  color: #333;
}

.card .number.warning {
  color: #ff9a4a;
}

.card .number.danger {
  color: #dc3545;
}

.inventory-table-wrapper {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
}

.inventory-table {
  width: 100%;
  border-collapse: collapse;
}

.inventory-table thead {
  background: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
}

.inventory-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #333;
  font-size: 0.9rem;
}

.inventory-table td {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.inventory-table tbody tr:hover {
  background: #f8f9fa;
}

.inventory-table tbody tr.status-critical {
  background: rgba(220, 53, 69, 0.05);
}

.inventory-table tbody tr.status-low {
  background: rgba(255, 193, 7, 0.05);
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.badge-ok {
  background: #d4edda;
  color: #155724;
}

.badge-low {
  background: #fff3cd;
  color: #856404;
}

.badge-critical {
  background: #f8d7da;
  color: #721c24;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.empty-state, .loading-state {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 8px;
  color: #666;
}

.alert {
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.alert-danger {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

/* Modal Styles */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #dee2e6;
}

.modal-header h2 {
  margin: 0;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #999;
}

.close-btn:hover {
  color: #333;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #333;
  font-weight: 500;
  font-size: 0.9rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  font-family: inherit;
}

.form-input:focus {
  outline: none;
  border-color: #FF9A4A;
  box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.1);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .inventory-header {
    flex-direction: column;
    gap: 1rem;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }

  .search-input {
    width: 100%;
  }

  .inventory-table {
    font-size: 0.85rem;
  }

  .inventory-table th,
  .inventory-table td {
    padding: 0.75rem 0.5rem;
  }
}
</style>
