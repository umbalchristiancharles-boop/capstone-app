<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 transition-opacity" @click="closeModal">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <form @submit.prevent="submitForm">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                  {{ isEdit ? 'Edit Staff Account' : 'Create Staff Account' }}
                </h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <!-- Username -->
                  <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username *</label>
                    <input
                      v-model="form.username"
                      type="text"
                      id="username"
                      :disabled="isEdit"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:bg-gray-100"
                      placeholder="owner_admin"
                      required
                    />
                  </div>

                  <!-- Email -->
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                    <input
                      v-model="form.email"
                      type="email"
                      id="email"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      placeholder="Enter email"
                      required
                    />
                  </div>

                  <!-- Full Name -->
                  <div>
                    <label for="fullName" class="block text-sm font-medium text-gray-700">Full Name *</label>
                    <input
                      v-model="form.fullName"
                      type="text"
                      id="fullName"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      placeholder="Enter full name"
                      required
                    />
                  </div>

                  <!-- Password -->
                  <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password {{ isEdit ? '' : '*' }}</label>
                    <input
                      v-model="form.password"
                      type="password"
                      id="password"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      placeholder="••••••••"
                      :required="!isEdit"
                    />
                  </div>

                  <!-- Phone -->
                  <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input
                      v-model="form.phone"
                      type="text"
                      id="phone"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      placeholder="+63 XXX XXX XXXX"
                    />
                  </div>

                  <!-- Branch -->
                  <div>
                    <label for="branch" class="block text-sm font-medium text-gray-700">Branch *</label>
                    <select
                      v-model="form.branchId"
                      id="branch"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      required
                    >
                      <option value="" disabled>Select Branch</option>
                      <option
                        v-for="branch in branches"
                        :key="branch.id"
                        :value="branch.id"
                      >
                        {{ branch.name }}
                      </option>
                    </select>
                  </div>

                  <!-- Role -->
                  <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role *</label>
                    <select
                      v-model="form.role"
                      id="role"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      required
                    >
                      <option value="" disabled>Select Role</option>
                      <option value="BRANCH_MANAGER">Branch Manager</option>
                      <option value="STAFF">Staff</option>
                    </select>
                  </div>
                </div>

                <!-- Address -->
                <div class="mt-4">
                  <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                  <textarea
                    v-model="form.address"
                    id="address"
                    rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="Enter address"
                  ></textarea>
                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="mt-4 rounded-md bg-red-50 p-4">
                  <div class="flex">
                    <div class="flex-shrink-0">
                      <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                      </svg>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm text-red-700">{{ errorMessage }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="submit"
              :disabled="isSubmitting"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
            >
              {{ isSubmitting ? 'Saving...' : (isEdit ? 'Update' : 'Create') }}
            </button>
            <button
              type="button"
              @click="closeModal"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'StaffModal',
  props: {
    show: Boolean,
    staff: Object,
    isEdit: Boolean,
  },
  emits: ['close', 'success'],
  data() {
    return {
      form: {
        username: '',
        email: '',
        fullName: '',
        password: '',
        phone: '',
        branchId: '',
        role: '',
        address: '',
      },
      branches: [],
      errorMessage: '',
      isSubmitting: false,
    }
  },
  watch: {
    show:  {
      immediate: true,
      async handler(newVal) {

        if (newVal) {
          await this.loadBranches()

          if (this.isEdit && this.staff) {
            this.form = {
              username: this.staff.username,
              email: this.staff.email,
              fullName: this.staff.fullName,
              password: '',
              phone: this.staff.phone || '',
              branchId: this.staff.branchId,
              role: this.staff.role || '',
              address: this.staff.address || '',
            }
          } else {
            this.form = {
              username: '',
              email: '',
              fullName:  '',
              password: '',
              phone: '',
              branchId: '',
              role: '',
              address: '',
            }
          }

          this.errorMessage = ''
        }
      }
    }
  },
  methods: {
    async loadBranches() {

        try {
        const res = await axios.get('/api/admin/branches', {
          withCredentials: true,
        })

        if (res.data.ok) {
          this.branches = res.data.branches
          console.log('📊 Branches count:', this.branches.length)
        } else {

        }
      } catch (e) {
        console.error('💥 Error loading branches:', e)
      }
    },

    async submitForm() {
      if (this.isSubmitting) return

      this.isSubmitting = true
      this.errorMessage = ''

      try {
        const url = this.isEdit
          ? `/api/admin/staff/${this.staff.id}`
          : '/api/admin/staff'

        const method = this.isEdit ? 'put' : 'post'

        const res = await axios[method](url, this.form, {
          withCredentials: true,
        })

        if (res.data.ok) {
          this.$emit('success', res.data.message || 'Success!')
          this.closeModal()
        } else {
          this.errorMessage = res.data.message || 'An error occurred.'
        }
      } catch (e) {
        if (e.response?. data?.errors) {
          const errors = Object.values(e.response.data.errors).flat()
          this.errorMessage = errors.join(', ')
        } else {
          this.errorMessage = e.response?.data?.message || 'An error occurred.  Please try again.'
        }
      } finally {
        this. isSubmitting = false
      }
    },

    closeModal() {
      this.$emit('close')
    }
  }
}
</script>
