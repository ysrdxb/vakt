<template>
  <div style="background: #1e293b; padding: 20px; border-radius: 10px; border: 1px solid #334155; margin-bottom: 20px;">
    <h3 style="color: #4ade80; margin-top: 0;">⚡ Vue 3 Interactive Component</h3>
    <p style="font-size: 1.3rem; color: #f8fafc;">
      Vue Counter: <strong style="color: #facc15; font-size: 1.8rem;">{{ count }}</strong>
    </p>
    <button @click="increment" style="background: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem;">
      ➕ Click to Increment (Vue 3 Reactive @click)
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const count = ref(0);
const increment = () => {
  count.value++;
};
</script>
