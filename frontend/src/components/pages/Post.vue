<template>
  <div class="max-w-7xl mx-auto">
    <div v-if="message" class="flex items-center justify-center h-screen text-red-500 text-lg font-medium">
      {{ message }}
    </div>

    <div v-else-if="!post" class="flex items-center justify-center h-screen text-gray-400 text-lg animate-pulse">
      Загрузка поста...
    </div>

    <div v-else class="max-w-7xl mx-auto">
      <article class="bg-white rounded-3xl shadow-md hover:shadow-lg transition-shadow p-6">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6 tracking-tight">
          {{ post.title }}
        </h1>
        <div class="flex flex-wrap items-center justify-between text-sm text-gray-500 mb-8 border-b border-gray-200 pb-4">
          <span>Автор: {{ post.author || 'Неизвестный автор' }}</span>
          <span>{{ new Date(post.created_at).toLocaleDateString('ru-RU') }}</span>
        </div>
        <div class="prose prose-lg max-w-none leading-relaxed text-gray-800 mb-12" v-html="post.content"></div>
        <div class="flex items-center justify-between text-gray-500 text-sm mt-4 border-t pt-4">
          <span class="flex items-center space-x-1">
              <Eye class="w-4 h-4" />
                 <span>{{ post.views  }}</span>
          </span>

          <div class="flex items-center space-x-2">
            <button
              @click="likePost"
              :class="{'bg-green-100 text-green-700': post.user_vote === 1}"
              class="flex items-center px-3 py-1 rounded-md text-green-500 font-bold hover:bg-green-200 hover:text-green-700 transition-colors cursor-pointer"
            >
              <ThumbsUp class="w-4 h-4 mr-1" /> <span class="ml-1">{{ post.likes }}</span>
            </button>

            <button
              @click="dislikePost"
              :class="{'bg-red-100 text-red-700': post.user_vote === -1}"
              class="flex items-center px-3 py-1 rounded-md text-red-500 font-bold hover:bg-red-200 hover:text-red-700 transition-colors cursor-pointer"
            >
              <ThumbsDown class="w-4 h-4 mr-1" /> <span class="ml-1">{{ post.dislikes }}</span>
            </button>
            <button
              v-if="userStore.isLoggedIn && userStore.user_id == post.user_id"
              @click="editPost"
              class="text-blue-500 hover:text-[#229ED9] transition-colors cursor-pointer"
              title="Редактировать"
            >
              <Edit class="w-4 h-4" />
            </button>
            <button
              v-if="userStore.isLoggedIn && userStore.user_id == post.user_id"
              @click="confirmDelete"
              class="text-red-500 hover:text-red-600 transition-colors cursor-pointer"
              title="Удалить"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>

        <div class="mt-8 bg-gray-50 rounded-xl p-4">
          <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
            <MessageCircle class="w-4 h-4 mr-1" />  Комментарии
            <span class="ml-2 text-gray-400 text-sm">({{ commentsStore.commentCount(postId) }})</span>
          </h3>

          <div v-if="commentsStore.commentsByPost[postId]?.length" class="max-h-60 overflow-y-auto mb-4">
            <div
              v-for="comment in commentsStore.commentsByPost[postId]"
              :key="comment.id"
              class="border-b border-gray-200 py-2"
            >
              <p class="text-gray-700">{{ comment.text }}</p>
              <small class="text-gray-400 text-xs">
                {{ userStore.getName(comment.user_id) }} · {{ formatDate(comment.created_at) }}
              </small>
            </div>
          </div>
          <div v-else class="text-gray-400 italic mb-4">Комментариев пока нет.</div>

          <div v-if="userStore.isLoggedIn" class="flex items-center mt-3 space-x-2">
            <textarea
              v-model="newComment"
              placeholder="Комментарий..."
              class="flex-1 border rounded-xl p-2 text-sm resize-none"
            ></textarea>
            <button
              @click="addComment"
              class="bg-[#229ED9] hover:bg-[#1B85BF] text-white font-medium py-2 px-4 rounded transition-colors cursor-pointer"
            >
              Добавить
            </button>
          </div>
          <div v-else class="text-gray-500 text-sm italic">Авторизуйтесь, чтобы оставить комментарий.</div>
        </div>
      </article>
    </div>
    <PostsForm
      v-model="openModal"
      :post="postToEdit"
      @post-created="handlePostUpdated"
    />

    <div
      v-if="showLoginModal"
      class="fixed inset-0 flex items-center justify-center z-50 bg-black/70"
    >
      <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-center relative">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Вход необходим</h3>
        <p class="text-gray-600 mb-6">Пожалуйста, авторизуйтесь, чтобы поставить лайк или дизлайк.</p>
        <button
          @click="redirectToLogin"
          class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-xl mr-2 transition-colors cursor-pointer"
        >
          Войти
        </button>
        <button
          @click="showLoginModal = false"
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-xl transition-colors cursor-pointer"
        >
          Отмена
        </button>
      </div>
    </div>
    <div v-if="showDeleteModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black/70">
      <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-center relative">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Удаление поста</h3>
        <p class="text-gray-600 mb-6">Вы действительно хотите удалить этот пост?</p>
        <button @click="deleteConfirmed" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-xl mr-2 transition-colors cursor-pointer">
          Да, удалить
        </button>
        <button @click="cancelDelete" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-xl transition-colors cursor-pointer">
          Отмена
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { Eye, ThumbsUp, ThumbsDown, Edit, Trash2, MessageCircle } from 'lucide-vue-next'
import { useRoute } from 'vue-router'
import { usePostsStore } from '../../stores/posts.js'
import { useUserStore } from '../../stores/user.js'
import { useCommentsStore } from '../../stores/comments.js'
import PostsForm from '../PostForm.vue'


