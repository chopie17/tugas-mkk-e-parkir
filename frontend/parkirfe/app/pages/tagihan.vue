<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-[#0a0a0a]">Kasir Tagihan Member</h1>
        <p class="text-xs text-[var(--nb-muted)] mt-1 font-medium">Pengelolaan dan pembayaran iuran langganan parkir bulanan member</p>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="nb-card bg-white border-2 border-black p-4 shadow-[4px_4px_0_#0a0a0a] flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto flex-1">
        <div class="relative w-full sm:max-w-xs">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/60">
            <AppIcon name="search" class="w-4 h-4" />
          </div>
          <input
            v-model="filterSearch"
            type="text"
            class="form-input pl-10 bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
            placeholder="Cari nama atau plat nomor..."
            @input="debouncedSearch"
          />
        </div>

        <select
          v-model="filterStatus"
          class="form-input w-full sm:w-56 bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a] font-medium"
          @change="page = 1; loadBills()"
        >
          <option value="">Semua Status Tagihan</option>
          <option value="belum_lunas">Menunggu Bayar (Belum Lunas)</option>
          <option value="lunas">Sudah Terbayar (Lunas)</option>
        </select>
      </div>

      <div class="text-xs text-[var(--nb-muted)] font-mono font-medium w-full sm:w-auto text-right">
        Total: <span class="text-black font-bold">{{ meta.total }}</span> lembar tagihan
      </div>
    </div>

    <!-- Bills Table -->
    <div class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] overflow-hidden">
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
              <td colspan="7" class="text-center py-12 text-[var(--nb-muted)] text-xs">
                <div class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <span>Memuat daftar tagihan...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="!bills.length">
              <td colspan="7" class="text-center py-12 text-black/60">
                <AppIcon name="credit-card" class="w-8 h-8 mx-auto mb-2 text-black/40" />
                <p class="text-sm font-bold text-black">Tidak ada data tagihan</p>
                <p class="text-xs text-[var(--nb-muted)] mt-1">Semua tagihan sudah berstatus lunas atau belum ada tagihan terdaftar.</p>
              </td>
            </tr>

            <tr v-for="bill in bills" :key="bill.id">
              <td>
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg bg-[var(--nb-yellow)] border-2 border-black flex items-center justify-center text-xs font-black text-black shadow-[1px_1px_0_#0a0a0a]">
                    {{ bill.member?.name?.charAt(0).toUpperCase() || 'M' }}
                  </div>
                  <div>
                    <p class="font-bold text-[#0a0a0a]">{{ bill.member?.name }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                      <span class="font-mono text-[11px] text-[var(--nb-muted)] font-semibold whitespace-nowrap">{{ bill.member?.plate_number }}</span>
                      <span class="text-black/30">·</span>
                      <span class="text-[10px] uppercase font-bold text-black px-1.5 py-0.2 rounded bg-[var(--nb-teal)]/30 border border-black">{{ bill.member?.vehicle_category }}</span>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <div class="flex items-center gap-1.5 text-xs text-black font-medium">
                  <AppIcon name="calendar" class="w-3.5 h-3.5 text-black/60" />
                  <span class="capitalize">{{ formatPeriod(bill.period) }}</span>
                </div>
              </td>
              <td class="font-mono font-bold text-[#0a0a0a] text-sm">
                {{ formatRupiah(bill.amount) }}
              </td>
              <td>
                <span
                  class="text-xs px-2.5 py-0.5 rounded-full font-bold border-2 border-black shadow-[1px_1px_0_#0a0a0a]"
                  :class="bill.status === 'lunas' ? 'bg-[var(--nb-lime)] text-black' : 'bg-[var(--nb-orange)]/30 text-black'"
                >
                  {{ bill.status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                </span>
              </td>
              <td>
                <span v-if="bill.invoice_number" class="font-mono text-xs text-black font-bold px-2 py-0.5 rounded bg-[var(--nb-bg)] border-2 border-black shadow-[1px_1px_0_#0a0a0a]">
                  {{ bill.invoice_number }}
                </span>
                <span v-else class="text-black/40 text-xs font-mono">—</span>
              </td>
              <td class="text-xs font-mono text-[var(--nb-ink)] font-medium">
                {{ bill.paid_at ? formatDate(bill.paid_at) : '—' }}
              </td>
              <td class="text-right">
                <button
                  v-if="bill.status === 'belum_lunas'"
                  class="nb-btn bg-[var(--nb-lime)] text-black text-xs font-bold py-1.5 px-3 border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-[#d4f028]"
                  @click="openPayModal(bill)"
                >
                  <AppIcon name="credit-card" class="w-3.5 h-3.5" />
                  <span>Bayar Kasir</span>
                </button>
                <span v-else class="text-xs text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded border border-emerald-400 font-mono font-bold">
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
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
      >
        <div class="nb-modal bg-white border-3 border-black shadow-[8px_8px_0_#0a0a0a] w-full max-w-md p-6 space-y-5 animate-fade-in">
          <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-[var(--nb-lime)] border-2 border-black flex items-center justify-center">
                <AppIcon name="credit-card" class="w-4 h-4 text-black" />
              </div>
              <h2 class="text-base font-black text-black">Pembayaran Tagihan Member</h2>
            </div>
            <button class="p-1 text-black hover:bg-black/10 rounded-md transition-colors" @click="showPayModal = false">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <!-- Invoice Details Card -->
          <div class="p-4 rounded-xl bg-[var(--nb-bg)] border-2 border-black shadow-[2px_2px_0_#0a0a0a] space-y-2 text-xs">
            <div class="flex justify-between">
              <span class="text-black/70 font-medium">Nama Pelanggan</span>
              <span class="font-bold text-black">{{ selectedBill?.member?.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-black/70 font-medium">Plat Nomor / Kategori</span>
              <span class="font-mono font-bold text-black uppercase">
                {{ selectedBill?.member?.plate_number }} ({{ selectedBill?.member?.vehicle_category }})
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-black/70 font-medium">Periode Tagihan</span>
              <span class="font-bold text-black capitalize">{{ selectedBill ? formatPeriod(selectedBill.period) : '' }}</span>
            </div>
            <div class="pt-2 border-t-2 border-black/10 flex justify-between items-center text-sm">
              <span class="font-bold text-black">Total Tagihan</span>
              <span class="font-mono font-black text-black text-lg">{{ formatRupiah(selectedBill?.amount) }}</span>
            </div>
          </div>

          <!-- Cash Received Input -->
          <div class="space-y-2">
            <label class="form-label text-black font-bold">Uang Tunai Kasir Diterima (Rp)</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-black font-mono text-xs font-black select-none">
                Rp
              </div>
              <input
                v-model.number="cashReceived"
                type="number"
                class="form-input pl-11 font-mono font-bold text-base bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                placeholder="0"
                min="0"
              />
            </div>

            <!-- Dynamic Change Banner -->
            <div
              v-if="cashReceived"
              class="p-3 rounded-xl text-xs font-mono flex items-center justify-between border-2 border-black shadow-[2px_2px_0_#0a0a0a]"
              :class="cashReceived >= (selectedBill?.amount || 0)
                ? 'bg-[var(--nb-teal)]/30 text-black'
                : 'bg-rose-100 text-rose-900'"
            >
              <span class="font-bold">{{ cashReceived >= (selectedBill?.amount || 0) ? 'Kembalian:' : 'Uang Kurang:' }}</span>
              <span class="font-black text-sm">
                {{ formatRupiah(Math.abs(cashReceived - (selectedBill?.amount || 0))) }}
              </span>
            </div>
          </div>

          <div v-if="payError" class="p-3 rounded-xl bg-rose-100 border-2 border-black text-rose-900 text-xs font-bold shadow-[2px_2px_0_#0a0a0a]">
            {{ payError }}
          </div>

          <div class="flex gap-3 pt-2">
            <button class="nb-btn bg-white text-black flex-1 text-xs font-bold border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-gray-100" @click="showPayModal = false">Batal</button>
            <button
              class="nb-btn bg-[var(--nb-lime)] text-black flex-1 text-xs font-black border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-[#d4f028]"
              :disabled="paying || !cashReceived || cashReceived < (selectedBill?.amount || 0)"
              @click="processPayment"
            >
              <svg v-if="paying" class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <span v-else class="flex items-center justify-center gap-1.5">
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
