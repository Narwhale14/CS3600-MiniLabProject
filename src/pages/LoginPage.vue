<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { loggedIn, api, checkEmail } from '../auth';

const router = useRouter();

const user = ref('');
const password = ref('');
const errors = reactive({ user: '', password: '' });

async function submit() {
  const trimmedUser = user.value.trim();
  errors.user = trimmedUser === '' ? 'Please enter your username or email' : trimmedUser.includes('@') ? checkEmail(trimmedUser) : '';
  errors.password = password.value === '' ? 'Please enter your password' : '';
  if(errors.user || errors.password) return;

  const data = await api('login', { user: trimmedUser, password: password.value });
  errors.password = data.error ?? '';
  if(data.error) return;

  loggedIn.value = true;
  router.push('/');
}
</script>

<template>
  <form class="form" novalidate @submit.prevent="submit">
    <h1 class="title-text">log in</h1>

    <div class="fields">
      <!-- username/email field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.user }" v-model="user" placeholder="Username or Email"/>
        <p v-if="errors.user" class="error">{{ errors.user }}</p>
      </div>

      <!-- password field -->
      <div class="field">
        <input class="form-input" :class="{ invalid: errors.password }" v-model="password" type="password" placeholder="Password"/>
        <p v-if="errors.password" class="error">{{ errors.password }}</p>
      </div>
    </div>

    <button class="submit-button">Log In</button>
    <p class="switch-text">Don't have an account? <RouterLink to="/signup">Sign Up</RouterLink></p>
  </form>
</template>

<style scoped src="./form.css"></style>
