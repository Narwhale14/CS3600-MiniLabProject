<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue';
import { api, checkUsername, checkEmail, checkPassword, checkConfirmPassword } from '../auth';

const savedUsername = ref('');
const savedEmail = ref('');

const editing = ref(false);
const username = ref('');
const email = ref('');
const currentPassword = ref('');
const password = ref('');
const confirmPassword = ref('');
const error = ref('');

const errors = reactive({ username: '', email: '', currentPassword: '', password: '', confirmPassword: '' });

async function getUser() {
  const data = await api('getUser');
  error.value = data.error ?? '';
  if(data.error) return;

  savedUsername.value = username.value = data.username;
  savedEmail.value = email.value = data.email;
}

function stop() {
  username.value = savedUsername.value;
  email.value = savedEmail.value;
  currentPassword.value = '';
  password.value = '';
  confirmPassword.value = '';

  Object.assign(errors, { username: '', email: '', currentPassword: '', password: '', confirmPassword: '' });
  error.value = '';
  editing.value = false;
}

async function save() {
  errors.username = checkUsername(username.value);
  errors.email = checkEmail(email.value);
  errors.password = password.value ? checkPassword(password.value) : '';
  errors.confirmPassword = password.value ? checkConfirmPassword(password.value, confirmPassword.value) : '';
  errors.currentPassword = password.value && !currentPassword.value ? 'Current password is required' : '';
  if(Object.values(errors).some(error => error)) return;

  const data = await api('editUser', { username: username.value.trim(), email: email.value.trim() });
  error.value = data.error ?? '';
  if(data.error) return;

  savedUsername.value = username.value.trim();
  savedEmail.value = email.value.trim();

  if(password.value) {
    const passwordData = await api('changePassword', { currentPassword: currentPassword.value, newPassword: password.value });
    error.value = passwordData.error ?? '';
    if(passwordData.error) return;
  }

  stop();
}

onMounted(getUser);
</script>

<template>
  <form class="form" novalidate @submit.prevent="save">
    <h1 class="title-text">profile</h1>

    <p v-if="error" class="error">{{ error }}</p>

    <div class="fields">
      <!-- username field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.username }" v-model="username" :disabled="!editing" placeholder="Username"/>
        <p v-if="errors.username" class="error">{{ errors.username }}</p>
      </div>

      <!-- email field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.email }" v-model="email" :disabled="!editing" type="email" placeholder="Email"/>
        <p v-if="errors.email" class="error">{{ errors.email }}</p>
      </div>

      <!-- current password field -->
      <div v-if="editing" class="field">
        <input class="form-input" :class="{ invalid: errors.currentPassword }" v-model="currentPassword" type="password" placeholder="Current Password"/>
        <p v-if="errors.currentPassword" class="error">{{ errors.currentPassword }}</p>
      </div>

      <!-- new password field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.password }" v-model="password" :disabled="!editing" type="password" :placeholder="editing ? 'New Password (Optional)' : '********'"/>
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

<style scoped src="./form.css"></style>
