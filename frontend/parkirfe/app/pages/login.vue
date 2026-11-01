<template>
  <div class="w-full max-w-md">
    <!-- Header Title -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4 bg-gradient-to-tr from-blue-600 via-indigo-600 to-sky-400 p-0.5 shadow-xl shadow-blue-500/20">
        <div class="w-full h-full bg-[#0b101d] rounded-[14px] flex items-center justify-center">
          <AppIcon name="gate" class="w-7 h-7 text-blue-400" />
        </div>
      </div>
      <h1 class="text-2xl font-bold tracking-tight text-white">Selamat Datang Kembali</h1>
      <p class="text-sm text-slate-400 mt-1.5">Masuk ke portal operasional E-Parkir Plaza Andalas</p>
    </div>

    <!-- Login Card -->
    <div class="glass-card p-6 sm:p-8 relative">
      <form @submit.prevent="handleLogin" class="space-y-5">
        <!-- Field Login -->
        <div>
          <label class="form-label">Email atau Username</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
              <AppIcon name="user" class="w-4 h-4" />
            </div>
            <input
              v-model="form.login"
              type="text"
              class="form-input pl-10"
              :class="{ error: errors.login }"
              placeholder="Contoh: petugas1 atau admin@parkir.test"
              autocomplete="username"
              required
            />
          </div>
          <p v-if="errors.login" class="text-xs mt-1.5 text-rose-400 flex items-center gap-1">
            <AppIcon name="alert-triangle" class="w-3.5 h-3.5" />
            <span>{{ errors.login }}</span>
          </p>
        </div>

        <!-- Field Password -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="form-label !mb-0">Password</label>
            <NuxtLink to="/forgot-password" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
              Lupa password?
            </NuxtLink>
          </div>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
              <AppIcon name="lock" class="w-4 h-4" />
            </div>
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              class="form-input pl-10 pr-10"
              :class="{ error: errors.password }"
              placeholder="Masukkan password"
              autocomplete="current-password"
              required
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200"
              @click="showPassword = !showPassword"
              title="Lihat / Sembunyikan Password"
            >
              <AppIcon :name="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4" />
            </button>
          </div>
          <p v-if="errors.password" class="text-xs mt-1.5 text-rose-400 flex items-center gap-1">
            <AppIcon name="alert-triangle" class="w-3.5 h-3.5" />
            <span>{{ errors.password }}</span>
          </p>
        </div>

        <!-- Global Error Alert -->
        <div
          v-if="errorMessage"
          class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-start gap-2.5 animate-shake"
        >
          <AppIcon name="alert-triangle" class="w-4 h-4 text-rose-400 shrink-0 mt-0.5" />
          <span>{{ errorMessage }}</span>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="btn btn-primary w-full py-3 text-sm font-semibold tracking-wide"
          :disabled="loading"
        >
          <svg v-if="loading" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span v-else class="flex items-center gap-2">
            <span>Masuk ke Sistem</span>
            <AppIcon name="arrow-right" class="w-4 h-4" />
          </span>
        </button>
      </form>

      <!-- Quick Demo Account Pills -->
      <div class="mt-6 pt-5 border-t border-slate-800/80">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2.5 flex items-center gap-1.5">
          <AppIcon name="key" class="w-3.5 h-3.5 text-amber-400" />
          <span>Akses Cepat (Demo Akun)</span>
        </p>
        <div class="grid grid-cols-2 gap-2">
          <button
            type="button"
            class="px-2.5 py-2 rounded-lg bg-slate-800/70 hover:bg-slate-700/80 border border-slate-700/70 text-left transition-all group"
            @click="fillDemo('superadmin', 'password123')"
          >
            <p class="text-xs font-semibold text-slate-200 group-hover:text-blue-400 transition-colors">Super Admin</p>
            <p class="text-[10px] text-slate-400 font-mono">superadmin</p>
          </button>
          <button
            type="button"
            class="px-2.5 py-2 rounded-lg bg-slate-800/70 hover:bg-slate-700/80 border border-slate-700/70 text-left transition-all group"
            @click="fillDemo('petugas1', 'password123')"
          >
            <p class="text-xs font-semibold text-slate-200 group-hover:text-blue-400 transition-colors">Petugas Shift</p>
            <p class="text-[10px] text-slate-400 font-mono">petugas1</p>
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
