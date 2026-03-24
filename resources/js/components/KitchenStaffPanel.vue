<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Kitchen Staff Panel'"
    :panelDescription="'Manage kitchen orders and preparation.'"
    :enableProfileUpdate="true"
    :canEditProfile="false"
    @profile-updated="onProfileUpdated"
    @logout="confirmLogout"
  >
    <template #main>
      <section class="panel-block">
        <div class="panel-header"><h2>Kitchen Tasks</h2></div>
        <div class="panel-body">
          <div class="kitchen-grid">
            <div class="kitchen-column">
              <h3>Create Dish</h3>
              <form @submit.prevent="submitDish">
                <div class="form-row">
                  <label>Dish name</label>
                  <input v-model="form.name" type="text" required />
                </div>

                <div class="form-row">
                  <label>Ingredients</label>
                  <div class="ingredients">
                    <div v-for="(ing, idx) in form.ingredients" :key="idx" class="ingredient-row">
                      <input v-model="ing.name" placeholder="Ingredient name" required />
                      <input v-model="ing.per_serving" placeholder="Per serving (optional)" class="small" />
                      <select v-model="ing.unit">
                        <option value="">unspecified</option>
                        <option value="pcs">pcs</option>
                        <option value="g">g</option>
                        <option value="kg">kg</option>
                        <option value="ml">ml</option>
                        <option value="l">l</option>
                        <option value="pack">pack</option>
                      </select>
                      <button type="button" @click="removeIngredient(idx)">Remove</button>
                    </div>
                    <button type="button" @click="addIngredient">Add ingredient</button>
                  </div>
                </div>

                <div class="form-actions">
                  <button type="submit">Create Dish</button>
                </div>
              </form>

              <div v-if="message" class="message">{{ message }}</div>
            </div>

            <div class="kitchen-column">
              <h3>My Dishes</h3>
              <div v-if="loading">Loading...</div>
              <div v-else>
                <div v-if="dishes.length === 0">No dishes yet.</div>
                <ul class="dish-list">
                  <li v-for="d in dishes" :key="d.id">
                    <strong>{{ d.name }}</strong> <small>({{ d.status }})</small>
                    <ul>
                      <li v-for="ing in d.ingredients" :key="ing.id">
                        {{ ing.name }} <em v-if="ing.unit">- per serving: {{ ing.per_serving || 0 }} {{ ing.unit }}</em>
                        <div class="low-stock">
                          <button @click.prevent="showLowStock(ing)">Mark Low Stock</button>
                          <div v-if="lowStockVisible[ing.id]" class="low-stock-form">
                            <select v-model="lowStockForm[ing.id].unit">
                              <option value="pcs">pcs</option>
                              <option value="g">g</option>
                              <option value="kg">kg</option>
                              <option value="ml">ml</option>
                              <option value="l">l</option>
                              <option value="pack">pack</option>
                            </select>
                            <button @click.prevent="submitLowStock(ing)">Submit</button>
                            <button @click.prevent="hideLowStock(ing)">Cancel</button>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'

const userProfile = ref({})
const dishes = ref([])
const loading = ref(false)
const message = ref('')

const form = ref({
  name: '',
  ingredients: [ { name: '', unit: 'pcs', per_serving: 0 } ]
})

function addIngredient() {
  form.value.ingredients.push({ name: '', unit: 'pcs', per_serving: 0 })
}

function removeIngredient(idx) {
  form.value.ingredients.splice(idx, 1)
}

async function loadDishes() {
  loading.value = true
  try {
    const res = await axios.get('/api/staff/kitchen/dishes')
    dishes.value = res.data || []
  } catch (e) {
    console.error('Failed to load dishes', e)
  } finally {
    loading.value = false
  }
}

async function submitDish() {
  message.value = ''
  try {
      const payload = {
        name: form.value.name,
        ingredients: form.value.ingredients.map(i => ({ name: i.name, unit: i.unit, per_serving: i.per_serving }))
      }
    const res = await axios.post('/api/staff/kitchen/dishes', payload)
    message.value = 'Dish created'
    form.value.name = ''
    form.value.ingredients = [ { name: '', quantity: 1, unit: 'pcs', per_serving: 0 } ]
    await loadDishes()
  } catch (e) {
    console.error('Failed to create dish', e)
    message.value = e?.response?.data?.error || 'Failed to create dish'
  }
}

const lowStockVisible = ref({})
const lowStockForm = ref({})

function showLowStock(ing) {
  lowStockVisible.value[ing.id] = true
  if (!lowStockForm.value[ing.id]) {
    lowStockForm.value[ing.id] = { unit: ing.unit || 'pcs' }
  }
}

function hideLowStock(ing) {
  lowStockVisible.value[ing.id] = false
}

async function submitLowStock(ing) {
  try {
    const payload = { unit: lowStockForm.value[ing.id].unit }
    const res = await axios.post(`/api/staff/kitchen/ingredients/${ing.id}/low-stock`, payload)
    alert(res.data.message || 'Requested')
    lowStockVisible.value[ing.id] = false
    await loadDishes()
  } catch (e) {
    console.error('Failed low-stock', e)
    alert(e?.response?.data?.message || 'Failed to submit')
  }
}

function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/staff/profile', { withCredentials: true })
    if (res.data && res.data.user) userProfile.value = res.data.user
  } catch (e) {
    console.error('Failed to load staff profile for kitchen panel', e)
  }
  loadDishes()
})

// Logout state and handlers (consistent with other staff panels)
const isLoggingOut = ref(false)

function confirmLogout() {
  if (isLoggingOut.value) return
  if (!confirm('Are you sure you want to logout?')) return
  performLogout()
}

async function performLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  setTimeout(() => {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    try { window.location.replace('/staff-landing') } catch (e) {}
  }, 500)
}
</script>

<style scoped>
.panel-block { padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff }
.panel-header h2 { margin: 0 0 8px 0 }
.panel-body { color: #374151 }
</style>
