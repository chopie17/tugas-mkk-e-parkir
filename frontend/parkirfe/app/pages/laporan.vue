  <template>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black tracking-tight text-[#0a0a0a]">Laporan & Rekapitulasi Kasir</h1>
          <p class="text-xs text-[var(--nb-muted)] mt-1 font-medium">Audit transaksi pembayaran parkir non-member dan iuran bulanan member</p>
        </div>

        <button
          class="nb-btn bg-white text-black font-bold text-xs border-2 border-black shadow-[3px_3px_0_#0a0a0a] hover:bg-gray-100"
          @click="printReport"
        >
          <AppIcon name="printer" class="w-4 h-4" />
          <span>Cetak Dokumen Laporan</span>
        </button>
      </div>

      <!-- Segmented Tab Switcher -->
      <div class="p-1.5 rounded-2xl bg-white border-2 border-black shadow-[3px_3px_0_#0a0a0a] inline-flex items-center gap-1.5">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all duration-150"
          :class="activeTab === tab.id
            ? 'bg-[var(--nb-lime)] text-black border-2 border-black shadow-[2px_2px_0_#0a0a0a]'
            : 'text-black/70 hover:text-black hover:bg-black/5'"
          @click="activeTab = tab.id; page = 1; loadReport()"
        >
          <AppIcon :name="tab.icon" class="w-4 h-4" />
          <span>{{ tab.label }}</span>
        </button>
      </div>

      <!-- KPI Summary Metrics -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="nb-card bg-[var(--nb-teal)]/20 border-2 border-black p-5 shadow-[4px_4px_0_#0a0a0a]">
          <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-black">Total Transaksi</span>
            <div class="w-8 h-8 rounded-lg bg-[var(--nb-teal)] border-2 border-black flex items-center justify-center text-black">
              <AppIcon name="chart-bar" class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-mono font-black text-black mt-3">{{ meta.total ?? 0 }}</p>
          <p class="text-[11px] text-black/70 font-medium mt-1">Sesuai rentang filter tanggal</p>
        </div>

        <div class="nb-card bg-[var(--nb-lime)]/30 border-2 border-black p-5 shadow-[4px_4px_0_#0a0a0a]">
          <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-black">Total Pendapatan Kas</span>
            <div class="w-8 h-8 rounded-lg bg-[var(--nb-lime)] border-2 border-black flex items-center justify-center text-black">
              <AppIcon name="coins" class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-mono font-black text-black mt-3">{{ formatRupiah(meta.total_amount) }}</p>
          <p class="text-[11px] text-black/70 font-medium mt-1">Total kas masuk terverifikasi</p>
        </div>

        <div class="nb-card bg-[var(--nb-yellow)]/30 border-2 border-black p-5 shadow-[4px_4px_0_#0a0a0a]">
          <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-black">Rata-rata Transaksi</span>
            <div class="w-8 h-8 rounded-lg bg-[var(--nb-yellow)] border-2 border-black flex items-center justify-center text-black">
              <AppIcon name="receipt" class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-mono font-black text-black mt-3">
            {{ meta.total ? formatRupiah(Math.round(meta.total_amount / meta.total)) : 'Rp 0' }}
          </p>
          <p class="text-[11px] text-black/70 font-medium mt-1">Nilai nominal rata-rata</p>
        </div>
      </div>

      <!-- Filter & Search Toolbar -->
      <div class="nb-card bg-white border-2 border-black p-4 shadow-[4px_4px_0_#0a0a0a] flex flex-col lg:flex-row gap-3 items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto flex-1">
          <div class="relative w-full sm:max-w-xs">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/60">
              <AppIcon name="search" class="w-4 h-4" />
            </div>
            <input
              v-model="filterSearch"
              type="text"
              class="form-input pl-10 bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
              :placeholder="activeTab === 'non-member' ? 'Cari token atau plat...' : 'Cari nama atau invoice...'"
              @input="debouncedSearch"
            />
          </div>

          <div class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-40">
              <input
                v-model="dateFrom"
                type="date"
                class="form-input text-xs font-mono bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                @change="page = 1; loadReport()"
              />
            </div>
            <span class="text-xs text-black/70 font-bold">s/d</span>
            <div class="relative flex-1 sm:w-40">
              <input
                v-model="dateTo"
                type="date"
                class="form-input text-xs font-mono bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                @change="page = 1; loadReport()"
              />
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2 w-full lg:w-auto justify-end">
          <button
            v-if="filterSearch || dateFrom || dateTo"
            class="nb-btn bg-rose-100 text-rose-900 border-2 border-black shadow-[2px_2px_0_#0a0a0a] text-xs font-bold hover:bg-rose-200"
            @click="clearFilters"
          >
            <AppIcon name="close" class="w-3.5 h-3.5" />
            <span>Reset Filter</span>
          </button>
        </div>
      </div>

      <!-- Table Non-Member -->
      <div v-if="activeTab === 'non-member'" class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="data-table">
            <thead>
              <tr>
                <th class="whitespace-nowrap">Token Tiket</th>
                <th class="whitespace-nowrap">Plat Nomor</th>
                <th class="whitespace-nowrap">Kategori</th>
                <th class="whitespace-nowrap">Durasi</th>
                <th class="whitespace-nowrap">Tarif Biaya</th>
                <th class="whitespace-nowrap">Tunai Diterima</th>
                <th class="whitespace-nowrap">Kembalian</th>
                <th class="whitespace-nowrap">No. Invoice</th>
                <th class="whitespace-nowrap">Petugas Kasir</th>
                <th class="whitespace-nowrap">Waktu Keluar</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="10" class="text-center py-12 text-[var(--nb-muted)] text-xs">
                  <div class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span>Memuat laporan non-member...</span>
                  </div>
                </td>
              </tr>

              <tr v-else-if="!rows.length">
                <td colspan="10" class="text-center py-12 text-black/60">
                  <AppIcon name="receipt" class="w-8 h-8 mx-auto mb-2 text-black/40" />
                  <p class="text-sm font-bold text-black">Tidak ada transaksi ditemukan</p>
                  <p class="text-xs text-[var(--nb-muted)] mt-1">Coba sesuaikan filter pencarian atau tanggal.</p>
                </td>
              </tr>

              <tr v-for="r in rows" :key="r.id">
                <td class="whitespace-nowrap">
                  <span class="font-mono text-xs font-black text-black px-2 py-0.5 rounded bg-[var(--nb-lime)]/30 border-2 border-black shadow-[1px_1px_0_#0a0a0a]">
                    {{ r.token }}
                  </span>
                </td>
                <td class="whitespace-nowrap">
                  <span class="font-mono text-[11px] font-bold text-black px-1.5 py-0.5 rounded bg-white border-2 border-black shadow-[1px_1px_0_#0a0a0a] whitespace-nowrap inline-block tracking-tight">
                    {{ r.plate_number }}
                  </span>
                </td>
                <td class="whitespace-nowrap">
                  <span class="inline-flex items-center gap-1.5 text-xs text-black font-medium capitalize">
                    <AppIcon :name="r.vehicle_category === 'motor' ? 'motorcycle' : 'car'" class="w-3.5 h-3.5 text-black/70" />
                    <span>{{ r.vehicle_category }}</span>
                  </span>
                </td>
                <td class="font-mono text-xs text-black font-semibold whitespace-nowrap">{{ r.duration_hours }} Jam</td>
                <td class="font-mono font-black text-black whitespace-nowrap">{{ formatRupiah(r.fee) }}</td>
                <td class="font-mono text-xs text-black font-medium whitespace-nowrap">{{ formatRupiah(r.cash_received) }}</td>
                <td class="font-mono text-xs text-[var(--nb-muted)] font-medium whitespace-nowrap">{{ formatRupiah(r.change_amount) }}</td>
                <td class="font-mono text-xs text-black font-semibold whitespace-nowrap">{{ r.invoice_number }}</td>
                <td class="whitespace-nowrap">
                  <span class="text-xs font-bold text-black px-2 py-0.5 rounded bg-[var(--nb-yellow)]/30 border border-black">
                    {{ r.processor?.name || '—' }}
                  </span>
                </td>
                <td class="text-xs font-mono text-[var(--nb-ink)] font-medium whitespace-nowrap">{{ formatDateTime(r.exit_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <PaginationBar :meta="meta" :page="page" @prev="page--; loadReport()" @next="page++; loadReport()" />
      </div>

      <!-- Table Member -->
      <div v-if="activeTab === 'member'" class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="data-table">
            <thead>
              <tr>
                <th class="whitespace-nowrap">Nama Member</th>
                <th class="whitespace-nowrap">Plat Nomor</th>
                <th class="whitespace-nowrap">Periode Iuran</th>
                <th class="whitespace-nowrap">Nominal Tagihan</th>
                <th class="whitespace-nowrap">Tunai Diterima</th>
                <th class="whitespace-nowrap">Kembalian</th>
                <th class="whitespace-nowrap">No. Invoice</th>
                <th class="whitespace-nowrap">Petugas Kasir</th>
                <th class="whitespace-nowrap">Waktu Pembayaran</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="9" class="text-center py-12 text-[var(--nb-muted)] text-xs">
                  <div class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span>Memuat laporan member...</span>
                  </div>
                </td>
              </tr>

              <tr v-else-if="!rows.length">
                <td colspan="9" class="text-center py-12 text-black/60">
                  <AppIcon name="users" class="w-8 h-8 mx-auto mb-2 text-black/40" />
                  <p class="text-sm font-bold text-black">Tidak ada data pembayaran member</p>
                  <p class="text-xs text-[var(--nb-muted)] mt-1">Coba sesuaikan filter pencarian atau tanggal.</p>
                </td>
              </tr>

              <tr v-for="r in rows" :key="r.id">
                <td class="whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md bg-[var(--nb-yellow)] border border-black flex items-center justify-center text-[11px] font-black text-black">
                      {{ r.member?.name?.charAt(0).toUpperCase() || 'M' }}
                    </div>
                    <span class="font-bold text-black text-xs">{{ r.member?.name }}</span>
                  </div>
                </td>
                <td class="whitespace-nowrap">
                  <span class="font-mono text-[11px] font-bold text-black px-1.5 py-0.5 rounded bg-white border-2 border-black shadow-[1px_1px_0_#0a0a0a] whitespace-nowrap inline-block tracking-tight">
                    {{ r.member?.plate_number }}
                  </span>
                </td>
                <td class="capitalize text-xs text-black font-semibold whitespace-nowrap">{{ formatPeriod(r.period) }}</td>
                <td class="font-mono font-black text-black whitespace-nowrap">{{ formatRupiah(r.amount) }}</td>
                <td class="font-mono text-xs text-black font-medium whitespace-nowrap">{{ formatRupiah(r.cash_received) }}</td>
                <td class="font-mono text-xs text-[var(--nb-muted)] font-medium whitespace-nowrap">{{ formatRupiah(r.change_amount) }}</td>
                <td class="whitespace-nowrap">
                  <span class="font-mono text-xs text-black font-bold px-2 py-0.5 rounded bg-[var(--nb-bg)] border-2 border-black shadow-[1px_1px_0_#0a0a0a]">
                    {{ r.invoice_number }}
                  </span>
                </td>
                <td class="whitespace-nowrap">
                  <span class="text-xs font-bold text-black px-2 py-0.5 rounded bg-[var(--nb-yellow)]/30 border border-black">
                    {{ r.processor?.name || '—' }}
                  </span>
                </td>
                <td class="text-xs font-mono text-[var(--nb-ink)] font-medium whitespace-nowrap">{{ formatDate(r.paid_at) }}</td>
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
