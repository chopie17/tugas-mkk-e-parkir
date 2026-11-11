<template>
  <div class="nb-page h-dvh w-full flex flex-col items-center select-none relative overflow-y-auto lg:overflow-hidden">

    <main class="nb-main w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-[2vh] flex-1 min-h-0 flex flex-col justify-center relative z-10">

      <!-- ─── HERO ─────────────────────────────────────────────────────────── -->
      <section class="flex items-center justify-between gap-6">
        <div class="max-w-2xl">
          <h1 class="nb-h1 font-extrabold tracking-tight leading-[1.1]">
            <span class="text-[var(--nb-purple)]">Selamat datang</span> di<br class="hidden sm:block" />
            Plaza Andalas.
          </h1>
          <p class="mt-2 nb-lead text-[var(--nb-muted)] leading-relaxed max-w-xl">
            Pilih sesuai jenis kendaraan Anda. Gerbang akan terbuka otomatis setelah karcis terbit atau kartu member terverifikasi.
          </p>
        </div>

        <!-- Ilustrasi geometris -->
        <svg class="hidden md:block nb-art shrink-0" viewBox="0 0 200 200" aria-hidden="true">
          <defs><clipPath id="nb-circ"><circle cx="100" cy="100" r="94" /></clipPath></defs>
          <circle cx="100" cy="100" r="94" fill="#5ad8c9" />
          <g clip-path="url(#nb-circ)">
            <rect x="96" y="104" width="70" height="100" fill="#6c47ff" />
            <path d="M96 30 h40 a28 28 0 0 1 28 28 v46 h-68 z" fill="#e4f26b" />
            <path d="M28 120 a42 42 0 0 1 84 0 z" fill="#ff7a2f" />
            <path d="M52 120 a22 22 0 0 1 44 0 z" fill="#ff9a4d" />
            <path d="M118 66 q10 8 20 0" fill="none" stroke="#0a0a0a" stroke-width="3" stroke-linecap="round" />
          </g>
          <circle cx="100" cy="100" r="94" fill="none" stroke="#0a0a0a" stroke-width="4" />
        </svg>
      </section>

      <!-- ─── 2 PILIHAN KIOSK ──────────────────────────────────────────────── -->
      <div class="nb-grid grid grid-cols-1 lg:grid-cols-2">

        <!-- PILIHAN 1: PENGUNJUNG UMUM -->
        <section class="nb-card nb-pad nb-gap flex flex-col justify-between">
          <div class="nb-stack">
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <div class="nb-tile bg-[var(--nb-teal)] w-12 h-12 rounded-2xl flex items-center justify-center">
                  <AppIcon name="ticket" class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-[var(--nb-muted)]">Pengunjung umum</p>
                  <h2 class="text-xl font-extrabold">Karcis tiket masuk</h2>
                </div>
              </div>
              <span class="nb-chip bg-white text-xs">Sekali pakai</span>
            </div>

            <p class="nb-body leading-relaxed text-[var(--nb-text)] max-w-md">
              Untuk kendaraan non-langganan, tekan tombol di bawah. Karcis barcode akan dicetak dan palang gerbang terbuka otomatis.
            </p>

            <button
              type="button"
              class="nb-btn nb-btn-lg w-full bg-[var(--nb-lime)] flex items-center justify-center gap-3"
              :disabled="dispensingTicket || isGateOpen"
              @click="handleDispenseTicket"
            >
              <svg v-if="dispensingTicket" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              <template v-else>
                <AppIcon name="printer" class="w-6 h-6" />
                <span>Tekan untuk ambil tiket</span>
              </template>
            </button>

            <div class="nb-note nb-hide-short bg-[#f6f6f1]">
              <div class="flex items-center gap-2 font-bold text-sm">
                <AppIcon name="info" class="w-4 h-4 text-[var(--nb-purple)]" />
                <span>Petunjuk gerbang masuk</span>
              </div>
              <ul class="mt-2 space-y-1 text-sm text-[var(--nb-muted)] leading-relaxed">
                <li>Tidak perlu scan tiket saat masuk. Langsung tekan tombol karcis.</li>
                <li>Simpan karcis untuk discan di gerbang keluar.</li>
              </ul>
            </div>
          </div>

          <!-- Karcis terakhir -->
          <div v-if="lastDispensedTicket" class="nb-note bg-[#f1fbd0]">
            <div class="flex items-center justify-between pb-2 border-b-2 border-dashed border-black/30">
              <span class="font-bold text-sm flex items-center gap-1.5">
                <AppIcon name="check-circle" class="w-4 h-4" />
                <span>Karcis parkir terbit</span>
              </span>
              <span class="font-mono text-xs text-[var(--nb-muted)]">{{ formatTime(lastDispensedTicket.entry_at) }} WIB</span>
            </div>

            <div class="flex items-center justify-between gap-3 mt-3">
              <div>
                <span class="text-xs text-[var(--nb-muted)] block">Kode tiket</span>
                <span class="text-2xl font-mono font-extrabold tracking-widest">{{ lastDispensedTicket.token }}</span>
              </div>
              <button
                type="button"
                class="nb-btn nb-btn-sm bg-white flex items-center gap-1.5"
                @click="downloadReceiptImage(lastDispensedTicket)"
              >
                <AppIcon name="receipt" class="w-4 h-4" />
                <span>Unduh ulang karcis</span>
              </button>
            </div>
          </div>
        </section>

        <!-- PILIHAN 2: MEMBER -->
        <section class="nb-card nb-pad nb-gap flex flex-col justify-between">
          <div class="nb-stack">
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <div class="nb-tile bg-[var(--nb-purple)] text-white w-12 h-12 rounded-2xl flex items-center justify-center">
                  <AppIcon name="users" class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-[var(--nb-muted)]">Langganan bulanan</p>
                  <h2 class="text-xl font-extrabold">Member Parkir Plaza Andalas</h2>
                </div>
              </div>
              <span class="nb-chip bg-white text-xs whitespace-nowrap">Tap / Scan QR</span>
            </div>

            <p class="nb-body leading-relaxed text-[var(--nb-text)] max-w-md">
              Tempelkan kartu QR/RFID ke scanner, atau ketik kode member / plat nomor di bawah. Gerbang terbuka tanpa mencetak tiket berbayar.
            </p>

            <div class="space-y-3">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <AppIcon name="scan" class="w-5 h-5" />
                </div>
                <input
                  ref="memberInputRef"
                  v-model="memberCodeInput"
                  type="text"
                  class="nb-input"
                  placeholder="Kode member atau plat nomor"
                  @keydown.enter="handleScanMember"
                  @input="memberCodeInput = memberCodeInput.toUpperCase()"
                  @blur="refocusScanner"
                />
              </div>

              <button
                type="button"
                class="nb-btn nb-btn-lg w-full bg-[var(--nb-purple)] text-white flex items-center justify-center gap-3"
                :disabled="scanningMember || !memberCodeInput.trim() || isGateOpen"
                @click="handleScanMember"
              >
                <svg v-if="scanningMember" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <template v-else>
                  <AppIcon name="scan" class="w-6 h-6" />
                  <span>Verifikasi &amp; buka gerbang</span>
                </template>
              </button>
            </div>
          </div>

          <!-- Uji cepat -->
          <div class="nb-note bg-[#f6f6f1]">
            <p class="text-sm font-bold mb-2">Uji cepat kartu member</p>
            <div class="flex flex-wrap gap-2">
              <button type="button" class="nb-pill" @click="fillMember('MBR-DEMO0001')">MBR-DEMO0001 (Budi)</button>
              <button type="button" class="nb-pill" @click="fillMember('MBR-DEMO0002')">MBR-DEMO0002 (Siti)</button>
              <button type="button" class="nb-pill" @click="fillMember('BA 1234 XY')">Plat: BA 1234 XY</button>
            </div>
          </div>
        </section>
      </div>
    </main>

    <!-- ─── OVERLAY GERBANG ──────────────────────────────────────────────────── -->
    <Teleport to="body">
      <!-- 1. Gerbang dibuka -->
      <div
        v-if="isGateOpen"
        class="nb-overlay fixed inset-0 z-50 flex items-center justify-center p-4"
        role="status"
        aria-live="polite"
      >
        <div class="nb-card nb-modal w-full max-w-lg p-6 sm:p-8 text-center space-y-6 my-auto">
          <div class="space-y-2">
            <span class="nb-chip bg-[var(--nb-lime)] text-sm font-bold">
              <span class="w-2.5 h-2.5 rounded-full bg-black animate-ping"></span>
              Akses diizinkan, pintu masuk dibuka
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight pt-2">Silakan masuk</h2>
            <p v-if="gateMessage" class="text-base text-[var(--nb-text)] font-semibold">{{ gateMessage }}</p>
          </div>

          <!-- Animasi palang -->
          <div class="flex items-center justify-center">
            <div class="w-72 h-32 bg-[var(--nb-teal)] rounded-2xl border-2 border-black p-4 flex items-center justify-between relative overflow-hidden shadow-[4px_4px_0_#0a0a0a]">
              <div class="w-8 h-[72px] bg-[var(--nb-yellow)] rounded-lg border-2 border-black flex flex-col items-center justify-between py-1.5 z-20">
                <span class="w-2.5 h-2.5 rounded-full bg-[var(--nb-purple)] animate-pulse"></span>
                <span class="w-4 h-0.5 bg-black/40"></span>
                <span class="w-2 h-2 rounded-full bg-black"></span>
              </div>

              <div
                class="h-4 rounded-r-full origin-left border-2 border-black -rotate-45 translate-y-[-18px] w-52"
                style="background: repeating-linear-gradient(45deg, #ff7a2f, #ff7a2f 10px, #ffffff 10px, #ffffff 20px);"
              ></div>

              <div class="flex flex-col gap-1.5 p-2 rounded-lg bg-white border-2 border-black shrink-0">
                <span class="w-4 h-4 rounded-full bg-[var(--nb-lime)] border-2 border-black animate-pulse"></span>
                <span class="w-4 h-4 rounded-full bg-black/10 border-2 border-black"></span>
              </div>
            </div>
          </div>

          <!-- Countdown -->
          <div class="flex justify-center">
            <div class="nb-note bg-[#f1fbd0] flex items-center gap-3 text-left">
              <AppIcon name="clock" class="w-6 h-6" />
              <div>
                <p class="text-xs font-semibold text-[var(--nb-muted)]">Palang menutup otomatis dalam</p>
                <p class="text-3xl font-extrabold font-mono leading-none">{{ gateCountdown }} detik</p>
              </div>
            </div>
          </div>

          <div v-if="gateSubMessage" class="nb-note bg-[#f6f6f1] text-sm text-[var(--nb-text)]">
            {{ gateSubMessage }}
          </div>
        </div>
      </div>

      <!-- 2. Akses ditolak -->
      <div
        v-if="statusFeedback && !statusFeedback.allowed && !isGateOpen"
        class="nb-overlay fixed inset-0 z-50 flex items-center justify-center p-4"
        role="alert"
        @click.self="statusFeedback = null"
      >
        <div class="nb-card nb-modal w-full max-w-md p-6 sm:p-8 text-center space-y-5 my-auto">
          <div class="nb-tile bg-[var(--nb-orange)] w-14 h-14 rounded-2xl flex items-center justify-center mx-auto">
            <AppIcon name="alert-triangle" class="w-7 h-7" />
          </div>

          <div class="space-y-2">
            <span class="nb-chip bg-[var(--nb-orange)] text-sm font-bold">Akses ditolak, gerbang terkunci</span>
            <h3 class="text-xl font-extrabold pt-2">{{ statusFeedback.message }}</h3>
            <p v-if="statusFeedback.subtext" class="text-sm text-[var(--nb-muted)] leading-relaxed">
              {{ statusFeedback.subtext }}
            </p>
          </div>

          <button type="button" class="nb-btn nb-btn-md w-full bg-white" @click="statusFeedback = null">
            Tutup
          </button>
        </div>
      </div>
    </Teleport>

    <!-- ─── HIDDEN SCANNER TRAP INPUT ───────────────────────────────────────────
         Input ini tidak terlihat tapi selalu siap menerima input dari scanner hardware
         (USB HID barcode scanner / RFID reader). Ketika user tidak ada di field manual,
         scanner langsung inject ke sini, diproses oleh handleHiddenScannerKeydown.
    -->
    <input
      ref="hiddenScannerRef"
      v-model="hiddenScannerBuffer"
      type="text"
      autocomplete="off"
      autocorrect="off"
      autocapitalize="off"
      spellcheck="false"
      tabindex="-1"
      aria-hidden="true"
      class="fixed -bottom-96 left-0 w-px h-px opacity-0 pointer-events-none"
      @keydown="handleHiddenScannerKeydown"
      @blur="refocusScanner"
    />
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'
import { nextTick } from 'vue'

