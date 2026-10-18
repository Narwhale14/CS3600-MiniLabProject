<script setup lang="ts">
import LinkShortener from './LinkShortener.vue';
</script>

<template>
  <div class="main-page">
    <LinkShortener/>
  </div>
</template>

<style scoped>
.main-page {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 2rem;
  
  width: 100vw;
  height: 100vh;
  box-sizing: border-box;
  padding-bottom: 8rem;

  overflow: hidden;
}
</style>