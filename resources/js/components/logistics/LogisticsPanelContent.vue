<template>
  <section class="logistics-panel">
    <h2 class="section-title">Add Product (from Supplier)</h2>

    <form @submit.prevent="onAddProduct" class="add-product-form">
      <div class="form-grid">
        <div class="form-row">
          <label>Product name</label>
          <input v-model="name" type="text" required />
        </div>

        <div class="form-row">
          <label>Price</label>
          <input v-model.number="price" type="number" min="0" step="0.01" required />
        </div>

        <div class="form-row">
          <label>Category</label>
          <select v-model="category" required>
            <option value="">Select a category</option>
            <option value="Beverage">Beverage</option>
            <option value="Meat">Meat</option>
            <option value="Vegetable">Vegetable</option>
            <option value="Grain">Grain</option>
            <option value="Condiment">Condiment</option>
            <option value="Dairy">Dairy</option>
            <option value="Egg">Egg</option>
            <option value="Spice">Spice</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="form-row">
          <label>Expiration Date</label>
          <input v-model="expiresAt" type="datetime-local" required />
        </div>
      </div>

      <div class="form-row full-width">
        <label>Pricing Type</label>
        <div class="pricing-type-options">
          <div class="option-group">
            <input 
              type="radio" 
              id="type-individual" 
              value="individual" 
              v-model="perPackOrIndividual"
            />
            <label for="type-individual" class="option-label">
              <span class="option-badge type-individual">Individual</span>
              <span class="option-desc">Sold by individual units</span>
            </label>
          </div>
          <div class="option-group">
            <input 
              type="radio" 
              id="type-per_pack" 
              value="per_pack" 
              v-model="perPackOrIndividual"
            />
            <label for="type-per_pack" class="option-label">
              <span class="option-badge type-per_pack">Per Pack</span>
              <span class="option-desc">Sold in packs only</span>
            </label>
          </div>
          <div class="option-group">
            <input 
              type="radio" 
              id="type-both" 
              value="both" 
              v-model="perPackOrIndividual"
            />
            <label for="type-both" class="option-label">
              <span class="option-badge type-both">Both Options</span>
              <span class="option-desc">Can be sold both ways</span>
            </label>
          </div>
        </div>
      </div>

      <p class="muted">Supplier and branch will be recorded from your account automatically.</p>

      <div v-if="submitError" class="error-msg">{{ submitError }}</div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="submitting">{{ submitting ? 'Adding...' : 'Add Product' }}</button>
      </div>
    </form>

    <h2 class="section-title">Active Deliveries</h2>
    <div v-if="!deliveries.length" class="empty-state">No active deliveries.</div>
    <div v-else class="delivery-grid">
      <div v-for="d in deliveries" :key="d.id" class="delivery-card">
        <div class="delivery-title">{{ d.title || d.reference || ('Delivery #' + d.id) }}</div>
        <div class="delivery-meta">
          <span class="delivery-status">{{ d.status || 'Pending' }}</span>
          <span class="delivery-date">{{ d.updated_at || d.created_at || '' }}</span>
        </div>
        <div class="delivery-body">{{ d.note || d.description || '' }}</div>
      </div>
    </div>

    <!-- Suppliers list removed from supplier panel -->
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'

// Toast notification helper (fallback if not imported)
const showToast = (msg, type = 'info') => {
  // Try to use toast if available, otherwise console
  console.log(`[${type.toUpperCase()}] ${msg}`)
}

const props = withDefaults(defineProps<{
  deliveries?: Array<any>
}>(), {
  deliveries: () => []
})
const emit = defineEmits(['product-added'])

const name = ref('')
const price = ref(0)
const category = ref('')
const expiresAt = ref('')
const perPackOrIndividual = ref('')
const submitting = ref(false)
const submitError = ref('')

