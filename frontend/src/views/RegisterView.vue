<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-icon">WTE</div>
      <h1 class="auth-title">建立帳號</h1>
      <p class="auth-subtitle">加入 WTE，告別選擇困難</p>

      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label>暱稱</label>
          <input v-model="name" type="text" placeholder="你的名字" required autocomplete="name" />
        </div>
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
            placeholder="至少 8 個字元"
            required
            autocomplete="new-password"
          />
        </div>
        <div class="form-group">
          <label>確認密碼</label>
          <input
            v-model="passwordConfirmation"
            type="password"
            placeholder="再輸入一次密碼"
            required
            autocomplete="new-password"
          />
        </div>

        <p v-if="error" class="error-msg">{{ error }}</p>

        <button type="submit" class="btn-primary btn-block" :disabled="loading">
          {{ loading ? '建立中...' : '建立帳號' }}
        </button>
      </form>

      <p class="auth-link">
        已有帳號？
        <router-link to="/login">立即登入</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const error = ref('')

async function handleRegister() {
  if (password.value !== passwordConfirmation.value) {
    error.value = '兩次輸入的密碼不一致'
    return
  }
  loading.value = true
  error.value = ''
  try {
    await auth.register(name.value, email.value, password.value, passwordConfirmation.value)
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) {
      error.value = Object.values(errors).flat().join('，')
    } else {
      error.value = err.response?.data?.message || '註冊失敗，請稍後再試'
    }
  } finally {
    loading.value = false
  }
}
</script>
