<template>
  <div class="p-8 text-center">
    <p class="text-sm font-bold text-[var(--nb-muted)]">Mengalihkan ke Terminal Kasir & Gate...</p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
navigateTo('/tiket')
</script>