async function onAddProduct() {
  // Validate before setting submitting
  if (!name.value || price.value === null || !category.value || !expiresAt.value || !perPackOrIndividual.value) {
    submitError.value = 'All fields are required'
    return
  }
  
  submitting.value = true
  submitError.value = ''
  
  try {
    const payload = {
      name: name.value,
      price: price.value,
      category: category.value,
      per_pack_or_individual: perPackOrIndividual.value,
      expires_at: expiresAt.value,
      stock: 0
    }

    console.log('Submitting payload:', payload)
    const res = await axios.post('/api/staff/inventory/products', payload, { withCredentials: true })
    
    if (res && res.data) {
      showToast('Product added successfully', 'success')
      
      // Emit event so parent can refresh product list
      try {
        if (res.data.product) {
          emit('product-added', res.data.product)
        }
      } catch (e) {
        console.warn('Error emitting product-added event', e)
      }

      // Reset form
      name.value = ''
      price.value = 0
      category.value = ''
      perPackOrIndividual.value = ''
      expiresAt.value = ''
    } else {
      submitError.value = 'Failed to add product: Invalid response'
      showToast(submitError.value, 'error')
    }
  } catch (err) {
    console.error('Failed to add product', err)
    
    // Extract error message from various response formats
    let errorMsg = 'Failed to add product'
    
    if (err.response?.data?.errors) {
      // Laravel validation errors
      const errors = err.response.data.errors
      errorMsg = Object.values(errors).flat().join(', ')
    } else if (err.response?.data?.message) {
      errorMsg = err.response.data.message
    } else if (err.response?.data?.error) {
      errorMsg = err.response.data.error
    } else if (err.message) {
      errorMsg = err.message
    }
    
    submitError.value = errorMsg
    showToast(errorMsg, 'error')
    console.error('Error details:', { 
      status: err.response?.status, 
      data: err.response?.data,
      message: errorMsg 
    })
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
/* Form layout for supplier add product */
.add-product-form { display:block; margin-bottom:1rem }
.form-grid { display:grid; grid-template-columns: 1fr 1fr; gap:0.75rem; align-items:end }
.form-row { display:flex; flex-direction:column; gap:6px }
.form-row.full-width { grid-column:1/-1 }
.form-row label { font-weight:600; color:#374151; font-size:0.9rem }
.form-row input, .form-row select { padding:8px 10px; border-radius:8px; border:1px solid #e6e9ee; background:#fff; font-size:0.9rem }
.form-actions { margin-top:0.5rem }
.form-actions .btn-primary { padding:8px 12px; border-radius:8px; background:linear-gradient(135deg,#2b8aef,#1a6ed8); color:#fff; border:none; cursor:pointer }
.form-actions .btn-primary:disabled { opacity:0.6; cursor:not-allowed }
.error-msg { background:#fee2e2; color:#dc2626; padding:10px 12px; border-radius:8px; font-size:0.9rem; margin-top:0.5rem; border:1px solid #fecaca }
.muted { color:#6b7280; font-size:0.9rem; margin-top:0.5rem }

/* Pricing type options */
.pricing-type-options { display:flex; flex-direction:column; gap:10px; margin-top:6px; padding:8px; background:#f9fafb; border-radius:8px; border:1px solid #e5e7eb }
.option-group { display:flex; align-items:flex-start; gap:12px; cursor:pointer; padding:8px; border-radius:6px; transition:background 0.2s }
.option-group:hover { background:#f3f4f6 }
.option-group input[type="radio"] { margin-top:5px; cursor:pointer; accent-color:#7c3aed }
.option-label { display:flex; flex-direction:column; gap:4px; cursor:pointer; flex:1 }
.option-badge { display:inline-block; padding:4px 10px; border-radius:6px; font-size:0.85rem; font-weight:600; width:fit-content }
.option-badge.type-individual { background:#dbeafe; color:#1e40af }
.option-badge.type-per_pack { background:#d1fae5; color:#065f46 }
.option-badge.type-both { background:#fef3c7; color:#92400e }
.option-desc { font-size:0.8rem; color:#6b7280 }

/* Deliveries */
.delivery-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(240px,1fr)); gap:0.75rem; margin-top:0.75rem }
.delivery-card { background:#fff; border-radius:10px; padding:0.75rem; box-shadow:0 8px 24px rgba(15,23,42,0.06); border:1px solid #eef2f6 }
.delivery-title { font-weight:700; color:#0f172a }
.delivery-meta { display:flex; gap:0.5rem; align-items:center; font-size:0.85rem; color:#4b5563; margin-top:4px }
.delivery-status { background:#f1f5f9; padding:4px 8px; border-radius:8px }
.delivery-date { color:#9ca3af }
.delivery-body { margin-top:8px; color:#374151; font-size:0.95rem }

.empty-state { color:#6b7280 }

.section-title { margin-top:0.6rem; margin-bottom:0.4rem }
</style>
