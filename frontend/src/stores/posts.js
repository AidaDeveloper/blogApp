import { defineStore } from 'pinia'
import axios from 'axios'

export const usePostsStore = defineStore('posts', {
  state: () => ({
    allPosts: [],
    singlePost: null,
    message: ''
  }),
  actions: {
    async fetchAll() {
      try {
        const { data } = await axios.get('/api/posts.php?action=list')
        if (data.status === 'success') {
          this.allPosts = data.posts
        } else {
          this.message = data.message || 'Не удалось загрузить посты'
        }
      } catch (err) {
        this.message = 'Ошибка сети'
      }
    },
    async fetchOne(postId) {
      try {
        const { data } = await axios.get(`/api/posts.php?action=single&id=${postId}`)
        if (data.status === 'success') {
          this.singlePost = data.post
          return data.post
        }
        return null
      } catch (err) {
        this.message = 'Ошибка сети'
        return null
      }
    },
    async fetchOneNoView(postId) {
      try {
        const { data } = await axios.get(`/api/posts.php?action=single_no_view&id=${postId}`)
        if (data.status === 'success') {
          return data.post
        }
        return null
      } catch (err) {
        this.message = 'Ошибка сети'
        return null
      }
    },
    async create(postData) {
      try {
        const { data } = await axios.post('/api/posts.php?action=create', postData, { withCredentials: true })
        if (data.status === 'success') {
          this.allPosts.unshift(data.post)
          this.message = ''
          return true
        } else {
          this.message = data.message || 'Не удалось создать пост'
          return false
        }
      } catch (err) {
        this.message = 'Ошибка сети'
        return false
      }
    },
    async updatePost(postData) {
      try {
        const { data } = await axios.post('/api/posts.php?action=update', postData, { withCredentials: true })
        if (data.status === 'success') {
          const index = this.allPosts.findIndex(p => p.id === postData.id)
          if (index !== -1) {
            this.allPosts[index] = data.post
          }
          return true
        } else {
          this.message = data.message || 'Не удалось обновить пост'
          return false
        }
      } catch (err) {
        this.message = 'Ошибка сети'
        return false
      }
    },

    async deletePost(postId) {
      try {
        const { data } = await axios.post('/api/posts.php?action=delete', { id: postId }, { withCredentials: true })
        if (data.status === 'success') {
          this.allPosts = this.allPosts.filter(p => p.id !== postId)
        } else {
          this.message = data.message || 'Не удалось удалить пост'
        }
        return data
      } catch (err) {
        this.message = 'Ошибка сети'
        return { status: 'error', message: this.message }
      }
    },

    async like(postId) {
      try {
        const { data } = await axios.post('/api/posts.php?action=like', { id: postId }, { withCredentials: true })
        if (data.status === 'success') {
          const { data: updatedPostData } = await axios.get(`/api/posts.php?action=single_no_view&id=${postId}`)
          if (updatedPostData.status === 'success') {
            const index = this.allPosts.findIndex(p => p.id === postId)
            if (index !== -1) {
              this.allPosts[index].likes = data.votes.likes
              this.allPosts[index].dislikes = data.votes.dislikes
              this.allPosts[index].user_vote = 1
            }
          }
        } else {
          this.message = data.message || 'Не удалось поставить лайк'
        }
      } catch (err) { this.message = 'Ошибка сети' }
    },

    async dislike(postId) {
      try {
        const { data } = await axios.post(
          '/api/posts.php?action=dislike',
          { id: postId },
          { withCredentials: true }
        )
        if (data.status === 'success') {
          const index = this.allPosts.findIndex(p => p.id === postId)
          if (index !== -1) {
            this.allPosts[index].likes = data.votes.likes
            this.allPosts[index].dislikes = data.votes.dislikes
            this.allPosts[index].user_vote = -1
          }
        } else {
          this.message = data.message || 'Не удалось поставить дизлайк'
        }
      } catch (err) {
        this.message = 'Ошибка сети'
      }
    },
    async incrementView(postId) {
      try {
        await axios.post(
          '/api/posts.php?action=increment_view',
          { id: postId },
          { withCredentials: true }
        )
        if (this.singlePost && this.singlePost.id === postId) {
          this.singlePost.views = (this.singlePost.views || 0) + 1
        }
        const index = this.allPosts.findIndex(p => p.id === postId)
        if (index !== -1) {
          this.allPosts[index].views = (this.allPosts[index].views || 0) + 1
        }
      } catch (err) {
        console.error('Ошибка увеличения просмотров', err)
      }
    }
  }
})
