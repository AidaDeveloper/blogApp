import { defineStore } from 'pinia'
import axios from 'axios'

export const useCommentsStore = defineStore('comments', {
  state: () => ({
    commentsByPost: {},
    message: ''
  }),
  actions: {
    async fetchByPost(postId) {
      try {
        const { data } = await axios.get(`/api/comments.php?action=by_post&id=${postId}`)
        if (data.status === 'success') {
          this.commentsByPost = { ...this.commentsByPost, [postId]: data.comments }
        } else this.message = data.message || 'Не удалось загрузить комментарии'
      } catch (e) {
        console.error(e)
        this.message = 'Ошибка сети'
      }
    },
    async create(postId, text) {
      try {
        const response = await axios.post(
          '/api/comments.php?action=create',
          { post_id: postId, text },
          { withCredentials: true }
        )
        const { data } = response
        if (data.status === 'success') {
          if (!this.commentsByPost[postId]) this.commentsByPost[postId] = []
          this.commentsByPost[postId].push(data.comment)
          return true
        } else {
          this.message = data.message || 'Не удалось добавить комментарий'
          return false
        }

      } catch (error) {
        console.error('Ошибка при создании комментария:', error)
        if (error.response) {
          console.error( error.response)
        } else if (error.request) {
        } else {
          console.error(error.message)
        }

        this.message = 'Ошибка сети'
        return false
      }
    },
    commentCount(postId) {
      return this.commentsByPost[postId]?.length || 0
    }
  }
})
