<template>
  <div class="flex h-screen overflow-hidden bg-[#070b14] text-slate-100">
    <!-- Sidebar Backdrop for Mobile -->
    <div
      v-if="mobileSidebarOpen"
      class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
      @click="mobileSidebarOpen = false"
    />

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 flex flex-col bg-[#0b101d] border-r border-slate-800/80 transition-all duration-300 shadow-2xl"
      :class="[
        sidebarOpen ? 'w-64' : 'w-20',
        mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Brand Logo -->
      <div class="flex items-center gap-3.5 px-5 h-16 border-b border-slate-800/80 shrink-0">
        <div class="shrink-0 w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-sky-400 p-0.5 shadow-lg shadow-blue-500/25 flex items-center justify-center">
          <div class="w-full h-full bg-[#0b101d]/60 rounded-[10px] flex items-center justify-center backdrop-blur-sm">
            <AppIcon name="gate" class="w-5 h-5 text-white" />
          </div>
        </div>
        <div v-if="sidebarOpen" class="min-w-0 flex-1 overflow-hidden transition-all duration-200">
          <div class="flex items-center gap-2">
            <span class="text-sm font-bold tracking-tight text-white">E-PARKIR</span>
            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold tracking-wide bg-blue-500/15 text-blue-400 border border-blue-500/20">PRO</span>
          </div>
          <p class="text-[11px] font-medium text-slate-400 tracking-wider uppercase truncate">Plaza Andalas</p>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 py-4 px-3 space-y-6 overflow-y-auto">
        <div v-for="group in navGroups" :key="group.label" class="space-y-1">
          <p
            v-if="sidebarOpen && group.label"
            class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500"
          >
            {{ group.label }}
          </p>
          <div v-else-if="!sidebarOpen && group.label" class="h-px bg-slate-800/60 my-2 mx-2"></div>

          <NuxtLink
            v-for="item in group.items"
            :key="item.to"
            :to="item.to"
            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 relative"
            :class="isRouteActive(item.to)
              ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 shadow-sm shadow-blue-500/10'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 border border-transparent'"
            :title="!sidebarOpen ? item.label : ''"
            @click="mobileSidebarOpen = false"
          >
            <!-- Active indicator dot/bar -->
            <span
              v-if="isRouteActive(item.to)"
              class="absolute left-0 top-2 bottom-2 w-1 bg-blue-500 rounded-r-full"
            />
            <AppIcon
              :name="item.icon"
              class="w-5 h-5 shrink-0 transition-transform duration-150 group-hover:scale-105"
              :class="isRouteActive(item.to) ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-200'"
            />
            <span v-if="sidebarOpen" class="truncate">{{ item.label }}</span>
          </NuxtLink>
        </div>
      </nav>

      <!-- User Account & Quick Status -->
      <div class="p-3 border-t border-slate-800/80 bg-[#090d18]/60 shrink-0">
        <div
          class="flex items-center gap-3 p-2 rounded-xl bg-slate-900/60 border border-slate-800/80"
          :class="!sidebarOpen ? 'justify-center p-1.5' : ''"
        >
          <div class="relative shrink-0">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-sm font-bold text-white shadow-md shadow-blue-600/20">
              {{ userInitial }}
            </div>
            <!-- Online Dot -->
            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-[#0b101d] rounded-full"></span>
          </div>

          <div v-if="sidebarOpen" class="min-w-0 flex-1">
            <p class="text-xs font-semibold text-white truncate">{{ authStore.user?.name || 'Petugas' }}</p>
            <div class="flex items-center gap-1.5 mt-0.5">
              <span
                class="inline-block text-[10px] font-mono font-medium px-1.5 py-0.2 rounded"
                :class="authStore.isSuperAdmin ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30'"
              >
                {{ authStore.user?.role === 'super_admin' ? 'Super Admin' : 'Petugas' }}
              </span>
            </div>
          </div>

          <button
            v-if="sidebarOpen"
            class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors"
            title="Keluar dari sistem"
            @click="handleLogout"
          >
            <AppIcon name="logout" class="w-4 h-4" />
          </button>
        </div>

        <button
          v-if="!sidebarOpen"
          class="mt-2 w-full flex items-center justify-center p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors"
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
      <header class="h-16 shrink-0 bg-[#0b101d]/90 backdrop-blur-md border-b border-slate-800/80 px-6 flex items-center justify-between z-20">
        <div class="flex items-center gap-4">
          <!-- Toggle Sidebar Button Desktop -->
          <button
            class="hidden lg:flex p-2 rounded-xl text-slate-400 hover:text-slate-100 hover:bg-slate-800/70 transition-all border border-transparent hover:border-slate-700/60"
            @click="sidebarOpen = !sidebarOpen"
            title="Sembunyikan/Tampilkan Menu"
          >
            <AppIcon name="chevron-left" class="w-4 h-4 transition-transform duration-200" :class="!sidebarOpen ? 'rotate-180' : ''" />
          </button>

          <!-- Mobile Hamburger Toggle -->
          <button
            class="flex lg:hidden p-2 rounded-xl text-slate-400 hover:text-slate-100 hover:bg-slate-800/70"
            @click="mobileSidebarOpen = !mobileSidebarOpen"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>

          <!-- Current Route Title Breadcrumb -->
          <div class="flex items-center gap-2 text-sm">
            <span class="text-slate-400 font-medium">Plaza Andalas</span>
            <span class="text-slate-600">/</span>
            <span class="font-semibold text-slate-200">{{ currentRouteName }}</span>
          </div>
        </div>

        <!-- Right Side Stats / Info -->
        <div class="flex items-center gap-4">
          <!-- Live Terminal Badge -->
          <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/90 border border-slate-800 text-xs">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-slate-400 font-medium">Terminal Gate 01</span>
            <span class="text-slate-600">·</span>
            <span class="text-emerald-400 font-medium">Online</span>
          </div>

          <!-- Realtime Date & Time -->
          <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900/80 border border-slate-800/80 text-xs text-slate-300 font-mono">
            <AppIcon name="clock" class="w-3.5 h-3.5 text-blue-400" />
            <span>{{ liveTimeString }}</span>
          </div>
        </div>
      </header>

      <!-- Main Content Scroll Area -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
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
  if (path.startsWith('/tiket')) return 'Tiket Masuk & Kasir'
  if (path.startsWith('/scan')) return 'Gate Scan Member'
  if (path.startsWith('/members')) return 'Data Keanggotaan'
  if (path.startsWith('/tagihan')) return 'Tagihan Bulanan'
  if (path.startsWith('/laporan')) return 'Laporan Transaksi'
  if (path.startsWith('/users')) return 'Manajemen Petugas'
  if (path.startsWith('/tariff')) return 'Pengaturan Tarif'
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
  const groups = [
    {
      label: 'OPERASIONAL',
      items: [
        { to: '/dashboard', label: 'Dashboard', icon: 'dashboard' },
        { to: '/tiket', label: 'Tiket Non-Member', icon: 'ticket' },
        { to: '/scan', label: 'Gate Scan Member', icon: 'scan' },
      ],
    },
    {
      label: 'KEANGGOTAAN',
      items: [
        { to: '/members', label: 'Data Member', icon: 'users' },
        { to: '/tagihan', label: 'Tagihan Member', icon: 'card' },
      ],
    },
    {
      label: 'LAPORAN & ANALITIK',
      items: [
        { to: '/laporan', label: 'Laporan Transaksi', icon: 'report' },
      ],
    },
  ]

  if (authStore.isSuperAdmin) {
    groups.push({
      label: 'SISTEM & TARIF',
      items: [
        { to: '/users', label: 'Manajemen Pengguna', icon: 'user' },
        { to: '/tariff', label: 'Pengaturan Tarif', icon: 'tariff' },
      ],
    })
  }

  return groups
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
