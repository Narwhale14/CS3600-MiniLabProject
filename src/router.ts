import { createRouter, createWebHistory } from 'vue-router';
import LinkShortener from './pages/LinkShortener.vue';
import LoginPage from './pages/LoginPage.vue';
import SignupPage from './pages/SignupPage.vue';
import ViewAllLinks from './pages/ViewAllLinks.vue';
import ProfilePage from './pages/ProfilePage.vue';

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: LinkShortener },
        { path: '/login', component: LoginPage },
        { path: '/signup', component: SignupPage },
        { path: '/links', component: ViewAllLinks },
        { path: '/profile', component: ProfilePage }
    ]
});