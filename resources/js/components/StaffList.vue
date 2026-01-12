<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header Section -->
      <div class="flex items-center justify-between mb-8">
        <button @click="goBack" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Admin Panel
        </button>
        <h1 class="text-3xl font-bold text-gray-900">Staff Management</h1>
        <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add Staff
        </button>
      </div>

      <!-- Alert Messages -->
      <div v-if="alertMessage" :class="['mb-4 rounded-md p-4', alertType === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800']">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg v-if="alertType === 'success'" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <svg v-else class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium">{{ alertMessage }}</p>
          </div>
        </div>
      </div>

      <!-- Staff Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="staff in staffList" :key="staff.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ staff.username }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ staff.full_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ staff.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ staff.branch_name || 'N/A' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', staff.role === 'BRANCH_MANAGER' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800']">
                      {{ staff.role === 'BRANCH_MANAGER' ? 'Branch Manager' : 'Staff' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ staff.phone_number || 'N/A' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', staff.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                      {{ staff.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button @click="editStaff(staff)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
                    <button @click="confirmDelete(staff.id, staff.username)" class="text-red-600 hover:text-red-900">Delete</button>
                  </td>
                </tr>
                <tr v-if="staffList.length === 0">
                  <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">No staff accounts found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal -->
      <StaffModal
        :show="showModal"
        :staff="selectedStaff"
        :isEdit="!!selectedStaff"
        @close="closeModal"
        @success="handleSaved"
      />
    </div>
  </div>
</template>

<script>
import StaffModal from './StaffModal.vue';

export default {
  name: 'StaffList',
  components:  {
    StaffModal
  },
  data() {
    return {
      staffList: [],
      branches: [],
      showModal: false,
      selectedStaff:  null,
      alertMessage: '',
      alertType: 'success',
    };
  },
  mounted() {
    this.fetchStaff();
    this.fetchBranches();
  },
  methods: {
    async fetchStaff() {
      try {
        const response = await fetch('/api/admin/staff');
        const data = await response. json();
        if (data.ok) {
          this.staffList = data.staff;
        }
      } catch (error) {
        this.showAlert('Failed to load staff list', 'error');
      }
    },

    async fetchBranches() {
      try {
        const response = await fetch('/api/admin/branches');
        const data = await response.json();
        if (data.ok) {
          this.branches = data.branches;
        }
      } catch (error) {
        console.error('Failed to load branches');
      }
    },

    openCreateModal() {
      this.selectedStaff = null;
      this.showModal = true;
    },

    editStaff(staff) {
      this.selectedStaff = { ...staff };
      this.showModal = true;
    },

   async confirmDelete(id, username) {
  if (!confirm(`Are you sure you want to delete "${username}"?`)) {
    return;
  }

  try {
    const response = await fetch(`/api/admin/staff/${id}`, {
      method: 'DELETE',
      headers:  {
        'Content-Type':  'application/json',
        'Accept': 'application/json'
      }
    });

    const data = await response.json();

    if (response.ok) {
      this.showAlert(data.message || 'Staff account deleted successfully!', 'success');
      this.fetchStaff();
    } else {
      this.showAlert(data.message || 'Failed to delete staff account', 'error');
    }
  } catch (error) {
    console.error('Delete error:', error);
    this.showAlert('Failed to delete staff account', 'error');
  }
}

    ,closeModal() {
      this.showModal = false;
      this.selectedStaff = null;
    },

    handleSaved() {
      this.closeModal();
      this.fetchStaff();
      this.showAlert('Staff account saved successfully!', 'success');
    },

    goBack() {
      this.$router.push('/admin-panel');
    },

    showAlert(message, type) {
      this.alertMessage = message;
      this.alertType = type;
      setTimeout(() => {
        this.alertMessage = '';
      }, 3000);
    }
  }
};
</script>
