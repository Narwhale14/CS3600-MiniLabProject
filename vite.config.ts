import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [ vue() ],
  server: {
    port: 5173,
    strictPort: true,
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:80',
        changeOrigin: true
      },
      '^/(?!api(?:/|$)|login/?$|signup/?$|links/?$|profile/?$)[A-Za-z0-9_-]{1,24}/?(?:\\?.*)?$': {
        target: 'http://127.0.0.1:80',
        changeOrigin: true,
        rewrite: (path) => {
          const code = path.split('?')[0].replace(/^\/|\/$/g, '')
          return '/api/shortlink.php?code=' + encodeURIComponent(code)
        }
      }
    }
  }
});
