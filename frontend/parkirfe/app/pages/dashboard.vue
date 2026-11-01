<template>
  <div class="space-y-6">
    <!-- Welcome Header & Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-2xl font-bold tracking-tight text-white">Ringkasan Operasional</h1>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            Realtime
          </span>
        </div>
        <p class="text-xs text-slate-400 mt-1">Aktivitas arus kendaraan, tiket masuk, dan pendapatan parkir hari ini</p>
      </div>

      <div class="flex items-center gap-2.5">
        <button
          class="btn btn-secondary text-xs"
          @click="loadData"
          :disabled="recentLoading"
          title="Segarkan data ringkasan"
        >
          <AppIcon name="refresh" class="w-3.5 h-3.5" :class="recentLoading ? 'animate-spin' : ''" />
          <span>Perbarui Data</span>
        </button>
      </div>
    </div>

    <!-- 4 KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- 1. Tiket Non-Member -->
      <div class="stat-card">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tiket Non-Member</span>
          <div class="w-9 h-9 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
            <AppIcon name="ticket" class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-3">
          <p class="text-2xl font-bold font-mono tracking-tight text-white">
            {{ summary.non_member.transactions }}
            <span class="text-xs font-sans font-normal text-slate-400">tiket</span>
          </p>
          <div class="flex items-center gap-1.5 mt-2 text-[11px] text-blue-400">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
            <span>Kendaraan Umum Hari Ini</span>
          </div>
        </div>
      </div>

      <!-- 2. Pendapatan Non-Member -->
      <div class="stat-card">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pendapatan Tiket</span>
          <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
            <AppIcon name="receipt" class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-3">
          <p class="text-2xl font-bold font-mono tracking-tight text-emerald-400">
            {{ formatRupiah(summary.non_member.total_fee) }}
          </p>
          <div class="flex items-center gap-1.5 mt-2 text-[11px] text-emerald-400">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span>Tarif Parkir Per Jam</span>
          </div>
        </div>
      </div>

      <!-- 3. Tagihan Member Lunas -->
      <div class="stat-card">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Iuran Member</span>
          <div class="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
            <AppIcon name="users" class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-3">
          <p class="text-2xl font-bold font-mono tracking-tight text-white">
            {{ summary.member.transactions }}
            <span class="text-xs font-sans font-normal text-slate-400">transaksi</span>
          </p>
          <div class="flex items-center gap-1.5 mt-2 text-[11px] text-purple-400">
            <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
            <span>Iuran Member Terbayar</span>
          </div>
        </div>
      </div>

      <!-- 4. Grand Total Pendapatan -->
      <div class="stat-card bg-gradient-to-br from-slate-900/90 via-slate-900/60 to-blue-950/40 border-blue-500/20">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-300">Total Kas Hari Ini</span>
          <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 shadow-sm shadow-amber-500/20">
            <AppIcon name="coins" class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-3">
          <p class="text-2xl font-bold font-mono tracking-tight text-white">
            {{ formatRupiah(summary.grand_total) }}
          </p>
          <div class="flex items-center gap-1.5 mt-2 text-[11px] text-amber-400">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
            <span>Akumulasi Seluruh Pembayaran</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Operations Split Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- 1. Tiket Masuk Non-Member Quick Terminal -->
      <div class="glass-card p-6 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-blue-500/15 flex items-center justify-center text-blue-400">
                <AppIcon name="ticket" class="w-4 h-4" />
              </div>
              <h2 class="text-base font-semibold text-white">Pintu Masuk Non-Member</h2>
            </div>
            <NuxtLink to="/tiket" class="text-xs text-blue-400 hover:text-blue-300 flex items-center gap-1 transition-colors">
              <span>Buka Kasir Lengkap</span>
              <AppIcon name="arrow-right" class="w-3.5 h-3.5" />
            </NuxtLink>
          </div>

          <p class="text-xs text-slate-400 mb-5 leading-relaxed">
            Tekan tombol di bawah untuk mencetak tiket parkir barcode bagi kendaraan non-member yang baru masuk gate.
          </p>

          <button
            class="btn btn-primary w-full py-3.5 text-sm font-semibold tracking-wide"
            @click="issueTicket"
            :disabled="issuingTicket"
          >
            <svg v-if="issuingTicket" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span v-else class="flex items-center gap-2">
              <AppIcon name="printer" class="w-4 h-4" />
              <span>Cetak Tiket Masuk Sekarang</span>
            </span>
          </button>
        </div>

        <!-- Last Issued Ticket Thermal Preview -->
        <div v-if="lastTicket" class="mt-5 p-4 rounded-xl bg-slate-950/70 border border-emerald-500/30">
          <div class="flex items-center justify-between border-b border-slate-800/80 pb-2.5 mb-3">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400">
              <AppIcon name="check-circle" class="w-4 h-4" />
              <span>Tiket Berhasil Dicetak</span>
            </span>
            <span class="text-[11px] font-mono text-slate-400">{{ formatTime(lastTicket.entry_at) }} WIB</span>
          </div>

          <div class="flex items-center justify-between">
            <div>
              <p class="text-[11px] uppercase tracking-wider text-slate-500">KODE TOKEN</p>
              <p class="text-3xl font-mono font-bold tracking-widest text-white mt-0.5">{{ lastTicket.token }}</p>
            </div>
            <NuxtLink
              :to="`/tiket?token=${lastTicket.token}`"
              class="btn btn-outline text-xs"
            >
              Proses Bayar
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- 2. Gate Scan Member Quick Terminal -->
      <div class="glass-card p-6 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-indigo-500/15 flex items-center justify-center text-indigo-400">
                <AppIcon name="scan" class="w-4 h-4" />
              </div>
              <h2 class="text-base font-semibold text-white">Gate Akses Member (QR)</h2>
            </div>
            <NuxtLink to="/scan" class="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 transition-colors">
              <span>Mode Full Terminal</span>
              <AppIcon name="arrow-right" class="w-3.5 h-3.5" />
            </NuxtLink>
          </div>

          <p class="text-xs text-slate-400 mb-4 leading-relaxed">
            Verifikasi akses member dengan scanner optik atau masukkan kode member manual (tekan Enter).
          </p>

          <div class="flex gap-2">
            <div class="relative flex-1">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                <AppIcon name="scan" class="w-4 h-4" />
              </div>
              <input
                v-model="scanCode"
                type="text"
                class="form-input pl-9 font-mono uppercase tracking-wider"
                placeholder="Contoh: MBR-DEMO0001"
                @keydown.enter="doScan"
              />
            </div>
            <button
              class="btn btn-primary px-5 shrink-0"
              @click="doScan"
              :disabled="scanning || !scanCode.trim()"
            >
              <svg v-if="scanning" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <span v-else class="flex items-center gap-1.5">
                <AppIcon name="search" class="w-4 h-4" />
                <span>Verifikasi</span>
              </span>
            </button>
          </div>
        </div>

        <!-- Scan Result Feedback -->
        <div
          v-if="scanResult"
          class="mt-4 p-4 rounded-xl border transition-all"
          :class="scanResult.allowed
            ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300'
            : 'bg-rose-500/10 border-rose-500/30 text-rose-300'"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-2">
              <AppIcon :name="scanResult.allowed ? 'check-circle' : 'x-circle'" class="w-5 h-5 shrink-0" />
              <span class="text-sm font-bold tracking-wide">
                {{ scanResult.allowed ? 'AKSES DIIZINKAN (GATE DIBUKA)' : 'AKSES DITOLAK' }}
              </span>
            </div>
            <span class="text-[11px] font-mono opacity-80">{{ formatTime(new Date().toISOString()) }}</span>
          </div>

          <div v-if="scanResult.member" class="mt-2.5 pt-2 border-t border-white/10 text-xs text-slate-300 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="font-semibold text-white">{{ scanResult.member.name }}</span>
              <span class="text-slate-400">·</span>
              <span class="font-mono text-slate-200">{{ scanResult.member.plate_number }}</span>
            </div>
            <span class="uppercase text-[10px] px-2 py-0.5 rounded bg-white/10 font-medium">
              {{ scanResult.member.vehicle_category }}
            </span>
          </div>

          <p class="text-xs mt-1.5 opacity-90">{{ scanResult.reason }}</p>
        </div>
      </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="glass-card overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-800/80 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <AppIcon name="receipt" class="w-4 h-4 text-slate-400" />
          <h2 class="text-sm font-semibold text-white">Transaksi Kasir Terakhir Hari Ini</h2>
        </div>
        <NuxtLink to="/laporan" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
          Lihat Semua Laporan →
        </NuxtLink>
      </div>

      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Invoice / Token</th>
              <th>Kategori</th>
              <th>Kendaraan / Keterangan</th>
              <th>Biaya / Jumlah</th>
              <th>Waktu Transaksi</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="recentLoading">
              <td colspan="6" class="text-center py-10 text-xs text-slate-500">
                <div class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <span>Memuat data transaksi...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="!recentTransactions.length">
              <td colspan="6" class="text-center py-12 text-slate-400">
                <AppIcon name="receipt" class="w-8 h-8 mx-auto mb-2 text-slate-600" />
                <p class="text-sm font-medium text-slate-300">Belum ada pembayaran hari ini</p>
                <p class="text-xs text-slate-500 mt-1">Transaksi yang dibayar akan muncul otomatis di sini.</p>
              </td>
            </tr>

            <tr v-for="trx in recentTransactions" :key="trx.id + trx.type">
              <td>
                <span class="font-mono text-xs font-semibold px-2 py-1 rounded bg-slate-800/80 text-blue-300 border border-slate-700/60">
                  {{ trx.invoice_number || trx.token }}
                </span>
              </td>
              <td>
                <span
                  class="text-xs px-2.5 py-0.5 rounded-full font-medium"
                  :class="trx.type === 'member' ? 'badge-paid' : 'badge-active'"
                >
                  {{ trx.type === 'member' ? 'Member' : 'Non-Member' }}
                </span>
              </td>
              <td class="font-medium text-slate-200">{{ trx.vehicle }}</td>
              <td class="font-mono font-semibold text-emerald-400">{{ formatRupiah(trx.amount) }}</td>
              <td class="text-xs font-mono text-slate-400">{{ formatTime(trx.time) }} WIB</td>
              <td>
                <span class="badge-active text-xs px-2.5 py-0.5">Lunas</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Dashboard — E-Parkir Plaza Andalas' })