definePageMeta({ layout: false })
useHead({
  title: 'Terminal Gerbang Masuk (Gate IN) — E-Parkir Plaza Andalas',
  link: [
    { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
    { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
    {
      rel: 'stylesheet',
      href: 'https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
    },
  ],
})

const config = useRuntimeConfig()
const apiBase = computed(() => {
  let url = config.public.apiBase || 'http://localhost:8000/api'
  if (import.meta.client && typeof window !== 'undefined') {
    const currentHost = window.location.hostname
    if (url.includes('localhost') && currentHost !== 'localhost' && currentHost !== '127.0.0.1') {
      url = url.replace('localhost', currentHost)
    } else if (url.includes('127.0.0.1') && currentHost !== 'localhost' && currentHost !== '127.0.0.1') {
      url = url.replace('127.0.0.1', currentHost)
    }
  }
  return url
})

// Gate State (Kiosk ini khusus Gerbang Masuk / Gate IN)
const gateDirection = ref<'in'>('in')
const isGateOpen = ref(false)
const gateCountdown = ref(0)
let gateTimer: any = null

// Real-time Clock
const liveTime = ref('')
let clockTimer: any = null

// Non-Member States (Pencetakan Karcis Masuk)
const dispensingTicket = ref(false)
const lastDispensedTicket = ref<any>(null)

// Member States
const memberCodeInput = ref('')
const scanningMember = ref(false)
const memberInputRef = ref<HTMLInputElement>()

// Hidden scanner trap input (always focused to catch hardware scanner input)
const hiddenScannerRef = ref<HTMLInputElement>()
const hiddenScannerBuffer = ref('')

// ─── USB / HARDWARE SCANNER DEVICE DETECTION ─────────────────────────────────
const isScannerConnected = ref(false)
const scannerDeviceName = ref('')
const scannerConnecting = ref(false)
const lastScanFromHardware = ref(false)

function markHardwareScannerActive(deviceName = 'USB Barcode Scanner') {
  isScannerConnected.value = true
  lastScanFromHardware.value = true
  scannerDeviceName.value = deviceName
}

async function checkConnectedUsbDevices() {
  if (typeof navigator === 'undefined') return

  try {
    if ('hid' in navigator) {
      const devices = await (navigator as any).hid.getDevices()
      if (devices && devices.length > 0) {
        isScannerConnected.value = true
        scannerDeviceName.value = devices[0].productName || 'USB Barcode Scanner (HID)'
        return
      }
    }

    if ('usb' in navigator) {
      const usbDevices = await (navigator as any).usb.getDevices()
      if (usbDevices && usbDevices.length > 0) {
        isScannerConnected.value = true
        scannerDeviceName.value = usbDevices[0].productName || 'USB Scanner Device'
        return
      }
    }

    if (!lastScanFromHardware.value) {
      isScannerConnected.value = false
      scannerDeviceName.value = ''
    }
  } catch (err) {
    console.debug('Error checking USB devices:', err)
  }
}

async function connectUsbScanner() {
  if (typeof navigator === 'undefined') return
  scannerConnecting.value = true

  try {
    if ('hid' in navigator) {
      const devices = await (navigator as any).hid.requestDevice({ filters: [] })
      if (devices && devices.length > 0) {
        isScannerConnected.value = true
        scannerDeviceName.value = devices[0].productName || 'USB Barcode Scanner (HID)'
        playChime('success')
        return
      }
    } else if ('usb' in navigator) {
      const dev = await (navigator as any).usb.requestDevice({ filters: [] })
      if (dev) {
        isScannerConnected.value = true
        scannerDeviceName.value = dev.productName || 'USB Scanner Device'
        playChime('success')
        return
      }
    } else {
      alert('Browser ini belum mendukung WebHID/WebUSB. Namun alat scanner USB tetap langsung berfungsi saat dicolokkan ke port USB.')
    }
  } catch (err: any) {
    if (err?.name !== 'AbortError') {
      console.warn('Gagal request USB device:', err)
    }
  } finally {
    scannerConnecting.value = false
  }
}

function initHardwareListeners() {
  if (typeof navigator === 'undefined') return

  if ('hid' in navigator) {
    (navigator as any).hid.addEventListener('connect', (event: any) => {
      isScannerConnected.value = true
      scannerDeviceName.value = event?.device?.productName || 'USB Barcode Scanner (HID)'
      playChime('success')
    });
    (navigator as any).hid.addEventListener('disconnect', () => {
      checkConnectedUsbDevices()
      playChime('error')
    })
  }

  if ('usb' in navigator) {
    (navigator as any).usb.addEventListener('connect', (event: any) => {
      isScannerConnected.value = true
      scannerDeviceName.value = event?.device?.productName || 'USB Scanner Device'
      playChime('success')
    });
    (navigator as any).usb.addEventListener('disconnect', () => {
      checkConnectedUsbDevices()
      playChime('error')
    })
  }
}

// ─── GATE CENTER OVERLAY STATE ───────────────────────────────────────────────
const gateMessage = ref('')
const gateSubMessage = ref('')

// Status Feedback Banner (Pesan Ditolak / Gagal)
const statusFeedback = ref<{
  allowed: boolean
  message: string
  subtext?: string
} | null>(null)

function updateClock() {
  const now = new Date()
  liveTime.value = now.toLocaleDateString('id-ID', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }) + ' ' + now.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  }) + ' WIB'
}

