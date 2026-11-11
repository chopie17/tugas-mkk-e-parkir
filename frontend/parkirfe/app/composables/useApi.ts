/**
 * Composable useApi — wrapper untuk $fetch yang otomatis menyertakan
 * Authorization Bearer token dari auth store.
 */
export function useApi() {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  // Dynamic Base URL: menyesuaikan dengan IP/host tempat frontend dibuka (komputer atau HP)
  let baseUrl = config.public.apiBase || 'http://localhost:8000/api'
  if (import.meta.client && typeof window !== 'undefined') {
    const currentHost = window.location.hostname
    if (baseUrl.includes('localhost') && currentHost !== 'localhost' && currentHost !== '127.0.0.1') {
      baseUrl = baseUrl.replace('localhost', currentHost)
    } else if (baseUrl.includes('127.0.0.1') && currentHost !== 'localhost' && currentHost !== '127.0.0.1') {
      baseUrl = baseUrl.replace('127.0.0.1', currentHost)
    }
  }

  const apiFetch = $fetch.create({
    baseURL: baseUrl,
    onRequest({ options }) {
      if (authStore.token) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${authStore.token}`,
          Accept: 'application/json',
        }
      }
    },
    async onResponseError({ response }) {
      // Auto logout jika token tidak valid (401)
      if (response.status === 401 && authStore.isLoggedIn) {
        authStore.logout()
        await navigateTo('/login')
      }
    },
  })

  return { apiFetch }
}
