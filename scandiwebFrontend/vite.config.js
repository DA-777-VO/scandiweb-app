import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    // слушаем на всех интерфейсах внутри контейнера
    host: '0.0.0.0',
    port: 3000,
    proxy: {
      '/graphql': {
        // внутри Docker сети backend доступен по имени сервиса 'backend'
        // но браузер делает запросы через свой localhost:8000
        // поэтому прокси работает: браузер → vite (3000) → backend (8000)
        target: 'http://backend:8000',
        changeOrigin: true,
      }
    }
  }
})
