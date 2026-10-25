<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { checkUsername, checkConfirmPassword, checkPassword, csrfToken } from '../auth'

const savedUsername = ref('')
const savedEmail = ref('')

const editing = ref(false)
const username = ref('')
const email = ref('')
const currentPassword = ref('')
const password = ref('')
const confirmPassword = ref('')
const error = ref('')

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const errors = reactive({
  username: '',
  email: '',
  currentPassword: '',
  password: '',
  confirmPassword: ''
})

async function getUser() {
  try {
    const response = await fetch('/api/shortlink.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken.value
      },
      body: JSON.stringify({
        action: 'getUser'
      })
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.error || 'Failed to load profile'
      return
    }

    savedUsername.value = data.username
    savedEmail.value = data.email
    username.value = data.username
    email.value = data.email
    error.value = ''

  } catch (err) {
    console.error('Failed to load profile:', err)
    error.value = 'Unable to connect to the server'
  }
}

function stop() {
  username.value = savedUsername.value
  email.value = savedEmail.value
  currentPassword.value = ''
  password.value = ''
  confirmPassword.value = ''

  Object.assign(errors, {
    username: '',
    email: '',
    currentPassword: '',
    password: '',
    confirmPassword: ''
  })

  error.value = ''
  editing.value = false
}

async function save() {
  errors.username = checkUsername(username.value)

  const trimmedEmail = email.value.trim()

  if (trimmedEmail === '') {
    errors.email = 'Email is required'
  } else if (!emailPattern.test(trimmedEmail)) {
    errors.email = 'Please enter a valid email'
  } else {
    errors.email = ''
  }

  errors.password = password.value ? checkPassword(password.value) : ''
  errors.confirmPassword = password.value
    ? checkConfirmPassword(password.value, confirmPassword.value)
    : ''

  errors.currentPassword = password.value && !currentPassword.value
    ? 'Current password is required'
    : ''

  if (Object.values(errors).some(error => error)) return

  try {
    const response = await fetch('/api/shortlink.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken.value
      },
      body: JSON.stringify({
        action: 'editUser',
        username: username.value.trim(),
        email: email.value.trim()
      })
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.error || 'Failed to update profile'
      return
    }

    savedUsername.value = username.value.trim()
    savedEmail.value = email.value.trim()

    if (password.value) {
      const passwordResponse = await fetch('/api/shortlink.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken.value
        },
        body: JSON.stringify({
          action: 'changePassword',
          currentPassword: currentPassword.value,
          newPassword: password.value
        })
      })

      const passwordData = await passwordResponse.json()

      if (!passwordResponse.ok) {
        error.value = passwordData.error || 'Failed to change password'
        return
      }
    }

    stop()

  } catch (err) {
    console.error('Failed to update profile:', err)
    error.value = 'Unable to connect to the server'
  }
}

onMounted(getUser)
</script>

<template>
  <form class="form" novalidate @submit.prevent="save">
    <h1 class="title-text">profile</h1>

    <p v-if="error" class="error">{{ error }}</p>

    <div class="fields">
      <!-- username field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.username }" v-model="username" :disabled="!editing" placeholder="Username" autocomplete="username"/>
        <p v-if="errors.username" class="error">{{ errors.username }}</p>
      </div>

      <!-- email field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.email }" v-model="email" :disabled="!editing" type="email" placeholder="Email" autocomplete="email"/>
        <p v-if="errors.email" class="error">{{ errors.email }}</p>
      </div>

      <!-- current password field -->
      <div v-if="editing && password" class="field">
        <input class="form-input" :class="{ invalid: errors.currentPassword }" v-model="currentPassword" type="password" placeholder="Current Password" autocomplete="current-password"/>
        <p v-if="errors.currentPassword" class="error">{{ errors.currentPassword }}</p>
      </div>

      <!-- new password field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.password }" v-model="password" :disabled="!editing" type="password" :placeholder="editing ? 'New Password (Optional)' : '••••••••'" autocomplete="new-password"/>
        <p v-if="errors.password" class="error">{{ errors.password }}</p>
      </div>

      <!-- confirm password field -->
      <div v-if="editing" class="field">
        <input class="form-input" :class="{ invalid: errors.confirmPassword }" v-model="confirmPassword" type="password" placeholder="Confirm Password" autocomplete="new-password"/>
        <p v-if="errors.confirmPassword" class="error">{{ errors.confirmPassword }}</p>
      </div>
    </div>

    <div v-if="editing" class="actions">
      <button type="button" class="submit-button" @click="stop">Cancel</button>
      <button class="submit-button">Save</button>
    </div>
    <button v-else type="button" class="submit-button" @click="editing = true">Edit</button>
  </form>
</template>

<style scoped src="./form.css"></style>

<style scoped>
.actions {
  display: flex;
  gap: 1rem;
  width: 20rem;
  max-width: 100%;
}

.actions .submit-button {
  flex: 1;
}

.form-input:disabled {
  border-color: gray;
}
</style>
