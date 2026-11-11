<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center gap-2.5">
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-[#0a0a0a]">Konfigurasi Tarif Parkir</h1>
          <span class="nb-pill bg-[var(--nb-lime)] text-xs text-[#0a0a0a] font-black border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a]">
            Super Admin
          </span>
        </div>
        <p class="text-xs sm:text-sm text-[var(--nb-muted)] mt-1 font-medium">
          Atur besaran tarif progresif per jam kendaraan non-member dan iuran langganan member bulanan Plaza Andalas
        </p>
      </div>

      <button
        class="nb-btn bg-white text-black font-bold text-xs border-2 border-black shadow-[3px_3px_0_#0a0a0a] hover:bg-gray-100 flex items-center gap-1.5 self-start sm:self-auto"
        @click="loadTariffs"
        :disabled="loading"
        title="Segarkan data tarif"
      >
        <AppIcon name="refresh" class="w-3.5 h-3.5" :class="loading ? 'animate-spin' : ''" />
        <span>Muat Ulang Tarif</span>
      </button>
    </div>

    <!-- Success Feedback Alert -->
    <div
      v-if="successMsg"
      class="p-4 rounded-xl bg-emerald-100 border-2 border-black shadow-[3px_3px_0_#0a0a0a] text-black text-xs sm:text-sm flex items-center gap-2.5 font-bold animate-fade-in"
    >
      <AppIcon name="check-circle" class="w-5 h-5 text-emerald-700 shrink-0" />
      <span>{{ successMsg }}</span>
    </div>

    <!-- ─── 4 TARIFF CARDS (FULL WIDTH GRID: 1 col mobile, 2 tablet, 4 desktop) ─── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <div
        v-for="tariff in tariffs"
        :key="tariff.id"
        class="nb-card bg-white border-2 border-black p-5 shadow-[4px_4px_0_#0a0a0a] rounded-2xl flex flex-col justify-between transition-all"
        :class="editingId === tariff.id ? 'ring-2 ring-black shadow-[6px_6px_0_#0a0a0a] bg-[var(--nb-yellow)]/10' : ''"
      >
        <div>
          <!-- Header card with category pill & icon -->
          <div class="flex items-start justify-between pb-3 border-b-2 border-black/10 mb-4">
            <div>
              <span
                class="inline-block text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full border-2 border-black shadow-[1px_1px_0_#0a0a0a]"
                :class="tariff.type === 'non_member_hourly'
                  ? 'bg-[var(--nb-lime)] text-black'
                  : 'bg-[var(--nb-purple)] text-white'"
              >
                {{ tariff.type === 'non_member_hourly' ? 'Non-Member · Jam' : 'Member · Bulan' }}
              </span>
              <h3 class="text-sm font-black text-black capitalize mt-2 flex items-center gap-1.5">
                <span>{{ tariff.vehicle_category === 'motor' ? 'Sepeda Motor' : 'Mobil Roda 4' }}</span>
              </h3>
            </div>

            <div
              class="w-10 h-10 rounded-xl border-2 border-black shadow-[2px_2px_0_#0a0a0a] flex items-center justify-center text-black"
              :class="tariff.vehicle_category === 'motor' ? 'bg-[var(--nb-teal)]' : 'bg-[var(--nb-yellow)]'"
            >
              <AppIcon :name="tariff.vehicle_category === 'motor' ? 'motorcycle' : 'car'" class="w-5 h-5" />
            </div>
          </div>

          <!-- View Mode -->
          <div v-if="editingId !== tariff.id" class="space-y-3">
            <div>
              <p class="text-xs text-[var(--nb-muted)] font-medium">Nominal Tarif Saat Ini</p>
              <p class="text-2xl font-mono font-black text-black tracking-tight mt-0.5">
                {{ formatRupiah(tariff.amount) }}
              </p>
              <p class="text-[11px] text-black/70 font-semibold mt-1">
                Berlaku per {{ tariff.type === 'non_member_hourly' ? '1 jam durasi' : '1 bulan langganan' }}
              </p>
            </div>

            <button
              class="nb-btn bg-white text-black font-bold border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-gray-100 w-full text-xs mt-3 flex items-center justify-center gap-1.5 py-2"
              @click="startEdit(tariff)"
            >
              <AppIcon name="edit" class="w-3.5 h-3.5" />
              <span>Ubah Tarif</span>
            </button>
          </div>

          <!-- Edit Mode Form -->
          <div v-else class="space-y-3">
            <div>
              <label class="form-label text-black font-bold text-xs">Nominal Baru (Rp)</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-black/60 font-mono text-xs font-bold">
                  Rp
                </div>
                <input
                  v-model.number="editAmount"
                  type="number"
                  class="form-input pl-9 font-mono font-bold text-sm bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                  min="0"
                  step="500"
                  autofocus
                  @keydown.enter="updateTariff(tariff)"
                />
              </div>
            </div>

            <div class="flex gap-2">
              <button
                class="nb-btn bg-white text-black flex-1 text-xs font-bold border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-gray-100 py-1.5"
                @click="editingId = null"
              >
                Batal
              </button>
              <button
                class="nb-btn bg-[var(--nb-lime)] text-black flex-1 text-xs font-black border-2 border-black shadow-[2px_2px_0_#0a0a0a] hover:bg-[#d4f028] py-1.5"
                @click="updateTariff(tariff)"
                :disabled="saving"
              >
                <svg v-if="saving" class="w-3.5 h-3.5 animate-spin text-black mx-auto" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span v-else class="flex items-center justify-center gap-1">
                  <AppIcon name="check" class="w-3.5 h-3.5" />
                  <span>Simpan</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── LOWER SECTION: POLICIES & INTERACTIVE SIMULATOR (FULL WIDTH 2-COL) ─── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

      <!-- Left (7 Cols): Ketentuan & Regulasi Operasional Tarif -->
      <div class="lg:col-span-7 nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl p-6 space-y-5">
        <div class="flex items-center gap-2.5 pb-3 border-b-2 border-black/10">
          <div class="w-9 h-9 rounded-xl bg-[var(--nb-teal)]/30 border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] flex items-center justify-center text-black">
            <AppIcon name="info" class="w-5 h-5" />
          </div>
          <div>
            <h2 class="text-base font-black text-[#0a0a0a]">Aturan & Kebijakan Tarif Parkir</h2>
            <p class="text-xs text-[var(--nb-muted)] font-medium">Prinsip penetapan tarif dan kalkulasi otomatis sistem</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Policy 1 -->
          <div class="p-4 rounded-xl bg-[var(--nb-bg)] border-2 border-black shadow-[2px_2px_0_#0a0a0a] space-y-1.5">
            <div class="flex items-center gap-2 text-xs font-black text-black">
              <span class="w-6 h-6 rounded-lg bg-[var(--nb-lime)] border-2 border-black flex items-center justify-center text-[11px]">1</span>
              <span>Pembulatan Jam Progresif</span>
            </div>
            <p class="text-[11px] text-black/80 font-medium leading-relaxed">
              Durasi parkir non-member dihitung dengan pembulatan ke atas (ceil per jam). Kelebihan waktu 1 menit dihitung 1 jam berikutnya dengan durasi minimal 1 jam.
            </p>
          </div>

          <!-- Policy 2 -->
          <div class="p-4 rounded-xl bg-[var(--nb-bg)] border-2 border-black shadow-[2px_2px_0_#0a0a0a] space-y-1.5">
            <div class="flex items-center gap-2 text-xs font-black text-black">
              <span class="w-6 h-6 rounded-lg bg-[var(--nb-purple)] text-white border-2 border-black flex items-center justify-center text-[11px]">2</span>
              <span>Langganan Member Bebas Gate</span>
            </div>
            <p class="text-[11px] text-black/80 font-medium leading-relaxed">
              Member aktif bebas akses keluar masuk tanpa biaya jam-jaman. Akses portal langsung terbuka via scan QR/barcode selama tagihan bulanan lunas.
            </p>
          </div>

          <!-- Policy 3 -->
          <div class="p-4 rounded-xl bg-[var(--nb-bg)] border-2 border-black shadow-[2px_2px_0_#0a0a0a] space-y-1.5">
            <div class="flex items-center gap-2 text-xs font-black text-black">
              <span class="w-6 h-6 rounded-lg bg-[var(--nb-yellow)] border-2 border-black flex items-center justify-center text-[11px]">3</span>
              <span>Realtime Gate Sync</span>
            </div>
            <p class="text-[11px] text-black/80 font-medium leading-relaxed">
              Setiap pembaruan tarif di halaman ini langsung tersinkronisasi ke portal kasir tiket dan sistem pembayaran tanpa perlu merestart server.
            </p>
          </div>

          <!-- Policy 4 -->
          <div class="p-4 rounded-xl bg-[var(--nb-bg)] border-2 border-black shadow-[2px_2px_0_#0a0a0a] space-y-1.5">
            <div class="flex items-center gap-2 text-xs font-black text-black">
              <span class="w-6 h-6 rounded-lg bg-[var(--nb-orange,#ff7a2f)] text-white border-2 border-black flex items-center justify-center text-[11px]">4</span>
              <span>SOP Tiket Fisik Hilang</span>
            </div>
            <p class="text-[11px] text-black/80 font-medium leading-relaxed">
              Kehilangan tiket karcis fisik dikenakan denda verifikasi STNK sesuai peraturan pengelola gedung Plaza Andalas dan divalidasi manual oleh petugas kasir.
            </p>
          </div>
        </div>

        <div class="p-3.5 rounded-xl bg-[var(--nb-lime)]/20 border-2 border-black flex items-center gap-3 text-xs font-bold text-black">
          <AppIcon name="shield" class="w-5 h-5 text-black shrink-0" />
          <span>Hanya akun dengan hak akses Super Admin yang diizinkan untuk mengubah nilai konfigurasi tarif ini.</span>
        </div>
      </div>

      <!-- Right (5 Cols): Live Tariff Simulator / Calculator -->
      <div class="lg:col-span-5 nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl p-6 flex flex-col justify-between space-y-5">
        <div>
          <div class="flex items-center gap-2.5 pb-3 border-b-2 border-black/10 mb-4">
            <div class="w-9 h-9 rounded-xl bg-[var(--nb-yellow)] border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] flex items-center justify-center text-black">
              <AppIcon name="receipt" class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-black text-[#0a0a0a]">Estimasi Hitung Tarif</h2>
              <p class="text-xs text-[var(--nb-muted)] font-medium">Uji kalkulasi biaya parkir dengan tarif aktif</p>
            </div>
          </div>

          <div class="space-y-4">
            <!-- Tipe Parkir Selection -->
            <div>
              <label class="form-label text-black font-bold text-xs">Jenis</label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  class="py-2 px-3 rounded-xl border-2 border-black text-xs font-bold transition-all text-center"
                  :class="simType === 'non_member'
                    ? 'bg-[var(--nb-lime)] text-black shadow-[2px_2px_0_#0a0a0a]'
                    : 'bg-white text-black hover:bg-gray-100 shadow-[1px_1px_0_#0a0a0a]'"
                  @click="simType = 'non_member'"
                >
                  Non-Member
                </button>
                <button
                  type="button"
                  class="py-2 px-3 rounded-xl border-2 border-black text-xs font-bold transition-all text-center"
                  :class="simType === 'member'
                    ? 'bg-[var(--nb-purple)] text-white shadow-[2px_2px_0_#0a0a0a]'
                    : 'bg-white text-black hover:bg-gray-100 shadow-[1px_1px_0_#0a0a0a]'"
                  @click="simType = 'member'"
                >
                  Member Bulanan
                </button>
              </div>
            </div>

            <!-- Kategori Kendaraan -->
            <div>
              <label class="form-label text-black font-bold text-xs">Kategori Kendaraan</label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  class="py-2 px-3 rounded-xl border-2 border-black text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                  :class="simCategory === 'motor'
                    ? 'bg-[var(--nb-teal)] text-black shadow-[2px_2px_0_#0a0a0a]'
                    : 'bg-white text-black hover:bg-gray-100 shadow-[1px_1px_0_#0a0a0a]'"
                  @click="simCategory = 'motor'"
                >
                  <AppIcon name="motorcycle" class="w-4 h-4" />
                  <span>Sepeda Motor</span>
                </button>
                <button
                  type="button"
                  class="py-2 px-3 rounded-xl border-2 border-black text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                  :class="simCategory === 'mobil'
                    ? 'bg-[var(--nb-yellow)] text-black shadow-[2px_2px_0_#0a0a0a]'
                    : 'bg-white text-black hover:bg-gray-100 shadow-[1px_1px_0_#0a0a0a]'"
                  @click="simCategory = 'mobil'"
                >
                  <AppIcon name="car" class="w-4 h-4" />
                  <span>Mobil Roda 4</span>
                </button>
              </div>
            </div>

            <!-- Durasi Input -->
            <div>
              <label class="form-label text-black font-bold text-xs">
                {{ simType === 'non_member' ? 'Estimasi Durasi Parkir (Jam)' : 'Durasi Masa Langganan (Bulan)' }}
              </label>
              <div class="flex items-center gap-3">
                <input
                  v-model.number="simDuration"
                  type="number"
                  class="form-input font-mono font-bold text-base bg-white border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                  min="1"
                  max="720"
                />
                <span class="text-xs font-mono font-bold text-black/70">
                  {{ simType === 'non_member' ? 'Jam' : 'Bulan' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Simulation Result Box -->
        <div class="p-4 rounded-xl bg-[var(--nb-bg)] border-2 border-black shadow-[3px_3px_0_#0a0a0a] space-y-2.5">
          <div class="flex justify-between items-center text-xs text-black/70 font-medium">
            <span>Tarif Unit (Per {{ simType === 'non_member' ? 'Jam' : 'Bulan' }})</span>
            <span class="font-mono font-bold text-black">{{ formatRupiah(activeSimUnitRate) }}</span>
          </div>
          <div class="flex justify-between items-center text-xs text-black/70 font-medium">
            <span>Kuantitas Terhitung</span>
            <span class="font-mono font-bold text-black">{{ simDuration || 1 }} {{ simType === 'non_member' ? 'Jam' : 'Bulan' }}</span>
          </div>
          <div class="pt-2 border-t-2 border-black/10 flex justify-between items-center">
            <span class="text-xs font-black text-black">Estimasi Total Biaya</span>
            <span class="text-2xl font-black font-mono text-black">{{ formatRupiah(simTotalFee) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Pengaturan Tarif — E-Parkir Plaza Andalas' })

const { apiFetch } = useApi()

const tariffs = ref<any[]>([])
const loading = ref(false)
const editingId = ref<number | null>(null)
const editAmount = ref(0)
const saving = ref(false)
const successMsg = ref('')

// ── Simulator State ──────────────────────────────────────────────────────────
const simType = ref<'non_member' | 'member'>('non_member')
const simCategory = ref<'motor' | 'mobil'>('motor')
const simDuration = ref<number>(2)

function formatRupiah(n?: number) {
  return 'Rp ' + (n || 0).toLocaleString('id-ID')
}

const activeSimUnitRate = computed(() => {
  const targetType = simType.value === 'non_member' ? 'non_member_hourly' : 'member_monthly'
  const found = tariffs.value.find(t => t.type === targetType && t.vehicle_category === simCategory.value)
  return found?.amount || 0
})

const simTotalFee = computed(() => {
  const duration = Math.max(1, simDuration.value || 1)
  return activeSimUnitRate.value * duration
})

async function loadTariffs() {
  loading.value = true
  try {
    const data = await apiFetch<any>('/tariffs')
    tariffs.value = data.tariffs || []
  } catch (e: any) {
    console.error('Error load tariffs:', e)
  } finally {
    loading.value = false
  }
}

function startEdit(tariff: any) {
  editingId.value = tariff.id
  editAmount.value = tariff.amount
}

async function updateTariff(tariff: any) {
  saving.value = true
  successMsg.value = ''
  try {
    await apiFetch(`/tariffs/${tariff.id}`, {
      method: 'PUT',
      body: { amount: editAmount.value },
    })
    editingId.value = null
    successMsg.value = `Tarif ${tariff.vehicle_category === 'motor' ? 'Sepeda Motor' : 'Mobil'} (${tariff.type === 'non_member_hourly' ? 'per jam' : 'bulanan'}) berhasil diperbarui ke ${formatRupiah(editAmount.value)}.`
    setTimeout(() => {
      successMsg.value = ''
    }, 4000)
    await loadTariffs()
  } catch (e: any) {
    alert(e?.data?.message ?? 'Gagal memperbarui tarif parkir')
  } finally {
    saving.value = false
  }
}

onMounted(loadTariffs)
</script>
