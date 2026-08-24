<template>
  <div class="login-card">
    <div class="login-logo">
      <div class="login-logo-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:32px;height:32px;color:#fff">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
      </div>
      <h1>Vakt</h1>
      <p>Security Operations Center</p>
    </div>

    <div v-if="statusMessage" class="alert success">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
      {{ statusMessage }}
    </div>

    <div v-if="errorMessage" class="alert danger">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
      {{ errorMessage }}
    </div>

    <form :action="actionUrl" method="POST" @submit="handleSubmit">
      <input type="hidden" name="_token" :value="csrfToken" />

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input
          id="email"
          type="email"
          name="email"
          v-model="email"
          class="form-control"
          placeholder="operator@vakt.is"
          required
          autocomplete="email"
          autofocus
        />
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="pw-wrap">
          <input
            id="password"
            :type="showPw ? 'text' : 'password'"
            name="password"
            v-model="password"
            class="form-control"
            placeholder="••••••••"
            required
            autocomplete="current-password"
            style="padding-right: 48px; font-family: monospace; font-size: 1.2rem; letter-spacing: 2px;"
          />
          <button type="button" class="pw-toggle" @click="showPw = !showPw">
            <svg v-if="!showPw" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
          </button>
        </div>
      </div>

      <label class="checkbox-wrap">
        <input type="checkbox" name="remember" v-model="remember" />
        <span>Remember me</span>
      </label>

      <button type="submit" class="btn-submit" :disabled="isSubmitting">
        <span v-if="!isSubmitting">Sign In</span>
        <span v-else>
          <span class="spinner"></span> Authenticating...
        </span>
      </button>
    </form>

    <div class="login-footer">
      Authorized Access Only
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  actionUrl: { type: String, required: true },
  csrfToken: { type: String, required: true },
  oldEmail: { type: String, default: '' },
  errorMessage: { type: String, default: '' },
  statusMessage: { type: String, default: '' }
});

const email = ref(props.oldEmail);
const password = ref('');
const remember = ref(false);
const showPw = ref(false);
const isSubmitting = ref(false);

const handleSubmit = () => {
  isSubmitting.value = true;
};
</script>
