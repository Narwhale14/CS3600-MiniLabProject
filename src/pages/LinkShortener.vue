<script setup lang="ts">
import { ref } from 'vue';
import { api } from '../auth';

const link = ref('');
const code = ref('');
const shortlink = ref('');
const isPrivate = ref(false);
const error = ref('');

async function shorten() {
  if(!link.value) return;

  const data = await api('createLink', { link: link.value, code: code.value, private: isPrivate.value });
  shortlink.value = data.shortlink ?? '';
  error.value = data.error ?? '';
}

async function copy() {
  try {
    await navigator.clipboard.writeText(shortlink.value);
  } catch(err) {
    console.error('Failed to copy:', err);
  }
}

function reset() {
  link.value = '';
  code.value = '';
  shortlink.value = '';
  isPrivate.value = false;
  error.value = '';
}
</script>

<template>
  <h1 class="title-text">shortlink</h1>

  <div class="controls-wrapper">
    <!-- link to shorten -->
    <div class="input-wrapper">
      <input class="link-input" v-model="link" placeholder="Enter your link"/>
      <button v-if="link" class="clear-button pi pi-times" @click="link = ''"></button>
    </div>

    <div class="side-grid">
      <!-- optional custom code and private checkbox -->
      <div class="input-wrapper">
        <input class="link-input" style="flex: 1; width: 0" v-model="code" maxlength="24" placeholder="Custom Link Code (Optional)"/>
        <button v-if="code" class="clear-button pi pi-times" @click="code = ''"></button>
      </div>
      <label class="side-item">
        <input type="checkbox" v-model="isPrivate" />
        Private
      </label>

      <!-- shortened link and copy button -->
      <div class="input-wrapper">
        <input class="link-input" style="flex: 1; width: 0" :value="shortlink" placeholder="Your shortened link" readonly/>
      </div>
      <button class="side-item copy-button" @click="copy"><span class="pi pi-copy"></span>Copy</button>
    </div>

    <p v-if="error" class="error">{{ error }}</p>

    <!-- shorten and reset buttons -->
    <div class="actions">
      <button class="submit-button" @click="shorten">Shorten</button>
      <button class="submit-button reset-button" @click="reset">Reset</button>
    </div>
  </div>
</template>

<style scoped>
.link-input {
  border: 0px;
  border-radius: 0.4rem;
  width: 30rem;
  height: 2.5rem;
  padding-left: 0.5rem;
  padding-right: 2.5rem;
  font-size: 1rem;
  color: gainsboro;
}

.clear-button {
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: gray;
  font-size: 1rem;
}

.clear-button:hover {
  color: gainsboro;
}

.input-wrapper {
  position: relative;
  display: flex;
  outline: 2px solid cornflowerblue;
  border-radius: 0.4rem;
}

.side-grid {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 1rem;
}

.side-item {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 1.15rem;
  color: cornflowerblue;
  user-select: none;
  -webkit-user-select: none;
}

.side-item input {
  width: 1.3rem;
  height: 1.3rem;
  margin: 0;
  accent-color: cornflowerblue;
}

.copy-button {
  padding: 0;
  background: none;
  border: none;
  font-family: inherit;
  cursor: pointer;
}

.copy-button:hover {
  color: color-mix(in oklab, cornflowerblue, white 30%);
}

.copy-button .pi {
  width: 1.3rem;
  text-align: center;
  color: gray;
}

.controls-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.actions {
  display: flex;
  gap: 1rem;
}

.actions .submit-button {
  flex: 1;
  width: auto;
}

.actions .reset-button {
  background: none;
  border: 2px solid cornflowerblue;
  color: cornflowerblue;
}

.actions .reset-button:hover {
  background-color: cornflowerblue;
  color: whitesmoke;
}
</style>

<style scoped src="./form.css"></style>
