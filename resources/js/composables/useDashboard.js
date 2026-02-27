import { ref, computed } from 'vue'
import axios from 'axios'

/**
 * Composable for fetching dashboard data
 * Designed to be modular and extensible for any data source
 */
export function useDashboard() {
  // Reactive state
  const isLoading = ref(false)
  const error = ref(null)
  const data = ref(null)
  const dateRange = ref('today')

  // Available date range options
  const dateRangeOptions = [
    { value: 'today', label: 'Today' },
    { value: 'yesterday', label: 'Yesterday' },
    { value: 'thisWeek', label: 'This Week' },
    { value: 'lastWeek', label: 'Last Week' },
    { value: 'thisMonth', label: 'This Month' },
    { value: 'lastMonth', label: 'Last Month' },
  ]

  // Data source configurations - easily extendable for new data sources
  const dataSources = {
    admin: {
      endpoint: '/api/admin/dashboard',
      mapping: {
        orders: 'orders',
        completed: 'completed',
        pending: 'pending',
        sales: 'sales',
        recentOrders: 'recent_orders',
        productionQueue: 'production_queue',
        topProducts: 'top_products',
        lowStockItems: 'low_stock_items',
        staffActivity: 'recent_activity',
        branchesCount: 'branches_count',
        staffCount: 'staff_count',
        branches: 'branches',
      }
    },
    manager: {
      endpoint: '/api/manager/dashboard',
      mapping: {
        orders: 'stats.orders',
        completed: 'stats.completed',
        pending: 'stats.pending',
        sales: 'stats.sales',
        recentOrders: 'recentOrders',
        productionQueue: 'productionQueue',
        staffActivity: 'staffActivity',
        staffCount: 'summary.totalEmployees',
      }
    }
  }

  /**
   * Get nested property from object using dot notation
   */
  function getNestedValue(obj, path) {
    return path.split('.').reduce((acc, part) => acc && acc[part], obj)
  }

  /**
   * Fetch dashboard data from API
   */
  async function fetchDashboard(source = 'admin') {
    const config = dataSources[source]
    if (!config) {
      error.value = `Unknown data source: ${source}`
      return
    }

    isLoading.value = true
    error.value = null

    try {
      const response = await axios.get(config.endpoint, {
        params: { range: dateRange.value },
        withCredentials: true,
      })

      if (response.data) {
        // Transform data according to mapping
        data.value = transformData(response.data, config.mapping)
      }
    } catch (e) {
      error.value = e.response?.data?.message || 'Failed to fetch dashboard data'
      console.error('Dashboard fetch error:', e)
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Transform API response to consistent format
   */
  function transformData(apiResponse, mapping) {
    const result = {}

    for (const [key, path] of Object.entries(mapping)) {
      result[key] = getNestedValue(apiResponse, path)
    }

    // Include raw response for flexibility
    result._raw = apiResponse

    return result
  }

  /**
   * Change date range and refetch data
   */
  async function changeRange(newRange) {
    dateRange.value = newRange
    await fetchDashboard()
  }

  /**
   * Add custom data source
   */
  function addDataSource(name, config) {
    dataSources[name] = config
  }

  // Computed properties for easy access
  const orders = computed(() => data.value?.orders ?? 0)
  const completed = computed(() => data.value?.completed ?? 0)
  const pending = computed(() => data.value?.pending ?? 0)
  const sales = computed(() => data.value?.sales ?? '₱0')
  const recentOrders = computed(() => data.value?.recentOrders ?? [])
  const productionQueue = computed(() => data.value?.productionQueue ?? [])
  const topProducts = computed(() => data.value?.topProducts ?? [])
  const lowStockItems = computed(() => data.value?.lowStockItems ?? [])
  const staffActivity = computed(() => data.value?.staffActivity ?? [])
  const branchesCount = computed(() => data.value?.branchesCount ?? 0)
  const staffCount = computed(() => data.value?.staffCount ?? 0)
  const branches = computed(() => data.value?.branches ?? [])

  return {
    // State
    isLoading,
    error,
    data,
    dateRange,
    dateRangeOptions,

    // Computed
    orders,
    completed,
    pending,
    sales,
    recentOrders,
    productionQueue,
    topProducts,
    lowStockItems,
    staffActivity,
    branchesCount,
    staffCount,
    branches,

    // Methods
    fetchDashboard,
    changeRange,
    addDataSource,
  }
}

export default useDashboard
