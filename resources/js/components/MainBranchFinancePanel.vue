<template>
  <!-- Main Branch Finance Panel wraps ManagerFinancePanel for /main-branch/finance route -->
  <!-- Main Branch Finance Manager sees aggregated data from ALL branches -->
  <div class="main-branch-finance-panel">
    <MainBranchFinanceBranchConfirmations />
    <ManagerFinancePanel :isMainBranchFinance="true" />
  </div>
</template>

<script setup>
import ManagerFinancePanel from './ManagerFinancePanel.vue'
import MainBranchFinanceBranchConfirmations from './MainBranchFinanceBranchConfirmations.vue'
</script>

<style scoped>
.main-branch-finance-panel {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
</style>
