<template>
  <div class="space-y-6 max-w-3xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-white">Terminal Scan Member</h1>
        <p class="text-xs text-slate-400 mt-1">Verifikasi kartu member QR code pada portal pintu masuk otomatis</p>
      </div>
      <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-xs text-slate-400 font-mono">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
        <span>GATE 01: AKTIF</span>
      </div>
    </div>

    <!-- Scanner Terminal Console -->
    <div class="glass-card p-6 sm:p-8 space-y-6">
      <!-- Visual Scanner Frame -->
      <div class="scanner-frame h-40 flex flex-col items-center justify-center p-6 text-center relative">
        <div class="scanner-laser"></div>
        <div class="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/30 flex items-center justify-center text-blue-400 mb-3 shadow-lg shadow-blue-500/10">
          <AppIcon name="scan" class="w-6 h-6" />
        </div>
        <p class="text-sm font-semibold text-white">Arahkan Scanner Optik ke Kartu QR Member</p>
        <p class="text-xs text-slate-400 mt-1">Atau ketikkan kode fisik member pada kolom input di bawah</p>
      </div>

      <!-- Scan Input Field -->
      <div class="flex gap-2">
        <div class="relative flex-1">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-mono text-xs">
            <AppIcon name="scan" class="w-4 h-4" />
          </div>
          <input
            ref="inputRef"
            v-model="code"
            type="text"
            class="form-input pl-10 text-center font-mono text-lg font-bold tracking-widest uppercase"
            placeholder="MBR-DEMO0001"
            @keydown.enter="doScan"
            @input="code = code.toUpperCase()"
            autofocus
          />
        </div>
        <button
          class="btn btn-primary px-6 shrink-0 font-semibold"
          @click="doScan"
          :disabled="scanning || !code.trim()"
        >
          <svg v-if="scanning" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span v-else class="flex items-center gap-1.5">
            <AppIcon name="search" class="w-4 h-4" />
            <span>Verifikasi Gate</span>
          </span>
        </button>
      </div>

      <!-- Live Scan Result Card -->
      <transition name="page">
        <div
          v-if="result"
          class="p-6 rounded-2xl border transition-all"
          :class="result.allowed
            ? 'bg-emerald-950/30 border-emerald-500/40 shadow-xl shadow-emerald-500/10'
            : 'bg-rose-950/30 border-rose-500/40 shadow-xl shadow-rose-500/10'"
        >
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
              <div
                class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 border"
                :class="result.allowed
                  ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400'
                  : 'bg-rose-500/20 border-rose-500/40 text-rose-400'"
              >
                <AppIcon :name="result.allowed ? 'gate' : 'x-circle'" class="w-7 h-7" />
              </div>

              <div>
                <span
                  class="inline-block text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full mb-1"
                  :class="result.allowed
                    ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                    : 'bg-rose-500/20 text-rose-300 border border-rose-500/30'"
                >
                  {{ result.allowed ? 'PORTAL DIBUKA · SILAKAN MASUK' : 'AKSES DITOLAK · PORTAL TERKUNCI' }}
                </span>
                <h3 class="text-lg font-bold text-white">{{ result.reason }}</h3>
              </div>
            </div>

            <button
              class="btn btn-secondary text-xs shrink-0"
              @click="reset"
            >
              <AppIcon name="refresh" class="w-3.5 h-3.5" />
              <span>Scan Berikutnya</span>
            </button>
          </div>

          <!-- Member Card Identity details -->
          <div v-if="result.member" class="mt-5 pt-4 border-t border-white/10 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
              <span class="text-[10px] text-slate-500 block uppercase">NAMA MEMBER</span>
              <span class="font-semibold text-white truncate block mt-0.5">{{ result.member.name }}</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
              <span class="text-[10px] text-slate-500 block uppercase">PLAT NOMOR</span>
              <span class="font-mono font-bold text-white block mt-0.5">{{ result.member.plate_number }}</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
              <span class="text-[10px] text-slate-500 block uppercase">JENIS KENDARAAN</span>
              <span class="font-semibold text-blue-400 block mt-0.5 uppercase">{{ result.member.vehicle_category }}</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
              <span class="text-[10px] text-slate-500 block uppercase">STATUS MEMBER</span>
              <span class="font-semibold text-emerald-400 block mt-0.5">Aktif Berlangganan</span>
            </div>
          </div>
        </div>
      </transition>
    </div>

    <!-- Scan History Ledger -->
    <div class="glass-card overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-800/80 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <AppIcon name="clock" class="w-4 h-4 text-slate-400" />
          <h2 class="text-sm font-semibold text-white">Log Riwayat Verifikasi Gate</h2>
        </div>
        <span class="text-xs text-slate-400 font-mono">{{ history.length }} log</span>
      </div>

      <div class="divide-y divide-slate-800/60 max-h-80 overflow-y-auto">
        <div v-if="!history.length" class="text-center py-10 text-xs text-slate-500">
          Belum ada riwayat verifikasi kartu pada sesi ini
        </div>

        <div
          v-for="(h, i) in history"
          :key="i"
          class="flex items-center justify-between gap-4 px-6 py-3.5 hover:bg-slate-800/40 transition-colors"
        >
          <div class="flex items-center gap-3 min-w-0">
            <span
              class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
              :class="h.allowed ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'"
            >
              <AppIcon :name="h.allowed ? 'check' : 'x'" class="w-4 h-4" />
            </span>
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-white truncate">{{ h.name }}</span>
                <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400 border border-slate-700">
                  {{ h.code }}
                </span>
              </div>
              <p class="text-[11px] text-slate-400 truncate mt-0.5">{{ h.reason }}</p>
            </div>
          </div>

          <span class="text-xs font-mono text-slate-500 shrink-0">{{ h.time }} WIB</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Scan Gate Member — E-Parkir Plaza Andalas' })

const { apiFetch } = useApi()
const inputRef = ref<HTMLInputElement>()
const code = ref('')
const scanning = ref(false)
const result = ref<any>(null)
const history = ref<any[]>([])

async function doScan() {
  if (!code.value.trim()) return
  scanning.value = true
  result.value = null
  try {
    const data = await apiFetch<any>('/members/scan', {
      method: 'POST',
      body: { member_code: code.value.trim().toUpperCase() },
    })
    result.value = data
    addHistory(code.value, data)
  } catch (e: any) {
    result.value = e?.data ?? { allowed: false, reason: 'Gagal terhubung ke scanner gate', member: null }
    addHistory(code.value, result.value)
  } finally {
    scanning.value = false
  }
}

function addHistory(scanCode: string, res: any) {
  history.value.unshift({
    code: scanCode,
    name: res.member?.name ?? scanCode,
    allowed: res.allowed,
    reason: res.reason,
    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
  })
  if (history.value.length > 20) history.value.pop()
}

function reset() {
  result.value = null
  code.value = ''
  nextTick(() => inputRef.value?.focus())
}
</script>
