<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup lang="ts">
/**
 * Root app component.
 * Init auth store dari localStorage saat pertama mount.
 */
const authStore = useAuthStore()

onMounted(() => {
  authStore.init()
})
</script>
