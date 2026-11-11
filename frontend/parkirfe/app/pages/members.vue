<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1">
      <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#0a0a0a]">Data Keanggotaan Member</h1>
        <p class="text-xs sm:text-sm text-[var(--nb-muted)] mt-1 font-medium">Kelola data pelanggan parkir langganan bulanan dan pendaftaran baru</p>
      </div>
      <button
        class="nb-btn nb-btn-md bg-[var(--nb-lime,#e4ff3a)] text-black border-2 border-black font-extrabold shadow-[3px_3px_0_#0a0a0a] shrink-0 flex items-center gap-2"
        @click="openForm()"
      >
        <AppIcon name="plus" class="w-4 h-4" />
        <span>Daftar Member Baru</span>
      </button>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="nb-card p-4 bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto flex-1">
        <div class="relative w-full sm:max-w-xs">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <AppIcon name="search" class="w-4 h-4 text-black" />
          </div>
          <input
            v-model="search"
            type="text"
            class="form-input pl-10 border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
            placeholder="Cari nama, plat, atau kode member..."
            @input="debouncedSearch"
          />
        </div>

        <select
          v-model="filterActive"
          class="form-input w-full sm:w-44 border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a] font-bold"
          @change="page = 1; loadMembers()"
        >
          <option value="">Semua Status</option>
          <option value="true">Status: Aktif</option>
          <option value="false">Status: Non-Aktif</option>
        </select>
      </div>

      <div class="text-xs text-[var(--nb-muted)] font-mono font-bold w-full sm:w-auto text-right">
        Total: <span class="text-[#0a0a0a] font-black text-sm">{{ meta.total }}</span> member terdaftar
      </div>
    </div>

    <!-- Data Table -->
    <div class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl overflow-hidden">
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
                <div class="flex items-center justify-center gap-2 font-bold">
                  <svg class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <span>Memuat data member...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="!members.length">
              <td colspan="7" class="text-center py-12 text-slate-500">
                <AppIcon name="users" class="w-8 h-8 mx-auto mb-2 text-slate-400" />
                <p class="text-sm font-bold text-slate-700">Tidak ada member ditemukan</p>
                <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
              </td>
            </tr>

            <tr v-for="m in members" :key="m.id">
              <td>
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-[#dbeafe] text-[#1e40af] border-2 border-[#0a0a0a] shadow-[1.5px_1.5px_0_#0a0a0a] hover:bg-[var(--nb-lime,#e4ff3a)] hover:text-black transition-all cursor-pointer"
                  @click="openMemberCard(m)"
                  title="Klik untuk melihat kartu & barcode"
                >
                  <AppIcon name="scan" class="w-3.5 h-3.5" />
                  <span>{{ m.member_code }}</span>
                </button>
              </td>
              <td>
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-lg bg-[var(--nb-purple,#6c47ff)] border-2 border-black flex items-center justify-center text-xs font-black text-white">
                    {{ m.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="font-extrabold text-[#0a0a0a]">{{ m.name }}</span>
                </div>
              </td>
              <td class="whitespace-nowrap">
                <span class="font-mono text-[11px] font-black px-2 py-0.5 rounded-lg bg-[#f1f1eb] text-[#0a0a0a] border-2 border-[#0a0a0a] whitespace-nowrap inline-block">
                  {{ m.plate_number }}
                </span>
              </td>
              <td>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-800">
                  <AppIcon :name="m.vehicle_category === 'motor' ? 'motorcycle' : 'car'" class="w-3.5 h-3.5 text-black" />
                  <span class="capitalize">{{ m.vehicle_category }}</span>
                </span>
              </td>
              <td class="font-mono font-black text-[#0a0a0a]">
                {{ formatRupiah(m.monthly_fee) }}
              </td>
              <td>
                <span
                  class="text-xs px-2.5 py-0.5 rounded-full font-bold border border-black shadow-[1px_1px_0_#0a0a0a]"
                  :class="m.is_active ? 'badge-active' : 'badge-inactive'"
                >
                  {{ m.is_active ? 'Aktif' : 'Non-Aktif' }}
                </span>
              </td>
              <td class="text-right">
                <div class="inline-flex items-center gap-1.5">
                  <button
                    class="nb-btn nb-btn-sm bg-[var(--nb-lime,#e4ff3a)] text-black font-extrabold"
                    @click="openMemberCard(m)"
                    title="Lihat & Cetak Kartu Member (Barcode / QR Code)"
                  >
                    <AppIcon name="scan" class="w-3.5 h-3.5" />
                    <span>Kartu</span>
                  </button>
                  <button
                    class="nb-btn nb-btn-sm bg-white"
                    @click="viewBills(m)"
                    title="Lihat riwayat tagihan"
                  >
                    <AppIcon name="credit-card" class="w-3.5 h-3.5" />
                    <span>Tagihan</span>
                  </button>
                  <button
                    class="nb-btn nb-btn-sm bg-white"
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
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
      >
        <div class="nb-modal w-full max-w-md p-6 space-y-4 bg-white border-3 border-black shadow-[8px_8px_0_#0a0a0a] rounded-3xl animate-fade-in text-[#0a0a0a]">
          <div class="flex items-center justify-between pb-3 border-b-2 border-black/10">
            <div class="flex items-center gap-2">
              <AppIcon name="user" class="w-5 h-5 text-black" />
              <h2 class="text-base font-black text-[#0a0a0a]">
                {{ editingMember ? 'Edit Data Member' : 'Pendaftaran Member Baru' }}
              </h2>
            </div>
            <button class="p-1 text-slate-500 hover:text-black rounded-lg transition-colors border border-transparent hover:border-black" @click="showForm = false">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="form-label font-bold text-xs">Nama Lengkap Pelanggan</label>
              <input
                v-model="form.name"
                type="text"
                class="form-input border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                :class="{ error: formErrors.name }"
                placeholder="Misal: Budi Santoso"
              />
              <p v-if="formErrors.name" class="text-xs mt-1 text-rose-600 font-bold">{{ formErrors.name }}</p>
            </div>

            <div>
              <label class="form-label font-bold text-xs">Plat Nomor Kendaraan</label>
              <input
                v-model="form.plate_number"
                type="text"
                class="form-input font-mono uppercase font-black border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                :class="{ error: formErrors.plate_number }"
                placeholder="Misal: BA 1234 XY"
                @input="form.plate_number = form.plate_number.toUpperCase()"
              />
              <p v-if="formErrors.plate_number" class="text-xs mt-1 text-rose-600 font-bold">{{ formErrors.plate_number }}</p>
            </div>

            <div>
              <label class="form-label font-bold text-xs">Kategori Kendaraan</label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border-2 border-black text-xs font-extrabold transition-all shadow-[2px_2px_0_#0a0a0a]"
                  :class="form.vehicle_category === 'motor'
                    ? 'bg-[var(--nb-lime,#e4ff3a)] text-black'
                    : 'bg-white text-slate-600 hover:bg-[#f1f1eb]'"
                  @click="form.vehicle_category = 'motor'"
                >
                  <AppIcon name="motorcycle" class="w-4 h-4" />
                  <span>Sepeda Motor</span>
                </button>
                <button
                  type="button"
                  class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border-2 border-black text-xs font-extrabold transition-all shadow-[2px_2px_0_#0a0a0a]"
                  :class="form.vehicle_category === 'mobil'
                    ? 'bg-[var(--nb-lime,#e4ff3a)] text-black'
                    : 'bg-white text-slate-600 hover:bg-[#f1f1eb]'"
                  @click="form.vehicle_category = 'mobil'"
                >
                  <AppIcon name="car" class="w-4 h-4" />
                  <span>Mobil Roda Empat</span>
                </button>
              </div>
              <p v-if="formErrors.vehicle_category" class="text-xs mt-1 text-rose-600 font-bold">{{ formErrors.vehicle_category }}</p>
            </div>

            <div v-if="editingMember">
              <label class="form-label font-bold text-xs">Status Keanggotaan</label>
              <select v-model="form.is_active" class="form-input border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a] font-bold">
                <option :value="true">Aktif (Bisa Menggunakan Gate)</option>
                <option :value="false">Non-Aktif (Akses Gate Ditangguhkan)</option>
              </select>
            </div>
          </div>

          <div v-if="formError" class="p-3 rounded-xl bg-[#fee2e2] border-2 border-[#0a0a0a] text-rose-800 text-xs font-bold shadow-[2px_2px_0_#0a0a0a]">
            {{ formError }}
          </div>

          <div class="flex gap-3 pt-2">
            <button class="nb-btn nb-btn-md bg-white flex-1 text-xs" @click="showForm = false">Batal</button>
            <button
              class="nb-btn nb-btn-md bg-[var(--nb-lime,#e4ff3a)] text-black font-extrabold flex-1 text-xs"
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
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
      >
        <div class="nb-modal w-full max-w-lg p-6 space-y-4 bg-white border-3 border-black shadow-[8px_8px_0_#0a0a0a] rounded-3xl animate-fade-in text-[#0a0a0a]">
          <div class="flex items-center justify-between pb-3 border-b-2 border-black/10">
            <div>
              <h2 class="text-base font-black text-[#0a0a0a]">Riwayat Tagihan Member</h2>
              <p class="text-xs text-[var(--nb-muted)] mt-0.5 font-bold">
                <span class="text-[#0a0a0a]">{{ selectedMember?.name }}</span>
                <span class="mx-1.5">·</span>
                <span class="font-mono text-blue-700">{{ selectedMember?.member_code }}</span>
              </p>
            </div>
            <button class="p-1 text-slate-500 hover:text-black rounded-lg transition-colors border border-transparent hover:border-black" @click="showBills = false">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
            <div v-if="!bills.length" class="text-center py-10 text-xs text-slate-500 font-bold">
              Tidak ada catatan tagihan bulanan
            </div>

            <div
              v-for="bill in bills"
              :key="bill.id"
              class="flex items-center justify-between p-3.5 rounded-2xl bg-[#f8fafc] border-2 border-black shadow-[2px_2px_0_#0a0a0a]"
            >
              <div>
                <p class="text-xs font-black text-[#0a0a0a] capitalize">{{ formatPeriod(bill.period) }}</p>
                <p class="text-[11px] font-mono text-[var(--nb-muted)] font-bold mt-0.5">
                  {{ bill.invoice_number ? bill.invoice_number : 'Menunggu Pembayaran' }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-xs font-mono font-black text-[#0a0a0a]">{{ formatRupiah(bill.amount) }}</p>
                <span
                  class="text-[10px] px-2 py-0.5 rounded-full font-bold border border-black inline-block mt-1 shadow-[1px_1px_0_#0a0a0a]"
                  :class="bill.status === 'lunas' ? 'bg-[#dcfce7] text-[#14532d]' : 'bg-[#fef08a] text-[#713f12]'"
                >
                  {{ bill.status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                </span>
              </div>
            </div>
          </div>

          <NuxtLink
            :to="`/tagihan?search=${selectedMember?.plate_number || ''}`"
            class="nb-btn nb-btn-md bg-white w-full text-xs text-center flex items-center justify-center gap-1.5"
            @click="showBills = false"
          >
            <span>Buka Halaman Pembayaran Kasir Tagihan</span>
            <AppIcon name="arrow-right" class="w-3.5 h-3.5" />
          </NuxtLink>
        </div>
      </div>
    </Teleport>

    <!-- Modal Kartu Member Barcode & QR Code -->
    <MemberCardModal
      v-model="showCardModal"
      :member="selectedMemberCard"
    />
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'
import PaginationBar from '~/components/ui/PaginationBar.vue'
import MemberCardModal from '~/components/ui/MemberCardModal.vue'

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

// Member Card Modal
const showCardModal = ref(false)
const selectedMemberCard = ref<any>(null)

function openMemberCard(member: any) {
  selectedMemberCard.value = member
  showCardModal.value = true
}

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
    let createdMember: any = null
    if (editingMember.value) {
      await apiFetch(`/members/${editingMember.value.id}`, { method: 'PUT', body: form })
    } else {
      const res = await apiFetch<any>('/members', { method: 'POST', body: form })
      createdMember = res?.member ?? res?.data
    }
    showForm.value = false
    await loadMembers()

    // Buka kartu member yang baru diterbitkan secara otomatis
    if (createdMember) {
      openMemberCard(createdMember)
    }
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
