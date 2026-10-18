<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { loggedIn } from '../auth'

const router = useRouter()
const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const user = ref('')
const password = ref('')
const errors = reactive({ user: '', password: '' })

function submit() {
  const trimmedUser = user.value.trim()
  if(trimmedUser === '') {
    errors.user = 'Please enter your username or email'
  } else if(trimmedUser.includes('@') && !emailPattern.test(trimmedUser)) {
    errors.user = 'Please enter a valid email'
  } else {
    errors.user = ''
  }

  if(password.value === '') {
    errors.password = 'Please enter your password'
  } else {
    errors.password = ''
  }

  if(errors.user || errors.password) return

  // frontend only for now, so any valid input counts as a successful login
  loggedIn.value = true
  router.push('/')
}
</script>

<template>
  <form class="form" novalidate @submit.prevent="submit">
    <h1 class="title-text">log in</h1>

    <div class="fields">
      <!-- username/email field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.user }" v-model="user" placeholder="Username or Email" autocomplete="username"/>
        <p v-if="errors.user" class="error">{{ errors.user }}</p>
      </div>

      <!-- password field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.password }" v-model="password" type="password" placeholder="Password" autocomplete="current-password"/>
        <p v-if="errors.password" class="error">{{ errors.password }}</p>
      </div>
    </div>

    <button class="submit-button">Log In</button>
    <p class="switch-text">Don't have an account? <RouterLink to="/signup">Sign Up</RouterLink></p>
  </form>
</template>

<style scoped src="./form.css"></style>
