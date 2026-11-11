<template>
  <div class="w-full max-w-md">
    <!-- Back button -->
    <div class="mb-5">
      <NuxtLink
        to="/login"
        class="inline-flex items-center gap-2 text-xs font-bold text-[#0a0a0a] hover:text-[var(--nb-purple,#6c47ff)] transition-colors px-3 py-1.5 rounded-full bg-white border-2 border-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a]"
      >
        <AppIcon name="arrow-left" class="w-3.5 h-3.5" />
        <span>Kembali ke Login</span>
      </NuxtLink>
    </div>

    <!-- Header -->
    <div class="text-center mb-6">
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#0a0a0a]">Pemulihan Akun</h1>
      <p class="text-xs sm:text-sm text-[var(--nb-muted)] mt-1 font-medium">
        {{ step === 1 ? 'Masukkan email akun petugas untuk menerima token pemulihan' : 'Masukkan token dan buat password baru' }}
      </p>
    </div>

    <!-- Step Progress Indicator -->
    <div class="flex items-center justify-center gap-3 mb-6">
      <div class="flex items-center gap-2">
        <span
          class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black border-2 border-[#0a0a0a]"
          :class="step >= 1 ? 'bg-[var(--nb-lime,#e4ff3a)] text-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a]' : 'bg-white text-slate-400'"
        >1</span>
        <span class="text-xs font-bold" :class="step >= 1 ? 'text-[#0a0a0a]' : 'text-slate-400'">Minta Token</span>
      </div>
      <div class="w-8 h-0.5 bg-[#0a0a0a]"></div>
      <div class="flex items-center gap-2">
        <span
          class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black border-2 border-[#0a0a0a]"
          :class="step >= 2 ? 'bg-[var(--nb-lime,#e4ff3a)] text-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a]' : 'bg-white text-slate-400'"
        >2</span>
        <span class="text-xs font-bold" :class="step >= 2 ? 'text-[#0a0a0a]' : 'text-slate-400'">Reset Password</span>
      </div>
    </div>

    <!-- Main Card -->
    <div class="nb-card p-6 sm:p-8 bg-white border-2 border-black shadow-[6px_6px_0_#0a0a0a] rounded-3xl">
      <!-- Step 1: Request Token -->
      <form v-if="step === 1" @submit.prevent="requestToken" class="space-y-4">
        <div>
          <label class="form-label font-bold text-xs">Email Terdaftar</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <AppIcon name="mail" class="w-4 h-4 text-black" />
            </div>
            <input
              v-model="email"
              type="email"
              class="form-input pl-10 border-2 border-black rounded-xl shadow-[3px_3px_0_#0a0a0a]"
              placeholder="nama@parkir.test"
              required
            />
          </div>
        </div>

        <div v-if="errorMsg" class="p-3.5 rounded-xl bg-[#fee2e2] border-2 border-[#0a0a0a] text-rose-800 text-xs font-bold flex items-start gap-2 shadow-[2px_2px_0_#0a0a0a]">
          <AppIcon name="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" />
          <span>{{ errorMsg }}</span>
        </div>

        <button
          type="submit"
          class="nb-btn nb-btn-lg w-full bg-[var(--nb-lime,#e4ff3a)] text-[#0a0a0a] border-2 border-black font-extrabold text-base shadow-[4px_4px_0_#0a0a0a] active:translate-x-1 active:translate-y-1 active:shadow-none"
          :disabled="loading || !email.trim()"
        >
          <svg v-if="loading" class="w-5 h-5 animate-spin text-black" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span v-else class="flex items-center justify-center gap-2">
            <span>Kirim Token Pemulihan</span>
            <AppIcon name="arrow-right" class="w-4 h-4" />
          </span>
        </button>
      </form>

      <!-- Step 2: Reset Form -->
      <form v-else @submit.prevent="resetPassword" class="space-y-4">
        <!-- Demo Banner Token -->
        <div class="p-3.5 rounded-2xl bg-[#dbeafe] border-2 border-[#0a0a0a] text-xs text-blue-950 space-y-1 shadow-[2px_2px_0_#0a0a0a]">
          <div class="flex items-center justify-between">
            <span class="font-bold text-blue-900">Token Pemulihan (Mode Demo):</span>
            <span class="font-mono font-black text-black bg-white border border-[#0a0a0a] px-2 py-0.5 rounded-lg">{{ resetToken }}</span>
          </div>
          <p class="text-[11px] text-blue-800 font-medium">Token otomatis diisikan pada form di bawah.</p>
        </div>

        <div>
          <label class="form-label font-bold text-xs">Token Reset</label>
          <input
            v-model="token"
            type="text"
            class="form-input font-mono font-bold border-2 border-black rounded-xl shadow-[3px_3px_0_#0a0a0a]"
            placeholder="Kode token reset"
            required
          />
        </div>

        <div>
          <label class="form-label font-bold text-xs">Password Baru</label>
          <input
            v-model="newPassword"
            type="password"
            class="form-input border-2 border-black rounded-xl shadow-[3px_3px_0_#0a0a0a]"
            placeholder="Minimal 8 karakter"
            required
          />
        </div>

        <div>
          <label class="form-label font-bold text-xs">Konfirmasi Password Baru</label>
          <input
            v-model="confirmPassword"
            type="password"
            class="form-input border-2 border-black rounded-xl shadow-[3px_3px_0_#0a0a0a]"
            placeholder="Ulangi password baru"
            required
          />
        </div>

        <div v-if="errorMsg" class="p-3.5 rounded-xl bg-[#fee2e2] border-2 border-[#0a0a0a] text-rose-800 text-xs font-bold flex items-start gap-2 shadow-[2px_2px_0_#0a0a0a]">
          <AppIcon name="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" />
          <span>{{ errorMsg }}</span>
        </div>

        <div v-if="successMsg" class="p-3.5 rounded-xl bg-[#dcfce7] border-2 border-[#0a0a0a] text-emerald-900 text-xs font-bold flex items-start gap-2 shadow-[2px_2px_0_#0a0a0a]">
          <AppIcon name="check-circle" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
          <span>{{ successMsg }}</span>
        </div>

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
