<script setup lang="ts">
import Sidebar from './components/Sidebar.vue';
import Header from './components/Header.vue'
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';

const sidebarOpen = ref(false);

// close the sidebar whenever the page changes
const route = useRoute();
watch(() => route.path, () => { sidebarOpen.value = false; });
</script>

<template>
  <div class="app">
    <Header @toggle-sidebar="sidebarOpen = !sidebarOpen"/>
    
    <Transition name="slide">
      <Sidebar v-if="sidebarOpen"></Sidebar>
    </Transition>

    <div class="main-page">
      <RouterView/>
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
  justify-content: safe center;
  align-items: center;
  gap: 2rem;

  box-sizing: border-box;
  padding-bottom: 8rem;

  overflow-y: auto;
}

.slide-enter-active, .slide-leave-active {
  transition: transform 0.2s ease;
}

.slide-enter-from, .slide-leave-to {
  transform: translateX(100%);
}
</style>