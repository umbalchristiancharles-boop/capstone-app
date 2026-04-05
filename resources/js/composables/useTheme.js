import { ref, watch, onMounted } from 'vue'

// Theme state - scoped to Super Admin only
export function useTheme() {
  const theme = ref('light') // 'light' or 'dark'
  const isThemeInitialized = ref(false)

  /**
   * Initialize theme from localStorage or system preference
   */
  const initializeTheme = () => {
    if (isThemeInitialized.value) return

    try {
      // Check localStorage for saved theme preference (Super Admin specific)
      const savedTheme = localStorage.getItem('superadmin_theme')

      if (savedTheme && ['light', 'dark'].includes(savedTheme)) {
        theme.value = savedTheme
        console.debug('[useTheme] initializeTheme: found superadmin_theme=', savedTheme)
      } else {
        // If Super Admin specific setting not present, try to inherit a global/app theme
        const globalTheme = (function() {
          try {
            // common keys used elsewhere in the app
            const keys = ['theme', 'app_theme', 'site_theme']
            for (const k of keys) {
              const v = localStorage.getItem(k)
              if (v && ['light', 'dark'].includes(v)) return v
            }

            // data attributes or classes on document may indicate dark mode
            const doc = document.documentElement
            const body = document.body
            if (doc) {
              const dataTheme = doc.getAttribute('data-theme') || doc.getAttribute('data-superadmin-theme')
              if (dataTheme === 'dark') return 'dark'
              if (doc.classList.contains('dark') || doc.classList.contains('dark-mode')) return 'dark'
            }
            if (body) {
              if (body.classList.contains('dark') || body.classList.contains('dark-mode')) return 'dark'
            }

            // fallback to system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark'

            return null
          } catch (e) {
            return null
          }
        })()

        theme.value = globalTheme || 'light'
        console.debug('[useTheme] initializeTheme: inherited globalTheme=', globalTheme, '-> final', theme.value)
      }
    } catch (e) {
      console.warn('Failed to initialize theme:', e)
      theme.value = 'light'
    }

    isThemeInitialized.value = true
    applyTheme()
  }

  /**
   * Apply theme to DOM
   */
  const applyTheme = () => {
    try {
      // Apply dark-mode class to document root for global CSS cascade
      if (theme.value === 'dark') {
        document.documentElement.classList.add('dark-mode')
        document.documentElement.classList.remove('light-mode')
        document.body.classList.add('dark-mode')
        document.body.classList.remove('light-mode')
        console.debug('[useTheme] applyTheme: applied dark-mode to document and body')
      } else {
        document.documentElement.classList.remove('dark-mode')
        document.documentElement.classList.add('light-mode')
        document.body.classList.remove('dark-mode')
        document.body.classList.add('light-mode')
        console.debug('[useTheme] applyTheme: applied light-mode to document and body')
      }

      // Also try to apply to common wrapper classes if they exist
      const adminLayout = document.querySelector('.admin-layout')
      if (adminLayout) {
        adminLayout.classList.remove('dark-mode', 'light-mode')
        adminLayout.classList.add(theme.value === 'dark' ? 'dark-mode' : 'light-mode')
      }

      const adminPage = document.querySelector('.admin-page')
      if (adminPage) {
        adminPage.classList.remove('dark-mode', 'light-mode')
        adminPage.classList.add(theme.value === 'dark' ? 'dark-mode' : 'light-mode')
      }

      // Update document attribute for CSS selectors
      if (theme.value === 'dark') {
        document.documentElement.setAttribute('data-superadmin-theme', 'dark')
      } else {
        document.documentElement.removeAttribute('data-superadmin-theme')
      }
    } catch (e) {
      console.warn('Failed to apply theme:', e)
    }
  }

  /**
   * Toggle between light and dark theme
   */
  const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light'
    persistTheme()
    applyTheme()
  }

  /**
   * Set theme to specific value
   */
  const setTheme = (newTheme) => {
    if (['light', 'dark'].includes(newTheme)) {
      theme.value = newTheme
      persistTheme()
      applyTheme()
    }
  }

  /**
   * Persist theme to localStorage
   */
  const persistTheme = () => {
    try {
      localStorage.setItem('superadmin_theme', theme.value)
    } catch (e) {
      console.warn('Failed to persist theme:', e)
    }
  }

  /**
   * Watch for theme changes and persist
   */
  watch(() => theme.value, () => {
    persistTheme()
    applyTheme()
  })

  return {
    theme,
    isThemeInitialized,
    initializeTheme,
    toggleTheme,
    setTheme,
    applyTheme
  }
}
