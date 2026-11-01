<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-white">Data Keanggotaan Member</h1>
        <p class="text-xs text-slate-400 mt-1">Kelola data pelanggan parkir langganan bulanan dan pendaftaran baru</p>
      </div>
      <button
        class="btn btn-primary shrink-0 shadow-lg shadow-blue-600/20"
        @click="openForm()"
      >
        <AppIcon name="plus" class="w-4 h-4" />
        <span>Daftar Member Baru</span>
      </button>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="glass-card p-4 flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto flex-1">
        <div class="relative w-full sm:max-w-xs">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
            <AppIcon name="search" class="w-4 h-4" />
          </div>
          <input
            v-model="search"
            type="text"
            class="form-input pl-10"
            placeholder="Cari nama, plat, atau kode member..."
            @input="debouncedSearch"
          />
        </div>

        <select
          v-model="filterActive"
          class="form-input w-full sm:w-44"
          @change="page = 1; loadMembers()"
        >
          <option value="">Semua Status</option>
          <option value="true">Status: Aktif</option>
          <option value="false">Status: Non-Aktif</option>
        </select>
      </div>

      <div class="text-xs text-slate-400 font-mono w-full sm:w-auto text-right">
        Total: <span class="text-white font-semibold">{{ meta.total }}</span> member terdaftar
      </div>
    </div>

    <!-- Data Table -->
    <div class="glass-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Kode Member</th>
              <th>Nama Lengkap</th>
              <th>Plat Nomor</th>
              <th>Kategori</th>
              <th>Iuran / Bulan</th>
              <th>Status</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="text-center py-12 text-slate-500 text-xs">
                <div class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <span>Memuat data member...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="!members.length">
              <td colspan="7" class="text-center py-12 text-slate-400">
                <AppIcon name="users" class="w-8 h-8 mx-auto mb-2 text-slate-600" />
                <p class="text-sm font-medium text-slate-300">Tidak ada member ditemukan</p>
                <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
              </td>
            </tr>

            <tr v-for="m in members" :key="m.id">
              <td>
                <span class="inline-flex items-center gap-1.5 font-mono text-xs font-bold px-2 py-1 rounded bg-blue-500/10 text-blue-300 border border-blue-500/25">
                  <AppIcon name="scan" class="w-3.5 h-3.5" />
                  <span>{{ m.member_code }}</span>
                </span>
              </td>
              <td>
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-slate-800 to-slate-700 flex items-center justify-center text-xs font-bold text-slate-200">
                    {{ m.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="font-semibold text-white">{{ m.name }}</span>
                </div>
              </td>
              <td>
                <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-slate-950 text-slate-200 border border-slate-700/80">
                  {{ m.plate_number }}
                </span>
              </td>
              <td>
                <span class="inline-flex items-center gap-1.5 text-xs text-slate-300">
                  <AppIcon :name="m.vehicle_category === 'motor' ? 'motorcycle' : 'car'" class="w-3.5 h-3.5 text-slate-400" />
                  <span class="capitalize">{{ m.vehicle_category }}</span>
                </span>
              </td>
              <td class="font-mono font-semibold text-emerald-400">
                {{ formatRupiah(m.monthly_fee) }}
              </td>
              <td>
                <span
                  class="text-xs px-2.5 py-0.5 rounded-full font-medium"
                  :class="m.is_active ? 'badge-active' : 'badge-inactive'"
                >
                  {{ m.is_active ? 'Aktif' : 'Non-Aktif' }}
                </span>
              </td>
              <td class="text-right">
                <div class="inline-flex items-center gap-2">
                  <button
                    class="btn btn-secondary text-xs py-1 px-2.5"
                    @click="viewBills(m)"
                    title="Lihat riwayat tagihan"
                  >
                    <AppIcon name="credit-card" class="w-3.5 h-3.5" />
                    <span>Tagihan</span>
                  </button>
                  <button
                    class="btn btn-outline text-xs py-1 px-2.5"
                    @click="openForm(m)"
                    title="Edit data member"
                  >
                    <AppIcon name="edit" class="w-3.5 h-3.5" />
                    <span>Edit</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <PaginationBar :meta="meta" :page="page" @prev="page--; loadMembers()" @next="page++; loadMembers()" />
    </div>

    <!-- Registration & Edit Modal -->
    <Teleport to="body">
      <div
        v-if="showForm"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
      >
        <div class="glass-card w-full max-w-md p-6 space-y-5 bg-[#0d1322] border-slate-700 shadow-2xl animate-fade-in">
          <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center gap-2">
              <AppIcon name="user" class="w-5 h-5 text-blue-400" />
              <h2 class="text-base font-bold text-white">
                {{ editingMember ? 'Edit Data Member' : 'Pendaftaran Member Baru' }}
              </h2>
            </div>
            <button class="p-1 text-slate-400 hover:text-white rounded-lg transition-colors" @click="showForm = false">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="form-label">Nama Lengkap Pelanggan</label>
              <input
                v-model="form.name"
                type="text"
                class="form-input"
                :class="{ error: formErrors.name }"
                placeholder="Misal: Budi Santoso"
              />
              <p v-if="formErrors.name" class="text-xs mt-1 text-rose-400">{{ formErrors.name }}</p>
            </div>

            <div>
              <label class="form-label">Plat Nomor Kendaraan</label>
              <input
                v-model="form.plate_number"
                type="text"
                class="form-input font-mono uppercase font-bold"
                :class="{ error: formErrors.plate_number }"
                placeholder="Misal: BA 1234 XY"
                @input="form.plate_number = form.plate_number.toUpperCase()"
              />
              <p v-if="formErrors.plate_number" class="text-xs mt-1 text-rose-400">{{ formErrors.plate_number }}</p>
            </div>

            <div>
              <label class="form-label">Kategori Kendaraan</label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border text-xs font-semibold transition-all"
                  :class="form.vehicle_category === 'motor'
                    ? 'bg-blue-600/20 border-blue-500 text-blue-300'
                    : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200'"
                  @click="form.vehicle_category = 'motor'"
                >
                  <AppIcon name="motorcycle" class="w-4 h-4" />
                  <span>Sepeda Motor</span>
                </button>
                <button
                  type="button"
                  class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border text-xs font-semibold transition-all"
                  :class="form.vehicle_category === 'mobil'
                    ? 'bg-blue-600/20 border-blue-500 text-blue-300'
                    : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200'"
                  @click="form.vehicle_category = 'mobil'"
                >
                  <AppIcon name="car" class="w-4 h-4" />
                  <span>Mobil Roda Empat</span>
                </button>
              </div>
              <p v-if="formErrors.vehicle_category" class="text-xs mt-1 text-rose-400">{{ formErrors.vehicle_category }}</p>
            </div>

            <div v-if="editingMember">
              <label class="form-label">Status Keanggotaan</label>
              <select v-model="form.is_active" class="form-input">
                <option :value="true">Aktif (Bisa Menggunakan Gate)</option>
                <option :value="false">Non-Aktif (Akses Gate Ditangguhkan)</option>
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
              @click="saveMember"
              :disabled="saving"
            >
              {{ saving ? 'Menyimpan...' : (editingMember ? 'Simpan Perubahan' : 'Daftarkan Member') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Member Bills Modal -->
    <Teleport to="body">
      <div
        v-if="showBills"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
      >
        <div class="glass-card w-full max-w-lg p-6 space-y-4 bg-[#0d1322] border-slate-700 shadow-2xl animate-fade-in">
          <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div>
              <h2 class="text-base font-bold text-white">Riwayat Tagihan Member</h2>
              <p class="text-xs text-slate-400 mt-0.5">
                <span class="text-white font-semibold">{{ selectedMember?.name }}</span>
                <span class="mx-1.5">·</span>
                <span class="font-mono text-blue-400">{{ selectedMember?.member_code }}</span>
              </p>
            </div>
            <button class="p-1 text-slate-400 hover:text-white rounded-lg transition-colors" @click="showBills = false">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
            <div v-if="!bills.length" class="text-center py-10 text-xs text-slate-500">
              Tidak ada catatan tagihan bulanan
            </div>

            <div
              v-for="bill in bills"
              :key="bill.id"
              class="flex items-center justify-between p-3.5 rounded-xl bg-slate-900/80 border border-slate-800"
            >
              <div>
                <p class="text-xs font-semibold text-white capitalize">{{ formatPeriod(bill.period) }}</p>
                <p class="text-[11px] font-mono text-slate-400 mt-0.5">
                  {{ bill.invoice_number ? bill.invoice_number : 'Menunggu Pembayaran' }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-xs font-mono font-bold text-white">{{ formatRupiah(bill.amount) }}</p>
                <span
                  class="text-[10px] px-2 py-0.5 rounded-full font-medium inline-block mt-1"
                  :class="bill.status === 'lunas' ? 'badge-active' : 'badge-pending'"
                >
                  {{ bill.status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                </span>
              </div>
            </div>
          </div>

          <NuxtLink
            :to="`/tagihan?search=${selectedMember?.plate_number || ''}`"
            class="btn btn-outline w-full text-xs"
            @click="showBills = false"
          >
            <span>Buka Halaman Pembayaran Kasir Tagihan</span>
            <AppIcon name="arrow-right" class="w-3.5 h-3.5" />
          </NuxtLink>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'
import PaginationBar from '~/components/ui/PaginationBar.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Data Member — E-Parkir Plaza Andalas' })

const { apiFetch } = useApi()

const members = ref<any[]>([])
const loading = ref(true)
const search = ref('')
const filterActive = ref('')
const page = ref(1)
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 15 })

// Form
const showForm = ref(false)
const editingMember = ref<any>(null)
const saving = ref(false)
const formError = ref('')
const formErrors = reactive<Record<string, string>>({})
const form = reactive({
  name: '',
  plate_number: '',
  vehicle_category: 'motor',
  is_active: true,
})

// Bills modal
const showBills = ref(false)
const selectedMember = ref<any>(null)
const bills = ref<any[]>([])

function formatRupiah(n: number) {
  return 'Rp ' + (n || 0).toLocaleString('id-ID')
}

function formatPeriod(d: string) {
  return new Date(d).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
}

let searchTimer: ReturnType<typeof setTimeout>
function debouncedSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    loadMembers()
  }, 350)
}

