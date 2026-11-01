<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-white">Kasir Tagihan Member</h1>
        <p class="text-xs text-slate-400 mt-1">Pengelolaan dan pembayaran iuran langganan parkir bulanan member</p>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="glass-card p-4 flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto flex-1">
        <div class="relative w-full sm:max-w-xs">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
            <AppIcon name="search" class="w-4 h-4" />
          </div>
          <input
            v-model="filterSearch"
            type="text"
            class="form-input pl-10"
            placeholder="Cari nama atau plat nomor..."
            @input="debouncedSearch"
          />
        </div>

        <select
          v-model="filterStatus"
          class="form-input w-full sm:w-48"
          @change="page = 1; loadBills()"
        >
          <option value="">Semua Status Tagihan</option>
          <option value="belum_lunas">Menunggu Bayar (Belum Lunas)</option>
          <option value="lunas">Sudah Terbayar (Lunas)</option>
        </select>
      </div>

      <div class="text-xs text-slate-400 font-mono w-full sm:w-auto text-right">
        Total: <span class="text-white font-semibold">{{ meta.total }}</span> lembar tagihan
      </div>
    </div>

    <!-- Bills Table -->
    <div class="glass-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Member & Kendaraan</th>
              <th>Periode Iuran</th>
              <th>Nominal Tagihan</th>
              <th>Status Pembayaran</th>
              <th>No. Invoice</th>
              <th>Waktu Bayar</th>
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
                  <span>Memuat daftar tagihan...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="!bills.length">
              <td colspan="7" class="text-center py-12 text-slate-400">
                <AppIcon name="credit-card" class="w-8 h-8 mx-auto mb-2 text-slate-600" />
                <p class="text-sm font-medium text-slate-300">Tidak ada data tagihan</p>
                <p class="text-xs text-slate-500 mt-1">Semua tagihan sudah berstatus lunas atau belum ada tagihan terdaftar.</p>
              </td>
            </tr>

            <tr v-for="bill in bills" :key="bill.id">
              <td>
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-200">
                    {{ bill.member?.name?.charAt(0).toUpperCase() || 'M' }}
                  </div>
                  <div>
                    <p class="font-semibold text-white">{{ bill.member?.name }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                      <span class="font-mono text-[11px] text-slate-400">{{ bill.member?.plate_number }}</span>
                      <span class="text-slate-600">·</span>
                      <span class="text-[10px] uppercase font-semibold text-blue-400">{{ bill.member?.vehicle_category }}</span>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <div class="flex items-center gap-1.5 text-xs text-slate-200 font-medium">
                  <AppIcon name="calendar" class="w-3.5 h-3.5 text-slate-400" />
                  <span class="capitalize">{{ formatPeriod(bill.period) }}</span>
                </div>
              </td>
              <td class="font-mono font-bold text-white text-sm">
                {{ formatRupiah(bill.amount) }}
              </td>
              <td>
                <span
                  class="text-xs px-2.5 py-0.5 rounded-full font-medium"
                  :class="bill.status === 'lunas' ? 'badge-active' : 'badge-pending'"
                >
                  {{ bill.status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                </span>
              </td>
              <td>
                <span v-if="bill.invoice_number" class="font-mono text-xs text-blue-300 px-2 py-0.5 rounded bg-slate-800/80 border border-slate-700/60">
                  {{ bill.invoice_number }}
                </span>
                <span v-else class="text-slate-600 text-xs font-mono">—</span>
              </td>
              <td class="text-xs font-mono text-slate-400">
                {{ bill.paid_at ? formatDate(bill.paid_at) : '—' }}
              </td>
              <td class="text-right">
                <button
                  v-if="bill.status === 'belum_lunas'"
                  class="btn btn-success text-xs py-1.5 px-3 shadow-md shadow-emerald-600/15"
                  @click="openPayModal(bill)"
                >
                  <AppIcon name="credit-card" class="w-3.5 h-3.5" />
                  <span>Bayar Kasir</span>
                </button>
                <span v-else class="text-xs text-emerald-400 font-mono font-medium">
                  Terverifikasi
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <PaginationBar :meta="meta" :page="page" @prev="page--; loadBills()" @next="page++; loadBills()" />
    </div>

    <!-- Payment Modal -->
    <Teleport to="body">
      <div
        v-if="showPayModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
      >
        <div class="glass-card w-full max-w-md p-6 space-y-5 bg-[#0d1322] border-slate-700 shadow-2xl animate-fade-in">
          <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center gap-2">
              <AppIcon name="credit-card" class="w-5 h-5 text-emerald-400" />
              <h2 class="text-base font-bold text-white">Pembayaran Tagihan Member</h2>
            </div>
            <button class="p-1 text-slate-400 hover:text-white rounded-lg transition-colors" @click="showPayModal = false">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <!-- Invoice Details Card -->
          <div class="p-4 rounded-xl bg-slate-900/90 border border-slate-800 space-y-2 text-xs">
            <div class="flex justify-between">
              <span class="text-slate-400">Nama Pelanggan</span>
              <span class="font-semibold text-white">{{ selectedBill?.member?.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-400">Plat Nomor / Kategori</span>
              <span class="font-mono text-slate-200 uppercase">
                {{ selectedBill?.member?.plate_number }} ({{ selectedBill?.member?.vehicle_category }})
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-400">Periode Tagihan</span>
              <span class="font-medium text-white capitalize">{{ selectedBill ? formatPeriod(selectedBill.period) : '' }}</span>
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-sm">
              <span class="font-bold text-slate-300">Total Tagihan</span>
              <span class="font-mono font-bold text-emerald-400 text-lg">{{ formatRupiah(selectedBill?.amount) }}</span>
            </div>
          </div>

          <!-- Cash Received Input -->
          <div class="space-y-2">
            <label class="form-label">Uang Tunai Kasir Diterima (Rp)</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-mono text-xs">
                Rp
              </div>
              <input
                v-model.number="cashReceived"
                type="number"
                class="form-input pl-10 font-mono font-bold text-base"
                placeholder="0"
                min="0"
              />
            </div>

            <!-- Dynamic Change Banner -->
            <div
              v-if="cashReceived"
              class="p-3 rounded-lg text-xs font-mono flex items-center justify-between"
              :class="cashReceived >= (selectedBill?.amount || 0)
                ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20'
                : 'bg-rose-500/10 text-rose-300 border border-rose-500/20'"
            >
              <span>{{ cashReceived >= (selectedBill?.amount || 0) ? 'Kembalian:' : 'Uang Kurang:' }}</span>
              <span class="font-bold text-sm">
                {{ formatRupiah(Math.abs(cashReceived - (selectedBill?.amount || 0))) }}
              </span>
            </div>
          </div>

          <div v-if="payError" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
            {{ payError }}
          </div>

          <div class="flex gap-3 pt-2">
            <button class="btn btn-ghost flex-1 text-xs" @click="showPayModal = false">Batal</button>
            <button
              class="btn btn-success flex-1 text-xs font-semibold"
              :disabled="paying || !cashReceived || cashReceived < (selectedBill?.amount || 0)"
              @click="processPayment"
            >
              <svg v-if="paying" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <span v-else class="flex items-center gap-1.5">
                <AppIcon name="check" class="w-4 h-4" />
                <span>Lunaskan Tagihan</span>
              </span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'
import PaginationBar from '~/components/ui/PaginationBar.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Tagihan Member — E-Parkir Plaza Andalas' })

const { apiFetch } = useApi()
const route = useRoute()

const bills = ref<any[]>([])
const loading = ref(true)
const filterSearch = ref('')
const filterStatus = ref('')
const page = ref(1)
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 15 })

const showPayModal = ref(false)
const selectedBill = ref<any>(null)
const cashReceived = ref(0)
const paying = ref(false)
const payError = ref('')

function formatRupiah(n?: number) {
  return 'Rp ' + (n || 0).toLocaleString('id-ID')
}

function formatPeriod(d: string) {
  return new Date(d).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
}

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

let searchTimer: ReturnType<typeof setTimeout>
function debouncedSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    loadBills()
  }, 350)
}

async function loadBills() {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: String(page.value) })
    if (filterSearch.value.trim()) params.append('search', filterSearch.value.trim())
    if (filterStatus.value) params.append('status', filterStatus.value)
    if (route.query.member_id) params.append('member_id', String(route.query.member_id))

    const data = await apiFetch<any>(`/bills?${params}`)
    bills.value = data.data
    meta.value = data.meta
  } catch {} finally {
    loading.value = false
  }
}

function openPayModal(bill: any) {
  selectedBill.value = bill
  cashReceived.value = bill.amount
  payError.value = ''
  showPayModal.value = true
}

async function processPayment() {
  paying.value = true
  payError.value = ''
  try {
    await apiFetch(`/bills/${selectedBill.value.id}/pay`, {
      method: 'POST',
      body: { cash_received: cashReceived.value },
    })
    showPayModal.value = false
    loadBills()
  } catch (e: any) {
    payError.value = e?.data?.message ?? 'Gagal memproses pembayaran tagihan'
  } finally {
    paying.value = false
  }
}

onMounted(() => {
  if (route.query.member_id) filterStatus.value = 'belum_lunas'
  if (route.query.search) filterSearch.value = String(route.query.search)
  loadBills()
})
</script>