function formatTime(iso: string) {
  if (!iso) return '-'
  return new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

// ─── AUDIO FEEDBACK SYNTHESIZER (Tanpa file eksternal) ──────────────────────
function playChime(type: 'success' | 'error' | 'print') {
  try {
    const AudioContext = window.AudioContext || (window as any).webkitAudioContext
    if (!AudioContext) return
    const ctx = new AudioContext()

    if (type === 'success') {
      // Dua nada harmonis (Bip-Bip tinggi)
      const osc1 = ctx.createOscillator()
      const gain1 = ctx.createGain()
      osc1.frequency.setValueAtTime(587.33, ctx.currentTime) // D5
      osc1.frequency.setValueAtTime(880, ctx.currentTime + 0.12) // A5
      gain1.gain.setValueAtTime(0.2, ctx.currentTime)
      gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35)
      osc1.connect(gain1)
      gain1.connect(ctx.destination)
      osc1.start()
      osc1.stop(ctx.currentTime + 0.35)
    } else if (type === 'error') {
      // Nada buzzer rendah (Buzzz)
      const osc = ctx.createOscillator()
      const gain = ctx.createGain()
      osc.type = 'sawtooth'
      osc.frequency.setValueAtTime(150, ctx.currentTime)
      osc.frequency.setValueAtTime(120, ctx.currentTime + 0.15)
      gain.gain.setValueAtTime(0.25, ctx.currentTime)
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4)
      osc.connect(gain)
      gain.connect(ctx.destination)
      osc.start()
      osc.stop(ctx.currentTime + 0.4)
    } else if (type === 'print') {
      // Nada mekanikal klik printer
      const osc = ctx.createOscillator()
      const gain = ctx.createGain()
      osc.frequency.setValueAtTime(987.77, ctx.currentTime) // B5
      gain.gain.setValueAtTime(0.15, ctx.currentTime)
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1)
      osc.connect(gain)
      gain.connect(ctx.destination)
      osc.start()
      osc.stop(ctx.currentTime + 0.1)
    }
  } catch {}
}

