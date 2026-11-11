<template>
  <div
    v-if="meta && meta.last_page > 1"
    class="flex flex-col sm:flex-row items-center justify-between gap-3 px-6 py-4 border-t-2 border-[#0a0a0a] bg-[#f8fafc]"
  >
    <div class="text-xs font-bold text-[var(--nb-muted)]">
      Menampilkan <span class="font-black text-[#0a0a0a]">{{ rangeStart }}</span> -
      <span class="font-black text-[#0a0a0a]">{{ rangeEnd }}</span> dari
      <span class="font-black text-[#0a0a0a]">{{ meta.total ?? 0 }}</span> data
    </div>

    <div class="flex items-center gap-2">
      <button
        class="nb-btn nb-btn-sm bg-white flex items-center gap-1.5"
        :disabled="page <= 1"
        @click="$emit('prev')"
      >
        <AppIcon name="chevron-left" class="w-3.5 h-3.5" />
        <span>Sebelumnya</span>
      </button>

      <div class="px-3 py-1 rounded-xl bg-white border-2 border-[#0a0a0a] text-xs font-mono font-black text-[#0a0a0a] shadow-[1.5px_1.5px_0_#0a0a0a]">
        Halaman <span class="text-[var(--nb-purple,#6c47ff)]">{{ page }}</span> / {{ meta.last_page }}
      </div>

      <button
        class="nb-btn nb-btn-sm bg-white flex items-center gap-1.5"
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
