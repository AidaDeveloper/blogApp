<template>
  <div class="max-w-7xl mx-auto  mt-50 px-4 flex items-center justify-center ">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
      <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Вход</h2>
      <form @submit.prevent="submitLogin" class="space-y-4">
        <div>
          <label for="login" class="block text-sm font-medium text-gray-700 mb-1">Логин</label>
          <input
            id="login"
            v-model="login"
            type="text"
            placeholder="Введите логин"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none"
          />
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="Введите пароль"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none"
          />
        </div>

        <button
          type="submit"
          class="w-full py-3 rounded-xl text-white font-semibold text-lg bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 shadow-lg transition-all duration-300 cursor-pointer"
        >
          Войти
        </button>
      </form>

      <p v-if="userStore.message" class="mt-4 text-center text-red-500">{{ sanitizedMessage }}</p>

      <p class="mt-6 text-center text-gray-600 text-sm">
        Нет аккаунта?
        <router-link to="/signup" class="text-blue-600 hover:underline cursor-pointer">Зарегистрироваться</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useUserStore } from '../stores/user.js'

const login = ref('')
const password = ref('')
const userStore = useUserStore()

const sanitizedMessage = computed(() => {
  if (!userStore.message) return ''
  return userStore.message.replace(/[<>]/g, '')
})

function sanitizeInput(str) {
  return str.trim().replace(/[<>]/g, '')
}

async function submitLogin() {
  const payload = {
    login: sanitizeInput(login.value),
    password: sanitizeInput(password.value)
  }

  const data = await userStore.loginUser(payload)
  if (data.status === 'success') {
    window.location.href = '/posts'
  } else {
    userStore.message = data.message
  }
}
</script>
