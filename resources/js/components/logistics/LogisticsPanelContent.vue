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
      </div>

      <p class="muted">Supplier and branch will be recorded from your account automatically.</p>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="submitting">Add Product</button>
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
const props = withDefaults(defineProps<{
  deliveries?: Array<any>
}>(), {
  deliveries: () => []
})
const emit = defineEmits(['product-added'])

const name = ref('')
const price = ref(0)
const submitting = ref(false)
async function onAddProduct() {
  if (!name.value || price.value === null) return
  submitting.value = true
  try {
    const payload = {
      name: name.value,
      price: price.value,
      stock: 0
    }

    const res = await axios.post('/api/staff/inventory/products', payload, { withCredentials: true })
    alert('Product added successfully')
    // Emit event so parent can refresh product list or optimistically add
    try {
      if (res && res.data && res.data.product) {
        emit('product-added', res.data.product)
      }
    } catch (e) {}

    name.value = ''
    price.value = 0
  } catch (err) {
    console.warn('Failed to add product', err)
    alert('Failed to add product')
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
/* Form layout for supplier add product */
.add-product-form { display:block; margin-bottom:1rem }
.form-grid { display:grid; grid-template-columns: 1fr 220px; gap:0.75rem; align-items:end }
.form-row { display:flex; flex-direction:column; gap:6px }
.form-row label { font-weight:600; color:#374151; font-size:0.9rem }
.form-row input { padding:8px 10px; border-radius:8px; border:1px solid #e6e9ee; background:#fff }
.form-actions { margin-top:0.5rem }
.form-actions .btn-primary { padding:8px 12px; border-radius:8px; background:linear-gradient(135deg,#2b8aef,#1a6ed8); color:#fff; border:none }
.muted { color:#6b7280; font-size:0.9rem; margin-top:0.5rem }

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
