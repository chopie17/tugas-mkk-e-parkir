/**
 * Route middleware — pastikan user sudah login.
 * Jika tidak, redirect ke /login.
 */
export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()
  authStore.init()

  if (!authStore.isLoggedIn && to.path !== '/login') {
    return navigateTo('/login')
  }
})
