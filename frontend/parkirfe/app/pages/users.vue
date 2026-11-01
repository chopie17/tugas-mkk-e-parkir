<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-white">Manajemen Akun Pengguna</h1>
        <p class="text-xs text-slate-400 mt-1">Kelola hak akses petugas loket operasional dan administrator sistem</p>
      </div>

      <button class="btn btn-primary shadow-lg shadow-blue-600/20" @click="openForm()">
        <AppIcon name="plus" class="w-4 h-4" />
        <span>Tambah Pengguna Baru</span>
      </button>
    </div>

    <!-- Table -->
    <div class="glass-card overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-800/80 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <AppIcon name="user" class="w-4 h-4 text-slate-400" />
          <h2 class="text-sm font-semibold text-white">Daftar Akun Petugas & Administrator</h2>
        </div>
        <span class="text-xs text-slate-400 font-mono">{{ users.length }} akun</span>
      </div>

      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Nama Pengguna</th>
              <th>Username</th>
              <th>Alamat Email</th>
              <th>Hak Akses (Role)</th>
              <th>Tanggal Dibuat</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="6" class="text-center py-12 text-slate-500 text-xs">
                <div class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <span>Memuat akun pengguna...</span>
                </div>
              </td>
            </tr>

            <tr v-for="u in users" :key="u.id">
              <td>
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-slate-800 to-slate-700 flex items-center justify-center text-xs font-bold text-slate-200">
                    {{ u.name?.charAt(0).toUpperCase() || 'U' }}
                  </div>
                  <div>
                    <p class="font-semibold text-white">{{ u.name }}</p>
                    <span v-if="u.id === authStore.user?.id" class="text-[10px] text-blue-400 font-medium">Akun Anda Saat Ini</span>
                  </div>
                </div>
              </td>
              <td>
                <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-slate-800/80 text-blue-300 border border-slate-700/60">
                  {{ u.username }}
                </span>
              </td>
              <td class="text-xs text-slate-300">{{ u.email }}</td>
              <td>
                <span
                  class="inline-flex items-center gap-1.5 text-xs px-2.5 py-0.5 rounded-full font-medium"
                  :class="u.role === 'super_admin'
                    ? 'bg-purple-500/15 text-purple-300 border border-purple-500/30'
                    : 'bg-blue-500/15 text-blue-300 border border-blue-500/30'"
                >
                  <AppIcon :name="u.role === 'super_admin' ? 'shield' : 'user'" class="w-3.5 h-3.5" />
                  <span>{{ u.role === 'super_admin' ? 'Super Admin' : 'Petugas' }}</span>
                </span>
              </td>
              <td class="text-xs font-mono text-slate-400">{{ formatDate(u.created_at) }}</td>
              <td class="text-right">
                <div class="inline-flex items-center gap-2">
                  <button
                    class="btn btn-secondary text-xs py-1 px-2.5"
                    @click="openForm(u)"
                    title="Edit Akun"
                  >
                    <AppIcon name="edit" class="w-3.5 h-3.5" />
                    <span>Edit</span>
                  </button>
                  <button
                    v-if="u.id !== authStore.user?.id"
                    class="btn btn-danger text-xs py-1 px-2.5"
                    @click="deleteUser(u)"
                    title="Hapus Akun"
                  >
                    <AppIcon name="trash" class="w-3.5 h-3.5" />
                    <span>Hapus</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <Teleport to="body">
      <div
        v-if="showForm"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
      >
        <div class="glass-card w-full max-w-md p-6 space-y-5 bg-[#0d1322] border-slate-700 shadow-2xl animate-fade-in">
          <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center gap-2">
              <AppIcon name="user" class="w-5 h-5 text-blue-400" />
              <h2 class="text-base font-bold text-white">{{ editing ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' }}</h2>
            </div>
            <button class="p-1 text-slate-400 hover:text-white rounded-lg transition-colors" @click="showForm = false">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="form-label">Nama Lengkap</label>
              <input v-model="form.name" type="text" class="form-input" placeholder="Misal: Andi Prasetyo" required />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="form-label">Username</label>
                <input v-model="form.username" type="text" class="form-input font-mono" placeholder="petugas2" required />
              </div>
              <div>
                <label class="form-label">Email</label>
                <input v-model="form.email" type="email" class="form-input" placeholder="andi@parkir.test" required />
              </div>
            </div>

            <div>
              <label class="form-label">
                Password {{ editing ? '(Kosongkan jika tidak diganti)' : '' }}
              </label>
              <input
                v-model="form.password"
                type="password"
                class="form-input"
                placeholder="Minimal 8 karakter"
                :required="!editing"
              />
            </div>

            <div>
              <label class="form-label">Hak Akses (Role)</label>
              <select v-model="form.role" class="form-input">
                <option value="petugas">Petugas Loket Operasional</option>
                <option value="super_admin">Super Administrator (Akses Penuh)</option>
              </select>
            </div>
          </div>

          <div v-if="formError" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
            {{ formError }}
          </div>

          <div class="flex gap-3 pt-2">
            <button class="btn btn-ghost flex-1 text-xs" @click="showForm = false">Batal</button>
            <button
              class="btn btn-primary flex-1 text-xs font-semibold"
              @click="saveUser"
              :disabled="saving"
            >
              {{ saving ? 'Menyimpan...' : 'Simpan Akun' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Manajemen Pengguna — E-Parkir Plaza Andalas' })

const { apiFetch } = useApi()
const authStore = useAuthStore()

const users = ref<any[]>([])
const loading = ref(true)
const showForm = ref(false)
const editing = ref<any>(null)
const saving = ref(false)
const formError = ref('')
const form = reactive({ name: '', username: '', email: '', password: '', role: 'petugas' })

function formatDate(d: string) {
  return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'
}

async function loadUsers() {
  loading.value = true
  try {
    const data = await apiFetch<any>('/users')
    users.value = data.users
  } catch {} finally {
    loading.value = false
  }
}

function openForm(user?: any) {
  editing.value = user ?? null
  formError.value = ''
  if (user) {
    form.name = user.name
    form.username = user.username
    form.email = user.email
    form.password = ''
    form.role = user.role
  } else {
    Object.assign(form, { name: '', username: '', email: '', password: '', role: 'petugas' })
  }
  showForm.value = true
}

async function saveUser() {
  saving.value = true
  formError.value = ''
  try {
    const body: any = { ...form }
    if (editing.value && !body.password) delete body.password
    if (editing.value) {
      await apiFetch(`/users/${editing.value.id}`, { method: 'PUT', body })
    } else {
      await apiFetch('/users', { method: 'POST', body })
    }
    showForm.value = false
    loadUsers()
  } catch (e: any) {
    formError.value = e?.data?.message ?? 'Gagal menyimpan akun pengguna'
  } finally {
    saving.value = false
  }
}

async function deleteUser(user: any) {
  if (!confirm(`Konfirmasi: Hapus akun pengguna "${user.name}"?`)) return
  try {
    await apiFetch(`/users/${user.id}`, { method: 'DELETE' })
    loadUsers()
  } catch (e: any) {
    alert(e?.data?.message ?? 'Gagal menghapus akun pengguna')
  }
}

onMounted(loadUsers)
</script>
