<template>
  <main class="customer-display">
    <header class="customer-display__header">
      <div class="customer-display__brand">
        <img class="customer-display__logo" :src="logoSrc" alt="Chikin Tayo" />
        <div class="customer-display__heading">
          <p class="customer-display__eyebrow">Customer display <span v-if="snapshot?.branch_name">· {{ snapshot.branch_name }}</span></p>
          <h1>Your Order</h1>
        </div>
      </div>
      <span class="customer-display__status" :class="{ 'customer-display__status--waiting': !snapshot?.active }">
        <span></span>{{ snapshot?.active ? 'Live order' : 'Waiting for cashier' }}
      </span>
    </header>

    <section class="customer-display__layout" aria-live="polite">
      <section class="customer-display__items customer-display__panel" aria-label="Order items">
        <div class="customer-display__items-heading">
          <div>
            <p class="customer-display__section-eyebrow">Your selection</p>
            <h2>Items</h2>
          </div>
          <span class="customer-display__item-count">{{ snapshot?.total_items || 0 }}</span>
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
      </section>

      <aside class="customer-display__summary customer-display__panel" aria-label="Order total">
        <p class="customer-display__section-eyebrow">Order overview</p>
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
import logoSrc from '../assets/chikinlogo.png'

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
  --display-ink: #30251f;
  --display-muted: #806f63;
  --display-border: #ead9c9;
  --display-accent: #e76532;
  min-height: 100vh;
  padding: clamp(20px, 4vw, 56px);
  background:
    radial-gradient(ellipse at top left, rgba(255, 220, 184, 0.42), transparent 42%),
    #f5eee7;
  color: var(--display-ink);
  font-family: 'Inter', 'Segoe UI', sans-serif;
}

.customer-display__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  max-width: 1200px;
  margin: 0 auto 20px;
  padding: 20px 24px;
  border: 1px solid rgba(219, 188, 160, 0.58);
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.92);
  box-shadow: 0 12px 30px rgba(61, 42, 31, 0.07);
}

.customer-display__brand { display: flex; min-width: 0; align-items: center; gap: 20px; }
.customer-display__logo { display: block; width: clamp(112px, 15vw, 180px); height: auto; object-fit: contain; }
.customer-display__heading { min-width: 0; }
.customer-display__eyebrow {
  margin: 0 0 6px;
  color: #a65327;
  font-size: 0.76rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.customer-display h1 {
  margin: 0;
  color: #242d3b;
  font-size: clamp(1.8rem, 3vw, 2.5rem);
  font-weight: 800;
  letter-spacing: -0.045em;
  line-height: 1.08;
}

.customer-display__status {
  display: inline-flex;
  flex: 0 0 auto;
  align-items: center;
  gap: 8px;
  padding: 9px 13px;
  border: 1px solid #b7e5c7;
  border-radius: 999px;
  color: #187344;
  background: #edfaf1;
  font-size: 0.82rem;
  font-weight: 700;
}

.customer-display__status span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #20a35b;
}

.customer-display__status--waiting { border-color: #e4d7cb; color: #74665c; background: #f8f3ee; }
.customer-display__status--waiting span { background: #9b8d83; }

.customer-display__layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(290px, 0.38fr);
  align-items: start;
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.customer-display__panel {
  min-width: 0;
  border: 1px solid rgba(219, 188, 160, 0.58);
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.96);
  box-shadow: 0 12px 30px rgba(61, 42, 31, 0.06);
}

.customer-display__items { padding: 22px 24px; }
.customer-display__items-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--display-border);
}

.customer-display__section-eyebrow {
  margin: 0 0 4px;
  color: #a65327;
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.customer-display h2 {
  margin: 0;
  color: #3d2a1f;
  font-size: 1.12rem;
  font-weight: 800;
}

.customer-display__item-count {
  display: grid;
  min-width: 34px;
  height: 34px;
  place-items: center;
  padding: 0 8px;
  border-radius: 10px;
  color: #8b481f;
  background: #fff2e7;
  font-weight: 700;
}

.customer-display__empty {
  display: grid;
  min-height: 220px;
  place-items: center;
  padding: 32px 16px;
  color: var(--display-muted);
  font-size: 0.95rem;
  text-align: center;
}

.customer-display__list { display: grid; }
.customer-display__item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  padding: 17px 2px;
  border-bottom: 1px solid #f0e5dc;
}

.customer-display__item:last-child { border-bottom: 0; }
.customer-display__item h3,
.customer-display__item p { margin: 0; }
.customer-display__item h3 { color: #35271f; font-size: 0.98rem; font-weight: 750; }
.customer-display__item p { margin-top: 5px; color: var(--display-muted); font-size: 0.85rem; }
.customer-display__item > strong { color: #3d2a1f; font-size: 0.95rem; white-space: nowrap; }

.customer-display__summary {
  align-self: start;
  padding: 22px;
  border-top: 4px solid var(--display-accent);
}

.customer-display__summary h2 { margin-bottom: 16px; }

.customer-display__row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 9px 0;
  color: #6d5e53;
  font-size: 0.88rem;
}

.customer-display__row span:last-child { color: #3d2a1f; font-weight: 650; white-space: nowrap; }
.customer-display__total {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-top: 8px;
  padding-top: 16px;
  border-top: 1px solid var(--display-border);
  color: #6d5e53;
  font-size: 0.88rem;
}

.customer-display__total strong {
  color: #c44f25;
  font-size: clamp(2rem, 3vw, 2.6rem);
  font-weight: 850;
  letter-spacing: -0.04em;
  line-height: 1.1;
}

.customer-display__note {
  margin: 16px 0 0;
  color: #8b7b70;
  font-size: 0.85rem;
}

@media (max-width: 760px) {
  .customer-display__layout { grid-template-columns: minmax(0, 1fr); }
  .customer-display__summary { grid-row: 1; }
}

@media (max-width: 520px) {
  .customer-display { padding: 16px; }
  .customer-display__header { align-items: flex-start; flex-direction: column; gap: 14px; padding: 16px; }
  .customer-display__brand { gap: 12px; }
  .customer-display__logo { width: 92px; }
  .customer-display__eyebrow { font-size: 0.64rem; letter-spacing: 0.07em; }
  .customer-display__header h1 { font-size: 1.85rem; }
  .customer-display__items { padding: 18px 16px; }
  .customer-display__summary { padding: 18px; }
  .customer-display__empty { min-height: 160px; }
}
</style>