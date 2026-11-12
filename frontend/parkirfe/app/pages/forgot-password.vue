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
        {{ step === 1 ? 'Masukkan email akun petugas untuk menerima kode OTP pemulihan' : 'Masukkan kode OTP 6 digit dan buat password baru' }}
      </p>
    </div>

    <!-- Step Progress Indicator -->
    <div class="flex items-center justify-center gap-3 mb-6">
      <div class="flex items-center gap-2">
        <span
          class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black border-2 border-[#0a0a0a]"
          :class="step >= 1 ? 'bg-[var(--nb-lime,#e4ff3a)] text-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a]' : 'bg-white text-slate-400'"
        >1</span>
        <span class="text-xs font-bold" :class="step >= 1 ? 'text-[#0a0a0a]' : 'text-slate-400'">Kirim OTP</span>
      </div>
      <div class="w-8 h-0.5 bg-[#0a0a0a]"></div>
      <div class="flex items-center gap-2">
        <span
          class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black border-2 border-[#0a0a0a]"
          :class="step >= 2 ? 'bg-[var(--nb-lime,#e4ff3a)] text-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a]' : 'bg-white text-slate-400'"
        >2</span>
        <span class="text-xs font-bold" :class="step >= 2 ? 'text-[#0a0a0a]' : 'text-slate-400'">Verifikasi & Reset</span>
      </div>
    </div>

    <!-- Main Card -->
    <div class="nb-card p-6 sm:p-8 bg-white border-2 border-black shadow-[6px_6px_0_#0a0a0a] rounded-3xl">
      <!-- Step 1: Request OTP -->
      <form v-if="step === 1" @submit.prevent="requestToken" class="space-y-4">
        <div>
          <label class="form-label font-bold text-xs">Email Terdaftar Petugas</label>
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
          <p class="text-[11px] text-[var(--nb-muted)] font-medium mt-1.5">
            Kode verifikasi OTP 6 digit akan dikirim ke email terdaftar via Mailtrap SMTP.
          </p>
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
            <span>Kirim Kode OTP ke Email</span>
            <AppIcon name="arrow-right" class="w-4 h-4" />
          </span>
        </button>
      </form>

      <!-- Step 2: Reset Form With OTP -->
      <form v-else @submit.prevent="resetPassword" class="space-y-4">
        <!-- OTP Sent Notice Banner -->
        <div class="p-3.5 rounded-2xl bg-[var(--nb-yellow)]/30 border-2 border-black text-xs text-black space-y-1 shadow-[2px_2px_0_#0a0a0a]">
          <div class="flex items-center justify-between">
            <span class="font-black text-black">OTP Dikirim ke Email:</span>
            <span class="font-mono font-bold text-black">{{ email }}</span>
          </div>
          <p class="text-[11px] text-black/70 font-medium">Silakan buka inbox email Anda untuk melihat 6 digit kode OTP.</p>
        </div>

        <div>
          <label class="form-label font-bold text-xs">Kode OTP (6 Digit Angka)</label>
          <input
            v-model="token"
            type="text"
            maxlength="6"
            class="form-input text-center font-mono font-black text-2xl tracking-[0.35em] border-2 border-black rounded-xl shadow-[3px_3px_0_#0a0a0a] bg-white"
            placeholder="000000"
            required
            autofocus
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

        <div class="flex gap-2 pt-1">
          <button
            type="button"
            class="nb-btn bg-white text-black font-bold border-2 border-black px-4 py-2 text-xs shadow-[2px_2px_0_#0a0a0a]"
            @click="step = 1"
          >
            Ubah Email
          </button>
          <button
            type="submit"
            class="nb-btn flex-1 bg-[var(--nb-lime,#e4ff3a)] text-[#0a0a0a] border-2 border-black font-extrabold text-sm shadow-[3px_3px_0_#0a0a0a] py-3.5 active:translate-x-1 active:translate-y-1 active:shadow-none"
            :disabled="loading || token.length < 6"
          >
            <svg v-if="loading" class="w-5 h-5 animate-spin text-black mx-auto" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span v-else class="flex items-center justify-center gap-2">
              <AppIcon name="check" class="w-4 h-4" />
              <span>Verifikasi & Ganti Password</span>
            </span>
          </button>
        </div>
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
const newPassword = ref('')
const confirmPassword = ref('')
const loading = ref(false)
const errorMsg = ref('')
const successMsg = ref('')

async function requestToken() {
  errorMsg.value = ''
  loading.value = true
  try {
    await $fetch<any>('/forgot-password', {
      baseURL: config.public.apiBase,
      method: 'POST',
      body: { email: email.value },
      headers: { Accept: 'application/json' },
    })
    token.value = '' // Kolom OTP dikosongkan agar pengguna menginput manual dari inbox email
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
        reset_token: token.value.trim(),
        password: newPassword.value,
        password_confirmation: confirmPassword.value,
      },
      headers: { Accept: 'application/json' },
    })
    successMsg.value = 'Password berhasil diperbarui! Mengalihkan ke login...'
    setTimeout(() => router.push('/login'), 1800)
  } catch (e: any) {
    errorMsg.value = e?.data?.message ?? 'Kode OTP tidak valid atau telah kedaluwarsa.'
  } finally {
    loading.value = false
  }
}
</script>
