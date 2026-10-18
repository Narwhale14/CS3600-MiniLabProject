<script setup lang="ts">
import { ref } from 'vue'

const link = ref('')
const shortlink = ref('')

async function shorten() {
  if(!link.value) return;
}

async function copy() {
  try {
    await navigator.clipboard.writeText(shortlink.value)
  } catch(err) {
    console.error('Failed to copy: ', err)
  }
}
</script>

<template>
  <h1 class="title-text">shortbeep</h1>
  <div class="controls-wrapper">
    <!-- input and shorten button -->
    <div class="input-wrapper">
      <input class="link-input" v-model="link" placeholder="Enter your link"/>
      <button v-if="link" class="clear-button pi pi-times" @click="link = ''"></button>
      <button class="button pi pi-arrow-right" @click="shorten"></button>
    </div>

    <!-- output and copy button -->
    <div class="input-wrapper">
      <input class="link-input" :value="shortlink" placeholder="Your shortened link" readonly/>
      <button v-if="shortlink" class="clear-button pi pi-times" @click="shortlink = ''"></button>
      <button class="button pi pi-clone" @click="copy"></button>
    </div>
  </div>
</template>

<style scoped>
.title-text {
  font-size: 5rem;
  color: cornflowerblue;
  user-select: none;
}

.link-input {
  border: 0px;
  border-radius: 0.4rem 0 0 0.4rem;
  width: 30rem;
  height: 2.5rem;
  padding-left: 0.5rem;
  padding-right: 2.5rem;
  font-size: 1rem;
  color: gainsboro;
}

.clear-button {
  position: absolute;
  right: 3rem;
  top: 50%;
  transform: translateY(-50%);

  background: none;
  border: none;

  color: gray;
}

.clear-button:hover {
  color: gainsboro
}

.button {
  width: 2.5rem;
  border: 0;
  border-radius: 0 0.4rem 0.4rem 0;
  color: gainsboro
}

.button:hover {
  color: whitesmoke
}

.input-wrapper {
  position: relative;
  display: flex;
  outline: 2px solid cornflowerblue;
  border-radius: 0.4rem;
}

.controls-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
</style>