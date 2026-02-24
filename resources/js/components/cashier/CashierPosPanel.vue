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
<script setup>
defineProps({ transactions: Array })
</script>
