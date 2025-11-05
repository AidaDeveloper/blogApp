<template>
  <div class="max-w-7xl mx-auto mt-12 px-4">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-4xl font-extrabold text-gray-800">Блог</h1>
      <button
        v-if="userStore.isLoggedIn"
        @click="openModal = true"
        class="bg-[#229ED9] text-white font-medium py-2 px-4 rounded hover:bg-[#1B85BF] transition-colors text-sm md:text-base cursor-pointer"
      >
        Добавить пост
      </button>
    </div>

    <div v-if="postsStore.message" class="text-red-500 mb-6 text-center">{{ postsStore.message }}</div>
    <div v-if="postsStore.allPosts.length === 0" class="text-gray-500 text-center text-lg mt-12">
      Пока постов нет
    </div>

    <PostsForm v-model="openModal" :post="postToEdit" @post-created="handlePostCreated" />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="post in postsStore.allPosts"
        :key="post.id"
        class="bg-white rounded-2xl shadow-md hover:shadow-lg transition-shadow p-6"
      >
        <router-link :to="`/post/${post.id}`" class="block mb-4">
          <h2 class="text-2xl font-bold text-gray-800 hover:text-[#229ED9] transition-colors mb-2 cursor-pointer">
            {{ post.title }}
          </h2>
          <p class="text-gray-700 text-base md:text-lg">
            {{ post.content.length > 300 ? post.content.substring(0, 300) + '...' : post.content }}
          </p>
          <p class="text-gray-400 text-sm mt-2">Автор: {{ post.author }}</p>
        </router-link>

        <div class="flex items-center justify-between text-gray-500 text-sm mt-4">
            <span class="flex items-center space-x-1">
              <Eye class="w-4 h-4" />
              <span>{{ post.views }}</span>
          </span>

          <div class="flex items-center space-x-2">
            <button
              @click="likePost(post.id)"
              :class="{'bg-green-100 text-green-700': post.user_vote === 1}"
              class="flex items-center px-2 py-1 rounded-md text-green-500 font-bold hover:bg-green-200 hover:text-green-700 transition-colors cursor-pointer"
            >
              <ThumbsUp class="w-4 h-4 mr-1" />
              <span class="ml-1">{{ post.likes }}</span>
            </button>

            <button
              @click="dislikePost(post.id)"
              :class="{'bg-red-100 text-red-700': post.user_vote === -1}"
              class="flex items-center px-2 py-1 rounded-md text-red-500 font-bold hover:bg-red-200 hover:text-red-700 transition-colors cursor-pointer"
            >
              <ThumbsDown class="w-4 h-4 mr-1" />
              <span class="ml-1">{{ post.dislikes }}</span>
            </button>

            <button
              v-if="userStore.isLoggedIn && userStore.user_id == post.user_id"
              @click="editPost(post)"
              class="text-blue-500 hover:text-[#229ED9] transition-colors cursor-pointer"
              title="Редактировать"
            >
              <Edit class="w-4 h-4" />
            </button>
            <button
              v-if="userStore.isLoggedIn && userStore.user_id == post.user_id"
              @click="confirmDelete(post)"
              class="text-red-500 hover:text-red-600 transition-colors cursor-pointer"
              title="Удалить"
            >
              <Trash2 class="w-4 h-4" />
            </button>

            <button @click="openComments(post.id)" class="ml-4 text-gray-500 hover:text-blue-600 transition-colors flex items-center cursor-pointer" title="Комментарии" >
              <span class="mr-1"><MessageCircle class="w-4 h-4 mr-1" /></span> {{ commentsStore.commentCount(post.id) }} </button>
          </div>
        </div>

        <div v-if="activePostId === post.id" class="mt-4 bg-gray-50 rounded-xl p-4">
          <div class="max-h-32 overflow-y-auto">
            <div
              v-for="comment in commentsStore.commentsByPost[post.id] || []"
              :key="comment.id"
              class="border-b border-gray-200 py-2"
            >
              <p class="text-gray-700">{{ comment.text }}</p>
              <small class="text-gray-400 text-xs">
                {{ userStore.getName(comment.user_id) }} · {{ formatDate(comment.created_at) }}
              </small>
            </div>
          </div>

          <div v-if="userStore.isLoggedIn" class="flex items-center mt-3 space-x-2">
          <textarea
            v-model="newComment"
            placeholder="Комментарий..."
            class="flex-1 border rounded-xl p-2 text-sm resize-none"
          ></textarea>
            <button
              @click="addComment(post.id)"
              class="bg-[#229ED9] hover:bg-[#1B85BF] text-white font-medium py-2 px-4 rounded transition-colors cursor-pointer"
            >
             Добавить
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showLoginModal"  class="fixed inset-0 flex items-center justify-center z-50" style="background-color: rgba(0,0,0,0.9);">
      <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-center relative">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Вход необходим</h3>
        <p class="text-gray-600 mb-6">Пожалуйста, авторизуйтесь, чтобы поставить лайк или дизлайк.</p>
        <button
          @click="redirectToLogin" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-xl mr-2 transition-colors cursor-pointer" >
          Войти </button>
        <button @click="showLoginModal = false" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-xl transition-colors cursor-pointer" >
          Отмена
        </button>
      </div>
    </div>
    <div v-if="showDeleteModal"
         class="fixed inset-0 flex items-center justify-center z-50" style="background-color: rgba(0,0,0,0.9);"
    >
      <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-center relative">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Удаление поста</h3>
        <p class="text-gray-600 mb-6">Вы действительно хотите удалить этот пост?</p>
        <button @click="deleteConfirmed" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-xl mr-2 transition-colors cursor-pointer" > Да, удалить
        </button>
        <button @click="cancelDelete" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-xl transition-colors cursor-pointer" > Отмена
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { Eye, ThumbsUp, ThumbsDown, Edit, Trash2, MessageCircle } from 'lucide-vue-next'

