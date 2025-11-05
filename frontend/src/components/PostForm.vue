<template>
  <!-- Модалка с формой -->
  <transition name="fade">
    <div
      v-if="showModal"
      class="fixed inset-0 flex items-center justify-center z-50" style="background-color: rgba(0,0,0,0.9);"
    >
      <div class="bg-white w-full max-w-lg p-6 rounded-xl shadow-lg relative">
        <button
          @click="closeModal"
          class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 cursor-pointer"
        >
          ✕
        </button>

        <h2 class="text-2xl font-bold mb-4 text-center">
          {{ modalTitle }}
        </h2>
        <form @submit.prevent="submitPost" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Заголовок</label>
            <input
              v-model="title"
              type="text"
              placeholder="Введите заголовок"
              class="w-full px-4 py-2 border rounded-lg focus:outline-none transition-colors duration-200"
              style="border-color: #D1D5DB;"
              @focus="this.style.borderColor = '#229ED9'"
              @blur="this.style.borderColor = '#D1D5DB'"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Содержание</label>
            <textarea
              v-model="content"
              placeholder="Введите текст поста"
              rows="5"
              class="w-full px-4 py-2 border rounded-lg focus:outline-none transition-colors duration-200"
              style="border-color: #D1D5DB;"
              @focus="this.style.borderColor = '#229ED9'"
              @blur="this.style.borderColor = '#D1D5DB'"
            ></textarea>
          </div>

          <button
            type="submit"
            class="w-full py-3 rounded-xl text-white font-semibold text-lg transition-all duration-300 cursor-pointer"
            style="background-color: #229ED9;"
            @mouseover="this.style.backgroundColor = '#1B84C0'"
            @mouseout="this.style.backgroundColor = '#229ED9'"
          >
            Опубликовать
          </button>
        </form>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed } from 'vue'
import { usePostsStore } from '../stores/posts.js'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  post: { type: Object, default: null }

})
const modalTitle = computed(() => props.post ? 'Редактировать пост' : 'Новый пост')

const emit = defineEmits(['update:modelValue','post-created'])

const postsStore = usePostsStore()
const showModal = ref(props.modelValue)

const title = ref('')
const content = ref('')

watch(() => props.modelValue, val => showModal.value = val)
watch(showModal, val => emit('update:modelValue', val))
watch(
  () => props.post,
  (newPost) => {
    if (newPost) {
      title.value = newPost.title
      content.value = newPost.content
    } else {
      title.value = ''
      content.value = ''
    }
  },
  { immediate: true }
)



function closeModal() {
  showModal.value = false
}

async function submitPost() {
  if (!title.value.trim() || !content.value.trim()) return alert('Заполните все поля')

  let postData = { title: title.value.trim(), content: content.value.trim() }

  if (props.post) {
    postData.id = props.post.id
    const success = await postsStore.updatePost(postData)
    if (success) {
      emit('post-created', postData)
      closeModal()
    } else {
      alert(postsStore.message || 'Ошибка при редактировании поста')
    }
  } else {
    const success = await postsStore.create(postData)
    if (success) {
      emit('post-created', postsStore.allPosts[0])
      closeModal()
      title.value = ''
      content.value = ''
    } else {
      alert(postsStore.message || 'Ошибка при создании поста')
    }
  }
}

</script>


<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
