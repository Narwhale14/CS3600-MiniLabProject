<script setup lang="ts">
import { reactive, ref } from 'vue';
import { checkUsername, checkConfirmPassword, checkPassword } from '../auth';

const savedUsername = ref('');

const editing = ref(false);
const username = ref(savedUsername.value);
const password = ref('');
const confirmPassword = ref('');
const errors = reactive({ username: '', password: '', confirmPassword: '' });

function stop() {
  username.value = savedUsername.value;
  password.value = '';
  confirmPassword.value = '';
  Object.assign(errors, { username: '', password: '', confirmPassword: '' });
  editing.value = false;
}

function save() {
  errors.username = checkUsername(username.value);
  errors.password = password.value ? checkPassword(password.value) : '';
  errors.confirmPassword = password.value ? checkConfirmPassword(password.value, confirmPassword.value) : '';

  if(Object.values(errors).some(error => error)) return;

  // send changes to backend here
  savedUsername.value = username.value.trim();
  stop();
}
</script>

<template>
  <form class="form" novalidate @submit.prevent="save">
    <h1 class="title-text">profile</h1>

    <div class="fields">
      <!-- username field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.username }" v-model="username" :disabled="!editing" placeholder="Username" autocomplete="username"/>
        <p v-if="errors.username" class="error">{{ errors.username }}</p>
      </div>

      <!-- password field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.password }" v-model="password" :disabled="!editing" type="password" :placeholder="editing ? 'New Password' : '••••••••'" autocomplete="new-password"/>
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