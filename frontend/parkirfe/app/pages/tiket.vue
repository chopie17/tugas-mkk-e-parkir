<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-white">Kasir Parkir Non-Member</h1>
        <p class="text-xs text-slate-400 mt-1">Pencetakan tiket masuk portal dan penyelesaian kasir pembayaran keluar</p>
      </div>
      <button
        class="btn btn-primary shadow-lg shadow-blue-600/20"
        @click="issueTicket"
        :disabled="issuingTicket"
      >
        <svg v-if="issuingTicket" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <span v-else class="flex items-center gap-2">
          <AppIcon name="printer" class="w-4 h-4" />
          <span>Cetak Tiket Masuk Baru</span>
        </span>
      </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
      <!-- Left 7 cols: Issuance Voucher + Cashier Terminal -->
      <div class="xl:col-span-7 space-y-6">
        <!-- 1. Newly Issued Ticket Mockup Card -->
        <div v-if="newTicket" class="glass-card p-6 border-emerald-500/30 bg-gradient-to-b from-slate-900/95 to-[#0b1220]/95 relative overflow-hidden">
          <div class="flex items-center justify-between pb-3 border-b border-slate-800/80 mb-4">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
              <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Tiket Masuk Siap Cetak</span>
            </div>
            <button class="p-1 text-slate-400 hover:text-white rounded-lg transition-colors" @click="newTicket = null">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <!-- Thermal Receipt Mockup -->
          <div class="bg-white text-slate-900 p-5 rounded-xl shadow-xl max-w-sm mx-auto font-mono text-xs">
            <div class="text-center pb-3 border-b border-dashed border-slate-300">
              <p class="font-bold text-sm tracking-tight text-slate-950 font-sans">E-PARKIR PLAZA ANDALAS</p>
              <p class="text-[10px] text-slate-500 font-sans mt-0.5">Sistem Manajemen Parkir Resmi</p>
              <p class="text-[10px] text-slate-400 font-sans">Jl. Pemuda No. 1, Kota Padang</p>
            </div>

            <div class="py-4 text-center">
              <p class="text-[10px] text-slate-500 uppercase tracking-widest">KODE TIKET MASUK</p>
              <p class="text-3xl font-extrabold tracking-[0.25em] text-slate-950 my-1 font-mono">
                {{ newTicket.token }}
              </p>
              <div class="h-8 my-2 flex items-center justify-center gap-1 opacity-75">
                <span v-for="i in 28" :key="i" class="h-full bg-slate-900 inline-block" :style="{ width: (i % 3 === 0 ? '3px' : '1.5px') }"></span>
              </div>
            </div>

            <div class="pt-3 border-t border-dashed border-slate-300 space-y-1 text-[11px] text-slate-600">
              <div class="flex justify-between">
                <span>Waktu Masuk:</span>
                <span class="font-bold text-slate-800">{{ formatDateTime(newTicket.entry_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Pintu Masuk:</span>
                <span class="font-bold text-slate-800">Gate Masuk 01</span>
              </div>
              <div class="flex justify-between">
                <span>Status:</span>
                <span class="font-bold text-emerald-700">AKTIF / TERDAFTAR</span>
              </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200 text-center text-[9px] text-slate-400 font-sans">
              * Harap simpan tiket ini. Kehilangan tiket dikenakan denda sesuai peraturan Plaza Andalas.
            </div>
          </div>

          <div class="mt-5 flex gap-3 max-w-sm mx-auto">
            <button
              class="btn btn-outline flex-1 text-xs"
              @click="printTicketMockup"
            >
              <AppIcon name="printer" class="w-3.5 h-3.5" />
              <span>Simulasi Cetak</span>
            </button>
            <button
              class="btn btn-primary flex-1 text-xs"
              @click="loadToPayment(newTicket)"
            >
              <AppIcon name="credit-card" class="w-3.5 h-3.5" />
              <span>Buka Pembayaran</span>
            </button>
          </div>
        </div>

        <!-- 2. Search & Cashier Payment Form -->
        <div class="glass-card p-6 space-y-6">
          <div>
            <div class="flex items-center gap-2 mb-2">
              <AppIcon name="search" class="w-4 h-4 text-blue-400" />
              <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Cari Tiket Keluar</h2>
            </div>
            <p class="text-xs text-slate-400 mb-3">Ketik kode token tiket fisik pengemudi untuk menghitung durasi dan biaya parkir</p>

            <div class="flex gap-2">
              <div class="relative flex-1">
                <input
                  v-model="searchToken"
                  type="text"
                  class="form-input text-base font-mono font-bold tracking-widest uppercase pl-4"
                  placeholder="Misal: A1B2C3"
                  @keydown.enter="findTicket"
                  @input="searchToken = searchToken.toUpperCase()"
                />
              </div>
              <button
                class="btn btn-primary px-6 shrink-0"
                @click="findTicket"
                :disabled="finding || !searchToken.trim()"
              >
                <svg v-if="finding" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span v-else class="flex items-center gap-1.5">
                  <AppIcon name="search" class="w-4 h-4" />
                  <span>Cari Tiket</span>
                </span>
              </button>
            </div>
            <p v-if="findError" class="text-xs mt-2 text-rose-400 flex items-center gap-1">
              <AppIcon name="alert-triangle" class="w-3.5 h-3.5" />
              <span>{{ findError }}</span>
            </p>
          </div>

          <!-- Found Ticket Details & Payment Calculator -->
          <div v-if="foundTicket" class="pt-5 border-t border-slate-800/80 space-y-5 animate-fade-in">
            <!-- Ticket Info Header -->
            <div class="flex items-center justify-between p-4 rounded-xl bg-slate-900/90 border border-slate-800/80">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                  <AppIcon name="ticket" class="w-5 h-5" />
                </div>
                <div>
                  <p class="text-xs text-slate-400">Kode Token Tiket</p>
                  <p class="text-lg font-mono font-bold text-white tracking-widest">{{ foundTicket.token }}</p>
                </div>
              </div>
              <div class="text-right">
                <span
                  class="text-xs px-3 py-1 rounded-full font-medium"
                  :class="foundTicket.status === 'active' ? 'badge-active' : 'badge-paid'"
                >
                  {{ foundTicket.status === 'active' ? 'Tiket Belum Dibayar' : 'Sudah Lunas' }}
                </span>
                <p class="text-[11px] text-slate-400 mt-1 font-mono">Masuk: {{ formatDateTime(foundTicket.entry_at) }}</p>
              </div>
            </div>

            <!-- Active Ticket Form -->
            <div v-if="foundTicket.status === 'active'" class="space-y-4">
              <!-- Vehicle & Plate Selector -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="form-label">Kategori Kendaraan</label>
                  <div class="grid grid-cols-2 gap-2">
                    <button
                      type="button"
                      class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border text-xs font-semibold transition-all"
                      :class="payForm.vehicle_category === 'motor'
                        ? 'bg-blue-600/20 border-blue-500 text-blue-300 shadow-sm shadow-blue-500/15'
                        : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200'"
                      @click="payForm.vehicle_category = 'motor'; refreshPreview()"
                    >
                      <AppIcon name="motorcycle" class="w-4 h-4" />
                      <span>Motor</span>
                    </button>
                    <button
                      type="button"
                      class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border text-xs font-semibold transition-all"
                      :class="payForm.vehicle_category === 'mobil'
                        ? 'bg-blue-600/20 border-blue-500 text-blue-300 shadow-sm shadow-blue-500/15'
                        : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200'"
                      @click="payForm.vehicle_category = 'mobil'; refreshPreview()"
                    >
                      <AppIcon name="car" class="w-4 h-4" />
                      <span>Mobil</span>
                    </button>
                  </div>
                </div>

                <div>
                  <label class="form-label">Plat Nomor Kendaraan</label>
                  <input
                    v-model="payForm.plate_number"
                    type="text"
                    class="form-input font-mono font-bold uppercase tracking-wider"
                    placeholder="BA 1234 XY"
                    @input="payForm.plate_number = payForm.plate_number.toUpperCase()"
                  />
                </div>
              </div>

              <!-- Cost Calculation Summary Box -->
              <div v-if="preview" class="p-4 rounded-xl bg-slate-950/80 border border-blue-500/30 space-y-2">
                <div class="flex justify-between text-xs text-slate-400">
                  <span>Durasi Terhitung (Ceil per jam)</span>
                  <span class="font-mono text-slate-200 font-semibold">{{ preview.hours }} Jam</span>
                </div>
                <div class="flex justify-between text-xs text-slate-400">
                  <span>Tarif / Jam</span>
                  <span class="font-mono text-slate-200">{{ formatRupiah(preview.tariff_per_hour) }}</span>
                </div>
                <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-sm">
                  <span class="font-semibold text-blue-300">Total Tarif Parkir</span>
                  <span class="text-xl font-bold font-mono text-emerald-400">{{ formatRupiah(preview.fee) }}</span>
                </div>
              </div>

              <!-- Cash Input & Change Calculator -->
              <div class="space-y-2">
                <label class="form-label">Uang Tunai Diterima (Rp)</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-mono text-xs">
                    Rp
                  </div>
                  <input
                    v-model.number="payForm.cash_received"
                    type="number"
                    class="form-input pl-10 font-mono font-bold text-base"
                    placeholder="0"
                    min="0"
                  />
                </div>

                <!-- Change Calculation Banner -->
                <div
                  v-if="payForm.cash_received && preview"
                  class="p-3 rounded-lg text-xs font-mono flex items-center justify-between"
                  :class="payForm.cash_received >= preview.fee
                    ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20'
                    : 'bg-rose-500/10 text-rose-300 border border-rose-500/20'"
                >
                  <span>{{ payForm.cash_received >= preview.fee ? 'Kembalian Pelanggan:' : 'Uang Tunai Kurang:' }}</span>
                  <span class="font-bold text-sm">
                    {{ formatRupiah(Math.abs(payForm.cash_received - (preview?.fee || 0))) }}
                  </span>
                </div>
              </div>

              <!-- Error alert -->
              <div v-if="payError" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
                <AppIcon name="alert-triangle" class="w-4 h-4 shrink-0" />
                <span>{{ payError }}</span>
              </div>

              <!-- Submit Payment Button -->
              <button
                class="btn btn-success w-full py-3.5 text-sm font-semibold tracking-wide"
                :disabled="paying || !payForm.vehicle_category || !payForm.plate_number || (preview && payForm.cash_received < preview.fee)"
                @click="processPayment"
              >
                <svg v-if="paying" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span v-else class="flex items-center gap-2">
                  <AppIcon name="check" class="w-4 h-4" />
                  <span>Selesaikan Pembayaran & Buka Portal Keluar</span>
                </span>
              </button>
            </div>

            <!-- Paid Ticket Receipt Box -->
            <div v-else class="p-5 rounded-xl bg-slate-950/80 border border-emerald-500/30 space-y-3">
              <div class="flex items-center gap-2 text-emerald-400 font-semibold text-sm">
                <AppIcon name="check-circle" class="w-5 h-5" />
                <span>Pembayaran Sukses — Portal Keluar Dibuka</span>
              </div>

              <div class="grid grid-cols-2 gap-3 text-xs pt-2 border-t border-slate-800 font-mono">
                <div>
                  <span class="text-slate-500 block text-[10px]">NOMOR INVOICE</span>
                  <span class="text-white font-bold">{{ foundTicket.invoice_number }}</span>
                </div>
                <div>
                  <span class="text-slate-500 block text-[10px]">TOTAL TARIF</span>
                  <span class="text-emerald-400 font-bold">{{ formatRupiah(foundTicket.fee) }}</span>
                </div>
                <div>
                  <span class="text-slate-500 block text-[10px]">TUNAI DITERIMA</span>
                  <span class="text-slate-200">{{ formatRupiah(foundTicket.cash_received) }}</span>
                </div>
                <div>
                  <span class="text-slate-500 block text-[10px]">KEMBALIAN</span>
                  <span class="text-slate-200">{{ formatRupiah(foundTicket.change_amount) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 5 cols: Live Active Tickets Table -->
      <div class="xl:col-span-5">
        <div class="glass-card overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-800/80 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <AppIcon name="ticket" class="w-4 h-4 text-slate-400" />
              <h2 class="text-sm font-semibold text-white">Daftar Tiket Hari Ini</h2>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-mono">
              {{ tickets.length }} tiket
            </span>
          </div>

          <div class="overflow-y-auto max-h-[620px]">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Token</th>
                  <th>Waktu Masuk</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!tickets.length">
                  <td colspan="3" class="text-center py-12 text-slate-500 text-xs">
                    Belum ada riwayat tiket hari ini
                  </td>
                </tr>

                <tr
                  v-for="t in tickets"
                  :key="t.id"
                  class="cursor-pointer transition-colors"
                  :class="foundTicket?.token === t.token ? 'bg-blue-500/10' : ''"
                  @click="selectTicket(t)"
                >
                  <td>
                    <span class="font-mono text-xs font-bold text-white px-2 py-1 rounded bg-slate-800/70 border border-slate-700/60">
                      {{ t.token }}
                    </span>
                  </td>
                  <td class="text-xs font-mono text-slate-400">
                    {{ formatDateTime(t.entry_at) }}
                  </td>
                  <td>
                    <span
                      class="text-xs px-2 py-0.5 rounded-full font-medium"
                      :class="t.status === 'active' ? 'badge-active' : 'badge-paid'"
                    >
                      {{ t.status === 'active' ? 'Aktif' : 'Lunas' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Kasir Tiket — E-Parkir Plaza Andalas' })

const route = useRoute()
const { apiFetch } = useApi()

const issuingTicket = ref(false)
const newTicket = ref<any>(null)
const searchToken = ref('')
const finding = ref(false)
const findError = ref('')
const foundTicket = ref<any>(null)
const preview = ref<any>(null)
const paying = ref(false)
const payError = ref('')
const tickets = ref<any[]>([])

const payForm = reactive({
  vehicle_category: 'motor',
  plate_number: '',
  cash_received: 0,
})

function formatRupiah(n: number) {
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

async function loadTickets() {
  try {
    const today = new Date().toISOString().split('T')[0]
    const data = await apiFetch<any>(`/reports/non-member?date_from=${today}&per_page=50`)
    tickets.value = data.data || []
  } catch {}
}

async function issueTicket() {
  issuingTicket.value = true
  newTicket.value = null
  try {
    const data = await apiFetch<any>('/tickets', { method: 'POST' })
    newTicket.value = data.ticket
    loadTickets()
  } catch (e: any) {
    alert(e?.data?.message ?? 'Gagal mencetak tiket')
  } finally {
    issuingTicket.value = false
  }
}

async function findTicket() {
  if (!searchToken.value.trim()) return
  finding.value = true
  findError.value = ''
  foundTicket.value = null
  preview.value = null
  try {
    const data = await apiFetch<any>(`/tickets/${searchToken.value.trim()}`)
    foundTicket.value = data.ticket
    if (data.ticket.status === 'active' && payForm.vehicle_category) {
      await refreshPreview()
    }
  } catch (e: any) {
    findError.value = e?.data?.message ?? 'Tiket tidak ditemukan dalam sistem'
  } finally {
    finding.value = false
  }
}

function selectTicket(t: any) {
  foundTicket.value = t
  searchToken.value = t.token
  if (t.status === 'active') {
    refreshPreview()
  }
}

function loadToPayment(ticket: any) {
  foundTicket.value = ticket
  searchToken.value = ticket.token
  newTicket.value = null
  refreshPreview()
}

function printTicketMockup() {
  window.print()
}

async function refreshPreview() {
  if (!foundTicket.value || !payForm.vehicle_category) return
  try {
    const data = await apiFetch<any>(
      `/tickets/${foundTicket.value.token}/preview?vehicle_category=${payForm.vehicle_category}`
    )
    preview.value = data
    if (!payForm.cash_received || payForm.cash_received < data.fee) {
      payForm.cash_received = data.fee
    }
  } catch {}
}

async function processPayment() {
  paying.value = true
  payError.value = ''
  try {
    const data = await apiFetch<any>(`/tickets/${foundTicket.value.token}/pay`, {
      method: 'POST',
      body: {
        vehicle_category: payForm.vehicle_category,
        plate_number: payForm.plate_number,
        cash_received: payForm.cash_received,
      },
    })
    foundTicket.value = data.ticket
    loadTickets()
  } catch (e: any) {
    payError.value = e?.data?.message ?? 'Gagal memproses pembayaran kasir'
  } finally {
    paying.value = false
  }
}

onMounted(() => {
  loadTickets()
  if (route.query.token) {
    searchToken.value = String(route.query.token).toUpperCase()
    findTicket()
  }
})
</script>
