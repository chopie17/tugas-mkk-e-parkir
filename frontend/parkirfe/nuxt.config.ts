import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  telemetry: false,
  devtools: { enabled: false },

  devServer: {
    host: '0.0.0.0',
    port: 3000,
  },

  // Module dependencies
  modules: ['@pinia/nuxt', '@vueuse/nuxt'],

  // Tailwind CSS v4 via Vite plugin
  vite: {
    plugins: [tailwindcss()],
  },

  // Global CSS
  css: ['~/assets/css/main.css'],

  // Runtime config — override di .env
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://10.100.0.203:8000/api',
    },
  },

  // App metadata
  app: {
    head: {
      title: 'E-Parkir Plaza Andalas',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Sistem E-Parkir Plaza Andalas — manajemen tiket dan member parkir.' },
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap' },
      ],
    },
    pageTransition: { name: 'page', mode: 'out-in' },
  },
})
