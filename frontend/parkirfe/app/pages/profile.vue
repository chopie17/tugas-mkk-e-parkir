<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center gap-2.5">
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-[#0a0a0a]">Profil Saya</h1>
          <span
            class="nb-pill text-xs font-black border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a]"
            :class="authStore.isSuperAdmin ? 'bg-[var(--nb-purple)] text-white' : 'bg-[var(--nb-lime)] text-black'"
          >
            {{ authStore.user?.role === 'super_admin' ? 'Super Admin' : 'Petugas' }}
          </span>
        </div>
        <p class="text-xs sm:text-sm text-[var(--nb-muted)] mt-1 font-medium">
          Kelola data diri, username, alamat email, dan keamanan kata sandi akun Anda
        </p>
      </div>

      <div class="flex items-center gap-2">
        <NuxtLink
          to="/dashboard"
          class="nb-btn bg-white text-black font-bold text-xs border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-gray-100 flex items-center gap-1.5"
        >
          <AppIcon name="dashboard" class="w-3.5 h-3.5" />
          <span>Ke Dashboard</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Alert Notifications -->
    <div
      v-if="successMsg"
      class="p-4 rounded-xl bg-emerald-100 border-2 border-black shadow-[3px_3px_0_#0a0a0a] text-black text-xs sm:text-sm flex items-center justify-between gap-2 font-bold animate-fade-in"
    >
      <div class="flex items-center gap-2.5">
        <AppIcon name="check-circle" class="w-5 h-5 text-emerald-700 shrink-0" />
        <span>{{ successMsg }}</span>
      </div>
      <button class="p-1 hover:bg-black/10 rounded" @click="successMsg = ''">
        <AppIcon name="close" class="w-4 h-4" />
      </button>
    </div>

    <div
      v-if="errorMsg"
      class="p-4 rounded-xl bg-rose-100 border-2 border-black shadow-[3px_3px_0_#0a0a0a] text-rose-900 text-xs sm:text-sm flex items-center justify-between gap-2 font-bold animate-fade-in"
    >
      <div class="flex items-center gap-2.5">
        <AppIcon name="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0" />
        <span>{{ errorMsg }}</span>
      </div>
      <button class="p-1 hover:bg-black/10 rounded" @click="errorMsg = ''">
        <AppIcon name="close" class="w-4 h-4" />
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left (7 Cols): Edit Form -->
      <div class="lg:col-span-7 space-y-6">
        
        <!-- Form 1: Biodata & Data Akun -->
        <div class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl p-6 space-y-5">
          <div class="flex items-center gap-2.5 pb-3 border-b-2 border-black/10">
            <div class="w-9 h-9 rounded-xl bg-[var(--nb-lime)] border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] flex items-center justify-center text-black">
              <AppIcon name="user" class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-black text-[#0a0a0a]">Informasi Akun</h2>
              <p class="text-xs text-[var(--nb-muted)] font-medium">Perbarui nama lengkap, username, dan alamat email Anda</p>
            </div>
          </div>

          <form @submit.prevent="updateProfileInfo" class="space-y-4">
            <div>
              <label class="form-label font-bold text-xs text-black">Nama Lengkap</label>
              <input
                v-model="profileForm.name"
                type="text"
                class="form-input border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                placeholder="Nama Anda"
                required
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="form-label font-bold text-xs text-black">Username Akun</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-black/60 font-mono text-xs font-bold">
                    @
                  </div>
                  <input
                    v-model="profileForm.username"
                    type="text"
                    class="form-input pl-8 font-mono border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                    placeholder="username"
                    required
                  />
                </div>
              </div>

              <div>
                <label class="form-label font-bold text-xs text-black">Hak Akses (Role)</label>
                <input
                  :value="authStore.isSuperAdmin ? 'Super Administrator' : 'Petugas Kasir'"
                  disabled
                  class="form-input font-bold border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a] bg-gray-100 cursor-not-allowed opacity-80"
                />
              </div>
            </div>

            <div>
              <label class="form-label font-bold text-xs text-black">Alamat Email Terdaftar</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/60">
                  <AppIcon name="mail" class="w-4 h-4 text-black" />
                </div>
                <input
                  v-model="profileForm.email"
                  type="email"
                  class="form-input pl-10 border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                  placeholder="email@example.com"
                  required
                />
              </div>
              <p class="text-[11px] text-[var(--nb-muted)] mt-1 font-medium">
                Email ini digunakan untuk menerima kode OTP saat fitur pemulihan kata sandi dijalankan.
              </p>
            </div>

            <div class="pt-2">
              <button
                type="submit"
                class="nb-btn bg-[var(--nb-lime)] text-black font-extrabold text-xs py-2.5 px-5 border-2 border-black shadow-[3px_3px_0_#0a0a0a] hover:bg-[#d4f028] flex items-center gap-2"
                :disabled="savingProfile"
              >
                <svg v-if="savingProfile" class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span v-else class="flex items-center gap-1.5">
                  <AppIcon name="check" class="w-4 h-4" />
                  <span>Simpan Perubahan Akun</span>
                </span>
              </button>
            </div>
          </form>
        </div>

        <!-- Form 2: Ganti Password -->
        <div class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl p-6 space-y-5">
          <div class="flex items-center gap-2.5 pb-3 border-b-2 border-black/10">
            <div class="w-9 h-9 rounded-xl bg-[var(--nb-yellow)] border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] flex items-center justify-center text-black">
              <AppIcon name="lock" class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-black text-[#0a0a0a]">Keamanan & Kata Sandi</h2>
              <p class="text-xs text-[var(--nb-muted)] font-medium">Perbarui kata sandi login sistem Anda secara berkala</p>
            </div>
          </div>

          <form @submit.prevent="updatePassword" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="form-label font-bold text-xs text-black">Password Baru</label>
                <input
                  v-model="passwordForm.password"
                  type="password"
                  class="form-input border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                  placeholder="Minimal 8 karakter"
                  required
                />
              </div>

              <div>
                <label class="form-label font-bold text-xs text-black">Konfirmasi Password Baru</label>
                <input
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  class="form-input border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                  placeholder="Ketik ulang password baru"
                  required
                />
              </div>
            </div>

            <div class="pt-2">
              <button
                type="submit"
                class="nb-btn bg-[var(--nb-purple)] text-white font-extrabold text-xs py-2.5 px-5 border-2 border-black shadow-[3px_3px_0_#0a0a0a] hover:opacity-90 flex items-center gap-2"
                :disabled="savingPassword || !passwordForm.password"
              >
                <svg v-if="savingPassword" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span v-else class="flex items-center gap-1.5">
                  <AppIcon name="key" class="w-4 h-4" />
                  <span>Perbarui Password Baru</span>
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Right (5 Cols): Identity Card & Overview -->
      <div class="lg:col-span-5 space-y-6">
        <!-- Official ID Badge -->
        <div class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl p-6 text-center space-y-4">
          <!-- Big Avatar -->
          <div class="relative inline-block mx-auto mt-2">
            <div
              class="w-20 h-20 rounded-2xl border-3 border-black shadow-[3px_3px_0_#0a0a0a] flex items-center justify-center text-3xl font-black"
              :class="authStore.isSuperAdmin ? 'bg-[var(--nb-purple)] text-white' : 'bg-[var(--nb-lime)] text-black'"
            >
              {{ (authStore.user?.name || 'User').charAt(0).toUpperCase() }}
            </div>
            <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 border-2 border-black rounded-full shadow-sm" title="Aktif / Online"></span>
          </div>

          <div>
            <h3 class="text-xl font-black text-[#0a0a0a]">{{ authStore.user?.name }}</h3>
            <p class="font-mono text-xs font-bold text-[var(--nb-muted)] mt-0.5">@{{ authStore.user?.username }}</p>
            <p class="text-xs font-medium text-black/70 mt-1">{{ authStore.user?.email }}</p>
          </div>

          <!-- Role Pill -->
          <div class="inline-block">
            <span
              class="px-3 py-1 rounded-full text-xs font-black border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a]"
              :class="authStore.isSuperAdmin ? 'bg-purple-100 text-purple-900' : 'bg-lime-100 text-lime-900'"
            >
              {{ authStore.isSuperAdmin ? '🛡️ Super Administrator' : '🎫 Petugas Kasir Operasional' }}
            </span>
          </div>

          <!-- Permissions Overview -->
          <div class="pt-4 border-t-2 border-black/10 text-left text-xs space-y-2">
            <p class="font-black text-black uppercase tracking-wider text-[10px]">Wewenang & Hak Akses Akun:</p>
            <div v-if="authStore.isSuperAdmin" class="space-y-1.5 text-black/80 font-medium text-[11px]">
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✔</span>
                <span>Mengelola manajemen pengguna & petugas</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✔</span>
                <span>Mengatur tarif parkir motor & mobil</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✔</span>
                <span>Melihat laporan dan analitik menyeluruh</span>
              </div>
            </div>
            <div v-else class="space-y-1.5 text-black/80 font-medium text-[11px]">
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✔</span>
                <span>Mencetak tiket & melayani kasir keluar</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✔</span>
                <span>Verifikasi gate scan akses member</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✔</span>
                <span>Mendaftarkan member & menerima tagihan iuran</span>
              </div>
            </div>
          </div>

          <!-- System Status -->
          <div class="p-3 rounded-xl bg-[var(--nb-bg)] border-2 border-black text-left text-[11px] font-mono font-bold space-y-1">
            <div class="flex justify-between">
              <span class="text-[var(--nb-muted)]">Status Sistem:</span>
              <span class="text-emerald-700">Terotentikasi</span>
            </div>
            <div class="flex justify-between">
              <span class="text-[var(--nb-muted)]">Sesi Login:</span>
              <span class="text-black">Aktif (Sanctum)</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Profil Saya — E-Parkir Plaza Andalas' })

const authStore = useAuthStore()
const { apiFetch } = useApi()

const profileForm = reactive({
  name: '',
  username: '',
  email: '',
})

const passwordForm = reactive({
  password: '',
  password_confirmation: '',
})

const savingProfile = ref(false)
const savingPassword = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

function syncFromUser() {
  if (authStore.user) {
    profileForm.name = authStore.user.name || ''
    profileForm.username = authStore.user.username || ''
    profileForm.email = authStore.user.email || ''
  }
}

async function updateProfileInfo() {
  savingProfile.value = true
  successMsg.value = ''
  errorMsg.value = ''
  try {
    const res = await apiFetch<any>('/me', {
      method: 'PUT',
      body: {
        name: profileForm.name,
        username: profileForm.username,
        email: profileForm.email,
      },
    })

    const updatedUser = res.user || res.data
    if (updatedUser) {
      authStore.user = updatedUser
      if (import.meta.client) {
        localStorage.setItem('auth_user', JSON.stringify(updatedUser))
      }
    }

    successMsg.value = 'Informasi profil berhasil diperbarui!'
    setTimeout(() => { successMsg.value = '' }, 4000)
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memperbarui profil pengguna.'
  } finally {
    savingProfile.value = false
  }
}

async function updatePassword() {
  if (passwordForm.password !== passwordForm.password_confirmation) {
    errorMsg.value = 'Konfirmasi password baru tidak cocok.'
    return
  }

  savingPassword.value = true
  successMsg.value = ''
  errorMsg.value = ''
  try {
    await apiFetch<any>('/me', {
      method: 'PUT',
      body: {
        password: passwordForm.password,
        password_confirmation: passwordForm.password_confirmation,
      },
    })

    passwordForm.password = ''
    passwordForm.password_confirmation = ''
    successMsg.value = 'Kata sandi berhasil diperbarui dengan aman!'
    setTimeout(() => { successMsg.value = '' }, 4000)
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal memperbarui kata sandi.'
  } finally {
    savingPassword.value = false
  }
}

onMounted(() => {
  syncFromUser()
})
</script>

