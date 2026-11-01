<template>
  <div class="w-full max-w-md">
    <!-- Back button -->
    <div class="mb-6">
      <NuxtLink
        to="/login"
        class="inline-flex items-center gap-2 text-xs font-medium text-slate-400 hover:text-blue-400 transition-colors"
      >
        <AppIcon name="arrow-left" class="w-4 h-4" />
        <span>Kembali ke Halaman Login</span>
      </NuxtLink>
    </div>

    <!-- Header -->
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold tracking-tight text-white">Pemulihan Akun</h1>
      <p class="text-sm text-slate-400 mt-1">
        {{ step === 1 ? 'Masukkan email akun petugas Anda untuk menerima token pemulihan' : 'Masukkan kode token dan buat password baru' }}
      </p>
    </div>

    <!-- Step Progress Indicator -->
    <div class="flex items-center justify-center gap-3 mb-6">
      <div class="flex items-center gap-2">
        <span
          class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
          :class="step >= 1 ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-slate-800 text-slate-500'"
        >1</span>
        <span class="text-xs font-medium" :class="step >= 1 ? 'text-slate-200' : 'text-slate-500'">Minta Token</span>
      </div>
      <div class="w-8 h-px bg-slate-800"></div>
      <div class="flex items-center gap-2">
        <span
          class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
          :class="step >= 2 ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-slate-800 text-slate-500'"
        >2</span>
        <span class="text-xs font-medium" :class="step >= 2 ? 'text-slate-200' : 'text-slate-500'">Reset Password</span>
      </div>
    </div>

    <!-- Main Card -->
    <div class="glass-card p-6 sm:p-8">
      <!-- Step 1: Request Token -->
      <form v-if="step === 1" @submit.prevent="requestToken" class="space-y-5">
        <div>
          <label class="form-label">Email Terdaftar</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
              <AppIcon name="mail" class="w-4 h-4" />
            </div>
            <input
              v-model="email"
              type="email"
              class="form-input pl-10"
              placeholder="nama@parkir.test"
              required
            />
          </div>
        </div>

        <div v-if="errorMsg" class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-start gap-2">
          <AppIcon name="alert-triangle" class="w-4 h-4 text-rose-400 shrink-0 mt-0.5" />
          <span>{{ errorMsg }}</span>
        </div>

        <button
          type="submit"
          class="btn btn-primary w-full py-3 text-sm font-semibold"
          :disabled="loading || !email.trim()"
        >
          <svg v-if="loading" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span v-else class="flex items-center gap-2">
            <span>Kirim Token Pemulihan</span>
            <AppIcon name="arrow-right" class="w-4 h-4" />
          </span>
        </button>
      </form>

      <!-- Step 2: Reset Form -->
      <form v-else @submit.prevent="resetPassword" class="space-y-4">
        <!-- Demo Banner Token -->
        <div class="p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/20 text-xs text-blue-300 space-y-1">
          <div class="flex items-center justify-between">
            <span class="font-medium text-blue-200">Token Pemulihan (Mode Demo):</span>
            <span class="font-mono font-bold text-white bg-blue-600/40 px-2 py-0.5 rounded">{{ resetToken }}</span>
          </div>
          <p class="text-[11px] text-slate-400">Token otomatis diisikan pada form di bawah.</p>
        </div>

        <div>
          <label class="form-label">Token Reset</label>
          <input
            v-model="token"
            type="text"
            class="form-input font-mono"
            placeholder="Kode token reset"
            required
          />
        </div>

        <div>
          <label class="form-label">Password Baru</label>
          <input
            v-model="newPassword"
            type="password"
            class="form-input"
            placeholder="Minimal 8 karakter"
            required
          />
        </div>

        <div>
          <label class="form-label">Konfirmasi Password Baru</label>
          <input
            v-model="confirmPassword"
            type="password"
            class="form-input"
            placeholder="Ulangi password baru"
            required
          />
        </div>

        <div v-if="errorMsg" class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-start gap-2">
          <AppIcon name="alert-triangle" class="w-4 h-4 text-rose-400 shrink-0 mt-0.5" />
          <span>{{ errorMsg }}</span>
        </div>

        <div v-if="successMsg" class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-start gap-2">
          <AppIcon name="check-circle" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" />
          <span>{{ successMsg }}</span>
        </div>

        <button
          type="submit"
          class="btn btn-success w-full py-3 text-sm font-semibold"
          :disabled="loading"
        >
          <svg v-if="loading" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span v-else class="flex items-center gap-2">
            <AppIcon name="check" class="w-4 h-4" />
            <span>Simpan Password Baru</span>
          </span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'

definePageMeta({ layout: 'auth' })
useHead({ title: 'Lupa Password — E-Parkir Plaza Andalas' })

const config = useRuntimeConfig()
const router = useRouter()

const step = ref(1)
const email = ref('')
const token = ref('')
const resetToken = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const loading = ref(false)
const errorMsg = ref('')
const successMsg = ref('')

async function requestToken() {
  errorMsg.value = ''
  loading.value = true
  try {
    const data = await $fetch<any>('/forgot-password', {
      baseURL: config.public.apiBase,
      method: 'POST',
      body: { email: email.value },
      headers: { Accept: 'application/json' },
    })
    resetToken.value = data.reset_token
    token.value = data.reset_token
    step.value = 2
  } catch (e: any) {
    errorMsg.value = e?.data?.message ?? 'Email tidak ditemukan dalam sistem.'
  } finally {
    loading.value = false
  }
}

async function resetPassword() {
  errorMsg.value = ''
  successMsg.value = ''
  if (newPassword.value !== confirmPassword.value) {
    errorMsg.value = 'Konfirmasi password baru tidak cocok.'
    return
  }
  loading.value = true
  try {
    await $fetch('/reset-password', {
      baseURL: config.public.apiBase,
      method: 'POST',
      body: {
        email: email.value,
        reset_token: token.value,
        password: newPassword.value,
        password_confirmation: confirmPassword.value,
      },
      headers: { Accept: 'application/json' },
    })
    successMsg.value = 'Password berhasil diperbarui! Mengalihkan ke login...'
    setTimeout(() => router.push('/login'), 1800)
  } catch (e: any) {
    errorMsg.value = e?.data?.message ?? 'Token tidak valid atau telah kedaluwarsa.'
  } finally {
    loading.value = false
  }
}
</script>
