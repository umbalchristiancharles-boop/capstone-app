<template>
  <section>
    <h2>Staff List</h2>
    <div v-if="!staffList.length">No staff found.</div>
    <ul v-else>
      <li v-for="staff in staffList" :key="staff.id">{{ staff.name }} - {{ staff.status }}</li>
    </ul>
    <h2>HR Reports</h2>
    <div v-if="!hrReports.length">No HR reports available.</div>
    <ul v-else>
      <li v-for="report in hrReports" :key="report.id">{{ report.title }}</li>
    </ul>
  </section>
</template>
<script setup>
defineProps({ staffList: Array, hrReports: Array })
</script>