// ─── GATE OPEN / CLOSE CONTROLLER ──────────────────────────────────────────
function triggerGateOpen(durationSeconds: number = 5) {
  isGateOpen.value = true
  gateCountdown.value = durationSeconds
  playChime('success')

  if (gateTimer) clearInterval(gateTimer)
  gateTimer = setInterval(() => {
    gateCountdown.value--
    if (gateCountdown.value <= 0) {
      clearInterval(gateTimer)
      isGateOpen.value = false
      gateMessage.value = ''
      gateSubMessage.value = ''
    }
  }, 1000)
}

// ─── AUTO-DOWNLOAD TIKET KARCIS (CANVAS PNG GENERATOR) ──────────────────────
function downloadReceiptImage(ticket: any) {
  try {
    const canvas = document.createElement('canvas')
    canvas.width = 400
    canvas.height = 560
    const ctx = canvas.getContext('2d')
    if (!ctx) return

    // Background struk putih
    ctx.fillStyle = '#ffffff'
    ctx.fillRect(0, 0, canvas.width, canvas.height)

    // Border & Header
    ctx.fillStyle = '#0f172a'
    ctx.font = 'bold 20px sans-serif'
    ctx.textAlign = 'center'
    ctx.fillText('E-PARKIR PLAZA ANDALAS', 200, 45)

    ctx.font = '12px sans-serif'
    ctx.fillStyle = '#475569'
    ctx.fillText('Jl. Pemuda No. 1, Kota Padang', 200, 68)
    ctx.fillText('TIKET MASUK RESMI (NON-MEMBER)', 200, 88)

    // Garis putus-putus
    ctx.strokeStyle = '#94a3b8'
    ctx.setLineDash([6, 4])
    ctx.beginPath()
    ctx.moveTo(30, 110)
    ctx.lineTo(370, 110)
    ctx.stroke()

    // Token
    ctx.fillStyle = '#64748b'
    ctx.font = 'bold 12px sans-serif'
    ctx.fillText('KODE TIKET MASUK', 200, 140)

    ctx.fillStyle = '#090d16'
    ctx.font = 'bold 44px monospace'
    ctx.fillText(ticket.token, 200, 190)

    // Barcode Simulai
    ctx.fillStyle = '#0f172a'
    const barX = 60
    for (let i = 0; i < 40; i++) {
      const w = i % 4 === 0 ? 4 : (i % 2 === 0 ? 2 : 1)
      ctx.fillRect(barX + i * 7, 215, w, 40)
    }

    // Detail Garis putus-putus
    ctx.beginPath()
    ctx.moveTo(30, 280)
    ctx.lineTo(370, 280)
    ctx.stroke()

    // Rincian Waktu & Gate
    ctx.textAlign = 'left'
    ctx.font = '13px monospace'
    ctx.fillStyle = '#1e293b'

    const entryDate = new Date(ticket.entry_at).toLocaleString('id-ID', {
      day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    })

    ctx.fillText('Waktu Masuk : ' + entryDate + ' WIB', 40, 315)
    ctx.fillText('Terminal    : Gate Masuk 01', 40, 345)
    ctx.fillText('Jenis       : Sekali Pakai (Single-Use)', 40, 375)
    ctx.fillText('Tarif Motor : Rp 2.000 / Jam', 40, 405)
    ctx.fillText('Tarif Mobil : Rp 5.000 / Jam', 40, 435)

    // Footer
    ctx.setLineDash([])
    ctx.strokeStyle = '#cbd5e1'
    ctx.beginPath()
    ctx.moveTo(30, 465)
    ctx.lineTo(370, 465)
    ctx.stroke()

    ctx.textAlign = 'center'
    ctx.font = '10px sans-serif'
    ctx.fillStyle = '#64748b'
    ctx.fillText('* Harap simpan karcis ini untuk scan saat keluar.', 200, 495)
    ctx.fillText('* Kehilangan tiket dikenakan denda sesuai peraturan.', 200, 515)
    ctx.fillText('Terima kasih atas kunjungan Anda.', 200, 535)

    // Download file
    const link = document.createElement('a')
    link.download = `karcis-parkir-${ticket.token}.png`
    link.href = canvas.toDataURL('image/png')
    link.click()
  } catch (err) {
    console.error('Gagal membuat file karcis:', err)
  }
}

