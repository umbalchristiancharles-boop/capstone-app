<template>
  <section>
    <h2>Add Product (from Supplier)</h2>
    <form @submit.prevent="onAddProduct" class="add-product-form">
      <div class="form-row">
        <label>Product name</label>
        <input v-model="name" type="text" required />
      </div>

      <div class="form-row">
        <label>Price</label>
        <input v-model.number="price" type="number" min="0" step="0.01" required />
      </div>

      <p class="muted">Supplier and branch will be recorded from your account automatically.</p>

      <div class="form-actions">
        <button type="submit" :disabled="submitting">Add Product</button>
      </div>
    </form>

    <h2>Active Deliveries</h2>
    <div v-if="!deliveries.length">No active deliveries.</div>
    <ul v-else>
      <li v-for="d in deliveries" :key="d.id">{{ d.title }} - {{ d.status }}</li>
    </ul>

    <h2>Suppliers</h2>
    <div v-if="!suppliers.length">No suppliers found.</div>
    <ul v-else>
      <li v-for="s in suppliers" :key="s.id">{{ s.name }}</li>
    </ul>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({ deliveries: Array, suppliers: Array })

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
