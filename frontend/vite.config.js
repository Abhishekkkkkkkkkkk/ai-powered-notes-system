import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    host: true, // Needed for Docker container mapping
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://webserver:80', // Routes through Docker nginx container
        changeOrigin: true,
        secure: false,
      }
    }
  }
})