// ─── 1. NON-MEMBER: TEKAN TOMBOL AMBIL TIKET ──────────────────────────────
async function handleDispenseTicket() {
  dispensingTicket.value = true
  statusFeedback.value = null
  try {
    playChime('print')
    const res = await $fetch<any>(`${apiBase.value}/gate/tickets/dispense`, {
      method: 'POST',
      headers: { Accept: 'application/json' },
    })

    lastDispensedTicket.value = res.ticket

    // Auto-download tiket karcis fisik
    downloadReceiptImage(res.ticket)

    // Buka gerbang masuk
    gateMessage.value = `Tiket Masuk [${res.ticket.token}] Terbit · Gerbang Dibuka!`
    gateSubMessage.value = 'Karcis telah otomatis diunduh. Simpan karcis Anda untuk scan keluar.'
    triggerGateOpen(5)

    statusFeedback.value = {
      allowed: true,
      message: `Tiket Masuk [${res.ticket.token}] Terbit · Gerbang Dibuka!`,
      subtext: 'Karcis otomatis diunduh. Harap simpan tiket untuk discan saat keluar.',
    }
  } catch (e: any) {
    playChime('error')
    statusFeedback.value = {
      allowed: false,
      message: e?.data?.message ?? 'Gagal menerbitkan tiket. Hubungi petugas.',
    }
  } finally {
    dispensingTicket.value = false
  }
}

