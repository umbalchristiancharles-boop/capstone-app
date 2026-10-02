<template>
  <main class="customer-display">
    <header class="customer-display__header">
      <div>
        <p class="customer-display__eyebrow">{{ snapshot?.branch_name || 'Cashier' }}</p>
        <h1>Your Order</h1>
      </div>
      <span class="customer-display__status" :class="{ 'customer-display__status--waiting': !snapshot?.active }">
        <span></span>{{ snapshot?.active ? 'Live order' : 'Waiting for cashier' }}
      </span>
    </header>

    <section class="customer-display__layout" aria-live="polite">
      <div class="customer-display__items">
        <div class="customer-display__items-heading">
          <h2>Items</h2>
          <span>{{ snapshot?.total_items || 0 }}</span>
        </div>

        <p v-if="!snapshot?.active" class="customer-display__empty">The cashier display is not active.</p>
        <p v-else-if="!snapshot.items?.length" class="customer-display__empty">Your items will appear here as they are scanned.</p>
        <div v-else class="customer-display__list">
          <article v-for="(item, index) in snapshot.items" :key="`${item.name}-${index}`" class="customer-display__item">
            <div>
              <h3>{{ item.name }}</h3>
              <p>{{ item.quantity }} × {{ formatPrice(item.unit_price) }}</p>
            </div>
            <strong>{{ formatPrice(item.subtotal) }}</strong>
          </article>
        </div>
      </div>

      <aside class="customer-display__summary" aria-label="Order total">
        <h2>Summary</h2>
        <div class="customer-display__row"><span>Subtotal</span><span>{{ formatPrice(snapshot?.subtotal) }}</span></div>
        <div class="customer-display__row"><span>{{ snapshot?.discount_label || 'Discount' }}</span><span>-{{ formatPrice(snapshot?.discount) }}</span></div>
        <div class="customer-display__row"><span>Taxable</span><span>{{ formatPrice(snapshot?.taxable) }}</span></div>
        <div class="customer-display__row"><span>VAT ({{ snapshot?.vat_percent ?? 12 }}%)</span><span>{{ formatPrice(snapshot?.vat) }}</span></div>
        <div class="customer-display__total">
          <span>Total Due</span>
          <strong>{{ formatPrice(snapshot?.grand_total) }}</strong>
        </div>
        <p class="customer-display__note">Prices update as your order changes.</p>
      </aside>
    </section>
  </main>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

const sessionId = new URLSearchParams(window.location.search).get('session')
const storageKey = sessionId ? `staff-cashier-display-v1:${sessionId}` : null

function readSnapshot() {
  if (!storageKey) return null
  try {
    return JSON.parse(localStorage.getItem(storageKey) || 'null')
  } catch (error) {
    return null
  }
}

const snapshot = ref(readSnapshot())

function handleStorage(event) {
  if (event.key === storageKey) snapshot.value = readSnapshot()
}

function formatPrice(value) {
  const amount = Number(value) || 0
  return `₱${amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

onMounted(() => window.addEventListener('storage', handleStorage))
onBeforeUnmount(() => window.removeEventListener('storage', handleStorage))
</script>

<style scoped>
.customer-display {
  min-height: 100vh;
  padding: clamp(24px, 5vw, 64px);
  background: #f2f4ef;
  color: #20342f;
  font-family: 'Aptos', 'Segoe UI', sans-serif;
}

.customer-display__header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto 32px;
  padding-bottom: 22px;
  border-bottom: 1px solid #d8ded7;
}

.customer-display__eyebrow {
  margin: 0 0 8px;
  color: #61736b;
  font-size: 0.9rem;
  font-weight: 700;
}

.customer-display h1 {
  margin: 0;
  font-size: 2.5rem;
  line-height: 1;
}

.customer-display__status {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #237149;
  font-size: 0.9rem;
  font-weight: 700;
}

.customer-display__status span {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background: #31a66a;
}

.customer-display__status--waiting { color: #717d76; }
.customer-display__status--waiting span { background: #9ba49e; }

.customer-display__layout {
  display: grid;
  grid-template-columns: minmax(0, 1.5fr) minmax(280px, 0.8fr);
  gap: clamp(28px, 5vw, 72px);
  max-width: 1200px;
  margin: 0 auto;
}

.customer-display__items-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 14px;
  border-bottom: 2px solid #20342f;
}

.customer-display h2 {
  margin: 0;
  font-size: 1.2rem;
}

.customer-display__items-heading > span {
  color: #61736b;
  font-weight: 700;
}

.customer-display__empty {
  padding: 56px 12px;
  color: #78857e;
  text-align: center;
}

.customer-display__item {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  padding: 20px 0;
  border-bottom: 1px solid #d8ded7;
}

.customer-display__item h3,
.customer-display__item p { margin: 0; }
.customer-display__item h3 { font-size: 1rem; }
.customer-display__item p { margin-top: 6px; color: #61736b; }
.customer-display__item > strong { white-space: nowrap; }

.customer-display__summary {
  align-self: start;
  padding: 22px;
  background: #fff;
  border-top: 4px solid #ed7042;
}

.customer-display__summary h2 { margin-bottom: 16px; }

.customer-display__row {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 10px 0;
  color: #56675f;
}

.customer-display__total {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 10px;
  padding-top: 18px;
  border-top: 1px solid #d8ded7;
}

.customer-display__total strong {
  color: #b84e2b;
  font-size: 2.5rem;
}

.customer-display__note {
  margin: 20px 0 0;
  color: #78857e;
  font-size: 0.85rem;
}

@media (max-width: 700px) {
  .customer-display__header { align-items: flex-start; flex-direction: column; }
  .customer-display__layout { grid-template-columns: 1fr; }
  .customer-display h1 { font-size: 2rem; }
  .customer-display__total strong { font-size: 2.25rem; }
}
</style>