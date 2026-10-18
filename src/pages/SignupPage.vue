<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { loggedIn } from '../auth'

const router = useRouter()
const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const username = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const errors = reactive({ username: '', email: '', password: '', confirmPassword: '' })

function submit() {
  const trimmedUsername = username.value.trim()
  if(trimmedUsername === '') {
    errors.username = 'Username is required'
  } else if(!/^[a-zA-Z0-9_]+$/.test(trimmedUsername)) {
    errors.username = 'Username can only contain letters, numbers and underscores'
  } else {
    errors.username = ''
  }

  const trimmedEmail = email.value.trim()
  if(trimmedEmail === '') {
    errors.email = 'Email is required'
  } else if(!emailPattern.test(trimmedEmail)) {
    errors.email = 'Please enter a valid email'
  } else {
    errors.email = ''
  }

  if(password.value === '') {
    errors.password = 'Password is required'
  } else if(password.value.length < 8) {
    errors.password = 'Password must be at least 8 characters'
  } else {
    errors.password = ''
  }

  if(confirmPassword.value === '') {
    errors.confirmPassword = 'Please confirm your password'
  } else if(confirmPassword.value !== password.value) {
    errors.confirmPassword = 'Passwords do not match'
  } else {
    errors.confirmPassword = ''
  }

  if(Object.values(errors).some(error => error)) return

  // frontend only for now, so any valid input counts as a new account
  loggedIn.value = true
  router.push('/')
}
</script>

<template>
  <form class="form" novalidate @submit.prevent="submit">
    <h1 class="title-text">sign up</h1>

    <div class="fields">
      <!-- username field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.username }" v-model="username" placeholder="Username" autocomplete="username"/>
        <p v-if="errors.username" class="error">{{ errors.username }}</p>
      </div>

      <!-- email field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.email }" v-model="email" type="email" placeholder="Email" autocomplete="email"/>
        <p v-if="errors.email" class="error">{{ errors.email }}</p>
      </div>

      <!-- password field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.password }" v-model="password" type="password" placeholder="Password" autocomplete="new-password"/>
        <p v-if="errors.password" class="error">{{ errors.password }}</p>
      </div>

      <!-- confirm password field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.confirmPassword }" v-model="confirmPassword" type="password" placeholder="Confirm Password" autocomplete="new-password"/>
        <p v-if="errors.confirmPassword" class="error">{{ errors.confirmPassword }}</p>
      </div>
    </div>

    <button class="submit-button">Sign Up</button>
    <p class="switch-text">Already have an account? <RouterLink to="/login">Log In</RouterLink></p>
  </form>
</template>

<style scoped src="./form.css"></style>
