<template>
  <div class="min-h-screen bg-[#070b14] relative flex flex-col justify-between items-center p-4 sm:p-6 lg:p-8 overflow-x-hidden">
    <!-- Ambient Backlight -->
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-gradient-to-b from-blue-600/20 to-indigo-600/5 blur-[120px] pointer-events-none rounded-full" />
    <div class="absolute -bottom-40 left-1/3 w-[500px] h-[300px] bg-indigo-600/10 blur-[100px] pointer-events-none rounded-full" />

    <!-- Top mini brand header -->
    <header class="w-full max-w-6xl py-4 flex items-center justify-between z-10">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center p-1.5 shadow-md shadow-blue-500/20">
          <AppIcon name="gate" class="w-5 h-5 text-white" />
        </div>
        <div class="flex flex-col">
          <span class="text-sm font-bold tracking-tight text-white leading-tight">E-PARKIR</span>
          <span class="text-[10px] font-medium text-slate-400 tracking-wider uppercase">Plaza Andalas</span>
        </div>
      </div>
      <div class="flex items-center gap-2 text-xs text-slate-400">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="hidden sm:inline">Server Operasional:</span>
        <span class="font-mono text-emerald-400 font-medium">Aktif</span>
      </div>
    </header>

    <!-- Center Card -->
    <div class="w-full flex-1 flex items-center justify-center py-6 z-10">
      <slot />
    </div>

    <!-- Footer -->
    <footer class="w-full max-w-6xl py-4 text-center text-xs text-slate-500 z-10 border-t border-slate-800/40">
      <p>© {{ new Date().getFullYear() }} E-Parkir Plaza Andalas · Hak Cipta Dilindungi · Sistem Manajemen Parkir Terintegrasi</p>
    </footer>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'
</script>
