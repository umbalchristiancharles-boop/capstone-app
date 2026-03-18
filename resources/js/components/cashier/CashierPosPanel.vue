<!--
  =============================================================================
  DEPRECATED COMPONENT - NOT USED
  =============================================================================
  This component is deprecated and not used in the application.

  The active implementation for POS/Cashier functionality is:
  - StaffCashierPanel.vue (for staff cashier operations)
  - CashierController.php (backend API)

  This file is kept for reference only and can be safely removed.
  =============================================================================
-->
<template>
  <section>
    <h2>POS Transactions</h2>
    <div v-if="!transactions.length">No transactions found.</div>
    <ul v-else>
      <li v-for="tx in transactions" :key="tx.id">{{ tx.code }} - {{ tx.amount }}</li>
    </ul>
    <button class="btn-primary">Create New Transaction</button>
  </section>
</template>
<script setup lang="ts">
const props = withDefaults(defineProps<{
  transactions?: Array<any>
}>(), {
  transactions: () => []
})
</script>
