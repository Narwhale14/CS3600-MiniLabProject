<script setup lang="ts">
import { ref } from 'vue'
import { csrfToken } from '../auth'

const link = ref('')
const code = ref('')
const shortlink = ref('')
const isPrivate = ref(false)

async function shorten() {
  if (!link.value) return

  try {
    const response = await fetch('/api/shortlink.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken.value
      },
      body: JSON.stringify({
        action: 'createLink',
        link: link.value,
        code: code.value,
        private: isPrivate.value
      })
    })

    const data = await response.json()

    if (!response.ok) {
      console.error(data.error || 'Failed to shorten link')
      return
    }

    shortlink.value = data.shortlink

  } catch (err) {
    console.error('Failed to shorten link:', err)
  }
}

async function copy() {
  try {
    await navigator.clipboard.writeText(shortlink.value)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}
</script>

<template>
  <h1 class="title-text">shortlink</h1>

  <div class="controls-wrapper">
    <!-- input and shorten button -->
    <div class="input-wrapper">
      <input
        class="link-input"
        v-model="link"
        placeholder="Enter your link"
      />
      <button
        v-if="link"
        class="clear-button pi pi-times"
        @click="link = ''"
      ></button>
      <button
        class="button pi pi-arrow-right"
        @click="shorten"
      ></button>
    </div>

    <!-- optional custom code -->
    <div class="code-row">
      <div class="input-wrapper" style="flex: 1">
        <input
          class="link-input"
          style="flex: 1"
          v-model="code"
          maxlength="24"
          placeholder="Custom Link Code (Optional)"
        />
        <button
          v-if="code"
          class="clear-button pi pi-times"
          style="right: 0.5rem"
          @click="code = ''"
        ></button>
      </div>

      <!-- public/private toggle -->
      <div class="checkbox-slot">
        <button
          type="button"
          class="privacy-button"
          :class="isPrivate ? 'pi pi-lock' : 'pi pi-lock-open'"
          :title="isPrivate ? 'Private — click to make public' : 'Public — click to make private'"
          :aria-label="isPrivate ? 'Make link public' : 'Make link private'"
          @click="isPrivate = !isPrivate"
        ></button>
      </div>
    </div>

    <!-- output and copy button -->
    <div class="input-wrapper">
      <input
        class="link-input"
        :value="shortlink"
        placeholder="Your shortened link"
        readonly
      />
      <button
        v-if="shortlink"
        class="clear-button pi pi-times"
        @click="shortlink = ''"
      ></button>
      <button
        class="button pi pi-clone"
        @click="copy"
      ></button>
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
  font-size: 1rem;
}

.clear-button:hover {
  color: gainsboro;
}

.button {
  width: 2.5rem;
  border: 0;
  border-radius: 0 0.4rem 0.4rem 0;
  color: gainsboro;
  font-size: 1rem;
}

.button:hover {
  color: whitesmoke;
}

.input-wrapper {
  position: relative;
  display: flex;
  outline: 2px solid cornflowerblue;
  border-radius: 0.4rem;
}

.code-row {
  display: flex;
}

.checkbox-slot {
  width: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.privacy-button {
  padding: 0;
  background: none;
  border: none;
  color: cornflowerblue;
  font-size: 1.5rem;
  cursor: pointer;
}

.privacy-button:hover {
  color: whitesmoke;
}

.controls-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
</style>