const { apiFetch } = useApi()

const issuingTicket = ref(false)
const lastTicket = ref<any>(null)
const scanCode = ref('')
const scanning = ref(false)
const scanResult = ref<any>(null)
const recentLoading = ref(true)
const recentTransactions = ref<any[]>([])

const summary = ref({
  non_member: { transactions: 0, total_fee: 0 },
  member: { transactions: 0, total_amount: 0 },
  grand_total: 0,
})

function formatRupiah(n: number) {
  return 'Rp ' + (n || 0).toLocaleString('id-ID')
}

function formatTime(iso: string) {
  if (!iso) return '-'
  return new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

async function loadData() {
  recentLoading.value = true
  try {
    const today = new Date().toISOString().split('T')[0]
    const [summaryData, nonMemberData, memberData] = await Promise.all([
      apiFetch<any>('/reports/summary'),
      apiFetch<any>(`/reports/non-member?per_page=5&date_from=${today}`),
      apiFetch<any>(`/reports/member?per_page=5&date_from=${today}`),
    ])

    summary.value = summaryData

    const nm = (nonMemberData.data || []).map((t: any) => ({
      id: t.id,
      type: 'non-member',
      token: t.token,
      vehicle: `${t.vehicle_category} · ${t.plate_number}`,
      amount: t.fee,
      time: t.exit_at,
      invoice_number: t.invoice_number,
    }))

    const mb = (memberData.data || []).map((b: any) => ({
      id: b.id,
      type: 'member',
      token: null,
      vehicle: `${b.member?.vehicle_category ?? '-'} · ${b.member?.name ?? '-'}`,
      amount: b.amount,
      time: b.paid_at,
      invoice_number: b.invoice_number,
    }))

    recentTransactions.value = [...nm, ...mb]
      .sort((a, b) => new Date(b.time).getTime() - new Date(a.time).getTime())
      .slice(0, 8)
  } catch (e) {
    console.error(e)
  } finally {
    recentLoading.value = false
  }
}

async function issueTicket() {
  issuingTicket.value = true
  lastTicket.value = null
  try {
    const data = await apiFetch<any>('/tickets', { method: 'POST' })
    lastTicket.value = data.ticket
  } catch (e: any) {
    alert(e?.data?.message ?? 'Gagal mencetak tiket')
  } finally {
    issuingTicket.value = false
  }
}

async function doScan() {
  if (!scanCode.value.trim()) return
  scanning.value = true
  scanResult.value = null
  try {
    const data = await apiFetch<any>('/members/scan', {
      method: 'POST',
      body: { member_code: scanCode.value.trim().toUpperCase() },
    })
    scanResult.value = data
  } catch (e: any) {
    scanResult.value = e?.data ?? { allowed: false, reason: 'Gagal terhubung ke scanner gate', member: null }
  } finally {
    scanning.value = false
  }
}

onMounted(loadData)
</script>