// ─── 2. MEMBER: SCAN ATAU INPUT MANUAL (ANTI-PASSBACK) ───────────────────────
async function handleScanMember() {
  if (!memberCodeInput.value.trim()) return
  scanningMember.value = true
  statusFeedback.value = null

  try {
    const res = await $fetch<any>(`${apiBase.value}/gate/members/scan`, {
      method: 'POST',
      body: {
        member_code: memberCodeInput.value.trim().toUpperCase(),
        direction: gateDirection.value,
      },
      headers: { Accept: 'application/json' },
    })

    // Berhasil masuk gerbang
    const memberName = res.member?.name || 'Member'
    const plate = res.member?.plate_number || ''

    gateMessage.value = `${res.reason} [${memberName} · ${plate}]`
    gateSubMessage.value = 'Status tercatat: DI DALAM AREA PARKIR (IN).'
    triggerGateOpen(5)

    statusFeedback.value = {
      allowed: true,
      message: `${res.reason} [${memberName} · ${plate}]`,
      subtext: 'Status member tercatat: DI DALAM AREA PARKIR (IN).',
    }

    memberCodeInput.value = ''
  } catch (e: any) {
    playChime('error')
    statusFeedback.value = {
      allowed: false,
      message: e?.data?.reason ?? e?.data?.message ?? 'Akses Member Ditolak.',
      subtext: 'Palang gerbang terkunci. Silakan hubungi pos petugas.',
    }
  } finally {
    scanningMember.value = false
  }
}

function fillMember(code: string) {
  memberCodeInput.value = code
}

// ─── GLOBAL HARDWARE SCANNER KEYBOARD LISTENER ─────────────────────────────
// Scanner hardware (USB/HID/RFID) mengirim karakter sangat cepat (<50ms/char)
// lalu diakhiri Enter. Hidden trap menangkap input ini dan meneruskannya ke verifikasi member.

let barcodeBuffer = ''
let lastKeyTime = 0
const SCANNER_CHAR_THRESHOLD_MS = 80  // karakter scanner HW < 80ms per char
const SCANNER_MIN_LENGTH = 4

/**
 * Smart router: arahkan scanner input langsung ke verifikasi member masuk
 */
function routeScannerInput(code: string) {
  const upper = code.trim().toUpperCase()
  if (!upper) return
  memberCodeInput.value = upper
  nextTick(() => handleScanMember())
}

/**
 * Handler keydown untuk hidden scanner trap.
 * Dipanggil saat hiddenScannerRef focused dan scanner hardware ngetik.
 */
function handleHiddenScannerKeydown(e: KeyboardEvent) {
  const currentTime = Date.now()
  if (currentTime - lastKeyTime > 300) {
    barcodeBuffer = ''
  }
  lastKeyTime = currentTime

  if (e.key === 'Enter') {
    if (barcodeBuffer.length >= SCANNER_MIN_LENGTH) {
      const captured = barcodeBuffer
      barcodeBuffer = ''
      hiddenScannerBuffer.value = ''
      markHardwareScannerActive('USB Barcode Scanner (HID)')
      routeScannerInput(captured)
    }
  } else if (e.key.length === 1) {
    barcodeBuffer += e.key
    hiddenScannerBuffer.value = barcodeBuffer
  }
}

/**
 * Fallback global keydown: tangkap scanner hardware bahkan saat tidak ada yang focused.
 * Jika user sedang fokus di input manual memberInputRef, biarkan field itu yang handle via @keydown.enter.
 */
