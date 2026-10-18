<script setup lang="ts">
const emit = defineEmits(['toggle-sidebar']);
</script>

<template>
  <div class="header">
    <RouterLink to="/" class="home-link">shortlink</RouterLink>
		<button class="profile-button pi pi-user" @click="emit('toggle-sidebar')"></button>
	</div>
</template>

<style scoped>
.header {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;

  background-color: var(--canvas-dark);
  width: 100%;
  height: 4rem;
  padding: 0 1rem;
  box-sizing: border-box;
}

.home-link {
  font-size: 1.75rem;
  color: cornflowerblue;
  text-decoration: none;
  user-select: none;
  font-weight: bold;
}

.profile-button {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  border: none;
  background-color: cornflowerblue;
  color: Canvas;
  cursor: pointer;
	font-size: x-large;
}
</style>