<template>
  <div class="max-w-7xl mx-auto mt-12 px-4">
    <h1 class="text-4xl font-extrabold text-gray-800 mb-8">Аналитика профиля</h1>

    <div v-if="analyticsLoading" class="text-gray-500 text-center text-lg animate-pulse">
      Загрузка данных...
    </div>

    <div v-else-if="analyticsError" class="text-red-500 text-center text-lg">
      {{ analyticsError }}
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
      <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center">
        <span class="text-gray-400 text-sm">Постов</span>
        <span class="text-3xl font-bold text-gray-800">{{ analytics.totalPosts }}</span>
      </div>

      <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center">
        <span class="text-gray-400 text-sm">Просмотры</span>
        <span class="text-3xl font-bold text-gray-800">{{ analytics.totalViews }}</span>
      </div>

      <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center">
        <span class="text-gray-400 text-sm">Лайки</span>
        <span class="text-3xl font-bold text-green-600">{{ analytics.totalLikes }}</span>
      </div>

      <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center">
        <span class="text-gray-400 text-sm">Дизлайки</span>
        <span class="text-3xl font-bold text-red-600">{{ analytics.totalDislikes }}</span>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">Подробная статистика по постам</h2>

      <div v-if="analytics.posts.length === 0" class="text-gray-500 italic">
        Постов пока нет 😢
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          v-for="post in analytics.posts"
          :key="post.id"
          class="bg-gray-50 rounded-xl p-4 flex flex-col space-y-2 shadow-sm"
        >
          <h3 class="font-semibold text-gray-800">{{ post.title }}</h3>
          <div class="flex justify-between text-gray-500 text-sm">
            <span>Просмотры: {{ post.views }}</span>
            <span class="flex items-center text-gray-700">
  <span class="flex items-center justify-center w-5 h-5 rounded-full bg-yellow-400 text-white mr-1">
    <ThumbsUp class="w-3 h-3" />
  </span>
  {{ post.likes }}

  <span class="mx-1 text-gray-400">/</span>

  <span class="flex items-center justify-center w-5 h-5 rounded-full bg-yellow-400 text-white mr-1">
    <ThumbsDown class="w-3 h-3" />
  </span>
  {{ post.dislikes }}
</span>
          </div>
          <div class="text-gray-400 text-xs">Дата: {{ formatDate(post.created_at) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Eye, ThumbsUp, ThumbsDown } from 'lucide-vue-next'
import { usePostsStore } from '../../stores/posts.js'
import { useUserStore } from '../../stores/user.js'

const postsStore = usePostsStore()
const userStore = useUserStore()

const analytics = ref({
  totalPosts: 0,
  totalViews: 0,
  totalLikes: 0,
  totalDislikes: 0,
  posts: []
})

const analyticsLoading = ref(true)
const analyticsError = ref('')

function formatDate(datetime) {
  if (!datetime) return ''
  const date = new Date(datetime)
  return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

async function fetchAnalytics() {
  analyticsLoading.value = true
  analyticsError.value = ''
  try {
    console.log(44)
    await postsStore.fetchAll()
    const userPosts = postsStore.allPosts.filter(p => p.user_id == userStore.user_id)

    analytics.value.totalPosts = userPosts.length
    analytics.value.totalViews = userPosts.reduce((sum, p) => sum + p.views, 0)
    analytics.value.totalLikes = userPosts.reduce((sum, p) => sum + p.likes, 0)
    analytics.value.totalDislikes = userPosts.reduce((sum, p) => sum + p.dislikes, 0)
    analytics.value.posts = userPosts
  } catch (e) {
    console.error(e)
    analyticsError.value = 'Ошибка загрузки аналитики'
  } finally {
    analyticsLoading.value = false
  }
}

onMounted(() => {
  fetchAnalytics()
})
</script>