function handleGlobalKeydown(e: KeyboardEvent) {
  const activeEl = document.activeElement as HTMLElement

  // Jika focus di hidden scanner input → sudah dihandle oleh handleHiddenScannerKeydown
  if (activeEl === hiddenScannerRef.value) return

  // Jika focus di field manual yang kita kenal → biarkan field itu yang handle
  if (activeEl === memberInputRef.value) return

  const activeTag = (activeEl?.tagName || '').toLowerCase()
  if ((activeTag === 'input' || activeTag === 'textarea') && activeEl !== hiddenScannerRef.value) {
    const currentTime = Date.now()
    if (currentTime - lastKeyTime > 300) barcodeBuffer = ''
    lastKeyTime = currentTime
    if (e.key === 'Enter' && barcodeBuffer.length >= SCANNER_MIN_LENGTH) {
      const captured = barcodeBuffer
      barcodeBuffer = ''
      markHardwareScannerActive('USB Barcode Scanner (HID)')
      routeScannerInput(captured)
      e.preventDefault()
    } else if (e.key.length === 1) {
      barcodeBuffer += e.key
    }
    return
  }

  // Tidak ada focus / focus di button/div → tangkap semua ke buffer
  const currentTime = Date.now()
  if (currentTime - lastKeyTime > 300) barcodeBuffer = ''
  lastKeyTime = currentTime

  if (e.key === 'Enter') {
    if (barcodeBuffer.length >= SCANNER_MIN_LENGTH) {
      const captured = barcodeBuffer
      barcodeBuffer = ''
      markHardwareScannerActive('USB Barcode Scanner (HID)')
      routeScannerInput(captured)
      e.preventDefault()
    }
  } else if (e.key.length === 1) {
    barcodeBuffer += e.key
    // Redirect focus ke hidden scanner agar karakter berikutnya tertangkap
    hiddenScannerRef.value?.focus()
  }
}

/** Refocus ke hidden scanner trap setelah interaksi UI */
function refocusScanner() {
  nextTick(() => {
    // Hanya refocus jika tidak ada modal/input yang sengaja dibuka user
    const activeEl = document.activeElement as HTMLElement
    const activeTag = (activeEl?.tagName || '').toLowerCase()
    if (activeTag !== 'input' && activeTag !== 'textarea') {
      hiddenScannerRef.value?.focus()
    }
  })
}

// ─── CROSS-WINDOW SYNC (Sync scan dari jendela Petugas ke Kiosk) ─────────────
let syncChannel: BroadcastChannel | null = null

function handleSyncEvent(event: any) {
  let data: any = null
  if (event instanceof MessageEvent) {
    data = event.data
  } else if (event?.key === 'parkir_kiosk_sync_event' && event.newValue) {
    try {
      data = JSON.parse(event.newValue)
    } catch {}
  }

  if (data?.type === 'SCAN_MEMBER' && data?.code) {
    memberCodeInput.value = data.code.toUpperCase()
    nextTick(() => handleScanMember())
  }
}

// ─── URL AUTO-TRIGGER (?scan=KODE) ───────────────────────────────────────────
// Ketika HP scan QR → HP buka URL kiosk dengan param ?scan=KODE → auto-process
function checkUrlScanParam() {
  const params = new URLSearchParams(window.location.search)
  const scanCode = params.get('scan')
  if (scanCode) {
    // Hapus param dari URL (clean URL) tanpa reload
    const cleanUrl = window.location.pathname
    window.history.replaceState({}, '', cleanUrl)
    // Trigger scan
    memberCodeInput.value = scanCode.toUpperCase()
    nextTick(() => handleScanMember())
  }
}

onMounted(() => {
  updateClock()
  clockTimer = setInterval(updateClock, 1000)
  window.addEventListener('keydown', handleGlobalKeydown)

  // Inisialisasi deteksi hardware scanner USB (WebHID/WebUSB)
  checkConnectedUsbDevices()
  initHardwareListeners()

  // Cross-window sync listener
  try {
    syncChannel = new BroadcastChannel('parkir_kiosk_sync')
    syncChannel.onmessage = handleSyncEvent
  } catch {}
  window.addEventListener('storage', handleSyncEvent)

  // Focus hidden scanner trap segera
  nextTick(() => {
    hiddenScannerRef.value?.focus()
    // Cek URL ?scan= parameter (dari QR scan via HP)
    checkUrlScanParam()
  })
})

onUnmounted(() => {
  if (clockTimer) clearInterval(clockTimer)
  if (gateTimer) clearInterval(gateTimer)
  window.removeEventListener('keydown', handleGlobalKeydown)
  if (syncChannel) {
    syncChannel.close()
    syncChannel = null
  }
  window.removeEventListener('storage', handleSyncEvent)
})
</script>

<!-- Soft neobrutalism: border tegas 2px, bayangan offset solid, sudut membulat, palet cerah -->
<style>
.nb-page {
  --nb-ink: #0a0a0a;
  --nb-bg: #f1f1eb;
  --nb-text: #1c1c1c;
  --nb-muted: #55555c;
  --nb-lime: #e4ff3a;
  --nb-purple: #6c47ff;
  --nb-teal: #5ad8c9;
  --nb-orange: #ff7a2f;
  --nb-yellow: #f4e96b;

  background: var(--nb-bg);
  color: var(--nb-text);
  font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
}
.nb-page .font-mono,
.nb-modal .font-mono {
  font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
}

/* Layout fit-to-screen: ukuran mengikuti tinggi layar agar tanpa scroll */
.nb-main { gap: clamp(0.75rem, 2.5vh, 1.5rem); }
.nb-grid { gap: clamp(0.75rem, 2.5vh, 2rem); }
.nb-pad { padding: clamp(1rem, 3vh, 2rem); }
.nb-gap { gap: clamp(0.6rem, 2vh, 1.5rem); }
.nb-stack > * + * { margin-top: clamp(0.6rem, 1.8vh, 1.25rem); }
.nb-h1 { font-size: clamp(1.75rem, 5.5vh, 3rem); }
.nb-lead { font-size: clamp(0.85rem, 2vh, 1.125rem); }
.nb-body { font-size: clamp(0.8rem, 1.9vh, 0.95rem); }
.nb-art { height: clamp(5rem, 17vh, 11rem); width: auto; aspect-ratio: 1; }
@media (max-height: 720px) {
  .nb-lead, .nb-hide-short { display: none; }
}

