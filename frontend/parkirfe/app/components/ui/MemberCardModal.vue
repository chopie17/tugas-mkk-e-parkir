<template>
  <Teleport to="body">
    <div
      v-if="modelValue && member"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
      @click.self="close"
    >
      <div class="nb-modal w-full max-w-xl p-6 sm:p-7 space-y-4 bg-white border-3 border-black shadow-[8px_8px_0_#0a0a0a] rounded-3xl relative my-auto animate-fade-in text-[#0a0a0a] print:p-0 print:border-none print:shadow-none">
        
        <!-- Header Modal (Hidden when printing) -->
        <div class="flex items-center justify-between pb-2 border-b-2 border-black/10 print:hidden">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[var(--nb-lime)] text-black border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] flex items-center justify-center font-black">
              <AppIcon name="scan" class="w-4 h-4" />
            </div>
            <div>
              <h2 class="text-base font-black text-[#0a0a0a]">Kartu Akses Member Parkir</h2>
              <p class="text-xs text-[var(--nb-muted)] font-medium">Smart Card 3D Resmi Pelanggan Plaza Andalas</p>
            </div>
          </div>
          <button
            type="button"
            class="p-1.5 text-black hover:bg-black/10 rounded-lg transition-colors border-2 border-transparent hover:border-black"
            @click="close"
          >
            <AppIcon name="close" class="w-4 h-4" />
          </button>
        </div>

        <!-- 3D Flip Instruction & Trigger Button -->
        <div class="flex items-center justify-between gap-2 pt-1 print:hidden">
          <span class="text-xs font-bold text-black/70 flex items-center gap-1.5">
            <span>💡</span>
            <span>Klik kartu untuk membalik (3D Flip)</span>
          </span>
          <button
            type="button"
            class="nb-btn bg-[var(--nb-yellow)] text-black text-xs font-black border-2 border-black shadow-[2px_2px_0_#0a0a0a] px-3 py-1 flex items-center gap-1.5 hover:bg-[#eae053] active:translate-x-0.5 active:translate-y-0.5"
            @click="isFlipped = !isFlipped"
          >
            <AppIcon name="refresh" class="w-3.5 h-3.5 transition-transform duration-500" :class="isFlipped ? 'rotate-180' : ''" />
            <span>{{ isFlipped ? 'Lihat Depan (Profil)' : 'Lihat Belakang (Barcode)' }}</span>
          </button>
        </div>

        <!-- ─── 3D PERSPECTIVE INTERACTIVE CARD CONTAINER ────────────────────── -->
        <div id="printable-member-card" class="w-full py-1">
          <div class="card-3d-scene w-full max-w-[490px] mx-auto">
            <div
              class="card-3d-object relative w-full aspect-[1.58/1] transition-transform duration-700 cursor-pointer select-none"
              :class="{ 'card-is-flipped': isFlipped }"
              @click="isFlipped = !isFlipped"
              title="Klik untuk membalik kartu (3D)"
            >
              <!-- ── SISI DEPAN (FRONT): Official Smart Card ID Badge ── -->
              <div
                class="card-face card-face-front absolute inset-0 w-full h-full rounded-2xl p-5 sm:p-6 border-3 border-black shadow-[6px_6px_0_#0a0a0a] flex flex-col justify-between overflow-hidden text-white"
                style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 50%, #312e81 100%);"
              >
                <!-- Background decorative glow & pattern -->
                <div class="absolute -right-12 -bottom-12 w-48 h-48 rounded-full bg-blue-500/15 blur-2xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-36 h-36 rounded-full bg-[var(--nb-lime)]/10 blur-xl pointer-events-none"></div>

                <!-- Card Top Header -->
                <div class="flex items-start justify-between relative z-10">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-[var(--nb-lime)] p-1 flex items-center justify-center border-2 border-black shadow-sm text-black">
                      <AppIcon name="gate" class="w-5 h-5 text-black" />
                    </div>
                    <div>
                      <h3 class="text-xs sm:text-sm font-black tracking-tight font-sans leading-tight">E-PARKIR PLAZA ANDALAS</h3>
                      <p class="text-[8.5px] sm:text-[9px] font-mono tracking-widest text-[var(--nb-lime)] uppercase font-bold">OFFICIAL MEMBERSHIP CARD</p>
                    </div>
                  </div>

                  <!-- RFID / Gold Chip Mockup -->
                  <div class="flex items-center gap-2">
                    <div class="w-8 sm:w-9 h-6 sm:h-7 rounded bg-gradient-to-tr from-amber-400 via-yellow-200 to-amber-500 border border-black p-0.5 flex flex-col justify-between shadow-sm">
                      <div class="w-full h-0.5 bg-amber-800/40"></div>
                      <div class="flex justify-between">
                        <span class="w-2 h-1 border-r border-amber-800/40"></span>
                        <span class="w-2 h-1 border-l border-amber-800/40"></span>
                      </div>
                      <div class="w-full h-0.5 bg-amber-800/40"></div>
                    </div>
                    <!-- Contactless Wave Icon -->
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-300 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.5 14.5A2.5 2.5 0 0 0 12 12m-5.5 5.5a6 6 0 0 0 8.5-8.5M3 21a10 10 0 0 0 14-14"/>
                    </svg>
                  </div>
                </div>

                <!-- Card Body: Member Info -->
                <div class="space-y-2 relative z-10 my-auto">
                  <div>
                    <p class="text-[9px] sm:text-[10px] text-slate-300 uppercase tracking-wider font-bold">NAMA PELANGGAN</p>
                    <div class="flex items-baseline justify-between gap-2">
                      <p class="text-base sm:text-xl font-black tracking-tight text-white font-sans truncate">{{ member.name }}</p>
                      <span v-if="member.phone_number" class="text-[9px] sm:text-[10px] font-mono font-bold text-emerald-300 shrink-0">
                        📱 {{ member.phone_number }}
                      </span>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-2 pt-0.5">
                    <div>
                      <p class="text-[9px] sm:text-[10px] text-slate-300 uppercase tracking-wider font-bold">PERUSAHAAN / INSTANSI</p>
                      <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded bg-black font-bold text-xs sm:text-sm text-[var(--nb-lime)] border border-black truncate max-w-[150px]" :title="member.company_name">
                        {{ member.company_name || 'Member Resmi' }}
                      </span>
                    </div>
                    <div>
                      <p class="text-[9px] sm:text-[10px] text-slate-300 uppercase tracking-wider font-bold">KENDARAAN</p>
                      <div class="flex items-center gap-1.5 mt-1 text-xs font-bold text-slate-200 capitalize">
                        <AppIcon :name="member.vehicle_category === 'motor' ? 'motorcycle' : 'car'" class="w-3.5 h-3.5 text-emerald-400" />
                        <span>{{ member.vehicle_category === 'motor' ? 'Sepeda Motor' : 'Mobil Roda 4' }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Card Bottom Bar -->
                <div class="pt-2 border-t border-white/20 flex items-center justify-between text-xs relative z-10 font-bold">
                  <div>
                    <span class="text-[8px] sm:text-[9px] text-slate-300 block uppercase font-mono">STATUS PARKIR</span>
                    <span
                      class="font-mono text-[10px] sm:text-[11px]"
                      :class="member.parking_status === 'in' ? 'text-emerald-400' : 'text-slate-300'"
                    >
                      {{ member.parking_status === 'in' ? '● DI DALAM AREA' : '○ DI LUAR AREA' }}
                    </span>
                  </div>
                  <div class="text-right">
                    <span class="text-[8px] sm:text-[9px] text-slate-300 block uppercase font-mono">IURAN BULANAN</span>
                    <span class="font-mono font-black text-[var(--nb-lime)] text-xs sm:text-sm">
                      Rp {{ (member.monthly_fee || 0).toLocaleString('id-ID') }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- ── SISI BELAKANG (BACK): Magnetic Stripe + QR + Barcode Pad ── -->
              <div
                class="card-face card-face-back absolute inset-0 w-full h-full rounded-2xl border-3 border-black shadow-[6px_6px_0_#0a0a0a] flex flex-col justify-between overflow-hidden bg-[#fafaf7] text-black"
              >
                <!-- Top Magnetic Stripe -->
                <div class="w-full bg-[#111827] h-8 sm:h-9 border-b-2 border-black flex items-center px-4 justify-between">
                  <span class="text-[8px] font-mono tracking-widest text-slate-400 font-bold uppercase">PLAZA ANDALAS SMART PASS</span>
                  <span class="text-[8px] font-mono text-[var(--nb-lime)] font-bold">CARD ID: {{ member.id }}</span>
                </div>

                <!-- Main Middle Area: QR Code + Barcode 1D & Info -->
                <div class="px-4 sm:px-5 py-2 flex items-center justify-between gap-3 sm:gap-4 my-auto">
                  <!-- QR Code Box -->
                  <div class="flex flex-col items-center shrink-0">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 p-1 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-[2px_2px_0_#0a0a0a]">
                      <div class="w-full h-full flex items-center justify-center" v-html="qrSvg"></div>
                    </div>
                    <span class="text-[8px] sm:text-[9px] font-mono font-black text-black mt-1 uppercase tracking-wider">SCAN QR</span>
                  </div>

                  <!-- 1D Barcode & Plate Signature -->
                  <div class="flex-1 min-w-0 space-y-1">
                    <div class="flex items-center justify-between border-b-2 border-black/10 pb-1">
                      <span class="text-[9px] font-mono font-black uppercase text-black/70">INSTANSI</span>
                      <span class="text-[9px] font-mono font-black px-1.5 py-0.2 rounded bg-black text-white truncate max-w-[130px]">{{ member.company_name || 'Plaza Andalas' }}</span>
                    </div>

                    <!-- 1D Barcode Vector -->
                    <div class="h-9 sm:h-11 w-full flex items-center justify-center overflow-hidden" v-html="barcodeSvg"></div>

                    <!-- Member Code Text -->
                    <p class="font-mono text-xs sm:text-sm font-black tracking-widest text-center text-black">
                      {{ member.member_code }}
                    </p>
                  </div>
                </div>

                <!-- Bottom Notice Bar -->
                <div class="px-4 py-1.5 bg-[#f0f0ea] border-t-2 border-black flex items-center justify-between text-[8px] sm:text-[9px] font-bold text-black/70">
                  <span>* Arahkan Barcode atau QR ini ke optik scanner gate</span>
                  <span class="font-mono text-black">ANTI-PASSBACK PROTECTED</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ─── ACTION BUTTONS (Hidden when printing) ────────────────────────── -->
        <div class="space-y-3 pt-1 print:hidden">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <!-- 1. Download Kartu PNG -->
            <button
              type="button"
              class="nb-btn bg-white text-black text-xs font-bold border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-gray-100 flex items-center justify-center gap-1.5 py-2"
              @click="downloadCardImage"
            >
              <AppIcon name="receipt" class="w-4 h-4 text-black" />
              <span>Unduh (PNG)</span>
            </button>

            <!-- 2. Cetak Kartu -->
            <button
              type="button"
              class="nb-btn bg-white text-black text-xs font-bold border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-gray-100 flex items-center justify-center gap-1.5 py-2"
              @click="printCard"
            >
              <AppIcon name="printer" class="w-4 h-4 text-black" />
              <span>Cetak Fisik</span>
            </button>

            <!-- 3. Salin Kode -->
            <button
              type="button"
              class="nb-btn bg-white text-black text-xs font-bold border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-gray-100 flex items-center justify-center gap-1.5 py-2"
              @click="copyCode"
            >
              <AppIcon :name="copied ? 'check' : 'key'" class="w-4 h-4 text-black" />
              <span>{{ copied ? 'Tersalin!' : 'Salin Kode' }}</span>
            </button>
          </div>

          <!-- Uji Langsung ke Gate Kiosk (Cross-Window Sync) -->
          <div class="p-3 rounded-2xl bg-[var(--nb-bg)] border-2 border-black shadow-[2px_2px_0_#0a0a0a] space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="font-black text-[#0a0a0a] flex items-center gap-1.5">
                <AppIcon name="scan" class="w-3.5 h-3.5" />
                <span>Simulasi Scan Langsung ke Tab Kiosk</span>
              </span>
              <span v-if="syncStatus" class="text-[11px] font-black text-emerald-700 animate-pulse">
                {{ syncStatus }}
              </span>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <button
                type="button"
                class="nb-btn bg-[var(--nb-lime)] text-black text-xs font-black border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-[#d4f028] flex items-center justify-center gap-1.5 py-2"
                @click="sendScanToKiosk('in')"
              >
                <span>⚡ Scan Masuk (Gate IN)</span>
              </button>
              <button
                type="button"
                class="nb-btn bg-white text-black text-xs font-black border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-gray-100 flex items-center justify-center gap-1.5 py-2"
                @click="sendScanToKiosk('out')"
              >
                <span>⚡ Scan Keluar (Gate OUT)</span>
              </button>
            </div>
            <p class="text-[10px] text-[var(--nb-muted)] font-medium">
              * Tombol di atas langsung mensimulasikan scan barcode kartu member ini ke gate Kiosk secara otomatis.
            </p>
          </div>

          <!-- Link Buka Kiosk Tab -->
          <NuxtLink
            to="/"
            target="_blank"
            class="nb-btn bg-[var(--nb-purple)] text-white w-full py-2.5 text-xs font-bold border-2 border-black shadow-[3px_3px_0_#0a0a0a] flex items-center justify-center gap-2 hover:opacity-90"
          >
            <AppIcon name="gate" class="w-4 h-4" />
            <span>Buka Terminal Gate Kiosk (Tab Baru)</span>
          </NuxtLink>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import AppIcon from './AppIcon.vue'
import { generateBarcodeSVG, generateQRCodeSVG } from '~/utils/barcode'

const props = defineProps<{
  modelValue: boolean
  member: any
}>()

const emit = defineEmits(['update:modelValue'])

const isFlipped = ref(false)
const copied = ref(false)
const syncStatus = ref('')

watch(() => props.modelValue, (val) => {
  if (val) {
    isFlipped.value = false
  }
})

function sendScanToKiosk(dir: 'in' | 'out') {
  if (!props.member?.member_code) return
  const payload = {
    type: 'SCAN_MEMBER',
    code: props.member.member_code,
    direction: dir,
    timestamp: Date.now(),
  }

  // 1. BroadcastChannel (modern real-time cross-tab)
  try {
    const channel = new BroadcastChannel('parkir_kiosk_sync')
    channel.postMessage(payload)
    channel.close()
  } catch {}

  // 2. LocalStorage Event (cross-window fallback)
  try {
    localStorage.setItem('parkir_kiosk_sync_event', JSON.stringify(payload))
  } catch {}

  syncStatus.value = `✓ Terkirim ke Gate ${dir.toUpperCase()}!`
  setTimeout(() => {
    syncStatus.value = ''
  }, 3000)
}

function close() {
  emit('update:modelValue', false)
}

const qrSvg = computed(() => {
  if (!props.member?.member_code) return ''
  let origin = 'http://192.168.1.9:3000'
  if (typeof window !== 'undefined') {
    origin = window.location.origin.replace('localhost', '192.168.1.9').replace('127.0.0.1', '192.168.1.9')
  }
  const scanUrl = `${origin}/?scan=${props.member.member_code}`
  return generateQRCodeSVG(scanUrl, 2)
})

const barcodeSvg = computed(() => {
  if (!props.member?.member_code) return ''
  return generateBarcodeSVG(props.member.member_code, 38)
})

async function copyCode() {
  if (!props.member?.member_code) return
  const text = props.member.member_code

  try {
    if (navigator?.clipboard?.writeText) {
      await navigator.clipboard.writeText(text)
    } else {
      // Fallback untuk non-HTTPS / LAN IP (http://192.168.x.x)
      const textArea = document.createElement('textarea')
      textArea.value = text
      textArea.style.position = 'fixed'
      textArea.style.left = '-999999px'
      textArea.style.top = '-999999px'
      document.body.appendChild(textArea)
      textArea.focus()
      textArea.select()
      document.execCommand('copy')
      textArea.remove()
    }
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Gagal menyalin kode member:', err)
  }
}

function printCard() {
  window.print()
}

/**
 * Render kartu member digital ke canvas dan unduh sebagai file PNG beresolusi tinggi.
 */
function downloadCardImage() {
  if (!props.member) return
  const m = props.member

  try {
    const canvas = document.createElement('canvas')
    canvas.width = 650
    canvas.height = 760
    const ctx = canvas.getContext('2d')
    if (!ctx) return

    // Background Container
    ctx.fillStyle = '#070b14'
    ctx.fillRect(0, 0, canvas.width, canvas.height)

    // ── 1. FRONT SMART CARD BADGE ──
    const grad = ctx.createLinearGradient(30, 30, 620, 380)
    grad.addColorStop(0, '#0f1b35')
    grad.addColorStop(0.5, '#0b1325')
    grad.addColorStop(1, '#15274d')

    ctx.fillStyle = grad
    ctx.beginPath()
    ctx.roundRect(30, 30, 590, 330, 20)
    ctx.fill()
    ctx.strokeStyle = '#3b82f6'
    ctx.lineWidth = 1.5
    ctx.stroke()

    // Header Kartu
    ctx.fillStyle = '#ffffff'
    ctx.font = 'bold 20px sans-serif'
    ctx.textAlign = 'left'
    ctx.fillText('E-PARKIR PLAZA ANDALAS', 60, 75)

    ctx.fillStyle = '#93c5fd'
    ctx.font = '10px monospace'
    ctx.fillText('OFFICIAL MEMBERSHIP CARD · RFID & SMART CHIP', 60, 95)

    // Gold Chip
    ctx.fillStyle = '#f59e0b'
    ctx.beginPath()
    ctx.roundRect(520, 55, 60, 42, 6)
    ctx.fill()
    ctx.strokeStyle = '#b45309'
    ctx.stroke()

    // Nama Member
    ctx.fillStyle = '#94a3b8'
    ctx.font = 'bold 11px sans-serif'
    ctx.fillText('NAMA PELANGGAN', 60, 150)

    ctx.fillStyle = '#ffffff'
    ctx.font = 'bold 26px sans-serif'
    ctx.fillText(m.name, 60, 185)

    // Plat Nomor
    ctx.fillStyle = '#94a3b8'
    ctx.font = 'bold 11px sans-serif'
    ctx.fillText('NOMOR PLAT KENDARAAN', 60, 235)

    ctx.fillStyle = '#0f172a'
    ctx.beginPath()
    ctx.roundRect(60, 245, 180, 38, 8)
    ctx.fill()
    ctx.strokeStyle = '#f59e0b'
    ctx.stroke()

    ctx.fillStyle = '#fde047'
    ctx.font = 'bold 20px monospace'
    ctx.textAlign = 'center'
    ctx.fillText(m.plate_number, 150, 271)

    // Kategori
    ctx.textAlign = 'left'
    ctx.fillStyle = '#94a3b8'
    ctx.font = 'bold 11px sans-serif'
    ctx.fillText('KATEGORI KENDARAAN', 300, 235)

    ctx.fillStyle = '#ffffff'
    ctx.font = 'bold 18px sans-serif'
    ctx.fillText(m.vehicle_category === 'motor' ? 'Sepeda Motor' : 'Mobil Roda 4', 300, 270)

    // Garis Bawah
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)'
    ctx.beginPath()
    ctx.moveTo(60, 305)
    ctx.lineTo(560, 305)
    ctx.stroke()

    ctx.fillStyle = '#10b981'
    ctx.font = 'bold 12px monospace'
    ctx.fillText('STATUS: AKTIF BERLANGGANAN', 60, 332)

    ctx.textAlign = 'right'
    ctx.fillStyle = '#e2e8f0'
    ctx.fillText('IURAN: Rp ' + (m.monthly_fee || 0).toLocaleString('id-ID') + ' / Bln', 560, 332)

    // ── 2. WHITE SCANNABLE BARCODE PAD ──
    ctx.fillStyle = '#ffffff'
    ctx.beginPath()
    ctx.roundRect(30, 380, 590, 340, 20)
    ctx.fill()

    ctx.textAlign = 'center'
    ctx.fillStyle = '#0f172a'
    ctx.font = 'bold 18px monospace'
    ctx.fillText(m.member_code, 325, 420)

    ctx.fillStyle = '#64748b'
    ctx.font = '11px sans-serif'
    ctx.fillText('SCAN BARCODE / QR CODE INI DI GERBANG MASUK & KELUAR', 325, 442)

    // Draw Barcode Lines
    ctx.fillStyle = '#0f172a'
    const barX = 75
    const barY = 465
    for (let i = 0; i < 65; i++) {
      const w = i % 5 === 0 ? 5 : (i % 3 === 0 ? 3 : (i % 2 === 0 ? 2 : 1))
      ctx.fillRect(barX + i * 7.5, barY, w, 70)
    }

    // Footer info
    ctx.fillStyle = '#475569'
    ctx.font = '11px sans-serif'
    ctx.fillText('* Kartu ini berlaku sebagai akses portal parkir resmi Plaza Andalas.', 325, 575)
    ctx.fillText('* Dilarang memindahtangankan kartu kepada kendaraan lain (Anti-Passback Protected).', 325, 595)

    // Download link
    const link = document.createElement('a')
    link.download = `kartu-member-${m.member_code}.png`
    link.href = canvas.toDataURL('image/png')
    link.click()
  } catch (err) {
    console.error('Gagal generate kartu:', err)
  }
}
</script>

<style scoped>
.card-3d-scene {
  perspective: 1200px;
}

.card-3d-object {
  transform-style: preserve-3d;
  transition: transform 0.65s cubic-bezier(0.4, 0.2, 0.2, 1);
}

.card-3d-object.card-is-flipped {
  transform: rotateY(180deg);
}

.card-face {
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
}

.card-face-back {
  transform: rotateY(180deg);
}

@media print {
  body * {
    visibility: hidden;
  }
  #printable-member-card, #printable-member-card * {
    visibility: visible;
  }
  #printable-member-card {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
