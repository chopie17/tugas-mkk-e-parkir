<template>
  <div class="space-y-6">
    <!-- Welcome Header & Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1">
      <div>
        <div class="flex items-center gap-2.5">
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-[#0a0a0a]">Ringkasan Operasional</h1>
          <span class="nb-pill bg-[var(--nb-lime)] text-xs text-[#0a0a0a] font-black border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a]">
            Realtime
          </span>
        </div>
        <p class="text-xs sm:text-sm text-[var(--nb-muted)] mt-1 font-medium">Aktivitas arus kendaraan, tiket masuk, dan pendapatan parkir hari ini</p>
      </div>

      <div class="flex items-center gap-2.5">
        <button
          class="nb-btn bg-white text-black font-bold text-xs border-2 border-black shadow-[3px_3px_0_#0a0a0a] hover:bg-gray-100 flex items-center gap-1.5"
          @click="loadData"
          :disabled="recentLoading"
          title="Segarkan data ringkasan"
        >
          <AppIcon name="refresh" class="w-3.5 h-3.5" :class="recentLoading ? 'animate-spin' : ''" />
          <span>Perbarui Data</span>
        </button>
      </div>
    </div>

    <!-- 4 KPI Stat Cards (Soft Neo-Brutalism) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- 1. Tiket Non-Member -->
      <div class="nb-card bg-[var(--nb-teal)]/20 border-2 border-black shadow-[4px_4px_0_#0a0a0a] p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-black uppercase tracking-wider text-black">Tiket Non-Member</span>
          <div class="w-9 h-9 rounded-xl bg-white border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] flex items-center justify-center text-black">
            <AppIcon name="ticket" class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-3">
          <p class="text-2xl font-black font-mono tracking-tight text-[#0a0a0a]">
            {{ summary?.non_member?.transactions ?? 0 }}
            <span class="text-xs font-sans font-bold text-black/70">tiket</span>
          </p>
          <div class="flex items-center gap-1.5 mt-2 text-[11px] font-bold text-black">
            <span class="w-2 h-2 rounded-full bg-teal-500 border border-black"></span>
            <span>Kendaraan Umum Hari Ini</span>
          </div>
        </div>
      </div>

      <!-- 2. Pendapatan Non-Member -->
      <div class="nb-card bg-[var(--nb-lime)]/30 border-2 border-black shadow-[4px_4px_0_#0a0a0a] p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-black uppercase tracking-wider text-black">Pendapatan Tiket</span>
          <div class="w-9 h-9 rounded-xl bg-white border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] flex items-center justify-center text-black">
            <AppIcon name="receipt" class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-3">
          <p class="text-2xl font-black font-mono tracking-tight text-[#0a0a0a]">
            {{ formatRupiah(summary?.non_member?.total_fee ?? 0) }}
          </p>
          <div class="flex items-center gap-1.5 mt-2 text-[11px] font-bold text-black">
            <span class="w-2 h-2 rounded-full bg-emerald-500 border border-black"></span>
            <span>Tarif Parkir Jam-Jaman</span>
          </div>
        </div>
      </div>

      <!-- 3. Tagihan Member Lunas -->
      <div class="nb-card bg-[var(--nb-purple)]/15 border-2 border-black shadow-[4px_4px_0_#0a0a0a] p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-black uppercase tracking-wider text-black">Iuran Member</span>
          <div class="w-9 h-9 rounded-xl bg-white border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] flex items-center justify-center text-[var(--nb-purple)]">
            <AppIcon name="users" class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-3">
          <p class="text-2xl font-black font-mono tracking-tight text-[#0a0a0a]">
            {{ summary?.member?.transactions ?? 0 }}
            <span class="text-xs font-sans font-bold text-black/70">transaksi</span>
          </p>
          <div class="flex items-center gap-1.5 mt-2 text-[11px] font-bold text-black">
            <span class="w-2 h-2 rounded-full bg-purple-600 border border-black"></span>
            <span>Iuran Member Terbayar</span>
          </div>
        </div>
      </div>

      <!-- 4. Grand Total Pendapatan -->
      <div class="nb-card bg-[var(--nb-yellow)]/30 border-2 border-black shadow-[4px_4px_0_#0a0a0a] p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-black uppercase tracking-wider text-black">Total Kas Hari Ini</span>
          <div class="w-9 h-9 rounded-xl bg-white border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] flex items-center justify-center text-amber-600">
            <AppIcon name="coins" class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-3">
          <p class="text-2xl font-black font-mono tracking-tight text-[#0a0a0a]">
            {{ formatRupiah(summary?.grand_total ?? 0) }}
          </p>
          <div class="flex items-center gap-1.5 mt-2 text-[11px] font-bold text-black">
            <span class="w-2 h-2 rounded-full bg-amber-500 border border-black"></span>
            <span>Akumulasi Pembayaran</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── STATISTIK DIAGRAM BATANG (SOFT NEO-BRUTALISM) ───────────────────── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- 1. Diagram Batang Arus Kendaraan Per Jam (Left 8 cols) -->
      <div class="lg:col-span-8 nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl p-6 flex flex-col justify-between">
        <!-- Chart Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b-2 border-black/10 gap-3">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-[var(--nb-lime)] border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] flex items-center justify-center text-black">
              <AppIcon name="chart-bar" class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-black text-[#0a0a0a]">Statistik Arus Kendaraan Masuk</h2>
              <p class="text-xs text-[var(--nb-muted)] font-medium">Distribusi volume kendaraan per interval jam operasional hari ini</p>
            </div>
          </div>

          <!-- Legend -->
          <div class="flex items-center gap-3 self-start sm:self-auto">
            <div class="flex items-center gap-1.5 text-xs font-bold">
              <span class="w-3 h-3 rounded bg-[var(--nb-lime)] border border-black shadow-[1px_1px_0_#0a0a0a]"></span>
              <span>Motor</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs font-bold">
              <span class="w-3 h-3 rounded bg-[var(--nb-purple)] border border-black shadow-[1px_1px_0_#0a0a0a]"></span>
              <span>Mobil</span>
            </div>
          </div>
        </div>

        <!-- Bar Chart Visual Area -->
        <div class="pt-6 pb-2">
          <!-- Chart area with horizontal grid lines -->
          <div class="relative h-56 w-full flex items-end gap-2 sm:gap-4 px-2 sm:px-4 border-b-2 border-black">
            <!-- Background Grid Lines -->
            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-40">
              <div class="border-b border-dashed border-black/30 w-full"></div>
              <div class="border-b border-dashed border-black/30 w-full"></div>
              <div class="border-b border-dashed border-black/30 w-full"></div>
              <div class="border-b border-dashed border-black/30 w-full"></div>
            </div>

            <!-- Empty state if 0 vehicles today -->
            <div
              v-if="totalVolumeCount === 0 && !recentLoading"
              class="absolute inset-0 flex flex-col items-center justify-center bg-white/70 z-20 backdrop-blur-2xs"
            >
              <div class="w-10 h-10 rounded-xl bg-[var(--nb-yellow)]/30 border-2 border-black flex items-center justify-center mb-1.5">
                <AppIcon name="chart-bar" class="w-5 h-5 text-black" />
              </div>
              <p class="text-xs font-black text-black">Belum ada data transaksi kendaraan hari ini</p>
              <p class="text-[10px] text-[var(--nb-muted)] font-medium mt-0.5">Diagram akan terisi otomatis seiring transaksi tiket diproses.</p>
            </div>

            <!-- Bars per Hour Interval -->
            <div
              v-for="(slot, idx) in hourlyTraffic"
              :key="idx"
              class="relative flex-1 h-full flex items-end justify-center gap-1 group z-10"
            >
              <!-- Bar Motor -->
              <div
                v-if="slot.motor > 0"
                class="w-full max-w-[18px] bg-[var(--nb-lime)] border-2 border-black rounded-t-md shadow-[2px_2px_0_#0a0a0a] transition-all duration-300 group-hover:scale-y-105 origin-bottom relative"
                :style="{ height: `${slot.motorHeight}%` }"
              >
                <!-- Tooltip hover -->
                <div class="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-black text-white text-[10px] font-mono font-bold px-1.5 py-0.5 rounded shadow pointer-events-none whitespace-nowrap z-20">
                  {{ slot.motor }} motor
                </div>
              </div>

              <!-- Bar Mobil -->
              <div
                v-if="slot.mobil > 0"
                class="w-full max-w-[18px] bg-[var(--nb-purple)] border-2 border-black rounded-t-md shadow-[2px_2px_0_#0a0a0a] transition-all duration-300 group-hover:scale-y-105 origin-bottom relative"
                :style="{ height: `${slot.mobilHeight}%` }"
              >
                <!-- Tooltip hover -->
                <div class="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-black text-white text-[10px] font-mono font-bold px-1.5 py-0.5 rounded shadow pointer-events-none whitespace-nowrap z-20">
                  {{ slot.mobil }} mobil
                </div>
              </div>

              <!-- Subtle baseline dot if both 0 -->
              <div
                v-if="slot.motor === 0 && slot.mobil === 0"
                class="w-1.5 h-1.5 rounded-full bg-black/20 mb-0.5"
              ></div>
            </div>
          </div>

          <!-- X-Axis Labels (Hours) -->
          <div class="flex justify-between px-2 sm:px-4 pt-2 text-[11px] font-mono font-bold text-black/70">
            <span v-for="(slot, idx) in hourlyTraffic" :key="idx" class="flex-1 text-center">
              {{ slot.label }}
            </span>
          </div>
        </div>

        <!-- Bottom Insights Pill Bar -->
        <div class="mt-4 pt-3 border-t-2 border-black/10 flex flex-wrap items-center justify-between gap-2 text-xs font-bold">
          <div class="flex items-center gap-2">
            <span class="nb-pill bg-[var(--nb-yellow)]/40 text-black border-2 border-black px-2.5 py-0.5 text-[11px]">
              ⚡ Jam Puncak: {{ peakHourText }}
            </span>
            <span class="text-[var(--nb-muted)]">Rata-rata: {{ avgTrafficPerHour }} unit/jam</span>
          </div>
          <span class="text-black font-mono">Total Volume: {{ totalVolumeCount }} Unit</span>
        </div>
      </div>

      <!-- 2. Breakdown Kategori & Pendapatan (Right 4 cols) -->
      <div class="lg:col-span-4 nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl p-6 flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-2.5 pb-3 border-b-2 border-black/10 mb-4">
            <div class="w-9 h-9 rounded-xl bg-[var(--nb-teal)]/30 border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] flex items-center justify-center text-black">
              <AppIcon name="receipt" class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-black text-[#0a0a0a]">Komposisi Kendaraan</h2>
              <p class="text-xs text-[var(--nb-muted)] font-medium">Rasio kategori kendaraan & iuran</p>
            </div>
          </div>

          <!-- Horizontal Comparison Bars -->
          <div class="space-y-4 pt-1">
            <!-- 1. Sepeda Motor -->
            <div>
              <div class="flex justify-between items-center text-xs mb-1.5 font-bold">
                <span class="flex items-center gap-1.5 text-black">
                  <AppIcon name="motorcycle" class="w-4 h-4 text-emerald-600" />
                  <span>Sepeda Motor</span>
                </span>
                <span class="font-mono text-black">{{ categoryBreakdown.motorCount }} unit ({{ categoryBreakdown.motorPct }}%)</span>
              </div>
              <div class="w-full h-3.5 bg-[var(--nb-bg)] border-2 border-black rounded-full overflow-hidden p-0.5 shadow-[1px_1px_0_#0a0a0a]">
                <div
                  class="h-full bg-[var(--nb-lime)] rounded-full transition-all duration-500"
                  :style="{ width: `${categoryBreakdown.motorPct}%` }"
                ></div>
              </div>
            </div>

            <!-- 2. Mobil Roda 4 -->
            <div>
              <div class="flex justify-between items-center text-xs mb-1.5 font-bold">
                <span class="flex items-center gap-1.5 text-black">
                  <AppIcon name="car" class="w-4 h-4 text-purple-600" />
                  <span>Mobil Roda Empat</span>
                </span>
                <span class="font-mono text-black">{{ categoryBreakdown.mobilCount }} unit ({{ categoryBreakdown.mobilPct }}%)</span>
              </div>
              <div class="w-full h-3.5 bg-[var(--nb-bg)] border-2 border-black rounded-full overflow-hidden p-0.5 shadow-[1px_1px_0_#0a0a0a]">
                <div
                  class="h-full bg-[var(--nb-purple)] rounded-full transition-all duration-500"
                  :style="{ width: `${categoryBreakdown.mobilPct}%` }"
                ></div>
              </div>
            </div>

            <!-- 3. Member Bulanan -->
            <div>
              <div class="flex justify-between items-center text-xs mb-1.5 font-bold">
                <span class="flex items-center gap-1.5 text-black">
                  <AppIcon name="users" class="w-4 h-4 text-amber-600" />
                  <span>Iuran Member Parkir</span>
                </span>
                <span class="font-mono text-black">{{ summary?.member?.transactions ?? 0 }} Trx</span>
              </div>
              <div class="w-full h-3.5 bg-[var(--nb-bg)] border-2 border-black rounded-full overflow-hidden p-0.5 shadow-[1px_1px_0_#0a0a0a]">
                <div
                  class="h-full bg-[var(--nb-yellow)] rounded-full transition-all duration-500"
                  :style="{ width: `${categoryBreakdown.memberPct}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Summary Box at Bottom -->
        <div class="mt-5 p-3.5 rounded-xl bg-[var(--nb-bg)] border-2 border-black shadow-[2px_2px_0_#0a0a0a] space-y-1.5 text-xs font-bold">
          <div class="flex justify-between text-black/70 font-medium">
            <span>Rasio Non-Member : Member</span>
            <span class="font-mono text-black font-bold">{{ summary?.non_member?.transactions ?? 0 }} : {{ summary?.member?.transactions ?? 0 }}</span>
          </div>
          <div class="flex justify-between text-black/70 font-medium">
            <span>Rata-rata Tarif / Kendaraan</span>
            <span class="font-mono text-black font-bold">
              {{ formatRupiah(summary?.non_member?.transactions ? Math.round((summary?.non_member?.total_fee || 0) / summary.non_member.transactions) : 0) }}
            </span>
          </div>
          <div class="pt-1.5 border-t-2 border-black/10 flex justify-between items-center">
            <span class="text-black">Total Pendapatan Tiket</span>
            <span class="font-mono text-sm font-black text-black">{{ formatRupiah(summary?.non_member?.total_fee ?? 0) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl overflow-hidden">
      <div class="px-6 py-4 border-b-2 border-black flex items-center justify-between bg-[var(--nb-yellow)]/20">
        <div class="flex items-center gap-2.5">
          <AppIcon name="receipt" class="w-4 h-4 text-black" />
          <h2 class="text-sm font-black text-[#0a0a0a]">Transaksi Kasir Terakhir Hari Ini</h2>
          <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-white border border-black shadow-[1px_1px_0_#0a0a0a]">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Live Sync</span>
          </span>
        </div>
        <NuxtLink to="/laporan" class="text-xs font-bold text-[var(--nb-purple)] hover:underline flex items-center gap-1">
          <span>Lihat Semua Laporan</span>
          <AppIcon name="arrow-right" class="w-3.5 h-3.5" />
        </NuxtLink>
      </div>

      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Kode Tiket</th>
              <th>Plat Nomor</th>
              <th>Kendaraan</th>
              <th>Masuk Jam</th>
              <th>Keluar Jam</th>
              <th>Total Bayar</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="recentLoading && !recentTransactions.length">
              <td colspan="7" class="text-center py-10 text-xs text-[var(--nb-muted)]">
                <div class="flex items-center justify-center gap-2 font-bold">
                  <svg class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <span>Memuat data aktivitas parkir hari ini...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="!recentTransactions.length">
              <td colspan="7" class="text-center py-12 text-black/60">
                <AppIcon name="receipt" class="w-8 h-8 mx-auto mb-2 text-black/40" />
                <p class="text-sm font-bold text-black">Belum ada aktivitas parkir hari ini</p>
                <p class="text-xs text-[var(--nb-muted)] mt-1 font-medium">Kendaraan yang baru masuk atau keluar akan otomatis tampil di sini secara realtime.</p>
              </td>
            </tr>

            <tr v-for="trx in recentTransactions" :key="trx.id">
              <!-- 1. Kode Tiket -->
              <td>
                <div class="flex items-center gap-1.5">
                  <span class="font-mono text-xs font-black px-2 py-0.5 rounded bg-[var(--nb-bg)] text-black border-2 border-black shadow-[1px_1px_0_#0a0a0a]">
                    {{ trx.token }}
                  </span>
                  <span
                    class="text-[10px] font-bold px-1.5 py-0.2 rounded border border-black"
                    :class="trx.type === 'member' ? 'bg-purple-100 text-purple-900' : 'bg-teal-100 text-teal-900'"
                  >
                    {{ trx.type === 'member' ? 'Member' : 'Umum' }}
                  </span>
                </div>
              </td>

              <!-- 2. Plat Nomor -->
              <td>
                <span
                  v-if="trx.plate_number"
                  class="font-mono font-black text-xs px-2 py-0.5 rounded bg-gray-100 text-black border border-black shadow-[1px_1px_0_#0a0a0a] uppercase whitespace-nowrap"
                >
                  {{ trx.plate_number }}
                </span>
                <span v-else class="text-[11px] text-[var(--nb-muted)] italic font-medium">
                  (Diinput saat keluar)
                </span>
              </td>

              <!-- 3. Kendaraan -->
              <td>
                <div v-if="trx.vehicle_category" class="flex items-center gap-1 text-xs font-bold text-black capitalize">
                  <AppIcon :name="trx.vehicle_category === 'motor' ? 'motorcycle' : 'car'" class="w-3.5 h-3.5 text-black/70" />
                  <span>{{ trx.vehicle_category === 'motor' ? 'Motor' : 'Mobil' }}</span>
                </div>
                <span v-else class="text-[11px] text-[var(--nb-muted)]">
                  Pilih saat keluar
                </span>
              </td>

              <!-- 4. Jam Masuk -->
              <td class="text-xs font-mono font-bold text-black">
                <div class="flex items-center gap-1">
                  <AppIcon name="clock" class="w-3 h-3 text-black/50" />
                  <span>{{ formatTime(trx.entry_at) }} WIB</span>
                </div>
              </td>

              <!-- 5. Jam Keluar -->
              <td class="text-xs font-mono font-bold">
                <span v-if="trx.exit_at" class="text-black">
                  {{ formatTime(trx.exit_at) }} WIB
                </span>
                <span v-else class="text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-300 text-[10px]">
                  Masih di dalam
                </span>
              </td>

              <!-- 6. Total Bayar -->
              <td class="font-mono text-xs font-black">
                <span v-if="trx.type === 'member'" class="text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded border border-emerald-500 font-bold">
                  Rp 0 (Member)
                </span>
                <span v-else-if="trx.status === 'paid'" class="text-black font-extrabold">
                  {{ formatRupiah(trx.fee) }}
                </span>
                <span v-else class="text-[11px] text-[var(--nb-muted)] font-normal italic">
                  Dihitung saat keluar
                </span>
              </td>

              <!-- 7. Status -->
              <td>
                <span
                  v-if="trx.status === 'active'"
                  class="nb-pill bg-[var(--nb-yellow)] text-black border-2 border-black text-xs font-black shadow-[1px_1px_0_#0a0a0a]"
                >
                  Masih Parkir
                </span>
                <span
                  v-else
                  class="nb-pill bg-[var(--nb-lime)] text-black border-2 border-black text-xs font-black shadow-[1px_1px_0_#0a0a0a]"
                >
                  Lunas / Keluar
                </span>
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

const recentLoading = ref(true)
const recentTransactions = ref<any[]>([])
const rawNonMember = ref<any[]>([])
const rawMember = ref<any[]>([])

const summary = ref<any>({
  non_member: { transactions: 0, total_fee: 0 },
  member: { transactions: 0, total_amount: 0 },
  grand_total: 0,
})

function formatRupiah(n?: number) {
  return 'Rp ' + (n || 0).toLocaleString('id-ID')
}

function formatTime(iso?: string) {
  if (!iso) return '-'
  return new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

function getTodayDate(): string {
  const d = new Date()
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

// ─── HOURLY TRAFFIC BAR CHART STATS ──────────────────────────────────────────
const hourlyTraffic = computed(() => {
  const slots = [
    { label: '08:00', motor: 0, mobil: 0 },
    { label: '10:00', motor: 0, mobil: 0 },
    { label: '12:00', motor: 0, mobil: 0 },
    { label: '14:00', motor: 0, mobil: 0 },
    { label: '16:00', motor: 0, mobil: 0 },
    { label: '18:00', motor: 0, mobil: 0 },
    { label: '20:00', motor: 0, mobil: 0 },
    { label: '22:00', motor: 0, mobil: 0 },
  ]

  // Map real timestamps from today's non-member transactions
  for (const item of rawNonMember.value) {
    const timeStr = item.exit_at || item.entry_at || item.created_at
    if (!timeStr) continue
    const hour = new Date(timeStr).getHours()
    const slotIdx = Math.min(slots.length - 1, Math.max(0, Math.floor((hour - 8) / 2)))
    if (item.vehicle_category === 'mobil') {
      slots[slotIdx].mobil++
    } else {
      slots[slotIdx].motor++
    }
  }

  // Calculate highest count for bar percentage height
  let maxCount = 0
  for (const s of slots) {
    const m = Math.max(s.motor, s.mobil)
    if (m > maxCount) maxCount = m
  }

  return slots.map(s => ({
    ...s,
    motorHeight: maxCount > 0 && s.motor > 0 ? Math.max(8, Math.round((s.motor / maxCount) * 92)) : 0,
    mobilHeight: maxCount > 0 && s.mobil > 0 ? Math.max(8, Math.round((s.mobil / maxCount) * 92)) : 0,
  }))
})

const totalVolumeCount = computed(() => {
  return hourlyTraffic.value.reduce((acc, curr) => acc + curr.motor + curr.mobil, 0)
})

const peakHourText = computed(() => {
  if (totalVolumeCount.value === 0) return '-'
  let bestSlot = hourlyTraffic.value[0]
  let bestSum = 0
  for (const s of hourlyTraffic.value) {
    const sum = s.motor + s.mobil
    if (sum > bestSum) {
      bestSum = sum
      bestSlot = s
    }
  }
  return `${bestSlot.label} WIB`
})

const avgTrafficPerHour = computed(() => {
  if (totalVolumeCount.value === 0) return 0
  return Math.round(totalVolumeCount.value / (hourlyTraffic.value.length || 1))
})

// ─── CATEGORY & REVENUE RATIO ────────────────────────────────────────────────
const categoryBreakdown = computed(() => {
  let motor = 0
  let mobil = 0
  for (const item of rawNonMember.value) {
    if (item.vehicle_category === 'mobil') mobil++
    else motor++
  }

  const total = motor + mobil
  const motorPct = total > 0 ? Math.round((motor / total) * 100) : 0
  const mobilPct = total > 0 ? (100 - motorPct) : 0
  const memberTotal = summary.value?.member?.transactions || 0
  const grandUnits = total + memberTotal
  const memberPct = grandUnits > 0 ? Math.round((memberTotal / grandUnits) * 100) : 0

  return {
    motorCount: motor,
    mobilCount: mobil,
    motorPct,
    mobilPct,
    memberPct,
  }
})

let pollTimer: ReturnType<typeof setInterval> | null = null

async function loadData(isPolling = false) {
  if (!isPolling) {
    recentLoading.value = true
  }
  try {
    const today = getTodayDate()
    
    // Safely fetch all endpoints so one failure does not break the other data
    const [summaryRes, nonMemberRes, memberRes, activityRes] = await Promise.allSettled([
      apiFetch<any>('/reports/summary'),
      apiFetch<any>(`/reports/non-member?per_page=50&date_from=${today}`),
      apiFetch<any>(`/reports/member?per_page=50&date_from=${today}`),
      apiFetch<any>('/reports/today-activity'),
    ])

    if (summaryRes.status === 'fulfilled' && summaryRes.value) {
      summary.value = summaryRes.value
    }

    const nonMemberData = nonMemberRes.status === 'fulfilled' ? nonMemberRes.value : { data: [] }
    const memberData = memberRes.status === 'fulfilled' ? memberRes.value : { data: [] }

    rawNonMember.value = nonMemberData.data || []
    rawMember.value = memberData.data || []

    if (activityRes.status === 'fulfilled' && activityRes.value?.data) {
      recentTransactions.value = activityRes.value.data
    } else {
      // Fallback jika endpoint activity gagal
      const nm = (nonMemberData.data || []).map((t: any) => ({
        id: 't-' + t.id,
        type: 'non_member',
        token: t.token,
        plate_number: t.plate_number,
        vehicle_category: t.vehicle_category,
        fee: t.fee,
        entry_at: t.entry_at,
        exit_at: t.exit_at,
        status: t.status,
      }))

      const mb = (memberData.data || []).map((b: any) => ({
        id: 'm-' + b.id,
        type: 'member',
        token: b.member?.member_code,
        plate_number: b.member?.plate_number,
        vehicle_category: b.member?.vehicle_category,
        fee: 0,
        entry_at: b.paid_at,
        exit_at: b.paid_at,
        status: 'paid',
      }))

      recentTransactions.value = [...nm, ...mb]
        .sort((a, b) => new Date(b.entry_at || 0).getTime() - new Date(a.entry_at || 0).getTime())
        .slice(0, 15)
    }
  } catch (e) {
    console.error('Error loadData dashboard:', e)
  } finally {
    recentLoading.value = false
  }
}

onMounted(() => {
  loadData()
  // Auto-refresh setiap 5 detik agar saat ada kendaraan baru masuk langsung terdeteksi
  pollTimer = setInterval(() => {
    loadData(true)
  }, 5000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})
</script>
