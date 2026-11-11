<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-[#0a0a0a]">Manajemen Akun Pengguna</h1>
        <p class="text-xs text-[var(--nb-muted)] mt-1 font-medium">Kelola hak akses petugas loket operasional dan administrator sistem</p>
      </div>

      <button class="nb-btn bg-[var(--nb-lime)] text-[#0a0a0a] font-bold border-2 border-black shadow-[3px_3px_0_#0a0a0a] hover:bg-[#d4f028]" @click="openForm()">
        <AppIcon name="plus" class="w-4 h-4" />
        <span>Tambah Pengguna Baru</span>
      </button>
    </div>

    <!-- Table -->
    <div class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] overflow-hidden">
      <div class="px-6 py-4 border-b-2 border-black flex items-center justify-between bg-[var(--nb-yellow)]/20">
        <div class="flex items-center gap-2">
          <AppIcon name="user" class="w-4 h-4 text-black" />
          <h2 class="text-sm font-black text-black">Daftar Akun Petugas & Administrator</h2>
        </div>
        <span class="text-xs text-black font-mono font-bold bg-white px-2.5 py-0.5 rounded-full border-2 border-black shadow-[1px_1px_0_#0a0a0a]">{{ users.length }} akun</span>
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
              <td colspan="6" class="text-center py-12 text-[var(--nb-muted)] text-xs">
                <div class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
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
                  <div class="w-8 h-8 rounded-lg bg-[var(--nb-purple)] border-2 border-black flex items-center justify-center text-xs font-black text-white shadow-[1px_1px_0_#0a0a0a]">
                    {{ u.name?.charAt(0).toUpperCase() || 'U' }}
                  </div>
                  <div>
                    <p class="font-bold text-[#0a0a0a]">{{ u.name }}</p>
                    <span v-if="u.id === authStore.user?.id" class="text-[10px] text-black font-bold px-1 rounded bg-[var(--nb-lime)] border border-black inline-block mt-0.5">Akun Anda Saat Ini</span>
                  </div>
                </div>
              </td>
              <td>
                <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-[var(--nb-bg)] text-black border-2 border-black shadow-[1px_1px_0_#0a0a0a]">
                  {{ u.username }}
                </span>
              </td>
              <td class="text-xs text-[var(--nb-ink)] font-medium">{{ u.email }}</td>
              <td>
                <span
                  class="inline-flex items-center gap-1.5 text-xs px-2.5 py-0.5 rounded-full font-bold border-2 border-black shadow-[1px_1px_0_#0a0a0a]"
                  :class="u.role === 'super_admin'
                    ? 'bg-[var(--nb-purple)] text-white'
                    : 'bg-[var(--nb-teal)]/30 text-black'"
                >
                  <AppIcon :name="u.role === 'super_admin' ? 'shield' : 'user'" class="w-3.5 h-3.5" />
                  <span>{{ u.role === 'super_admin' ? 'Super Admin' : 'Petugas' }}</span>
                </span>
              </td>
              <td class="text-xs font-mono text-[var(--nb-ink)] font-medium">{{ formatDate(u.created_at) }}</td>
              <td class="text-right">
                <div class="inline-flex items-center gap-2">
                  <button
                    class="nb-btn bg-white text-black border-2 border-black shadow-[2px_2px_0_#0a0a0a] text-xs font-bold py-1 px-2.5 hover:bg-gray-100"
                    @click="openForm(u)"
                    title="Edit Akun"
                  >
                    <AppIcon name="edit" class="w-3.5 h-3.5" />
                    <span>Edit</span>
                  </button>
                  <button
                    v-if="u.id !== authStore.user?.id"
                    class="nb-btn bg-rose-100 text-rose-900 border-2 border-black shadow-[2px_2px_0_#0a0a0a] text-xs font-bold py-1 px-2.5 hover:bg-rose-200"
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
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
      >
        <div class="nb-modal bg-white border-3 border-black shadow-[8px_8px_0_#0a0a0a] w-full max-w-md p-6 space-y-5 animate-fade-in">
          <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-[var(--nb-lime)] border-2 border-black flex items-center justify-center">
                <AppIcon name="user" class="w-4 h-4 text-black" />
              </div>
              <h2 class="text-base font-black text-black">{{ editing ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' }}</h2>
            </div>
            <button class="p-1 text-black hover:bg-black/10 rounded-md transition-colors" @click="showForm = false">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="form-label text-black font-bold">Nama Lengkap</label>
              <input v-model="form.name" type="text" class="form-input bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]" placeholder="Misal: Andi Prasetyo" required />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="form-label text-black font-bold">Username</label>
                <input v-model="form.username" type="text" class="form-input font-mono bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]" placeholder="petugas2" required />
              </div>
              <div>
                <label class="form-label text-black font-bold">Email</label>
                <input v-model="form.email" type="email" class="form-input bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]" placeholder="andi@parkir.test" required />
              </div>
            </div>

            <div>
              <label class="form-label text-black font-bold">
                Password {{ editing ? '(Kosongkan jika tidak diganti)' : '' }}
              </label>
              <input
                v-model="form.password"
                type="password"
                class="form-input bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                placeholder="Minimal 8 karakter"
                :required="!editing"
              />
            </div>

            <div>
              <label class="form-label text-black font-bold">Hak Akses (Role)</label>
              <select v-model="form.role" class="form-input bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a] font-medium">
                <option value="petugas">Petugas Loket Operasional</option>
                <option value="super_admin">Super Administrator (Akses Penuh)</option>
              </select>
            </div>
          </div>

          <div v-if="formError" class="p-3 rounded-xl bg-rose-100 border-2 border-black text-rose-900 text-xs font-bold shadow-[2px_2px_0_#0a0a0a]">
            {{ formError }}
          </div>

          <div class="flex gap-3 pt-2">
            <button class="nb-btn bg-white text-black flex-1 text-xs font-bold border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-gray-100" @click="showForm = false">Batal</button>
            <button
              class="nb-btn bg-[var(--nb-lime)] text-black flex-1 text-xs font-black border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-[#d4f028]"
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
