<template>
  <div class="w-full max-w-md">
    <!-- Header Title -->
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-3 bg-[var(--nb-lime,#e4ff3a)] border-2 border-[#0a0a0a] shadow-[3px_3px_0_#0a0a0a]">
        <AppIcon name="gate" class="w-7 h-7 text-black" />
      </div>
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#0a0a0a]">Selamat Datang</h1>
      <p class="text-xs sm:text-sm text-[var(--nb-muted)] mt-1 font-medium">Portal operasional petugas & admin E-Parkir Plaza Andalas</p>
    </div>

    <!-- Login Card -->
    <div class="nb-card p-6 sm:p-8 relative bg-white border-2 border-black shadow-[6px_6px_0_#0a0a0a] rounded-3xl">
      <form @submit.prevent="handleLogin" class="space-y-4">
        <!-- Field Login -->
        <div>
          <label class="form-label font-bold text-xs">Email atau Username</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <AppIcon name="user" class="w-4 h-4 text-black" />
            </div>
            <input
              v-model="form.login"
              type="text"
              class="form-input pl-10 border-2 border-black rounded-xl shadow-[3px_3px_0_#0a0a0a]"
              :class="{ error: errors.login }"
              placeholder="Contoh: petugas1 atau admin@parkir.test"
              autocomplete="username"
              required
            />
          </div>
          <p v-if="errors.login" class="text-xs mt-1.5 text-rose-600 font-bold flex items-center gap-1">
            <AppIcon name="alert-triangle" class="w-3.5 h-3.5" />
            <span>{{ errors.login }}</span>
          </p>
        </div>

        <!-- Field Password -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="form-label !mb-0 font-bold text-xs">Password</label>
            <NuxtLink to="/forgot-password" class="text-xs font-bold text-[var(--nb-purple,#6c47ff)] hover:underline">
              Lupa password?
            </NuxtLink>
          </div>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <AppIcon name="lock" class="w-4 h-4 text-black" />
            </div>
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              class="form-input pl-10 pr-10 border-2 border-black rounded-xl shadow-[3px_3px_0_#0a0a0a]"
              :class="{ error: errors.password }"
              placeholder="Masukkan password"
              autocomplete="current-password"
              required
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-black"
              @click="showPassword = !showPassword"
              title="Lihat / Sembunyikan Password"
            >
              <AppIcon :name="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4" />
            </button>
          </div>
          <p v-if="errors.password" class="text-xs mt-1.5 text-rose-600 font-bold flex items-center gap-1">
            <AppIcon name="alert-triangle" class="w-3.5 h-3.5" />
            <span>{{ errors.password }}</span>
          </p>
        </div>

        <!-- Global Error Alert -->
        <div
          v-if="errorMessage"
          class="p-3.5 rounded-xl bg-[#fee2e2] border-2 border-[#0a0a0a] text-rose-800 text-xs font-bold flex items-start gap-2.5 shadow-[2px_2px_0_#0a0a0a]"
        >
          <AppIcon name="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" />
          <span>{{ errorMessage }}</span>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="nb-btn nb-btn-lg w-full bg-[var(--nb-lime,#e4ff3a)] text-[#0a0a0a] border-2 border-black font-extrabold text-base shadow-[4px_4px_0_#0a0a0a] active:translate-x-1 active:translate-y-1 active:shadow-none"
          :disabled="loading"
        >
          <svg v-if="loading" class="w-5 h-5 animate-spin text-black" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span v-else class="flex items-center justify-center gap-2">
            <span>Masuk ke Sistem</span>
            <AppIcon name="arrow-right" class="w-4 h-4" />
          </span>
        </button>
      </form>

      <!-- Quick Demo Account Pills -->
      <div class="mt-6 pt-5 border-t-2 border-[#0a0a0a]/15">
        <p class="text-[11px] font-black uppercase tracking-wider text-[var(--nb-muted)] mb-2.5 flex items-center gap-1.5">
          <AppIcon name="key" class="w-3.5 h-3.5 text-black" />
          <span>Akses Cepat (Demo Akun)</span>
        </p>
        <div class="grid grid-cols-2 gap-2">
          <button
            type="button"
            class="px-3 py-2 rounded-xl bg-[#f8fafc] hover:bg-[var(--nb-lime,#e4ff3a)] border-2 border-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a] text-left transition-all active:translate-x-0.5 active:translate-y-0.5 active:shadow-none group"
            @click="fillDemo('superadmin', 'password123')"
          >
            <p class="text-xs font-extrabold text-[#0a0a0a]">Super Admin</p>
            <p class="text-[10px] text-[var(--nb-muted)] font-mono font-bold">superadmin</p>
          </button>
          <button
            type="button"
            class="px-3 py-2 rounded-xl bg-[#f8fafc] hover:bg-[var(--nb-lime,#e4ff3a)] border-2 border-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a] text-left transition-all active:translate-x-0.5 active:translate-y-0.5 active:shadow-none group"
            @click="fillDemo('petugas1', 'password123')"
          >
            <p class="text-xs font-extrabold text-[#0a0a0a]">Petugas</p>
            <p class="text-[10px] text-[var(--nb-muted)] font-mono font-bold">petugas1</p>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'

definePageMeta({ layout: 'auth' })
useHead({ title: 'Login — E-Parkir Plaza Andalas' })

const authStore = useAuthStore()
const router = useRouter()
const config = useRuntimeConfig()

const form = reactive({ login: '', password: '' })
const errors = reactive<Record<string, string>>({})
const errorMessage = ref('')
const loading = ref(false)
const showPassword = ref(false)

onMounted(() => {
  if (authStore.isLoggedIn) router.push('/dashboard')
})

function fillDemo(user: string, pass: string) {
  form.login = user
  form.password = pass
  errorMessage.value = ''
}

async function handleLogin() {
  errorMessage.value = ''
  Object.keys(errors).forEach(k => delete (errors as any)[k])
  loading.value = true

  try {
    const data = await $fetch<any>('/login', {
      baseURL: config.public.apiBase,
      method: 'POST',
      body: { login: form.login, password: form.password },
      headers: { Accept: 'application/json' },
    })

    authStore.setAuth(data.token, data.user)
    await router.push('/dashboard')
  } catch (err: any) {
    const status = err?.response?.status
    if (status === 422) {
      const validationErrors = err?.data?.errors ?? {}
      Object.assign(errors, validationErrors)
    } else if (status === 401) {
      errorMessage.value = err?.data?.message ?? 'Email/username atau password salah.'
    } else {
      errorMessage.value = 'Terjadi kesalahan sistem. Silakan coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>
