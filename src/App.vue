<script setup lang="ts">
import LinkShortener from './LinkShortener.vue';
import Sidebar from './Sidebar.vue';
import { ref } from 'vue';

const sidebarOpen = ref(false);
</script>

<template>
  <div class="app">
    <div class="header">
      <button class="profile-button pi pi-user" @click="sidebarOpen = !sidebarOpen"></button>
    </div>

    <Sidebar v-if="sidebarOpen"></Sidebar>
    
    <div class="main-page">
      <LinkShortener/>
    </div>
  </div>
</template>

<style scoped>
.app {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.main-page {
  flex: 1;

  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 2rem;

  box-sizing: border-box;
  padding-bottom: 8rem;

  overflow: hidden;
}

.header {
  display: flex;
  flex-direction: row;
  justify-content: flex-end;
  align-items: center;
  flex-shrink: 0;

  background-color: var(--canvas-dark);
  width: 100%;
  height: 4rem;
  padding: 0 1rem;
  box-sizing: border-box;
}

.profile-button {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  border: none;
  background-color: cornflowerblue;
  color: whitesmoke;
  cursor: pointer;
}
</style>