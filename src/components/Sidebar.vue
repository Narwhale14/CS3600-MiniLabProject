
<script setup lang="ts">
import { useRouter } from 'vue-router'
import { loggedIn, csrfToken, getSession } from '../auth'

const router = useRouter()

async function logout() {
        try {
                const response = await fetch('/api/shortlink.php', {
                        method: 'POST',
                        headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': csrfToken.value
                        },
                        body: JSON.stringify({
                                action: 'logout'
                        })
                })

                const data = await response.json()

                if (!response.ok) {
                        console.error(data.error || 'Failed to log out')
                        return
                }

                loggedIn.value = false
                csrfToken.value = ''
                router.push('/')

                await getSession()

        } catch (err) {
                console.error('Logout failed:', err)
        }
}
</script>

<template>
        <div class="sidebar">
                <button class="sidebar-button" @click="router.push('/')">Home</button>

                <template v-if="loggedIn">
                        <button class="sidebar-button" @click="router.push('/links')">View All Links</button>
                        <button class="sidebar-button logout-button" @click="logout">Log Out</button>
                </template>

                <template v-else>
                        <button class="sidebar-button" @click="router.push('/login')">Log In</button>
                        <button class="sidebar-button" @click="router.push('/signup')">Sign Up</button>
                </template>
        </div>
</template>

<style scoped>
.sidebar {
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 4rem;
        width: 16rem;
        right: 0;
        bottom: 0;
        background-color: var(--canvas-dark);
        gap: 1rem;
        padding: 1rem;
        box-sizing: border-box;
}

.sidebar-button {
        height: 2.5rem;
        border: none;
        border-radius: 0.4rem;
        background-color: cornflowerblue;
        color: Canvas;
        cursor: pointer;
        font-size: 1rem;
}

.sidebar-button:hover {
        background-color: color-mix(in oklab, cornflowerblue, black 10%);
}

.logout-button {
        margin-top: auto;
        background: none;
        border: 1px solid crimson;
        color: crimson;
}

.logout-button:hover {
        background-color: crimson;
        color: Canvas;
}
</style>
