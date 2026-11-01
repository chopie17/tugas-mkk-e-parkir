/**
 * Composable useApi — wrapper untuk $fetch yang otomatis menyertakan
 * Authorization Bearer token dari auth store.
 */
export function useApi() {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  const apiFetch = $fetch.create({
    baseURL: config.public.apiBase,
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
