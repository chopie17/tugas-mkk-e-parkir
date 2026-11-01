<script setup lang="ts">
// Root redirect ke dashboard (atau login jika belum login)
definePageMeta({ middleware: 'auth' })
await navigateTo('/dashboard', { replace: true })
</script>