async function loadMembers() {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: String(page.value) })
    if (search.value.trim()) params.append('search', search.value.trim())
    if (filterActive.value) params.append('is_active', filterActive.value)

    const data = await apiFetch<any>(`/members?${params}`)
    members.value = data.data
    meta.value = data.meta
  } catch {} finally {
    loading.value = false
  }
}

function openForm(member?: any) {
  editingMember.value = member ?? null
  formError.value = ''
  Object.keys(formErrors).forEach(k => delete (formErrors as any)[k])
  if (member) {
    form.name = member.name
    form.plate_number = member.plate_number
    form.vehicle_category = member.vehicle_category
    form.is_active = member.is_active
  } else {
    form.name = ''
    form.plate_number = ''
    form.vehicle_category = 'motor'
    form.is_active = true
  }
  showForm.value = true
}

async function saveMember() {
  saving.value = true
  formError.value = ''
  Object.keys(formErrors).forEach(k => delete (formErrors as any)[k])
  try {
    if (editingMember.value) {
      await apiFetch(`/members/${editingMember.value.id}`, { method: 'PUT', body: form })
    } else {
      await apiFetch('/members', { method: 'POST', body: form })
    }
    showForm.value = false
    loadMembers()
  } catch (e: any) {
    if (e?.response?.status === 422) {
      const errs = e?.data?.errors ?? {}
      Object.assign(formErrors, Object.fromEntries(Object.entries(errs).map(([k, v]) => [k, (v as string[])[0]])))
    } else {
      formError.value = e?.data?.message ?? 'Terjadi kesalahan saat menyimpan member'
    }
  } finally {
    saving.value = false
  }
}

async function viewBills(member: any) {
  selectedMember.value = member
  showBills.value = true
  bills.value = []
  try {
    const data = await apiFetch<any>(`/members/${member.id}/bills?per_page=12`)
    bills.value = data.data
  } catch {}
}

onMounted(loadMembers)
</script>