import { usePostsStore } from '../../stores/posts.js'
import { useUserStore } from '../../stores/user.js'
import { useCommentsStore } from '../../stores/comments.js'


import PostsForm from '../PostForm.vue'

const postsStore = usePostsStore()
const userStore = useUserStore()
const commentsStore = useCommentsStore()
const openModal = ref(false)
const showLoginModal = ref(false)
const showDeleteModal = ref(false)
const postToDelete = ref(null)
const postToEdit = ref(null)
const activePostId = ref(null)
const newComment = ref('')



onMounted(async () => {
  await userStore.fetchAll()
  await postsStore.fetchAll()
  postsStore.allPosts.forEach(post => {
    commentsStore.fetchByPost(post.id)
  })})
function confirmDelete(post) {
  console.log(3)
  postToDelete.value = post
  showDeleteModal.value = true
}

async function deleteConfirmed() {
  if (!postToDelete.value) return
  await postsStore.deletePost(postToDelete.value.id)
  showDeleteModal.value = false
  postToDelete.value = null
}

function cancelDelete() {
  showDeleteModal.value = false
  postToDelete.value = null
}

function editPost(post) {
  postToEdit.value = { ...post }
  openModal.value = true
}
async function likePost(postId) {
  if (!userStore.isLoggedIn) {
    showLoginModal.value = true
    return
  }
  await postsStore.like(postId)
}

async function dislikePost(postId) {
  if (!userStore.isLoggedIn) {
    showLoginModal.value = true
    return
  }
  await postsStore.dislike(postId)
}
function openComments(postId) {
  if (activePostId.value === postId) {
    activePostId.value = null
  } else {
    activePostId.value = postId
    commentsStore.fetchByPost(postId)
  }
}

async function addComment(postId) {
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



async function handlePostCreated(newPost) {
  openModal.value = false

  if (postToEdit.value) {
    const updated = await postsStore.updatePost(newPost)
    postToEdit.value = null
  } else {
    await postsStore.fetchAll()
  }
}
function redirectToLogin() {
  window.location.href = '/signin'
}
</script>
