<template>
  <div class="flex h-screen overflow-hidden bg-[var(--nb-bg,#f1f1eb)] text-[var(--nb-text,#1c1c1c)]">
    <!-- Sidebar Backdrop for Mobile -->
    <div
      v-if="mobileSidebarOpen"
      class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
      @click="mobileSidebarOpen = false"
    />

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 flex flex-col bg-[#ffffff] border-r-2 border-[#0a0a0a] transition-all duration-300"
      :class="[
        sidebarOpen ? 'w-64 shadow-[4px_0_0_#0a0a0a]' : 'w-20 shadow-[4px_0_0_#0a0a0a]',
        mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Brand Logo -->
      <div class="flex items-center gap-3.5 px-5 h-16 border-b-2 border-[#0a0a0a] shrink-0 bg-[#f8fafc]">
        <div class="shrink-0 w-10 h-10 rounded-xl bg-[var(--nb-lime,#e4ff3a)] border-2 border-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a] flex items-center justify-center">
          <AppIcon name="gate" class="w-5 h-5 text-black" />
        </div>
        <div v-if="sidebarOpen" class="min-w-0 flex-1 overflow-hidden transition-all duration-200">
          <div class="flex items-center gap-2">
            <span class="text-sm font-extrabold tracking-tight text-[#0a0a0a]">E-PARKIR</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold bg-[#dbeafe] text-[#1e40af] border border-[#0a0a0a] shadow-[1px_1px_0_#0a0a0a]">PRO</span>
          </div>
          <p class="text-[11px] font-bold text-[var(--nb-muted)] tracking-wider uppercase truncate">Plaza Andalas</p>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 py-4 px-3 space-y-1.5 overflow-y-auto">
        <template v-for="group in navGroups" :key="group.label || 'nav'">
          <NuxtLink
            v-for="item in group.items"
            :key="item.to"
            :to="item.to"
            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-150 relative"
            :class="isRouteActive(item.to)
              ? 'bg-[var(--nb-lime,#e4ff3a)] text-[#0a0a0a] border-2 border-[#0a0a0a] shadow-[3px_3px_0_#0a0a0a]'
              : 'text-[var(--nb-muted)] hover:text-[#0a0a0a] hover:bg-[#f1f1eb] border-2 border-transparent hover:border-[#0a0a0a]'"
            :title="!sidebarOpen ? item.label : ''"
            @click="mobileSidebarOpen = false"
          >
            <AppIcon
              :name="item.icon"
              class="w-5 h-5 shrink-0 transition-transform duration-150 group-hover:scale-105"
              :class="isRouteActive(item.to) ? 'text-black' : 'text-[var(--nb-muted)] group-hover:text-black'"
            />
            <span v-if="sidebarOpen" class="truncate">{{ item.label }}</span>
          </NuxtLink>
        </template>
      </nav>

      <!-- User Account & Quick Status -->
      <div class="p-3 border-t-2 border-[#0a0a0a] bg-[#f8fafc] shrink-0">
        <div
          class="flex items-center gap-3 p-2 rounded-xl bg-white border-2 border-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a]"
          :class="!sidebarOpen ? 'justify-center p-1.5' : ''"
        >
          <div class="relative shrink-0">
            <div class="w-9 h-9 rounded-xl bg-[var(--nb-purple,#6c47ff)] border-2 border-[#0a0a0a] flex items-center justify-center text-sm font-extrabold text-white shadow-[1.5px_1.5px_0_#0a0a0a]">
              {{ userInitial }}
            </div>
            <!-- Online Dot -->
            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-[#0a0a0a] rounded-full"></span>
          </div>

          <div v-if="sidebarOpen" class="min-w-0 flex-1">
            <p class="text-xs font-bold text-[#0a0a0a] truncate">{{ authStore.user?.name || 'Petugas' }}</p>
            <div class="flex items-center gap-1.5 mt-0.5">
              <span
                class="inline-block text-[10px] font-mono font-bold px-2 py-0.2 rounded-full border border-[#0a0a0a]"
                :class="authStore.isSuperAdmin ? 'bg-[#f3e8ff] text-[#6b21a8]' : 'bg-[#dbeafe] text-[#1e40af]'"
              >
                {{ authStore.user?.role === 'super_admin' ? 'Super Admin' : 'Petugas' }}
              </span>
            </div>
          </div>

          <!-- Tombol Keluar (Di dalam kolom akun) -->
          <button
            v-if="sidebarOpen"
            class="p-2 text-slate-500 hover:text-black hover:bg-[#fee2e2] rounded-lg transition-colors border border-transparent hover:border-[#0a0a0a] shrink-0"
            title="Keluar dari sistem"
            @click="handleLogout"
          >
            <AppIcon name="logout" class="w-4 h-4" />
          </button>
        </div>

        <button
          v-if="!sidebarOpen"
          class="mt-2 w-full flex items-center justify-center p-2 text-slate-500 hover:text-black hover:bg-[#fee2e2] rounded-lg transition-colors border border-transparent hover:border-[#0a0a0a]"
          title="Keluar dari sistem"
          @click="handleLogout"
        >
          <AppIcon name="logout" class="w-4 h-4" />
        </button>
      </div>
    </aside>

    <!-- Main Wrapper -->
    <div
      class="flex-1 flex flex-col overflow-hidden transition-all duration-300"
      :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'"
    >
      <!-- Top Navigation Header -->
      <header class="h-16 shrink-0 bg-[#ffffff] border-b-2 border-[#0a0a0a] px-6 flex items-center justify-between z-20 shadow-[0_2px_0_#0a0a0a]">
        <div class="flex items-center gap-4">
          <!-- Toggle Sidebar Button Desktop -->
          <button
            class="hidden lg:flex p-2 rounded-xl text-black bg-[#f1f1eb] hover:bg-[var(--nb-lime,#e4ff3a)] transition-all border-2 border-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a]"
            @click="sidebarOpen = !sidebarOpen"
            title="Sembunyikan/Tampilkan Menu"
          >
            <AppIcon name="chevron-left" class="w-4 h-4 transition-transform duration-200" :class="!sidebarOpen ? 'rotate-180' : ''" />
          </button>

          <!-- Mobile Hamburger Toggle -->
          <button
            class="flex lg:hidden p-2 rounded-xl text-black bg-[#f1f1eb] hover:bg-[var(--nb-lime,#e4ff3a)] border-2 border-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a]"
            @click="mobileSidebarOpen = !mobileSidebarOpen"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>

          <!-- Current Route Title Breadcrumb -->
          <div class="flex items-center gap-2 text-sm">
            <span class="text-[var(--nb-muted)] font-bold">Plaza Andalas</span>
            <span class="text-[#0a0a0a] font-black">/</span>
            <span class="font-black text-[#0a0a0a]">{{ currentRouteName }}</span>
          </div>
        </div>

        <!-- Right Side Stats / Info -->
        <div class="flex items-center gap-3">
          <!-- Live Terminal Badge -->
          <div class="hidden md:flex items-center gap-2 px-3 py-1 rounded-full bg-[#dcfce7] border-2 border-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a] text-xs font-bold">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600"></span>
            </span>
            <span class="text-[#14532d]">Gate 01 Online</span>
          </div>

          <!-- Realtime Date & Time -->
          <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-[#ffffff] border-2 border-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a] text-xs text-[#0a0a0a] font-mono font-bold">
            <AppIcon name="clock" class="w-3.5 h-3.5 text-[#0a0a0a]" />
            <span>{{ liveTimeString }}</span>
          </div>

          <!-- Profile Link -->
          <NuxtLink
            to="/profile"
            class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-white hover:bg-[#f1f1eb] border-2 border-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a] text-xs font-black text-[#0a0a0a]"
            title="Edit profil akun saya"
          >
            <AppIcon name="user" class="w-3.5 h-3.5" />
            <span>Profil</span>
          </NuxtLink>
        </div>
      </header>

      <!-- Main Content Scroll Area -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-[var(--nb-bg,#f1f1eb)]">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const sidebarOpen = ref(true)
const mobileSidebarOpen = ref(false)

const userInitial = computed(() => {
  return authStore.user?.name?.charAt(0).toUpperCase() || 'P'
})

const currentRouteName = computed(() => {
  const path = route.path
  if (path.startsWith('/dashboard')) return 'Dashboard'
  if (path.startsWith('/tiket')) return 'Terminal Kasir & Gate'
  if (path.startsWith('/scan')) return 'Gate Scan Member'
  if (path.startsWith('/members')) return 'Data Keanggotaan'
  if (path.startsWith('/tagihan')) return 'Tagihan Bulanan'
  if (path.startsWith('/laporan')) return 'Laporan Transaksi'
  if (path.startsWith('/users')) return 'Manajemen Pengguna'
  if (path.startsWith('/tariff')) return 'Pengaturan Tarif'
  if (path.startsWith('/profile')) return 'Profil Akun'
  return 'Dashboard'
})

function isRouteActive(targetPath: string) {
  if (targetPath === '/dashboard') {
    return route.path === '/dashboard' || route.path === '/'
  }
  return route.path.startsWith(targetPath)
}

// Real-time Clock
const liveTimeString = ref('')
let timer: any = null

function updateClock() {
  const now = new Date()
  liveTimeString.value = now.toLocaleDateString('id-ID', {
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

onMounted(() => {
  updateClock()
  timer = setInterval(updateClock, 1000)
  if (!authStore.isLoggedIn) {
    router.push('/login')
  }
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

const navGroups = computed(() => {
  if (authStore.isSuperAdmin) {
    return [
      {
        items: [
          { to: '/dashboard', label: 'Dashboard', icon: 'dashboard' },
          { to: '/laporan', label: 'Laporan Transaksi', icon: 'report' },
          { to: '/users', label: 'Manajemen Pengguna', icon: 'user' },
          { to: '/tariff', label: 'Pengaturan Tarif', icon: 'tariff' },
        ],
      },
    ]
  }

  return [
    {
      items: [
        { to: '/dashboard', label: 'Dashboard', icon: 'dashboard' },
        { to: '/tiket', label: 'Kasir & Gate', icon: 'ticket' },
        { to: '/members', label: 'Data Member', icon: 'users' },
        { to: '/laporan', label: 'Laporan Transaksi', icon: 'report' },
      ],
    },
  ]
})

async function handleLogout() {
  const { apiFetch } = useApi()
  try {
    await apiFetch('/logout', { method: 'POST' })
  } catch {}
  authStore.logout()
  await router.push('/login')
}
</script>
