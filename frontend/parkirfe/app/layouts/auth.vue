<template>
  <div class="min-h-screen bg-[var(--nb-bg,#f1f1eb)] text-[var(--nb-text,#1c1c1c)] relative flex flex-col justify-between items-center p-4 sm:p-6 lg:p-8 select-none overflow-x-hidden font-sans">
    <!-- Top Floating Pill Header -->
    <header class="w-full max-w-4xl py-2 flex items-center justify-between z-10">
      <NuxtLink to="/" class="flex items-center gap-2.5 group">
        <div class="w-9 h-9 rounded-full bg-[var(--nb-lime,#e4ff3a)] border-2 border-[#0a0a0a] shadow-[2px_2px_0_#0a0a0a] flex items-center justify-center group-hover:scale-105 transition-transform">
          <AppIcon name="gate" class="w-4 h-4 text-black" />
        </div>
        <div class="flex flex-col">
          <span class="text-sm font-black tracking-tight text-[#0a0a0a] leading-tight">E-PARKIR</span>
          <span class="text-[10px] font-bold text-[var(--nb-muted)] tracking-wider uppercase">Plaza Andalas</span>
        </div>
      </NuxtLink>

      <div class="flex items-center gap-2">
        <NuxtLink
          to="/login"
          class="nb-btn nb-btn-sm bg-white flex items-center gap-1.5"
        >
          <AppIcon name="user" class="w-3.5 h-3.5" />
          <span>Masuk</span>
        </NuxtLink>
      </div>
    </header>

    <!-- Center Card -->
    <div class="w-full flex-1 flex items-center justify-center py-6 z-10">
      <slot />
    </div>

    <!-- Footer & Accent Stripe -->
    <div class="w-full max-w-4xl py-3 text-center text-xs text-[var(--nb-muted)] font-medium z-10 flex flex-col sm:flex-row items-center justify-between gap-2 border-t-2 border-[#0a0a0a]/20">
      <p>© {{ new Date().getFullYear() }} E-Parkir Plaza Andalas · Portal Staf Operasional</p>
      <div class="flex items-center gap-1.5 font-bold text-[#0a0a0a]">
        <span class="w-2 h-2 rounded-full bg-emerald-500 border border-black"></span>
        <span>Sistem Aktif</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from '~/components/ui/AppIcon.vue'
</script>
