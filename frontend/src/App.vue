<script setup>
import { RouterLink, RouterView } from 'vue-router'
import { onMounted, computed, ref } from 'vue'
import { useUserStore } from './stores/user.js'

const userStore = useUserStore()

onMounted(() => {
  userStore.checkAuth()
})

const isLoggedIn = computed(() => userStore.isLoggedIn)

const handleAuthClick = () => {
  if (isLoggedIn.value) {
    userStore.logout()
  } else {
    window.location.href = '/signin'
  }
}

const isMenuOpen = ref(false)
</script>

<template>
  <header class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto  px-4">
      <div class="flex justify-between h-16 items-center">
        <div class="flex-shrink-0 flex items-center">
          <RouterLink to="/">
          </RouterLink>
        </div>

        <nav class="hidden md:flex space-x-8 items-center">
          <RouterLink
            to="/posts"
            class="text-gray-800 font-bold text-lg uppercase tracking-wide transition-colors hover:text-[#229ED9] cursor-pointer"
            active-class="text-[#229ED9] border-b-2 border-[#229ED9]"
            exact
          >
            Главная
          </RouterLink>
          <RouterLink
            to="/analytics"
            class="text-gray-800 font-bold text-lg uppercase tracking-wide transition-colors hover:text-[#229ED9] cursor-pointer"
            active-class="text-[#229ED9] border-b-2 border-[#229ED9]"
          >
            Аналитика
          </RouterLink>
        </nav>


        <div class="hidden md:flex items-center">
          <button
            @click="handleAuthClick"
            class="ml-4 px-3 py-1 text-sm font-medium text-white rounded hover:bg-[#1B85BF] transition-colors cursor-pointer"
            style="background-color: #229ED9;"
          >
            {{ isLoggedIn ? 'Выйти' : 'Войти' }}
          </button>
        </div>

        <div class="md:hidden flex items-center">
          <button @click="isMenuOpen = !isMenuOpen" class="text-gray-700 focus:outline-none">
            <svg v-if="!isMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div v-show="isMenuOpen" class="md:hidden px-2 pt-2 pb-3 space-y-1 bg-white border-t border-gray-200">
      <RouterLink
        to="/posts"
        class="block px-3 py-2 rounded-md text-gray-800 font-semibold text-lg uppercase tracking-wide hover:bg-indigo-50 hover:text-indigo-600 transition-colors cursor-pointer"
        exact
        @click="isMenuOpen = false"
      >Главная</RouterLink>
      <RouterLink
        to="/analytics"
        class="block px-3 py-2 rounded-md text-gray-800 font-semibold text-lg uppercase tracking-wide hover:bg-indigo-50 hover:text-indigo-600 transition-colors cursor-pointer"
        @click="isMenuOpen = false"
      >Аналитика</RouterLink>
      <button
        @click="handleAuthClick"
        class="w-full text-left px-3 py-2 rounded-md text-white font-bold text-lg bg-gradient-to-r  hover:shadow-lg hover:scale-105 transition transform cu"
        style="background-color: #229ED9;"
      >
        {{ isLoggedIn ? 'Выйти' : 'Войти' }}
      </button>
    </div>

  </header>

  <main class="max-w-7xl mx-auto mt-12 px-4">
    <RouterView />
  </main>
</template>

<style scoped>
[ v-cloak ] { display:none; }
</style>
