<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { loggedIn, csrfToken, checkUsername, checkPassword, checkConfirmPassword } from '../auth'

const router = useRouter()
const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const username = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const errors = reactive({ username: '', email: '', password: '', confirmPassword: '' })

async function submit() {
  errors.username = checkUsername(username.value)

  const trimmedEmail = email.value.trim()
  if(trimmedEmail === '') {
    errors.email = 'Email is required'
  } else if(!emailPattern.test(trimmedEmail)) {
    errors.email = 'Please enter a valid email'
  } else {
    errors.email = ''
  }

  errors.password = checkPassword(password.value)
  errors.confirmPassword = checkConfirmPassword(password.value, confirmPassword.value)

  if(Object.values(errors).some(error => error)) return

  try {
    const response = await fetch('/api/shortlink.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken.value
      },
      body: JSON.stringify({
        action: 'signup',
        username: username.value.trim(),
        email: email.value.trim(),
        password: password.value
      })
    })

    const data = await response.json()

    if (!response.ok) {
      errors.username = data.error || 'Signup failed'
      return
    }

    loggedIn.value = true
    router.push('/')

  } catch (err) {
    console.error('Signup failed:', err)
    errors.username = 'Unable to connect to the server'
  }
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
