<template>
  <section>
    <h2>Financial Reports</h2>
    <div v-if="!reports.length">No financial reports available.</div>
    <ul v-else>
      <li v-for="report in reports" :key="report.id">{{ report.title }}</li>
    </ul>
    <h2>Pending Transactions</h2>
    <div v-if="!transactions.length">No pending transactions.</div>
    <ul v-else>
      <li v-for="tx in transactions" :key="tx.id">{{ tx.description }} - {{ tx.status }}</li>
    </ul>
  </section>
</template>
<script setup>
defineProps({ reports: Array, transactions: Array })
</script>
