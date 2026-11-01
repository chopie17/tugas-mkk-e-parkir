/**
 * Auth Store — mengelola state autentikasi.
 * Token disimpan di localStorage agar persist setelah refresh.
 */
export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)
  const user  = ref<any | null>(null)

  /** Apakah user sudah login */
  const isLoggedIn  = computed(() => !!token.value)
  const isSuperAdmin = computed(() => user.value?.role === 'super_admin')
  const isPetugas   = computed(() => user.value?.role === 'petugas')

  /** Inisialisasi dari localStorage saat app mount */
  function init() {
    if (import.meta.client) {
      token.value = localStorage.getItem('auth_token')
      const u = localStorage.getItem('auth_user')
      if (u) {
        try { user.value = JSON.parse(u) } catch {}
      }
    }
  }

  /** Set token dan user setelah login berhasil */
  function setAuth(authToken: string, authUser: any) {
    token.value = authToken
    user.value  = authUser
    if (import.meta.client) {
      localStorage.setItem('auth_token', authToken)
      localStorage.setItem('auth_user', JSON.stringify(authUser))
    }
  }

  /** Logout — hapus token dan redirect */
  function logout() {
    token.value = null
    user.value  = null
    if (import.meta.client) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }

  return { token, user, isLoggedIn, isSuperAdmin, isPetugas, init, setAuth, logout }
})
