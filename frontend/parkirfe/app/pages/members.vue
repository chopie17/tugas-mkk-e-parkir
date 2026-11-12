<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1">
      <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#0a0a0a]">Data Keanggotaan Member</h1>
        <p class="text-xs sm:text-sm text-[var(--nb-muted)] mt-1 font-medium">Kelola data pelanggan parkir langganan bulanan dan pendaftaran baru</p>
      </div>
      <button
        class="nb-btn nb-btn-md bg-[var(--nb-lime,#e4ff3a)] text-black border-2 border-black font-extrabold shadow-[3px_3px_0_#0a0a0a] shrink-0 flex items-center gap-2"
        @click="openRegisterModal()"
      >
        <AppIcon name="plus" class="w-4 h-4" />
        <span>Daftar Member Baru</span>
      </button>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="nb-card p-4 bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto flex-1">
        <div class="relative w-full sm:max-w-xs">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <AppIcon name="search" class="w-4 h-4 text-black" />
          </div>
          <input
            v-model="search"
            type="text"
            class="form-input pl-10 border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
            placeholder="Cari nama, plat, kode, atau instansi..."
            @input="debouncedSearch"
          />
        </div>

        <select
          v-model="filterActive"
          class="form-input w-full sm:w-44 border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a] font-bold"
          @change="page = 1; loadMembers()"
        >
          <option value="">Semua Status</option>
          <option value="true">Status: Aktif</option>
          <option value="false">Status: Non-Aktif</option>
        </select>
      </div>

      <div class="text-xs text-[var(--nb-muted)] font-mono font-bold w-full sm:w-auto text-right">
        Total: <span class="text-[#0a0a0a] font-black text-sm">{{ meta.total }}</span> member terdaftar
      </div>
    </div>

    <!-- Data Table -->
    <div class="nb-card bg-white border-2 border-black shadow-[4px_4px_0_#0a0a0a] rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Member</th>
              <th>Kontak</th>
              <th>Kendaraan</th>
              <th>Plat Nomor</th>
              <th>Tagihan Aktif</th>
              <th>Status</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="text-center py-12 text-slate-500 text-xs">
                <div class="flex items-center justify-center gap-2 font-bold">
                  <svg class="w-4 h-4 animate-spin text-black" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <span>Memuat data member...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="!members.length">
              <td colspan="7" class="text-center py-12 text-slate-500">
                <AppIcon name="users" class="w-8 h-8 mx-auto mb-2 text-slate-400" />
                <p class="text-sm font-bold text-slate-700">Tidak ada member ditemukan</p>
                <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
              </td>
            </tr>

            <tr v-for="m in members" :key="m.id">
              <!-- Member col -->
              <td>
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-xl bg-[var(--nb-purple,#6c47ff)] border-2 border-black flex items-center justify-center text-sm font-black text-white shrink-0">
                    {{ m.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <span class="font-extrabold text-[#0a0a0a] block leading-tight">{{ m.name }}</span>
                    <span v-if="m.company_name" class="text-[10px] text-[var(--nb-muted)] font-mono font-bold block leading-tight">{{ m.company_name }}</span>
                    <button
                      type="button"
                      class="inline-flex items-center gap-1 font-mono text-[10px] font-black px-1.5 py-0.5 rounded-md bg-[#dbeafe] text-[#1e40af] border border-[#0a0a0a] shadow-[1px_1px_0_#0a0a0a] hover:bg-[var(--nb-lime)] hover:text-black transition-all cursor-pointer mt-0.5"
                      @click="openMemberCard(m)"
                      title="Lihat kartu & barcode"
                    >
                      <AppIcon name="scan" class="w-3 h-3" />
                      {{ m.member_code }}
                    </button>
                  </div>
                </div>
              </td>

              <!-- Kontak col -->
              <td>
                <div class="flex flex-col gap-1 text-[11px] font-mono">
                  <span v-if="m.phone_number" class="inline-flex items-center gap-1 font-bold text-black bg-[var(--nb-lime)]/30 px-1.5 py-0.5 rounded border border-black/30 w-fit">
                    📱 {{ m.phone_number }}
                  </span>
                  <span v-if="m.email" class="inline-flex items-center gap-1 text-slate-600 truncate max-w-[140px]" :title="m.email">
                    ✉️ {{ m.email }}
                  </span>
                  <span v-if="!m.phone_number && !m.email" class="text-[var(--nb-muted)] italic text-[10px]">Tidak ada kontak</span>
                </div>
              </td>

              <!-- Kendaraan col -->
              <td>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-800">
                  <AppIcon :name="m.vehicle_category === 'motor' ? 'motorcycle' : 'car'" class="w-4 h-4 text-black" />
                  <span class="capitalize">{{ m.vehicle_category }}</span>
                </span>
              </td>

              <!-- Plat Nomor col -->
              <td class="whitespace-nowrap">
                <span v-if="m.plate_number" class="font-mono text-[11px] font-black px-2 py-0.5 rounded-lg bg-[#f1f1eb] text-[#0a0a0a] border-2 border-[#0a0a0a] whitespace-nowrap inline-block shadow-[1px_1px_0_#0a0a0a]">
                  {{ m.plate_number }}
                </span>
                <span v-else class="text-[10px] text-[var(--nb-muted)] italic font-mono">Belum diisi</span>
              </td>

              <!-- Tagihan Aktif col -->
              <td>
                <template v-if="m.unpaid_bills_count > 0">
                  <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#fef08a] text-[#713f12] border border-black shadow-[1px_1px_0_#0a0a0a]">
                    ⚠️ {{ m.unpaid_bills_count }} tagihan
                  </span>
                </template>
                <template v-else>
                  <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#dcfce7] text-[#14532d] border border-black shadow-[1px_1px_0_#0a0a0a]">
                    ✓ Lunas
                  </span>
                </template>
              </td>

              <!-- Status col -->
              <td>
                <span
                  class="text-xs px-2.5 py-0.5 rounded-full font-bold border border-black shadow-[1px_1px_0_#0a0a0a]"
                  :class="m.is_active ? 'badge-active' : 'badge-inactive'"
                >
                  {{ m.is_active ? 'Aktif' : 'Non-Aktif' }}
                </span>
              </td>

              <!-- Aksi col -->
              <td class="text-right">
                <div class="inline-flex items-center gap-1.5">
                  <button
                    class="nb-btn nb-btn-sm bg-[var(--nb-lime,#e4ff3a)] text-black font-extrabold"
                    @click="openMemberCard(m)"
                    title="Lihat & Cetak Kartu Member"
                  >
                    <AppIcon name="scan" class="w-3.5 h-3.5" />
                    <span>Kartu</span>
                  </button>
                  <button
                    class="nb-btn nb-btn-sm bg-white"
                    @click="openEditModal(m)"
                    title="Edit data & kelola tagihan"
                  >
                    <AppIcon name="edit" class="w-3.5 h-3.5" />
                    <span>Edit/Tagihan</span>
                  </button>
                  <button
                    class="nb-btn nb-btn-sm bg-[#fee2e2] text-rose-800 border-rose-800"
                    @click="confirmDelete(m)"
                    title="Hapus member"
                  >
                    <AppIcon name="trash" class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <PaginationBar :meta="meta" :page="page" @prev="page--; loadMembers()" @next="page++; loadMembers()" />
    </div>

    <!-- ===== MODAL: Daftar Member Baru ===== -->
    <Teleport to="body">
      <div
        v-if="showRegisterModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @mousedown.self="showRegisterModal = false"
      >
        <div class="nb-modal w-full max-w-md p-6 space-y-4 bg-white border-2 border-black shadow-[8px_8px_0_#0a0a0a] rounded-3xl animate-fade-in text-[#0a0a0a] max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between pb-3 border-b-2 border-black/10">
            <div class="flex items-center gap-2">
              <AppIcon name="user-plus" class="w-5 h-5 text-black" />
              <h2 class="text-base font-black text-[#0a0a0a]">Pendaftaran Member Baru</h2>
            </div>
            <button class="p-1 text-slate-500 hover:text-black rounded-lg transition-colors border border-transparent hover:border-black" @click="showRegisterModal = false">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-4">
            <!-- Nama -->
            <div>
              <label class="form-label font-bold text-xs">Nama Lengkap Pelanggan <span class="text-rose-500">*</span></label>
              <input
                v-model="regForm.name"
                type="text"
                class="form-input border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                :class="{ error: regErrors.name }"
                placeholder="Misal: Budi Santoso"
              />
              <p v-if="regErrors.name" class="text-xs mt-1 text-rose-600 font-bold">{{ regErrors.name }}</p>
            </div>

            <!-- Instansi/Perusahaan -->
            <div>
              <div class="flex items-center justify-between">
                <label class="form-label font-bold text-xs">Instansi / Perusahaan</label>
                <span class="text-[10px] text-[var(--nb-muted)] font-mono font-bold">Opsional</span>
              </div>
              <input
                v-model="regForm.company_name"
                type="text"
                class="form-input border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                placeholder="Misal: PT Plaza Andalas"
              />
            </div>

            <!-- Plat Nomor -->
            <div>
              <div class="flex items-center justify-between">
                <label class="form-label font-bold text-xs">Plat Nomor Kendaraan</label>
                <span class="text-[10px] text-[var(--nb-muted)] font-mono font-bold">Opsional (bisa diisi saat keluar)</span>
              </div>
              <input
                v-model="regForm.plate_number"
                type="text"
                class="form-input font-mono uppercase font-black border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                :class="{ error: regErrors.plate_number }"
                placeholder="Misal: BA 1234 XY"
                @input="regForm.plate_number = regForm.plate_number.toUpperCase()"
              />
              <p v-if="regErrors.plate_number" class="text-xs mt-1 text-rose-600 font-bold">{{ regErrors.plate_number }}</p>
            </div>

            <!-- Kontak -->
            <div class="space-y-1">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <div class="flex items-center justify-between">
                    <label class="form-label font-bold text-xs">No. WhatsApp</label>
                    <span class="text-[10px] text-[var(--nb-muted)] font-mono font-bold">Opsional</span>
                  </div>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-black font-mono text-xs font-black select-none">WA</div>
                    <input
                      v-model="regForm.phone_number"
                      type="tel"
                      class="form-input pl-11 font-mono text-xs border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                      :class="{ error: regErrors.phone_number }"
                      placeholder="08123456789"
                    />
                  </div>
                  <p v-if="regErrors.phone_number" class="text-xs mt-1 text-rose-600 font-bold">{{ regErrors.phone_number }}</p>
                </div>
                <div>
                  <div class="flex items-center justify-between">
                    <label class="form-label font-bold text-xs">Alamat Email</label>
                    <span class="text-[10px] text-[var(--nb-muted)] font-mono font-bold">Opsional</span>
                  </div>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-black/60 select-none">
                      <AppIcon name="mail" class="w-3.5 h-3.5 text-black" />
                    </div>
                    <input
                      v-model="regForm.email"
                      type="email"
                      class="form-input pl-10 text-xs border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                      :class="{ error: regErrors.email }"
                      placeholder="member@email.com"
                    />
                  </div>
                  <p v-if="regErrors.email" class="text-xs mt-1 text-rose-600 font-bold">{{ regErrors.email }}</p>
                </div>
              </div>
              <p class="text-[11px] text-[var(--nb-muted)] font-medium pt-1">
                💡 Reminder tagihan: bisa via WhatsApp, Email, atau keduanya.
              </p>
            </div>

            <!-- Kategori Kendaraan -->
            <div>
              <label class="form-label font-bold text-xs">Kategori Kendaraan <span class="text-rose-500">*</span></label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border-2 border-black text-xs font-extrabold transition-all shadow-[2px_2px_0_#0a0a0a]"
                  :class="regForm.vehicle_category === 'motor' ? 'bg-[var(--nb-lime,#e4ff3a)] text-black' : 'bg-white text-slate-600 hover:bg-[#f1f1eb]'"
                  @click="regForm.vehicle_category = 'motor'"
                >
                  <AppIcon name="motorcycle" class="w-4 h-4" />
                  <span>Sepeda Motor</span>
                </button>
                <button
                  type="button"
                  class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border-2 border-black text-xs font-extrabold transition-all shadow-[2px_2px_0_#0a0a0a]"
                  :class="regForm.vehicle_category === 'mobil' ? 'bg-[var(--nb-lime,#e4ff3a)] text-black' : 'bg-white text-slate-600 hover:bg-[#f1f1eb]'"
                  @click="regForm.vehicle_category = 'mobil'"
                >
                  <AppIcon name="car" class="w-4 h-4" />
                  <span>Mobil Roda Empat</span>
                </button>
              </div>
              <p v-if="regErrors.vehicle_category" class="text-xs mt-1 text-rose-600 font-bold">{{ regErrors.vehicle_category }}</p>
            </div>
          </div>

          <div v-if="regError" class="p-3 rounded-xl bg-[#fee2e2] border-2 border-[#0a0a0a] text-rose-800 text-xs font-bold shadow-[2px_2px_0_#0a0a0a]">
            {{ regError }}
          </div>

          <div class="flex gap-3 pt-2">
            <button class="nb-btn nb-btn-md bg-white flex-1 text-xs" @click="showRegisterModal = false">Batal</button>
            <button
              class="nb-btn nb-btn-md bg-[var(--nb-lime,#e4ff3a)] text-black font-extrabold flex-1 text-xs"
              :disabled="regSaving"
              @click="saveMember"
            >
              {{ regSaving ? 'Mendaftarkan...' : 'Daftarkan Member' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ===== MODAL: Edit Data & Tagihan (Tabbed) ===== -->
    <Teleport to="body">
      <div
        v-if="showEditModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @mousedown.self="showEditModal = false"
      >
        <div class="nb-modal w-full max-w-xl bg-white border-2 border-black shadow-[8px_8px_0_#0a0a0a] rounded-3xl animate-fade-in text-[#0a0a0a] max-h-[90vh] flex flex-col">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 pb-4 border-b-2 border-black/10 shrink-0">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-[var(--nb-purple)] border-2 border-black flex items-center justify-center text-sm font-black text-white shrink-0">
                {{ editingMember?.name?.charAt(0)?.toUpperCase() }}
              </div>
              <div>
                <h2 class="text-sm font-black text-[#0a0a0a] leading-tight">{{ editingMember?.name }}</h2>
                <p class="text-[10px] font-mono text-[var(--nb-muted)] font-bold">{{ editingMember?.member_code }}</p>
              </div>
            </div>
            <button class="p-1 text-slate-500 hover:text-black rounded-lg transition-colors border border-transparent hover:border-black" @click="showEditModal = false">
              <AppIcon name="close" class="w-4 h-4" />
            </button>
          </div>

          <!-- Tabs -->
          <div class="flex border-b-2 border-black/10 px-6 shrink-0">
            <button
              v-for="tab in editTabs"
              :key="tab.key"
              class="pb-3 pt-3 px-3 text-xs font-extrabold border-b-2 transition-all -mb-0.5"
              :class="editTab === tab.key
                ? 'border-[#0a0a0a] text-[#0a0a0a]'
                : 'border-transparent text-[var(--nb-muted)] hover:text-[#0a0a0a]'"
              @click="switchTab(tab.key)"
            >
              {{ tab.label }}
              <span v-if="tab.key === 'bills' && editingMemberBills.filter(b => b.status !== 'lunas').length > 0"
                class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-[#fef08a] text-[#713f12] text-[9px] font-black border border-black">
                {{ editingMemberBills.filter(b => b.status !== 'lunas').length }}
              </span>
            </button>
          </div>

          <!-- Tab Content (scrollable) -->
          <div class="flex-1 overflow-y-auto p-6">

            <!-- TAB: Info Akun -->
            <div v-if="editTab === 'info'" class="space-y-4">
              <div>
                <label class="form-label font-bold text-xs">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input v-model="editForm.name" type="text" class="form-input border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]" :class="{ error: editErrors.name }" />
                <p v-if="editErrors.name" class="text-xs mt-1 text-rose-600 font-bold">{{ editErrors.name }}</p>
              </div>

              <div>
                <div class="flex items-center justify-between">
                  <label class="form-label font-bold text-xs">Instansi / Perusahaan</label>
                  <span class="text-[10px] text-[var(--nb-muted)] font-mono font-bold">Opsional</span>
                </div>
                <input v-model="editForm.company_name" type="text" class="form-input border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]" placeholder="PT Plaza Andalas" />
              </div>

              <div>
                <div class="flex items-center justify-between">
                  <label class="form-label font-bold text-xs">Plat Nomor Kendaraan</label>
                  <span class="text-[10px] text-[var(--nb-muted)] font-mono font-bold">Opsional</span>
                </div>
                <input
                  v-model="editForm.plate_number"
                  type="text"
                  class="form-input font-mono uppercase font-black border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]"
                  :class="{ error: editErrors.plate_number }"
                  placeholder="BA 1234 XY"
                  @input="editForm.plate_number = editForm.plate_number.toUpperCase()"
                />
                <p v-if="editErrors.plate_number" class="text-xs mt-1 text-rose-600 font-bold">{{ editErrors.plate_number }}</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <div class="flex items-center justify-between">
                    <label class="form-label font-bold text-xs">No. WhatsApp</label>
                    <span class="text-[10px] text-[var(--nb-muted)] font-mono font-bold">Opsional</span>
                  </div>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-black font-mono text-xs font-black select-none">WA</div>
                    <input v-model="editForm.phone_number" type="tel" class="form-input pl-11 font-mono text-xs border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]" placeholder="08123456789" />
                  </div>
                </div>
                <div>
                  <div class="flex items-center justify-between">
                    <label class="form-label font-bold text-xs">Alamat Email</label>
                    <span class="text-[10px] text-[var(--nb-muted)] font-mono font-bold">Opsional</span>
                  </div>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-black/60 select-none">
                      <AppIcon name="mail" class="w-3.5 h-3.5 text-black" />
                    </div>
                    <input v-model="editForm.email" type="email" class="form-input pl-10 text-xs border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a]" placeholder="member@email.com" />
                  </div>
                </div>
              </div>

              <div>
                <label class="form-label font-bold text-xs">Kategori Kendaraan</label>
                <div class="grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border-2 border-black text-xs font-extrabold transition-all shadow-[2px_2px_0_#0a0a0a]"
                    :class="editForm.vehicle_category === 'motor' ? 'bg-[var(--nb-lime,#e4ff3a)] text-black' : 'bg-white text-slate-600 hover:bg-[#f1f1eb]'"
                    @click="editForm.vehicle_category = 'motor'"
                  >
                    <AppIcon name="motorcycle" class="w-4 h-4" />
                    <span>Sepeda Motor</span>
                  </button>
                  <button
                    type="button"
                    class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border-2 border-black text-xs font-extrabold transition-all shadow-[2px_2px_0_#0a0a0a]"
                    :class="editForm.vehicle_category === 'mobil' ? 'bg-[var(--nb-lime,#e4ff3a)] text-black' : 'bg-white text-slate-600 hover:bg-[#f1f1eb]'"
                    @click="editForm.vehicle_category = 'mobil'"
                  >
                    <AppIcon name="car" class="w-4 h-4" />
                    <span>Mobil Roda Empat</span>
                  </button>
                </div>
              </div>

              <div>
                <label class="form-label font-bold text-xs">Status Keanggotaan</label>
                <select v-model="editForm.is_active" class="form-input border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a] font-bold">
                  <option :value="true">Aktif (Bisa Menggunakan Gate)</option>
                  <option :value="false">Non-Aktif (Akses Gate Ditangguhkan)</option>
                </select>
              </div>

              <div v-if="editError" class="p-3 rounded-xl bg-[#fee2e2] border-2 border-[#0a0a0a] text-rose-800 text-xs font-bold shadow-[2px_2px_0_#0a0a0a]">
                {{ editError }}
              </div>
            </div>

            <!-- TAB: Tagihan -->
            <div v-if="editTab === 'bills'" class="space-y-3">
              <div v-if="loadingBills" class="text-center py-10">
                <svg class="w-5 h-5 animate-spin text-black mx-auto" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <p class="text-xs text-[var(--nb-muted)] mt-2 font-bold">Memuat tagihan...</p>
              </div>

              <div v-else-if="!editingMemberBills.length" class="text-center py-10 text-xs text-slate-500 font-bold">
                <AppIcon name="check-circle" class="w-8 h-8 mx-auto mb-2 text-green-500" />
                Tidak ada catatan tagihan bulanan
              </div>

              <div
                v-for="bill in editingMemberBills"
                :key="bill.id"
                class="p-3.5 rounded-2xl bg-[#f8fafc] border-2 border-black shadow-[2px_2px_0_#0a0a0a] space-y-2"
              >
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <p class="text-xs font-black text-[#0a0a0a] capitalize">{{ formatPeriod(bill.period) }}</p>
                    <p class="text-[10px] font-mono text-[var(--nb-muted)] font-bold mt-0.5">{{ bill.invoice_number || '— menunggu pembayaran' }}</p>
                  </div>
                  <div class="text-right shrink-0">
                    <p class="text-xs font-mono font-black text-[#0a0a0a]">{{ formatRupiah(bill.amount) }}</p>
                    <span
                      class="text-[10px] px-2 py-0.5 rounded-full font-bold border border-black inline-block mt-1 shadow-[1px_1px_0_#0a0a0a]"
                      :class="bill.status === 'lunas' ? 'bg-[#dcfce7] text-[#14532d]' : 'bg-[#fef08a] text-[#713f12]'"
                    >
                      {{ bill.status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                    </span>
                  </div>
                </div>

                <!-- Bayar tagihan (hanya jika belum lunas) -->
                <div v-if="bill.status !== 'lunas'" class="pt-1 border-t border-black/10 flex flex-col sm:flex-row gap-2">
                  <div class="flex items-center gap-2 flex-1">
                    <span class="text-[11px] font-bold text-[var(--nb-muted)] whitespace-nowrap">Bayar cash:</span>
                    <input
                      v-model.number="bill._cash"
                      type="number"
                      class="form-input text-xs font-mono border-2 border-black rounded-xl shadow-[2px_2px_0_#0a0a0a] h-8 flex-1 min-w-0"
                      placeholder="0"
                      min="0"
                    />
                  </div>
                  <button
                    class="nb-btn nb-btn-sm bg-[var(--nb-lime)] text-black font-extrabold whitespace-nowrap"
                    :disabled="payingBillId === bill.id"
                    @click="payBill(bill)"
                  >
                    {{ payingBillId === bill.id ? 'Memproses...' : '✓ Bayar Tagihan' }}
                  </button>
                  <!-- Reminder -->
                  <div class="relative">
                    <button
                      class="nb-btn nb-btn-sm bg-white whitespace-nowrap"
                      @click="toggleReminderDropdown(bill.id)"
                    >
                      📢 Kirim Reminder
                    </button>
                    <div
                      v-if="reminderDropdown === bill.id"
                      class="absolute right-0 mt-1 z-10 w-44 bg-white border-2 border-black rounded-xl shadow-[4px_4px_0_#0a0a0a] overflow-hidden"
                    >
                      <button v-for="ch in reminderChannels" :key="ch.value"
                        class="w-full text-left px-3 py-2 text-xs font-bold hover:bg-[var(--nb-lime)] transition-colors flex items-center gap-2"
                        @click="sendReminder(bill, ch.value)"
                      >
                        {{ ch.icon }} {{ ch.label }}
                      </button>
                    </div>
                  </div>
                </div>
                <div v-if="bill._payError" class="text-xs text-rose-600 font-bold">{{ bill._payError }}</div>
                <div v-if="bill._paySuccess" class="text-xs text-green-700 font-bold">{{ bill._paySuccess }}</div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="p-6 pt-4 border-t-2 border-black/10 shrink-0">
            <div v-if="editTab === 'info'" class="flex gap-3">
              <button class="nb-btn nb-btn-md bg-white flex-1 text-xs" @click="showEditModal = false">Batal</button>
              <button
                class="nb-btn nb-btn-md bg-[var(--nb-lime,#e4ff3a)] text-black font-extrabold flex-1 text-xs"
                :disabled="editSaving"
                @click="saveEdit"
              >
                {{ editSaving ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
            <div v-else class="flex justify-end">
              <button class="nb-btn nb-btn-md bg-white text-xs" @click="showEditModal = false">Tutup</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ===== MODAL: Konfirmasi Hapus ===== -->
    <Teleport to="body">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
      >
        <div class="nb-modal w-full max-w-sm p-6 space-y-4 bg-white border-2 border-black shadow-[8px_8px_0_#0a0a0a] rounded-3xl animate-fade-in text-[#0a0a0a]">
          <div class="text-center">
            <div class="w-12 h-12 rounded-2xl bg-[#fee2e2] border-2 border-black flex items-center justify-center mx-auto mb-3">
              <AppIcon name="trash" class="w-6 h-6 text-rose-700" />
            </div>
            <h2 class="text-base font-black">Hapus Member?</h2>
            <p class="text-xs text-[var(--nb-muted)] mt-1 font-medium">
              Data member <span class="font-black text-[#0a0a0a]">{{ deletingMember?.name }}</span> akan dihapus dari sistem.
            </p>
          </div>
          <div class="flex gap-3">
            <button class="nb-btn nb-btn-md bg-white flex-1 text-xs" @click="showDeleteModal = false">Batal</button>
            <button
              class="nb-btn nb-btn-md bg-[#fee2e2] text-rose-800 border-rose-800 font-extrabold flex-1 text-xs"
              :disabled="deleting"
              @click="deleteMember"
            >
              {{ deleting ? 'Menghapus...' : 'Ya, Hapus' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Member Card Modal -->
    <MemberCardModal v-model="showCardModal" :member="selectedMemberCard" />
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'
import PaginationBar from '~/components/ui/PaginationBar.vue'
import MemberCardModal from '~/components/ui/MemberCardModal.vue'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Data Member — E-Parkir Plaza Andalas' })

const { apiFetch } = useApi()

// ─── List State ──────────────────────────────────────────────
const members = ref<any[]>([])
const loading = ref(true)
const search = ref('')
const filterActive = ref('')
const page = ref(1)
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 15 })

// ─── Register Modal ──────────────────────────────────────────
const showRegisterModal = ref(false)
const regSaving = ref(false)
const regError = ref('')
const regErrors = reactive<Record<string, string>>({})
const regForm = reactive({
  name: '',
  company_name: '',
  plate_number: '',
  phone_number: '',
  email: '',
  vehicle_category: 'motor',
})

function openRegisterModal() {
  regError.value = ''
  Object.keys(regErrors).forEach(k => delete (regErrors as any)[k])
  regForm.name = ''
  regForm.company_name = ''
  regForm.plate_number = ''
  regForm.phone_number = ''
  regForm.email = ''
  regForm.vehicle_category = 'motor'
  showRegisterModal.value = true
}

async function saveMember() {
  regSaving.value = true
  regError.value = ''
  Object.keys(regErrors).forEach(k => delete (regErrors as any)[k])
  try {
    const res = await apiFetch<any>('/members', { method: 'POST', body: regForm })
    const created = res?.member ?? res?.data
    showRegisterModal.value = false
    await loadMembers()
    if (created) openMemberCard(created)
  } catch (e: any) {
    if (e?.response?.status === 422) {
      const errs = e?.data?.errors ?? {}
      Object.assign(regErrors, Object.fromEntries(Object.entries(errs).map(([k, v]) => [k, (v as string[])[0]])))
    } else {
      regError.value = e?.data?.message ?? 'Terjadi kesalahan saat mendaftarkan member'
    }
  } finally {
    regSaving.value = false
  }
}

// ─── Edit + Tagihan Modal ────────────────────────────────────
const showEditModal = ref(false)
const editingMember = ref<any>(null)
const editTab = ref<'info' | 'bills'>('info')
const editTabs = [
  { key: 'info', label: 'Info Akun' },
  { key: 'bills', label: 'Tagihan' },
]
const editSaving = ref(false)
const editError = ref('')
const editErrors = reactive<Record<string, string>>({})
const editForm = reactive({
  name: '',
  company_name: '',
  plate_number: '',
  phone_number: '',
  email: '',
  vehicle_category: 'motor',
  is_active: true,
})

// Bills inside edit modal
const editingMemberBills = ref<any[]>([])
const loadingBills = ref(false)
const payingBillId = ref<number | null>(null)
const reminderDropdown = ref<number | null>(null)
const reminderChannels = [
  { value: 'whatsapp', label: 'WhatsApp', icon: '📱' },
  { value: 'email', label: 'Email', icon: '✉️' },
  { value: 'all', label: 'Keduanya', icon: '📢' },
]

function openEditModal(member: any) {
  editingMember.value = member
  editTab.value = 'info'
  editError.value = ''
  Object.keys(editErrors).forEach(k => delete (editErrors as any)[k])
  editForm.name = member.name
  editForm.company_name = member.company_name || ''
  editForm.plate_number = member.plate_number || ''
  editForm.phone_number = member.phone_number || ''
  editForm.email = member.email || ''
  editForm.vehicle_category = member.vehicle_category
  editForm.is_active = member.is_active
  editingMemberBills.value = []
  showEditModal.value = true
}

async function switchTab(tab: 'info' | 'bills') {
  editTab.value = tab
  if (tab === 'bills' && editingMember.value && !editingMemberBills.value.length) {
    await loadMemberBills()
  }
}

async function loadMemberBills() {
  loadingBills.value = true
  try {
    const data = await apiFetch<any>(`/members/${editingMember.value.id}/bills?per_page=24`)
    editingMemberBills.value = (data.data || []).map((b: any) => ({ ...b, _cash: b.amount, _payError: '', _paySuccess: '' }))
  } catch {} finally {
    loadingBills.value = false
  }
}

async function saveEdit() {
  editSaving.value = true
  editError.value = ''
  Object.keys(editErrors).forEach(k => delete (editErrors as any)[k])
  try {
    await apiFetch(`/members/${editingMember.value.id}`, { method: 'PUT', body: editForm })
    showEditModal.value = false
    await loadMembers()
  } catch (e: any) {
    if (e?.response?.status === 422) {
      const errs = e?.data?.errors ?? {}
      Object.assign(editErrors, Object.fromEntries(Object.entries(errs).map(([k, v]) => [k, (v as string[])[0]])))
    } else {
      editError.value = e?.data?.message ?? 'Terjadi kesalahan saat menyimpan'
    }
  } finally {
    editSaving.value = false
  }
}

async function payBill(bill: any) {
  payingBillId.value = bill.id
  bill._payError = ''
  bill._paySuccess = ''
  try {
    await apiFetch(`/bills/${bill.id}/pay`, { method: 'POST', body: { cash_amount: bill._cash } })
    bill._paySuccess = 'Tagihan berhasil dibayar!'
    bill.status = 'lunas'
    await loadMembers()
  } catch (e: any) {
    bill._payError = e?.data?.message ?? 'Gagal memproses pembayaran'
  } finally {
    payingBillId.value = null
  }
}

function toggleReminderDropdown(billId: number) {
  reminderDropdown.value = reminderDropdown.value === billId ? null : billId
}

async function sendReminder(bill: any, channel: string) {
  reminderDropdown.value = null
  try {
    await apiFetch(`/bills/${bill.id}/remind`, { method: 'POST', body: { channel } })
    bill._paySuccess = `Reminder via ${channel} berhasil dikirim!`
  } catch (e: any) {
    bill._payError = e?.data?.message ?? 'Gagal mengirim reminder'
  }
}

// ─── Delete Modal ─────────────────────────────────────────────
const showDeleteModal = ref(false)
const deletingMember = ref<any>(null)
const deleting = ref(false)

function confirmDelete(member: any) {
  deletingMember.value = member
  showDeleteModal.value = true
}

async function deleteMember() {
  deleting.value = true
  try {
    await apiFetch(`/members/${deletingMember.value.id}`, { method: 'DELETE' })
    showDeleteModal.value = false
    await loadMembers()
  } catch {} finally {
    deleting.value = false
  }
}

// ─── Member Card Modal ────────────────────────────────────────
const showCardModal = ref(false)
const selectedMemberCard = ref<any>(null)

function openMemberCard(member: any) {
  selectedMemberCard.value = member
  showCardModal.value = true
}

// ─── Helpers ──────────────────────────────────────────────────
function formatRupiah(n: number) {
  return 'Rp ' + (n || 0).toLocaleString('id-ID')
}

function formatPeriod(d: string) {
  return new Date(d).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
}

let searchTimer: ReturnType<typeof setTimeout>
function debouncedSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; loadMembers() }, 350)
}

async function loadMembers() {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: String(page.value) })
    if (search.value.trim()) params.append('search', search.value.trim())
    if (filterActive.value) params.append('is_active', filterActive.value)
    const data = await apiFetch<any>(`/members?${params}`)
    members.value = data.data
    meta.value = data.meta
  } catch {} finally {
    loading.value = false
  }
}

onMounted(loadMembers)
</script>
