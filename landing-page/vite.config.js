import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  base: '/laravel-auth-starter-kits/',
  build: {
    outDir: 'dist'
  }
})
