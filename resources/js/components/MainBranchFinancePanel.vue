<template>
  <!-- Main Branch Finance Panel wraps ManagerFinancePanel for /main-branch/finance route -->
  <!-- Main Branch Finance Manager sees aggregated data from ALL branches -->
  <ManagerFinancePanel :isMainBranchFinance="true" />
</template>

<script setup>
import ManagerFinancePanel from './ManagerFinancePanel.vue'
</script>

<style scoped>
/* Inherit styles from parent component */
</style>