const route = useRoute()
const postsStore = usePostsStore()
const userStore = useUserStore()
const commentsStore = useCommentsStore()

const post = ref(null)
const message = ref('')
const postId = Number(route.params.id)
const showLoginModal = ref(false)
const newComment = ref('')
const isInitialLoad = ref(true)
const isLoading = ref(true)
const showDeleteModal = ref(false)
const postToEdit = ref(null)
const openModal = ref(false)
const hasIncrementedView = ref(false)

async function fetchPost() {
  try {
    isLoading.value = true
    const data = await postsStore.fetchOne(postId)
    if (data) {
      post.value = data
      commentsStore.fetchByPost(postId)
    } else message.value = 'Пост не найден'
  } catch {
    message.value = 'Ошибка сети'
  } finally {
    isLoading.value = false
  }
}


async function likePost() {
  if (!userStore.isLoggedIn) {
    showLoginModal.value = true
    return
  }
  await postsStore.like(postId)
  post.value = await postsStore.fetchOneNoView(postId)
}

async function dislikePost() {
  if (isLoading.value) return
  if (!userStore.isLoggedIn) {
    showLoginModal.value = true
    return
  }
  isInitialLoad.value = false
  await postsStore.dislike(postId)
  post.value = await postsStore.fetchOneNoView(postId)
}
async function fetchPostData() {
  try {
    const data = await postsStore.fetchOneNoView(postId)
    if (data) {
      post.value = data
      commentsStore.fetchByPost(postId)
    } else message.value = 'Пост не найден'
  } catch {
    message.value = 'Ошибка сети'
  } finally {
    isLoading.value = false
  }
}

async function handlePostUpdated(updatedPost) {
  const updated = await postsStore.fetchOneNoView(postId)
  if (updated) {
    post.value = updated
  }
  postToEdit.value = null
  openModal.value = false
}
async function addComment() {
  if (!newComment.value.trim()) return
  await commentsStore.create(postId, newComment.value)
  newComment.value = ''
}

function formatDate(datetime) {
  if (!datetime) return ''
  const date = new Date(datetime)
  return date.toLocaleString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function redirectToLogin() {
  window.location.href = '/signin'
}

function editPost() {
  postToEdit.value = { ...post.value }
  openModal.value = true
}

function confirmDelete() {
  showDeleteModal.value = true
}

async function deleteConfirmed() {
  if (!post.value) return
  await postsStore.deletePost(post.value.id)
  showDeleteModal.value = false
  window.location.href = '/posts'
}

function cancelDelete() {
  showDeleteModal.value = false
}
onMounted(async () => {
  await fetchPostData()
  await postsStore.incrementView(postId)
  if (post.value) post.value.views++
})
</script>

<style scoped>
.prose img {
  border-radius: 1rem;
  margin: 1.5rem 0;
  max-width: 100%;
  height: auto;
}
</style>
