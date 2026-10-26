<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { loggedIn, api, checkUsername, checkEmail, checkPassword, checkConfirmPassword } from '../auth';

const router = useRouter();

const username = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const errors = reactive({ username: '', email: '', password: '', confirmPassword: '' });

async function submit() {
  errors.username = checkUsername(username.value);
  errors.email = checkEmail(email.value);
  errors.password = checkPassword(password.value);
  errors.confirmPassword = checkConfirmPassword(password.value, confirmPassword.value);
  if(Object.values(errors).some(error => error)) return;

  const data = await api('signup', { username: username.value.trim(), email: email.value.trim(), password: password.value });
  errors.username = data.error ?? '';
  if(data.error) return;

  loggedIn.value = true;
  router.push('/');
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
