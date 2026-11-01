<template>
  <div class="space-y-6 max-w-4xl">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-white">Konfigurasi Tarif Parkir</h1>
      <p class="text-xs text-slate-400 mt-1">Pengaturan tarif progresif per jam non-member dan iuran bulanan member Plaza Andalas</p>
    </div>

    <!-- Info Policy Banner -->
    <div class="p-4 rounded-xl bg-blue-500/10 border border-blue-500/20 text-xs text-slate-300 flex items-start gap-3">
      <AppIcon name="info" class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" />
      <div class="space-y-1">
        <p class="font-semibold text-blue-200">Aturan Perhitungan Tarif Parkir Sistem:</p>
        <p class="text-slate-400">
          • Non-Member dihitung berdasarkan pembulatan ke atas (ceil) per jam, dengan durasi minimal 1 jam.
        </p>
        <p class="text-slate-400">
          • Member aktif berlangganan bebas biaya keluar-masuk gerbang tanpa batas waktu selama tagihan bulanan lunas.
        </p>
      </div>
    </div>

    <!-- Tariff Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="tariff in tariffs"
        :key="tariff.id"
        class="glass-card p-6 flex flex-col justify-between transition-all"
        :class="editingId === tariff.id ? 'border-blue-500 shadow-xl shadow-blue-500/10' : ''"
      >
        <div>
          <div class="flex items-start justify-between pb-3 border-b border-slate-800/80 mb-4">
            <div>
              <span
                class="inline-block text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full"
                :class="tariff.type === 'non_member_hourly'
                  ? 'bg-blue-500/15 text-blue-300 border border-blue-500/30'
                  : 'bg-purple-500/15 text-purple-300 border border-purple-500/30'"
              >
                {{ tariff.type === 'non_member_hourly' ? 'Non-Member · Per Jam' : 'Member · Per Bulan' }}
              </span>
              <h3 class="text-base font-bold text-white capitalize mt-1.5 flex items-center gap-2">
                <span>{{ tariff.vehicle_category === 'motor' ? 'Sepeda Motor' : 'Mobil Roda Empat' }}</span>
              </h3>
            </div>

            <div class="w-10 h-10 rounded-xl bg-slate-800/80 flex items-center justify-center text-slate-300">
              <AppIcon :name="tariff.vehicle_category === 'motor' ? 'motorcycle' : 'car'" class="w-5 h-5" />
            </div>
          </div>

          <!-- View Mode -->
          <div v-if="editingId !== tariff.id" class="space-y-3">
            <div>
              <p class="text-3xl font-mono font-bold text-emerald-400">
                {{ formatRupiah(tariff.amount) }}
              </p>
              <p class="text-xs text-slate-400 font-medium mt-1">
                Berlaku per {{ tariff.type === 'non_member_hourly' ? '1 jam durasi parkir' : '1 bulan masa langganan' }}
              </p>
            </div>

            <button
              class="btn btn-outline w-full text-xs mt-4"
              @click="startEdit(tariff)"
            >
              <AppIcon name="edit" class="w-3.5 h-3.5" />
              <span>Perbarui Besaran Tarif</span>
            </button>
          </div>

          <!-- Edit Mode Form -->
          <div v-else class="space-y-4">
            <div>
              <label class="form-label">Nominal Tarif Baru (Rp)</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-mono text-xs">
                  Rp
                </div>
                <input
                  v-model.number="editAmount"
                  type="number"
                  class="form-input pl-10 font-mono font-bold text-base"
                  min="0"
                  autofocus
                />
              </div>
            </div>

            <div class="flex gap-2">
              <button class="btn btn-ghost flex-1 text-xs" @click="editingId = null">Batal</button>
              <button
                class="btn btn-success flex-1 text-xs font-semibold"
                @click="updateTariff(tariff)"
                :disabled="saving"
              >
                <svg v-if="saving" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span v-else class="flex items-center gap-1.5">
                  <AppIcon name="check" class="w-3.5 h-3.5" />
                  <span>Simpan Tarif</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Feedback Alert -->
    <div
      v-if="successMsg"
      class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center gap-2 animate-fade-in"
    >
      <AppIcon name="check-circle" class="w-5 h-5 text-emerald-400 shrink-0" />
      <span>{{ successMsg }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Tarif Parkir — E-Parkir Plaza Andalas' })

const { apiFetch } = useApi()

const tariffs = ref<any[]>([])
const editingId = ref<number | null>(null)
const editAmount = ref(0)
const saving = ref(false)
const successMsg = ref('')

function formatRupiah(n: number) {
  return 'Rp ' + (n || 0).toLocaleString('id-ID')
}

async function loadTariffs() {
  const data = await apiFetch<any>('/tariffs')
  tariffs.value = data.tariffs
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
    successMsg.value = `Tarif ${tariff.vehicle_category} (${tariff.type === 'non_member_hourly' ? 'per jam' : 'bulanan'}) berhasil diperbarui ke ${formatRupiah(editAmount.value)}.`
    setTimeout(() => {
      successMsg.value = ''
    }, 3500)
    loadTariffs()
  } catch (e: any) {
    alert(e?.data?.message ?? 'Gagal memperbarui tarif parkir')
  } finally {
    saving.value = false
  }
}

onMounted(loadTariffs)
</script>
