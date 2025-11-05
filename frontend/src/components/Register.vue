<template>
  <div class="max-w-7xl mx-auto  mt-50 px-4 flex items-center justify-center">
    <div class="w-full max-w-lg bg-white p-12 rounded-2xl shadow-xl">
      <h2 class="text-4xl font-extrabold text-center text-gray-800 mb-8">Регистрация</h2>
      <form @submit.prevent="submitRegister" class="space-y-6">
        <div>
          <label for="login" class="block text-lg font-medium text-gray-700 mb-2">Логин</label>
          <input
            id="login"
            v-model="login"
            type="text"
            placeholder="Введите логин"
            class="w-full px-6 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-300 focus:outline-none text-lg transition duration-200"
          />
        </div>
        <div>
          <label for="password" class="block text-lg font-medium text-gray-700 mb-2">Пароль</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="Введите пароль"
            class="w-full px-6 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-300 focus:outline-none text-lg transition duration-200"
          />
        </div>
        <button
          type="submit"
          class="w-full py-3 rounded-xl text-white font-semibold text-lg bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 shadow-lg transition-all duration-300 cursor-pointer"
        >
          Зарегистрироваться
        </button>
      </form>

      <p v-if="userStore.message" class="mt-6 text-center text-red-500 font-medium">{{ userStore.message }}</p>

      <p class="mt-8 text-center text-gray-600 text-base">
        Уже есть аккаунт?
        <router-link to="/signin" class="text-blue-600 hover:underline font-medium cursor-pointer">Войти</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useUserStore } from '../stores/user.js'

const login = ref('')
const password = ref('')
const userStore = useUserStore()

function sanitizeInput(str) {
  return str.replace(/[<>]/g, '')
}

async function submitRegister() {
  const data = {
    login: sanitizeInput(login.value),
    password: sanitizeInput(password.value)
  }

  const res = await userStore.register(data)
  if(res.status === 'success'){
    window.location.href = '/signin'
  }
}
</script>

