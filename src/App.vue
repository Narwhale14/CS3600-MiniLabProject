<script setup lang="ts">
import LinkShortener from './LinkShortener.vue';
import Sidebar from './Sidebar.vue';
import Header from './Header.vue'
import { ref } from 'vue';

const sidebarOpen = ref(false);
</script>

<template>
  <div class="app">
    <Header @toggle-sidebar="sidebarOpen = !sidebarOpen"/>
    
    <Transition name="slide">
      <Sidebar v-if="sidebarOpen"></Sidebar>
    </Transition>

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

.slide-enter-active, .slide-leave-active {
  transition: transform 0.2s ease;
}

.slide-enter-from, .slide-leave-to {
  transform: translateX(100%);
}
</style>