<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-[#0a0a0a]">Terminal Kasir & Gate</h1>
        <p class="text-xs text-[var(--nb-muted)] mt-1 font-medium">Kasir tiket non-member dan verifikasi gate scan member dalam satu terminal</p>
      </div>
      <div class="flex items-center gap-2">
        <button
          class="nb-btn bg-[var(--nb-lime)] text-[#0a0a0a] font-bold border-2 border-black shadow-[3px_3px_0_#0a0a0a] hover:bg-[#d4f028]"
          @click="issueTicket"
          :disabled="issuingTicket"
        >
          <svg v-if="issuingTicket" class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span v-else class="flex items-center gap-2">
            <AppIcon name="printer" class="w-4 h-4" />
            <span>Cetak Tiket Masuk Baru</span>
          </span>
        </button>
      </div>
    </div>

    <!-- ─── 2 OPERATION CARDS (side by side) ──────────────────────────────── -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- LEFT: Kasir Non-Member -->
      <div class="space-y-4">
        <!-- Newly Issued Ticket Preview -->
        <div v-if="newTicket" class="nb-card bg-[var(--nb-teal)]/20 p-5 border-2 border-black shadow-[4px_4px_0_#0a0a0a]">
          <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 border border-black animate-pulse"></span>
              <span class="text-xs font-bold uppercase tracking-wider text-black">Tiket Masuk Siap Cetak</span>
            </div>
            <button class="p-1 text-black hover:bg-black/10 rounded-md" @click="newTicket = null">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>
          <div class="bg-white text-black p-4 rounded-xl border-2 border-black shadow-[3px_3px_0_#0a0a0a] max-w-xs mx-auto font-mono text-xs">
            <div class="text-center pb-3 border-b border-dashed border-black/40">
              <p class="font-black text-sm tracking-tight text-black font-sans">E-PARKIR PLAZA ANDALAS</p>
              <p class="text-[10px] text-black/60 font-sans">Sistem Manajemen Parkir Resmi</p>
            </div>
            <div class="py-3 text-center">
              <p class="text-[10px] text-black/60 uppercase tracking-widest font-sans font-bold">KODE TIKET MASUK</p>
              <p class="text-2xl font-extrabold tracking-[0.2em] text-black my-1 font-mono">{{ newTicket.token }}</p>
              <div class="h-7 my-1.5 flex items-center justify-center gap-0.5 opacity-80">
                <span v-for="i in 28" :key="i" class="h-full bg-black inline-block" :style="{ width: (i % 3 === 0 ? '3px' : '1.5px') }"></span>
              </div>
            </div>
            <div class="pt-2 border-t border-dashed border-black/40 space-y-1 text-[11px] text-black">
              <div class="flex justify-between">
                <span class="text-black/60">Waktu Masuk:</span>
                <span class="font-bold">{{ formatDateTime(newTicket.entry_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-black/60">Status:</span>
                <span class="font-bold text-emerald-700">AKTIF</span>
              </div>
            </div>
          </div>
          <div class="mt-4 flex gap-2 max-w-xs mx-auto">
            <button class="nb-btn bg-white text-black flex-1 text-xs font-bold border-2 border-black shadow-[2px_2px_0_#0a0a0a]" @click="printTicketMockup">
              <AppIcon name="printer" class="w-3.5 h-3.5" />
              <span>Cetak</span>
            </button>
            <button class="nb-btn bg-[var(--nb-lime)] text-black flex-1 text-xs font-bold border-2 border-black shadow-[2px_2px_0_#0a0a0a]" @click="loadToPayment(newTicket)">
              <AppIcon name="credit-card" class="w-3.5 h-3.5" />
              <span>Buka Kasir</span>
            </button>
          </div>
        </div>

        <!-- Cashier / Search Card -->
        <div class="nb-card bg-white border-2 border-black p-5 shadow-[4px_4px_0_#0a0a0a] space-y-5">
          <div class="flex items-center gap-2 pb-3 border-b-2 border-black/10">
            <div class="w-8 h-8 rounded-lg bg-[var(--nb-lime)] border-2 border-black flex items-center justify-center">
              <AppIcon name="search" class="w-4 h-4 text-black" />
            </div>
            <div>
              <h2 class="text-sm font-black text-black">Kasir Keluar Non-Member</h2>
              <p class="text-[11px] text-[var(--nb-muted)]">Cari tiket token untuk hitung biaya & bayar</p>
            </div>
          </div>

          <div class="flex gap-2">
            <input
              v-model="searchToken"
              type="text"
              class="form-input flex-1 text-base font-mono font-bold tracking-widest uppercase border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
              placeholder="Misal: A1B2C3"
              @keydown.enter="findTicket"
              @input="searchToken = searchToken.toUpperCase()"
            />
            <button
              class="nb-btn bg-[var(--nb-purple)] text-white font-bold px-5 shrink-0 border-2 border-black shadow-[3px_3px_0_#0a0a0a] hover:opacity-90"
              @click="findTicket"
              :disabled="finding || !searchToken.trim()"
            >
              <svg v-if="finding" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <span v-else class="flex items-center gap-1.5">
                <AppIcon name="search" class="w-4 h-4" />
                <span>Cari</span>
              </span>
            </button>
          </div>
          <p v-if="findError" class="text-xs text-rose-600 font-semibold flex items-center gap-1 -mt-3">
            <AppIcon name="alert-triangle" class="w-3.5 h-3.5" />
            <span>{{ findError }}</span>
          </p>

          <!-- Found Ticket -->
          <div v-if="foundTicket" class="pt-4 border-t-2 border-black/10 space-y-4 animate-fade-in">
            <div class="flex items-center justify-between p-3.5 rounded-xl bg-[var(--nb-bg)] border-2 border-black shadow-[2px_2px_0_#0a0a0a]">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[var(--nb-teal)] border-2 border-black flex items-center justify-center text-black">
                  <AppIcon name="ticket" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-[10px] text-[var(--nb-muted)] font-medium">Kode Token</p>
                  <p class="text-base font-mono font-black text-black tracking-widest">{{ foundTicket.token }}</p>
                </div>
              </div>
              <span
                class="text-xs px-2.5 py-1 rounded-full font-bold border-2 border-black"
                :class="foundTicket.status === 'active' ? 'bg-[var(--nb-orange)]/40 text-black' : 'bg-[var(--nb-lime)] text-black'"
              >
                {{ foundTicket.status === 'active' ? 'Belum Bayar' : 'Lunas' }}
              </span>
            </div>

            <!-- Active Ticket Payment Form -->
            <div v-if="foundTicket.status === 'active'" class="space-y-4">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="form-label text-black font-bold">Kategori Kendaraan</label>
                  <div class="grid grid-cols-2 gap-2">
                    <button
                      type="button"
                      class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl border-2 border-black text-xs font-bold transition-all"
                      :class="payForm.vehicle_category === 'motor' ? 'bg-[var(--nb-lime)] text-black shadow-[2px_2px_0_#0a0a0a]' : 'bg-white text-black hover:bg-gray-100 shadow-[1px_1px_0_#0a0a0a]'"
                      @click="payForm.vehicle_category = 'motor'; refreshPreview()"
                    >
                      <AppIcon name="motorcycle" class="w-3.5 h-3.5" />
                      <span>Motor</span>
                    </button>
                    <button
                      type="button"
                      class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl border-2 border-black text-xs font-bold transition-all"
                      :class="payForm.vehicle_category === 'mobil' ? 'bg-[var(--nb-lime)] text-black shadow-[2px_2px_0_#0a0a0a]' : 'bg-white text-black hover:bg-gray-100 shadow-[1px_1px_0_#0a0a0a]'"
                      @click="payForm.vehicle_category = 'mobil'; refreshPreview()"
                    >
                      <AppIcon name="car" class="w-3.5 h-3.5" />
                      <span>Mobil</span>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="form-label text-black font-bold">Plat Nomor</label>
                  <input
                    v-model="payForm.plate_number"
                    type="text"
                    class="form-input font-mono font-bold uppercase tracking-wider border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                    placeholder="BA 1234 XY"
                    @input="payForm.plate_number = payForm.plate_number.toUpperCase()"
                  />
                </div>
              </div>

              <div v-if="preview" class="p-3.5 rounded-xl bg-[var(--nb-yellow)]/30 border-2 border-black shadow-[2px_2px_0_#0a0a0a] space-y-1.5">
                <div class="flex justify-between text-xs text-black/70">
                  <span class="font-medium">Durasi (Ceil/jam)</span>
                  <span class="font-mono text-black font-bold">{{ preview.hours }} Jam</span>
                </div>
                <div class="flex justify-between text-xs text-black/70">
                  <span class="font-medium">Tarif / Jam</span>
                  <span class="font-mono text-black font-bold">{{ formatRupiah(preview.tariff_per_hour) }}</span>
                </div>
                <div class="pt-2 border-t-2 border-black/10 flex justify-between items-center">
                  <span class="font-bold text-sm text-black">Total Tarif</span>
                  <span class="text-xl font-black font-mono text-black">{{ formatRupiah(preview.fee) }}</span>
                </div>
              </div>

              <div>
                <label class="form-label text-black font-bold">Uang Tunai Diterima (Rp)</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-black font-mono text-xs font-black select-none">Rp</div>
                  <input
                    v-model.number="payForm.cash_received"
                    type="number"
                    class="form-input pl-11 font-mono font-bold text-base border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                    placeholder="0"
                    min="0"
                  />
                </div>
                <div
                  v-if="payForm.cash_received && preview"
                  class="mt-2 p-2.5 rounded-xl text-xs font-mono flex items-center justify-between border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a]"
                  :class="payForm.cash_received >= preview.fee ? 'bg-[var(--nb-teal)]/30 text-black' : 'bg-rose-100 text-rose-900'"
                >
                  <span class="font-bold">{{ payForm.cash_received >= preview.fee ? 'Kembalian:' : 'Kurang:' }}</span>
                  <span class="font-black text-sm">{{ formatRupiah(Math.abs(payForm.cash_received - (preview?.fee || 0))) }}</span>
                </div>
              </div>

              <div v-if="payError" class="p-3 rounded-xl bg-rose-100 border-2 border-black text-rose-900 text-xs flex items-center gap-2 font-bold shadow-[2px_2px_0_#0a0a0a]">
                <AppIcon name="alert-triangle" class="w-4 h-4 shrink-0" />
                <span>{{ payError }}</span>
              </div>

              <button
                class="nb-btn bg-[var(--nb-lime)] text-black w-full py-3 text-sm font-black tracking-wide border-2 border-black shadow-[4px_4px_0_#0a0a0a] hover:bg-[#d4f028]"
                :disabled="paying || !payForm.vehicle_category || !payForm.plate_number || (preview && payForm.cash_received < preview.fee)"
                @click="processPayment"
              >
                <svg v-if="paying" class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span v-else class="flex items-center justify-center gap-2">
                  <AppIcon name="check" class="w-4 h-4" />
                  <span>Selesaikan Pembayaran & Buka Portal</span>
                </span>
              </button>
            </div>

            <!-- Paid Receipt -->
            <div v-else class="p-4 rounded-xl bg-[var(--nb-teal)]/20 border-2 border-black shadow-[2px_2px_0_#0a0a0a] space-y-3">
              <div class="flex items-center gap-2 text-black font-black text-sm">
                <AppIcon name="check-circle" class="w-5 h-5 text-emerald-600" />
                <span>Pembayaran Sukses — Portal Keluar Dibuka</span>
              </div>
              <div class="grid grid-cols-2 gap-2.5 text-xs pt-2 border-t-2 border-black/10 font-mono">
                <div>
                  <span class="text-black/60 block text-[10px] font-sans font-bold">INVOICE</span>
                  <span class="text-black font-black">{{ foundTicket.invoice_number }}</span>
                </div>
                <div>
                  <span class="text-black/60 block text-[10px] font-sans font-bold">TOTAL TARIF</span>
                  <span class="text-black font-black">{{ formatRupiah(foundTicket.fee) }}</span>
                </div>
                <div>
                  <span class="text-black/60 block text-[10px] font-sans font-bold">TUNAI DITERIMA</span>
                  <span class="text-black font-semibold">{{ formatRupiah(foundTicket.cash_received) }}</span>
                </div>
                <div>
                  <span class="text-black/60 block text-[10px] font-sans font-bold">KEMBALIAN</span>
                  <span class="text-black font-semibold">{{ formatRupiah(foundTicket.change_amount) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Gate Scan Member -->
      <div class="space-y-4">
        <div class="nb-card bg-white border-2 border-black p-5 shadow-[4px_4px_0_#0a0a0a] space-y-5">
          <div class="flex items-center justify-between pb-3 border-b-2 border-black/10">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-[var(--nb-purple)] border-2 border-black flex items-center justify-center">
                <AppIcon name="scan" class="w-4 h-4 text-white" />
              </div>
              <div>
                <h2 class="text-sm font-black text-black">Gate Scan Member</h2>
                <p class="text-[11px] text-[var(--nb-muted)]">Verifikasi QR / barcode kartu member</p>
              </div>
            </div>
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[var(--nb-lime)] border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a] text-[11px] text-black font-mono font-black">
              <span class="w-2 h-2 rounded-full bg-emerald-600 animate-ping"></span>
              <span>GATE 01</span>
            </div>
          </div>

          <!-- Scanner Visual Frame -->
          <div class="h-24 flex flex-col items-center justify-center border-2 border-dashed border-black rounded-2xl bg-[var(--nb-bg)] text-center gap-1.5">
            <div class="w-9 h-9 rounded-xl bg-[var(--nb-lime)] border-2 border-black flex items-center justify-center text-black shadow-[2px_2px_0_#0a0a0a]">
              <AppIcon name="scan" class="w-5 h-5" />
            </div>
            <p class="text-xs font-black text-[#0a0a0a]">Arahkan scanner ke kartu QR / Barcode Member</p>
          </div>

          <!-- Scan Input -->
          <div class="flex gap-2">
            <input
              ref="scanInputRef"
              v-model="scanCode"
              type="text"
              class="form-input flex-1 text-center font-mono text-base font-black tracking-widest uppercase border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
              placeholder="MBR-DEMO0001"
              @keydown.enter="doScan"
              @input="scanCode = scanCode.toUpperCase()"
            />
            <button
              class="nb-btn bg-[var(--nb-purple)] text-white px-5 shrink-0 font-bold border-2 border-black shadow-[3px_3px_0_#0a0a0a] hover:opacity-90"
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

          <!-- Scan Result -->
          <transition name="page">
            <div
              v-if="scanResult"
              class="p-5 rounded-2xl border-2 border-black shadow-[3px_3px_0_#0a0a0a]"
              :class="scanResult.allowed ? 'bg-[#dcfce7] text-[#14532d]' : 'bg-[#fee2e2] text-rose-900'"
            >
              <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                  <div
                    class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 border-2 border-black shadow-[2px_2px_0_#0a0a0a]"
                    :class="scanResult.allowed ? 'bg-white text-emerald-600' : 'bg-white text-rose-600'"
                  >
                    <AppIcon :name="scanResult.allowed ? 'gate' : 'x-circle'" class="w-6 h-6" />
                  </div>
                  <div>
                    <span class="inline-block text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full mb-1 border border-black bg-white shadow-[1px_1px_0_#0a0a0a]">
                      {{ scanResult.allowed ? 'PORTAL DIBUKA · SILAKAN MASUK' : 'AKSES DITOLAK' }}
                    </span>
                    <h3 class="text-sm font-black text-[#0a0a0a]">{{ scanResult.reason }}</h3>
                  </div>
                </div>
                <button class="nb-btn nb-btn-sm bg-white shrink-0" @click="resetScan">
                  <AppIcon name="refresh" class="w-3.5 h-3.5" />
                  <span>Reset</span>
                </button>
              </div>

              <!-- Member Detail -->
              <div v-if="scanResult.member" class="mt-4 pt-3 border-t-2 border-black/10 grid grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 rounded-xl bg-white border-2 border-black shadow-[1px_1px_0_#0a0a0a]">
                  <span class="text-[10px] text-slate-500 font-bold block uppercase">Nama Member</span>
                  <span class="font-extrabold text-[#0a0a0a] truncate block mt-0.5">{{ scanResult.member.name }}</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white border-2 border-black shadow-[1px_1px_0_#0a0a0a]">
                  <span class="text-[10px] text-slate-500 font-bold block uppercase">Plat Nomor</span>
                  <span class="font-mono font-black text-[#0a0a0a] block mt-0.5">{{ scanResult.member.plate_number }}</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white border-2 border-black shadow-[1px_1px_0_#0a0a0a]">
                  <span class="text-[10px] text-slate-500 font-bold block uppercase">Kendaraan</span>
                  <span class="font-black text-blue-700 block mt-0.5 uppercase">{{ scanResult.member.vehicle_category }}</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white border-2 border-black shadow-[1px_1px_0_#0a0a0a]">
                  <span class="text-[10px] text-slate-500 font-bold block uppercase">Status</span>
                  <span class="font-black text-emerald-700 block mt-0.5">Aktif Berlangganan</span>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>

    <!-- ─── 2 LOG TABLES (side by side) ──────────────────────────────────── -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- Left: Log Tiket Non-Member Hari Ini -->
      <div class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] overflow-hidden">
        <div class="px-5 py-4 border-b-2 border-black bg-[var(--nb-yellow)]/20 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <AppIcon name="ticket" class="w-4 h-4 text-black" />
            <h2 class="text-sm font-black text-black">Log Tiket Non-Member Hari Ini</h2>
          </div>
          <span class="text-xs px-2.5 py-0.5 rounded-full bg-white border-2 border-black font-mono font-bold text-black shadow-[1px_1px_0_#0a0a0a]">
            {{ tickets.length }} tiket
          </span>
        </div>
        <div class="overflow-y-auto max-h-72">
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
                <td colspan="3" class="text-center py-10 text-[var(--nb-muted)] text-xs font-medium">
                  Belum ada tiket hari ini
                </td>
              </tr>
              <tr
                v-for="t in tickets"
                :key="t.id"
                class="cursor-pointer transition-colors hover:bg-black/5"
                :class="foundTicket?.token === t.token ? 'bg-[var(--nb-lime)]/25' : ''"
                @click="selectTicket(t)"
              >
                <td>
                  <span class="font-mono text-xs font-black text-black px-1.5 py-1 rounded bg-white border-2 border-black shadow-[1.5px_1.5px_0_#0a0a0a]">
                    {{ t.token }}
                  </span>
                </td>
                <td class="text-xs font-mono text-[var(--nb-ink)] font-medium">{{ formatDateTime(t.entry_at) }}</td>
                <td>
                  <span
                    class="text-xs px-2 py-0.5 rounded-full font-bold border-2 border-black shadow-[1px_1px_0_#0a0a0a]"
                    :class="t.status === 'active' ? 'bg-[var(--nb-orange)]/30 text-black' : 'bg-[var(--nb-lime)] text-black'"
                  >
                    {{ t.status === 'active' ? 'Aktif' : 'Lunas' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right: Log Scan Member Sesi Ini -->
      <div class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] overflow-hidden">
        <div class="px-5 py-4 border-b-2 border-black bg-[var(--nb-purple)]/10 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <AppIcon name="scan" class="w-4 h-4 text-black" />
            <h2 class="text-sm font-black text-black">Log Scan Member Sesi Ini</h2>
          </div>
          <span class="text-xs px-2.5 py-0.5 rounded-full bg-white border-2 border-black font-mono font-bold text-black shadow-[1px_1px_0_#0a0a0a]">
            {{ scanHistory.length }} log
          </span>
        </div>
        <div class="divide-y-2 divide-black/10 max-h-72 overflow-y-auto">
          <div v-if="!scanHistory.length" class="text-center py-10 text-xs text-[var(--nb-muted)] font-bold">
            Belum ada riwayat verifikasi kartu pada sesi ini
          </div>
          <div
            v-for="(h, i) in scanHistory"
            :key="i"
            class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-[#fdfbf4] transition-colors"
          >
            <div class="flex items-center gap-3 min-w-0">
              <span
                class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 border-2 border-black font-bold"
                :class="h.allowed ? 'bg-[#dcfce7] text-[#14532d]' : 'bg-[#fee2e2] text-rose-800'"
              >
                <AppIcon :name="h.allowed ? 'check' : 'x'" class="w-4 h-4" />
              </span>
              <div class="min-w-0">
                <div class="flex items-center gap-2">
                  <span class="text-xs font-extrabold text-[#0a0a0a] truncate">{{ h.name }}</span>
                  <span class="font-mono text-[10px] font-bold px-1.5 rounded-md bg-[var(--nb-bg)] text-black border border-black">
                    {{ h.code }}
                  </span>
                </div>
                <p class="text-[11px] text-[var(--nb-muted)] font-medium truncate mt-0.5">{{ h.reason }}</p>
              </div>
            </div>
            <span class="text-xs font-mono font-bold text-[var(--nb-muted)] shrink-0">{{ h.time }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Terminal Kasir & Gate — E-Parkir Plaza Andalas' })

const route = useRoute()
const { apiFetch } = useApi()

// ── Kasir Non-Member state ───────────────────────────────────────────────────
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

// ── Gate Scan Member state ───────────────────────────────────────────────────
const scanInputRef = ref<HTMLInputElement>()
const scanCode = ref('')
const scanning = ref(false)
const scanResult = ref<any>(null)
const scanHistory = ref<any[]>([])

// ── Helpers ──────────────────────────────────────────────────────────────────
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

// ── Kasir functions ──────────────────────────────────────────────────────────
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

// ── Gate Scan functions ──────────────────────────────────────────────────────
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
    addScanHistory(scanCode.value, data)
  } catch (e: any) {
    scanResult.value = e?.data ?? { allowed: false, reason: 'Gagal terhubung ke scanner gate', member: null }
    addScanHistory(scanCode.value, scanResult.value)
  } finally {
    scanning.value = false
  }
}

function addScanHistory(code: string, res: any) {
  scanHistory.value.unshift({
    code,
    name: res.member?.name ?? code,
    allowed: res.allowed,
    reason: res.reason,
    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
  })
  if (scanHistory.value.length > 30) scanHistory.value.pop()
}

function resetScan() {
  scanResult.value = null
  scanCode.value = ''
  nextTick(() => scanInputRef.value?.focus())
}

// ── Init ─────────────────────────────────────────────────────────────────────
onMounted(() => {
  loadTickets()
  if (route.query.token) {
    searchToken.value = String(route.query.token).toUpperCase()
    findTicket()
  }
})
</script>
