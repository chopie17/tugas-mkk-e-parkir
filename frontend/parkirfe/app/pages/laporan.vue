<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-white">Laporan & Rekapitulasi Kasir</h1>
        <p class="text-xs text-slate-400 mt-1">Audit transaksi pembayaran parkir non-member dan iuran bulanan member</p>
      </div>

      <button
        class="btn btn-secondary text-xs"
        @click="printReport"
      >
        <AppIcon name="printer" class="w-4 h-4" />
        <span>Cetak Dokumen Laporan</span>
      </button>
    </div>

    <!-- Segmented Tab Switcher -->
    <div class="p-1 rounded-xl bg-slate-900/90 border border-slate-800/80 inline-flex items-center gap-1">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-150"
        :class="activeTab === tab.id
          ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25'
          : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'"
        @click="activeTab = tab.id; page = 1; loadReport()"
      >
        <AppIcon :name="tab.icon" class="w-4 h-4" />
        <span>{{ tab.label }}</span>
      </button>
    </div>

    <!-- KPI Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="stat-card">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Transaksi</span>
          <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
            <AppIcon name="chart-bar" class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-mono font-bold text-white mt-3">{{ meta.total ?? 0 }}</p>
        <p class="text-[11px] text-slate-500 mt-1">Sesuai rentang filter tanggal</p>
      </div>

      <div class="stat-card">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pendapatan Kas</span>
          <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
            <AppIcon name="coins" class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-mono font-bold text-emerald-400 mt-3">{{ formatRupiah(meta.total_amount) }}</p>
        <p class="text-[11px] text-emerald-500/80 mt-1">Total kas masuk terverifikasi</p>
      </div>

      <div class="stat-card">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Rata-rata Transaksi</span>
          <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
            <AppIcon name="receipt" class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-mono font-bold text-white mt-3">
          {{ meta.total ? formatRupiah(Math.round(meta.total_amount / meta.total)) : 'Rp 0' }}
        </p>
        <p class="text-[11px] text-slate-500 mt-1">Nilai nominal rata-rata</p>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="glass-card p-4 flex flex-col lg:flex-row gap-3 items-center justify-between">
      <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto flex-1">
        <div class="relative w-full sm:max-w-xs">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
            <AppIcon name="search" class="w-4 h-4" />
          </div>
          <input
            v-model="filterSearch"
            type="text"
            class="form-input pl-10"
            :placeholder="activeTab === 'non-member' ? 'Cari token atau plat...' : 'Cari nama atau invoice...'"
            @input="debouncedSearch"
          />
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <div class="relative flex-1 sm:w-40">
            <input
              v-model="dateFrom"
              type="date"
              class="form-input text-xs font-mono"
              @change="page = 1; loadReport()"
            />
          </div>
          <span class="text-xs text-slate-500">s/d</span>
          <div class="relative flex-1 sm:w-40">
            <input
              v-model="dateTo"
              type="date"
              class="form-input text-xs font-mono"
              @change="page = 1; loadReport()"
            />
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2 w-full lg:w-auto justify-end">
        <button
          v-if="filterSearch || dateFrom || dateTo"
          class="btn btn-ghost text-xs text-rose-400 hover:text-rose-300"
          @click="clearFilters"
        >
          <AppIcon name="close" class="w-3.5 h-3.5" />
          <span>Reset Filter</span>
        </button>
      </div>
    </div>

    <!-- Table Non-Member -->
    <div v-if="activeTab === 'non-member'" class="glass-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Token Tiket</th>
              <th>Plat Nomor</th>
              <th>Kategori</th>
              <th>Durasi</th>
              <th>Tarif Biaya</th>
              <th>Tunai Diterima</th>
              <th>Kembalian</th>
              <th>No. Invoice</th>
              <th>Petugas Kasir</th>
              <th>Waktu Keluar</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="10" class="text-center py-12 text-slate-500 text-xs">
                <div class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <span>Memuat laporan non-member...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="!rows.length">
              <td colspan="10" class="text-center py-12 text-slate-400">
                <AppIcon name="receipt" class="w-8 h-8 mx-auto mb-2 text-slate-600" />
                <p class="text-sm font-medium text-slate-300">Tidak ada transaksi ditemukan</p>
                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan filter pencarian atau tanggal.</p>
              </td>
            </tr>

            <tr v-for="r in rows" :key="r.id">
              <td>
                <span class="font-mono text-xs font-bold text-blue-300 px-2 py-0.5 rounded bg-slate-800/80 border border-slate-700/60">
                  {{ r.token }}
                </span>
              </td>
              <td>
                <span class="font-mono text-xs font-bold text-white px-2 py-0.5 rounded bg-slate-950 border border-slate-700">
                  {{ r.plate_number }}
                </span>
              </td>
              <td>
                <span class="inline-flex items-center gap-1.5 text-xs text-slate-300 capitalize">
                  <AppIcon :name="r.vehicle_category === 'motor' ? 'motorcycle' : 'car'" class="w-3.5 h-3.5 text-slate-400" />
                  <span>{{ r.vehicle_category }}</span>
                </span>
              </td>
              <td class="font-mono text-xs text-slate-300">{{ r.duration_hours }} Jam</td>
              <td class="font-mono font-bold text-emerald-400">{{ formatRupiah(r.fee) }}</td>
              <td class="font-mono text-xs text-slate-300">{{ formatRupiah(r.cash_received) }}</td>
              <td class="font-mono text-xs text-slate-400">{{ formatRupiah(r.change_amount) }}</td>
              <td class="font-mono text-xs text-slate-400">{{ r.invoice_number }}</td>
              <td>
                <span class="text-xs font-medium text-slate-300 px-2 py-0.5 rounded bg-slate-800">
                  {{ r.processor?.name || '—' }}
                </span>
              </td>
              <td class="text-xs font-mono text-slate-400">{{ formatDateTime(r.exit_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <PaginationBar :meta="meta" :page="page" @prev="page--; loadReport()" @next="page++; loadReport()" />
    </div>

    <!-- Table Member -->
    <div v-if="activeTab === 'member'" class="glass-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Nama Member</th>
              <th>Plat Nomor</th>
              <th>Periode Iuran</th>
              <th>Nominal Tagihan</th>
              <th>Tunai Diterima</th>
              <th>Kembalian</th>
              <th>No. Invoice</th>
              <th>Petugas Kasir</th>
              <th>Waktu Pembayaran</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="9" class="text-center py-12 text-slate-500 text-xs">
                <div class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <span>Memuat laporan member...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="!rows.length">
              <td colspan="9" class="text-center py-12 text-slate-400">
                <AppIcon name="users" class="w-8 h-8 mx-auto mb-2 text-slate-600" />
                <p class="text-sm font-medium text-slate-300">Tidak ada data pembayaran member</p>
                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan filter pencarian atau tanggal.</p>
              </td>
            </tr>

            <tr v-for="r in rows" :key="r.id">
              <td>
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-md bg-slate-800 flex items-center justify-center text-[11px] font-bold text-slate-300">
                    {{ r.member?.name?.charAt(0).toUpperCase() || 'M' }}
                  </div>
                  <span class="font-semibold text-white text-xs">{{ r.member?.name }}</span>
                </div>
              </td>
              <td>
                <span class="font-mono text-xs font-bold text-white px-2 py-0.5 rounded bg-slate-950 border border-slate-700">
                  {{ r.member?.plate_number }}
                </span>
              </td>
              <td class="capitalize text-xs text-slate-200 font-medium">{{ formatPeriod(r.period) }}</td>
              <td class="font-mono font-bold text-emerald-400">{{ formatRupiah(r.amount) }}</td>
              <td class="font-mono text-xs text-slate-300">{{ formatRupiah(r.cash_received) }}</td>
              <td class="font-mono text-xs text-slate-400">{{ formatRupiah(r.change_amount) }}</td>
              <td>
                <span class="font-mono text-xs text-blue-300 px-2 py-0.5 rounded bg-slate-800/80 border border-slate-700/60">
                  {{ r.invoice_number }}
                </span>
              </td>
              <td>
                <span class="text-xs font-medium text-slate-300 px-2 py-0.5 rounded bg-slate-800">
                  {{ r.processor?.name || '—' }}
                </span>
              </td>
              <td class="text-xs font-mono text-slate-400">{{ formatDate(r.paid_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <PaginationBar :meta="meta" :page="page" @prev="page--; loadReport()" @next="page++; loadReport()" />
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'
import PaginationBar from '~/components/ui/PaginationBar.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Laporan Transaksi — E-Parkir Plaza Andalas' })

const { apiFetch } = useApi()

const activeTab = ref<'non-member' | 'member'>('non-member')
const loading = ref(true)
const rows = ref<any[]>([])
const page = ref(1)
const meta = ref<any>({ total: 0, last_page: 1, total_amount: 0, total_fee: 0, per_page: 20 })

const filterSearch = ref('')
const dateFrom = ref('')
const dateTo = ref('')

const tabs = [
  { id: 'non-member' as const, label: 'Parkir Non-Member', icon: 'ticket' },
  { id: 'member' as const, label: 'Iuran Member Parkir', icon: 'users' },
]

function formatRupiah(n?: number) {
  return 'Rp ' + (n || 0).toLocaleString('id-ID')
}

function formatDateTime(iso: string) {
  if (!iso) return '-'
  return new Date(iso).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatDate(d: string) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatPeriod(d: string) {
  return new Date(d).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
}

let searchTimer: ReturnType<typeof setTimeout>
function debouncedSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    loadReport()
  }, 350)
}

function clearFilters() {
  filterSearch.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  page.value = 1
  loadReport()
}

function printReport() {
  window.print()
}

async function loadReport() {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: String(page.value), per_page: '20' })
    if (filterSearch.value.trim()) params.append('search', filterSearch.value.trim())
    if (dateFrom.value) params.append('date_from', dateFrom.value)
    if (dateTo.value) params.append('date_to', dateTo.value)

    const endpoint = activeTab.value === 'non-member' ? '/reports/non-member' : '/reports/member'
    const data = await apiFetch<any>(`${endpoint}?${params}`)

    rows.value = data.data
    meta.value = {
      ...data.meta,
      total_amount: data.meta?.total_fee ?? data.meta?.total_amount ?? 0,
    }
  } catch {} finally {
    loading.value = false
  }
}

onMounted(loadReport)
</script>