/* Header pill */
.nb-bar {
  background: #fff;
  border: 2px solid var(--nb-ink, #0a0a0a);
  border-radius: 22px;
  box-shadow: 4px 4px 0 #0a0a0a;
}

/* Kartu */
.nb-card {
  background: #fff;
  border: 2px solid #0a0a0a;
  border-radius: 24px;
  box-shadow: 6px 6px 0 #0a0a0a;
}
.nb-modal {
  box-shadow: 8px 8px 0 #0a0a0a;
}

/* Ikon tile */
.nb-tile {
  border: 2px solid #0a0a0a;
  box-shadow: 2px 2px 0 #0a0a0a;
}

/* Chip / badge */
.nb-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.35rem 0.8rem;
  border: 2px solid #0a0a0a;
  border-radius: 999px;
  font-weight: 600;
}

/* Kotak catatan */
.nb-note {
  border: 2px solid #0a0a0a;
  border-radius: 16px;
  padding: clamp(0.55rem, 1.4vh, 0.9rem) 1rem;
}

/* Tombol */
.nb-btn {
  border: 2px solid #0a0a0a;
  border-radius: 16px;
  font-weight: 800;
  color: #0a0a0a;
  box-shadow: 4px 4px 0 #0a0a0a;
  transition: transform 0.12s ease, box-shadow 0.12s ease;
  cursor: pointer;
}
.nb-btn.text-white { color: #fff; }
.nb-btn:hover:not(:disabled) {
  transform: translate(-1px, -1px);
  box-shadow: 6px 6px 0 #0a0a0a;
}
.nb-btn:active:not(:disabled) {
  transform: translate(4px, 4px);
  box-shadow: 0 0 0 #0a0a0a;
}
.nb-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  box-shadow: 2px 2px 0 #0a0a0a;
}
.nb-btn-sm { padding: 0.45rem 0.9rem; font-size: 0.8rem; border-radius: 12px; box-shadow: 3px 3px 0 #0a0a0a; }
.nb-btn-md { padding: 0.8rem 1.2rem; font-size: 0.95rem; }
.nb-btn-lg { padding: clamp(0.75rem, 2.2vh, 1.15rem) 1.4rem; font-size: clamp(1rem, 2.3vh, 1.15rem); }

/* Input */
.nb-input {
  width: 100%;
  padding: clamp(0.65rem, 1.8vh, 0.95rem) 1rem clamp(0.65rem, 1.8vh, 0.95rem) 2.9rem;
  background: #fff;
  color: #0a0a0a;
  border: 2px solid #0a0a0a;
  border-radius: 16px;
  box-shadow: 3px 3px 0 #0a0a0a;
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 1.05rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  outline: none;
  transition: box-shadow 0.12s ease, background 0.12s ease;
}
.nb-input::placeholder {
  color: #8a8a92;
  font-weight: 500;
  letter-spacing: 0;
  text-transform: none;
  font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
}
.nb-input:focus {
  background: #faf8ff;
  box-shadow: 4px 4px 0 var(--nb-purple, #6c47ff);
}

/* Pill demo */
.nb-pill {
  padding: 0.35rem 0.75rem;
  background: #fff;
  border: 2px solid #0a0a0a;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  box-shadow: 2px 2px 0 #0a0a0a;
  transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.12s ease;
  cursor: pointer;
}
.nb-pill:hover { background: var(--nb-lime, #e4ff3a); transform: translate(-1px, -1px); box-shadow: 3px 3px 0 #0a0a0a; }
.nb-pill:active { transform: translate(2px, 2px); box-shadow: 0 0 0 #0a0a0a; }

/* Overlay (nilai warna diulang karena overlay di-teleport ke body, di luar .nb-page) */
.nb-overlay {
  --nb-ink: #0a0a0a;
  --nb-text: #1c1c1c;
  --nb-muted: #55555c;
  --nb-lime: #e4ff3a;
  --nb-purple: #6c47ff;
  --nb-teal: #5ad8c9;
  --nb-orange: #ff7a2f;
  --nb-yellow: #f4e96b;
  background: rgba(241, 241, 235, 0.82);
  backdrop-filter: blur(6px);
  color: var(--nb-text);
  font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
}

/* Fokus keyboard terlihat jelas */
.nb-page button:focus-visible,
.nb-overlay button:focus-visible {
  outline: 3px solid #6c47ff;
  outline-offset: 3px;
}

@media (prefers-reduced-motion: reduce) {
  .nb-btn, .nb-pill, .nb-input { transition: none; }
  .nb-page .animate-ping,
  .nb-page .animate-pulse,
  .nb-overlay .animate-ping,
  .nb-overlay .animate-pulse { animation: none; }
}
</style>