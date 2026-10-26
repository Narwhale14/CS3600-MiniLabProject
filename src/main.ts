import { createApp } from 'vue'
import { router } from './router.ts'
import { getSession } from './auth'
import App from './App.vue'
import './style.css'

getSession().then(() => createApp(App).use(router).mount('#app'));
