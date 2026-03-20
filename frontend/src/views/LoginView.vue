<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-icon">WTE</div>
      <h1 class="auth-title">What To Eat !!</h1>
      <p class="auth-subtitle">登入帳號開始使用</p>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>電子郵件</label>
          <input
            v-model="email"
            type="email"
            placeholder="your@email.com"
            required
            autocomplete="email"
          />
        </div>
        <div class="form-group">
          <label>密碼</label>
          <input
            v-model="password"
            type="password"
            placeholder="••••••••"
            required
            autocomplete="current-password"
          />
        </div>

        <p v-if="error" class="error-msg">{{ error }}</p>

        <button type="submit" class="btn-primary btn-block" :disabled="loading">
          {{ loading ? '登入中...' : '登入' }}
        </button>
      </form>

      <p class="auth-link">
        還沒有帳號？
        <router-link to="/register">立即註冊</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    await auth.login(email.value, password.value)
  } catch (err) {
    error.value = err.response?.data?.message || '登入失敗，請稍後再試'
  } finally {
    loading.value = false
  }
}
</script>
