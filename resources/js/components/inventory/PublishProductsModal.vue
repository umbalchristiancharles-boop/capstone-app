<template>
  <div class="modal-backdrop">
    <div class="modal-panel">
      <header class="modal-header">
        <h3>Publish Products</h3>
        <button class="close-btn" @click="$emit('close')">✕</button>
      </header>

      <div class="modal-body">
        <div v-if="loading">Loading…</div>
        <div v-else>
          <div v-if="unpublished.length === 0">No unpublished products found.</div>
          <div v-else>
            <div class="controls">
              <label><input type="checkbox" v-model="selectAll" /> Select all</label>
              <button class="btn-primary" :disabled="selectedIds.length===0 || publishing" @click="publishSelected">Publish Selected</button>
            </div>

            <ul class="publish-list">
              <li v-for="p in unpublished" :key="p.id">
                <label>
                  <input type="checkbox" :value="p.id" v-model="selectedIds" />
                  <strong>{{ p.name }}</strong>
                  <small class="muted">(Stock: {{ p.stock ?? 0 }}, Price: {{ formatCurrency(p.price) }})</small>
                </label>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <footer class="modal-footer">
        <button class="btn" @click="$emit('close')">Close</button>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({ branchId: { type: [Number, String], default: null } })
const emit = defineEmits(['close', 'published'])

const loading = ref(true)
const publishing = ref(false)
const products = ref([])
const selectedIds = ref([])
const selectAll = ref(false)

watch(selectAll, (v) => {
  if (v) selectedIds.value = products.value.map(p => p.id)
  else selectedIds.value = []
})

const unpublished = computed(() => products.value.filter(p => !p.is_published))

function formatCurrency(n) {
  const num = parseFloat(n || 0)
  return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2 })
}

async function fetchProducts() {
  loading.value = true
  try {
    let url = '/api/staff/inventory/products?include_unpublished=1'
    if (props.branchId) url += `&branch_id=${encodeURIComponent(props.branchId)}`
    const res = await axios.get(url, { withCredentials: true })
    products.value = Array.isArray(res.data) ? res.data : (res.data?.data || [])
  } catch (e) {
    products.value = []
  } finally {
    loading.value = false
  }
}

async function publishSelected() {
  if (selectedIds.value.length === 0) return
  publishing.value = true
  try {
    try { await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) } catch (e) {}
    for (const id of selectedIds.value) {
      try {
        await axios.put(`/api/staff/inventory/products/${id}`, { is_published: 1 }, { withCredentials: true })
      } catch (err) {
        // continue on error for other items
        console.error('publish item failed', id, err)
      }
    }
    emit('published')
  } finally {
    publishing.value = false
    await fetchProducts()
  }
}

onMounted(() => { fetchProducts() })
</script>

<style scoped>
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:1200}
.modal-panel{background:#fff;border-radius:8px;width:720px;max-width:95%;box-shadow:0 8px 24px rgba(0,0,0,0.2);overflow:hidden}
.modal-header{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-bottom:1px solid #eee}
.modal-body{padding:12px 16px;max-height:520px;overflow:auto}
.modal-footer{padding:12px 16px;border-top:1px solid #eee;text-align:right}
.close-btn{background:transparent;border:0;font-size:16px}
.publish-list{list-style:none;padding:0;margin:8px 0}
.publish-list li{padding:8px 6px;border-bottom:1px solid #f4f4f4}
.controls{display:flex;gap:12px;align-items:center;margin-bottom:8px}
.muted{color:#666;margin-left:8px;font-size:12px}
.btn{padding:8px 12px;border-radius:6px;border:1px solid #ccc;background:#fff}
.btn-primary{padding:8px 12px;border-radius:6px;background:#ff8a4a;color:#fff;border:0}
</style>
