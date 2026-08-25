<template>
  <div class="login-wrapper">
    <div class="bg-animated">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
      <div class="blob blob-3"></div>
    </div>

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

      <div v-if="status" class="alert success">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        {{ status }}
      </div>

      <div v-if="Object.keys(form.errors).length > 0" class="alert danger">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        {{ Object.values(form.errors)[0] }}
      </div>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label class="form-label" for="email">Email Address</label>
          <input
            id="email"
            type="email"
            v-model="form.email"
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
              v-model="form.password"
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
          <input type="checkbox" v-model="form.remember" />
          <span>Remember me</span>
        </label>

        <button type="submit" class="btn-submit" :disabled="form.processing">
          <span v-if="!form.processing">Sign In</span>
          <span v-else>
            <span class="spinner"></span> Authenticating...
          </span>
        </button>
      </form>

      <div class="login-footer">
        Authorized Access Only
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

defineOptions({ layout: null }); // Do not use global Layout.vue

const props = defineProps({
  status: {
    type: String,
  },
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const showPw = ref(false);

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<style scoped>
.login-wrapper {
  background-color: #0b0c10;
  color: #f8f9fa;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9999;
}

.bg-animated {
  position: absolute;
  inset: 0;
  z-index: 0;
  overflow: hidden;
}
.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.5;
  animation: float 20s infinite alternate ease-in-out;
}
.blob-1 {
  top: -10%; left: -10%;
  width: 500px; height: 500px;
  background: rgba(0, 242, 254, 0.15);
  animation-delay: 0s;
}
.blob-2 {
  bottom: -20%; right: -10%;
  width: 600px; height: 600px;
  background: rgba(79, 172, 254, 0.15);
  animation-delay: -5s;
}
.blob-3 {
  top: 40%; left: 60%;
  width: 400px; height: 400px;
  background: rgba(106, 17, 203, 0.15);
  animation-delay: -10s;
}

@keyframes float {
  0% { transform: translate(0, 0) scale(1); }
  100% { transform: translate(30px, -50px) scale(1.1); }
}

.login-card {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 440px;
  background: rgba(22, 24, 31, 0.65);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 48px 40px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05) inset;
  transform: translateY(20px);
  opacity: 0;
  animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes slideUp {
  to { transform: translateY(0); opacity: 1; }
}

.login-logo {
  text-align: center;
  margin-bottom: 40px;
}

.login-logo-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  background: linear-gradient(135deg, #00f2fe, #4facfe);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  box-shadow: 0 10px 25px rgba(0, 242, 254, 0.4);
  position: relative;
  overflow: hidden;
}

.login-logo-icon::after {
  content: '';
  position: absolute;
  top: 0; left: -100%; width: 50%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
  transform: skewX(-20deg);
  animation: shine 3s infinite;
}

@keyframes shine {
  0% { left: -100%; }
  20% { left: 200%; }
  100% { left: 200%; }
}

.login-logo h1 {
  font-size: 2.2rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  margin-bottom: 4px;
  background: linear-gradient(to right, #fff, #a5b4fc);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.login-logo p {
  font-family: 'Space Mono', monospace;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: #00f2fe;
  opacity: 0.9;
}

.form-group {
  margin-bottom: 24px;
  position: relative;
}

.form-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 500;
  color: #94a3b8;
  margin-bottom: 8px;
  transition: color 0.3s;
}

.form-control {
  width: 100%;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 14px 16px;
  color: #f8f9fa;
  font-size: 1rem;
  transition: all 0.3s ease;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.form-control:focus {
  outline: none;
  border-color: #00f2fe;
  background: rgba(0, 0, 0, 0.4);
  box-shadow: 0 0 0 4px rgba(0, 242, 254, 0.1), inset 0 2px 4px rgba(0,0,0,0.1);
}

.form-group:focus-within .form-label {
  color: #00f2fe;
}

.pw-wrap {
  position: relative;
}

.pw-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  display: flex;
  transition: color 0.2s;
}

.pw-toggle:hover {
  color: #00f2fe;
}

.checkbox-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  margin-bottom: 32px;
}

.checkbox-wrap input {
  appearance: none;
  width: 18px;
  height: 18px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.2);
  position: relative;
  cursor: pointer;
  transition: all 0.2s;
}

.checkbox-wrap input:checked {
  background: #00f2fe;
  border-color: #00f2fe;
}

.checkbox-wrap input:checked::after {
  content: '';
  position: absolute;
  left: 5px;
  top: 2px;
  width: 4px;
  height: 8px;
  border: solid #000;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.checkbox-wrap span {
  font-size: 0.85rem;
  color: #94a3b8;
  user-select: none;
  transition: color 0.2s;
}

.checkbox-wrap:hover span {
  color: #f8f9fa;
}

.btn-submit {
  width: 100%;
  background: linear-gradient(135deg, #00f2fe, #4facfe);
  color: #000;
  border: none;
  border-radius: 12px;
  padding: 16px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 242, 254, 0.3);
}

.btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 242, 254, 0.4);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 2px solid rgba(0,0,0,0.2);
  border-radius: 50%;
  border-top-color: #000;
  animation: spin 0.8s linear infinite;
  margin-right: 8px;
  vertical-align: middle;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.login-footer {
  margin-top: 32px;
  text-align: center;
  font-family: 'Space Mono', monospace;
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.3);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.login-footer::before, .login-footer::after {
  content: '';
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  flex: 1;
}

.alert {
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 24px;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

.alert.danger {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #ff4757;
}

.alert.success {
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.2);
  color: #2ed573;
}
</style>
