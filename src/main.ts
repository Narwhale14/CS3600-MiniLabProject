import { createApp } from 'vue'
import { router } from './router.ts'
import { getSession } from './auth'
import App from './App.vue'
import './style.css'

async function main() {
  try {
    await getSession()
  } catch (err) {
    console.error('Session initialization failed:', err)
  }

  createApp(App).use(router).mount('#app')
}

main()
