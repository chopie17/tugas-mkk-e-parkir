<template>
  <div
    v-if="meta && meta.last_page > 1"
    class="flex flex-col sm:flex-row items-center justify-between gap-3 px-6 py-4 border-t border-slate-800/80 bg-slate-900/40"
  >
    <div class="text-xs text-slate-400">
      Menampilkan <span class="font-semibold text-slate-200">{{ rangeStart }}</span> -
      <span class="font-semibold text-slate-200">{{ rangeEnd }}</span> dari
      <span class="font-semibold text-slate-200">{{ meta.total ?? 0 }}</span> data
    </div>

    <div class="flex items-center gap-2">
      <button
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-700 border border-slate-700/80 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-slate-800/80"
        :disabled="page <= 1"
        @click="$emit('prev')"
      >
        <AppIcon name="chevron-left" class="w-3.5 h-3.5" />
        <span>Sebelumnya</span>
      </button>

      <div class="px-3 py-1 rounded-lg bg-slate-950/60 border border-slate-800 text-xs font-mono text-slate-300">
        Halaman <span class="text-blue-400 font-semibold">{{ page }}</span> / {{ meta.last_page }}
      </div>

      <button
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-700 border border-slate-700/80 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-slate-800/80"
        :disabled="page >= meta.last_page"
        @click="$emit('next')"
      >
        <span>Selanjutnya</span>
        <AppIcon name="chevron-right" class="w-3.5 h-3.5" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppIcon from './AppIcon.vue'

const props = defineProps<{
  meta: {
    total?: number
    per_page?: number
    last_page: number
    current_page?: number
  }
  page: number
}>()

defineEmits(['prev', 'next'])

const rangeStart = computed(() => {
  if (!props.meta?.total) return 0
  const perPage = props.meta.per_page || 15
  return (props.page - 1) * perPage + 1
})

const rangeEnd = computed(() => {
  if (!props.meta?.total) return 0
  const perPage = props.meta.per_page || 15
  return Math.min(props.page * perPage, props.meta.total)
})
</script>
