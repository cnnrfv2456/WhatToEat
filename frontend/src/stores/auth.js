import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))

  const isLoggedIn = computed(() => !!token.value)

  async function login(email, password) {
    const { data } = await api.post('/auth/login', { email, password })
    token.value = data.token
    user.value = data.user
    localStorage.setItem('token', data.token)
    router.push('/')
  }

  async function register(name, email, password, password_confirmation) {
    const { data } = await api.post('/auth/register', {
      name,
      email,
      password,
      password_confirmation,
    })
    token.value = data.token
    user.value = data.user
    localStorage.setItem('token', data.token)
    router.push('/')
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
      router.push('/login')
    }
  }

  async function fetchUser() {
    if (token.value && !user.value) {
      const { data } = await api.get('/user')
      user.value = data
    }
  }

  return { user, token, isLoggedIn, login, register, logout, fetchUser }
})
