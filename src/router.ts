import { createRouter, createWebHistory } from 'vue-router';
import LinkShortener from './pages/LinkShortener.vue';
import LoginPage from './pages/LoginPage.vue';
import SignupPage from './pages/SignupPage.vue';

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: LinkShortener },
        { path: '/login', component: LoginPage },
        { path: '/signup', component: SignupPage }
    ]
});