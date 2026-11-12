<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-[#0a0a0a]">Kasir Tagihan Member</h1>
        <p class="text-xs text-[var(--nb-muted)] mt-1 font-medium">Pengelolaan dan pembayaran iuran langganan parkir bulanan member</p>
      </div>
    </div>

    <!-- Reminder Feedback Banner -->
    <div
      v-if="reminderAlert"
      class="p-4 rounded-xl border-2 border-black shadow-[3px_3px_0_#0a0a0a] text-xs font-bold flex items-center justify-between gap-2 animate-fade-in"
      :class="reminderAlert.type === 'success' ? 'bg-[var(--nb-lime)] text-black' : 'bg-rose-100 text-rose-900'"
    >
      <div class="flex items-center gap-2">
        <AppIcon :name="reminderAlert.type === 'success' ? 'check-circle' : 'alert-triangle'" class="w-4 h-4 shrink-0" />
        <span>{{ reminderAlert.message }}</span>
      </div>
      <button class="p-1 hover:bg-black/10 rounded" @click="reminderAlert = null">
        <AppIcon name="close" class="w-3.5 h-3.5" />
      </button>
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
                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                      <span class="font-mono text-[11px] text-[var(--nb-muted)] font-semibold whitespace-nowrap">{{ bill.member?.plate_number }}</span>
                      <span class="text-black/30">·</span>
                      <span class="text-[10px] uppercase font-bold text-black px-1.5 py-0.2 rounded bg-[var(--nb-teal)]/30 border border-black">{{ bill.member?.vehicle_category }}</span>
                      <span v-if="bill.member?.phone_number" class="text-[9px] font-mono font-bold text-emerald-800 bg-emerald-100 px-1 py-0.2 rounded border border-emerald-500" title="WhatsApp Terdaftar">WA</span>
                      <span v-if="bill.member?.email" class="text-[9px] font-mono font-bold text-blue-800 bg-blue-100 px-1 py-0.2 rounded border border-blue-500" title="Email Terdaftar">Email</span>
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
                <div class="inline-flex items-center gap-1.5 justify-end">
                  <button
                    v-if="bill.status === 'belum_lunas'"
                    type="button"
                    class="nb-btn bg-white text-black text-xs font-bold py-1.5 px-2.5 border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-gray-100 flex items-center gap-1"
                    @click="openReminderModal(bill)"
                    title="Kirim pengingat tagihan (Pilihan WhatsApp / Email)"
                  >
                    <span>🔔</span>
                    <span>Ingatkan</span>
                  </button>
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
                </div>
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

    <!-- Reminder Modal (Pilihan Saluran WhatsApp / Email) -->
    <Teleport to="body">
      <div
        v-if="showReminderModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
      >
        <div class="nb-modal bg-white border-3 border-black shadow-[8px_8px_0_#0a0a0a] w-full max-w-md p-6 space-y-5 animate-fade-in text-[#0a0a0a] rounded-2xl">
          <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-[var(--nb-lime)] border-2 border-black flex items-center justify-center text-black">
                <AppIcon name="bell" class="w-4 h-4" />
              </div>
              <h2 class="text-base font-black text-black">Pilihan Saluran Pengingat</h2>
            </div>
            <button class="p-1 text-black hover:bg-black/10 rounded-md transition-colors" @click="showReminderModal = false">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <!-- Target Member Info -->
          <div class="p-3.5 rounded-xl bg-[var(--nb-bg)] border-2 border-black text-xs space-y-1.5">
            <div class="flex justify-between">
              <span class="text-[var(--nb-muted)] font-medium">Pelanggan:</span>
              <span class="font-black text-black">{{ reminderBill?.member?.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-[var(--nb-muted)] font-medium">Kendaraan:</span>
              <span class="font-mono font-bold text-black uppercase">{{ reminderBill?.member?.plate_number }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-[var(--nb-muted)] font-medium">Periode & Total:</span>
              <span class="font-bold text-black capitalize">{{ reminderBill ? formatPeriod(reminderBill.period) : '' }} ({{ formatRupiah(reminderBill?.amount) }})</span>
            </div>
          </div>

          <!-- Pilih Saluran Pengingat -->
          <div class="space-y-2">
            <label class="form-label text-black font-bold text-xs">Pilih Opsi Pengiriman Pesan:</label>
            <div class="space-y-2">
              <!-- Opsi WhatsApp -->
              <label
                class="flex items-center justify-between p-3 rounded-xl border-2 transition-all cursor-pointer"
                :class="[
                  reminderChannel === 'whatsapp' ? 'bg-[var(--nb-lime)]/30 border-black shadow-[2px_2px_0_#0a0a0a]' : 'bg-white border-black/30 hover:border-black',
                  !reminderBill?.member?.phone_number ? 'opacity-50 cursor-not-allowed bg-gray-50' : ''
                ]"
              >
                <div class="flex items-center gap-2.5">
                  <input
                    v-model="reminderChannel"
                    type="radio"
                    value="whatsapp"
                    :disabled="!reminderBill?.member?.phone_number"
                    class="w-4 h-4 text-black border-2 border-black accent-black"
                  />
                  <div>
                    <p class="font-bold text-xs text-black flex items-center gap-1.5">
                      <span>📱 WhatsApp (Fonnte)</span>
                      <span v-if="!reminderBill?.member?.phone_number" class="text-[10px] text-rose-600 font-normal">(Tidak ada no. WA)</span>
                    </p>
                    <p v-if="reminderBill?.member?.phone_number" class="text-[11px] font-mono text-[var(--nb-muted)]">
                      {{ reminderBill.member.phone_number }}
                    </p>
                  </div>
                </div>
              </label>

              <!-- Opsi Email -->
              <label
                class="flex items-center justify-between p-3 rounded-xl border-2 transition-all cursor-pointer"
                :class="[
                  reminderChannel === 'email' ? 'bg-[var(--nb-lime)]/30 border-black shadow-[2px_2px_0_#0a0a0a]' : 'bg-white border-black/30 hover:border-black',
                  !reminderBill?.member?.email ? 'opacity-50 cursor-not-allowed bg-gray-50' : ''
                ]"
              >
                <div class="flex items-center gap-2.5">
                  <input
                    v-model="reminderChannel"
                    type="radio"
                    value="email"
                    :disabled="!reminderBill?.member?.email"
                    class="w-4 h-4 text-black border-2 border-black accent-black"
                  />
                  <div>
                    <p class="font-bold text-xs text-black flex items-center gap-1.5">
                      <span>✉️ Email (SMTP)</span>
                      <span v-if="!reminderBill?.member?.email" class="text-[10px] text-rose-600 font-normal">(Tidak ada email)</span>
                    </p>
                    <p v-if="reminderBill?.member?.email" class="text-[11px] text-[var(--nb-muted)]">
                      {{ reminderBill.member.email }}
                    </p>
                  </div>
                </div>
              </label>

              <!-- Opsi Keduanya (Jika keduanya tersedia) -->
              <label
                v-if="reminderBill?.member?.phone_number && reminderBill?.member?.email"
                class="flex items-center justify-between p-3 rounded-xl border-2 transition-all cursor-pointer"
                :class="reminderChannel === 'all' ? 'bg-[var(--nb-lime)]/30 border-black shadow-[2px_2px_0_#0a0a0a]' : 'bg-white border-black/30 hover:border-black'"
              >
                <div class="flex items-center gap-2.5">
                  <input
                    v-model="reminderChannel"
                    type="radio"
                    value="all"
                    class="w-4 h-4 text-black border-2 border-black accent-black"
                  />
                  <div>
                    <p class="font-bold text-xs text-black">🚀 Kirim Keduanya (WhatsApp & Email)</p>
                    <p class="text-[11px] text-[var(--nb-muted)]">Pesan dikirim serentak ke WA dan Email member</p>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-2 pt-2 border-t-2 border-black/10">
            <button
              type="button"
              class="flex-1 nb-btn bg-white text-black font-bold text-xs py-2.5 border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-gray-100"
              @click="showReminderModal = false"
            >
              Batal
            </button>
            <button
              type="button"
              class="flex-1 nb-btn bg-[var(--nb-lime)] text-black font-black text-xs py-2.5 border-2 border-black shadow-[3px_3px_0_#0a0a0a] hover:bg-[#d4f028] flex items-center justify-center gap-1.5"
              :disabled="sendingReminder || (!reminderBill?.member?.phone_number && !reminderBill?.member?.email)"
              @click="executeSendReminder"
            >
              <svg v-if="sendingReminder" class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <span v-else class="flex items-center gap-1">
                <span>Kirim Pengingat</span>
                <span>→</span>
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

const showReminderModal = ref(false)
const reminderBill = ref<any>(null)
const reminderChannel = ref<'all' | 'whatsapp' | 'email'>('all')
const sendingReminder = ref(false)
const reminderAlert = ref<{ type: 'success' | 'error'; message: string } | null>(null)

function openReminderModal(bill: any) {
  reminderBill.value = bill
  const hasPhone = !!bill.member?.phone_number
  const hasEmail = !!bill.member?.email

  if (!hasPhone && !hasEmail) {
    reminderAlert.value = {
      type: 'error',
      message: `Member ${bill.member?.name || ''} belum memiliki nomor WhatsApp maupun email terdaftar.`,
    }
    return
  }

  if (hasPhone && !hasEmail) {
    reminderChannel.value = 'whatsapp'
  } else if (!hasPhone && hasEmail) {
    reminderChannel.value = 'email'
  } else {
    reminderChannel.value = 'all'
  }

  showReminderModal.value = true
}

async function executeSendReminder() {
  if (!reminderBill.value) return
  sendingReminder.value = true
  reminderAlert.value = null
  try {
    const res = await apiFetch<any>(`/bills/${reminderBill.value.id}/remind`, {
      method: 'POST',
      body: { channel: reminderChannel.value },
    })
    const details = res.detail?.messages?.join(' · ') || res.message
    reminderAlert.value = {
      type: 'success',
      message: `Pengingat untuk ${reminderBill.value.member?.name || 'Member'}: ${details}`,
    }
    showReminderModal.value = false
  } catch (e: any) {
    reminderAlert.value = {
      type: 'error',
      message: e?.data?.message ?? 'Gagal mengirim pengingat ke member.',
    }
  } finally {
    sendingReminder.value = false
  }
}

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
