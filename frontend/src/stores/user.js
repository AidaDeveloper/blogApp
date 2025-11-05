import { defineStore } from 'pinia'
import axios from 'axios'

export const useUserStore = defineStore('user', {
  state: () => ({
    user_id: localStorage.getItem('user_id') || null,
    login: localStorage.getItem('login') || null,
    isLoggedIn: !!localStorage.getItem('user_id'),
    usersById: {},
    message: ''
  }),
  actions: {
    async fetchAll() {
      try {
        const { data } = await axios.get('/api/users.php?action=list')
        this.usersById = data.reduce((acc, user) => {
          acc[user.id] = user
          return acc
        }, {})
      } catch (err) {
        console.error('Ошибка загрузки пользователей:', err)
        this.message = 'Не удалось загрузить пользователей'
      }
    },
    getName(userId) {
      return this.usersById[userId]?.login || 'Аноним'
    },
    async register(data) {
      return new Promise((resolve, reject) => {
        axios.post('/api/auth.php?action=register', data, {
          withCredentials: true,
          validateStatus: (status) => status < 500, // 400–499 не выбивают catch
        })
          .then(({ data }) => {
            this.message = data.message || ''
            resolve(data)
          })
          .catch((error) => {
            if (error.response && error.response.data) {
              this.message = error.response.data.message || 'Ошибка запроса'
            } else {
              this.message = 'Ошибка сети'
            }
            reject(error)
          })
      })
    },

    async loginUser(data) {
      return new Promise((resolve, reject) => {
        axios.post('/api/auth.php?action=login', data, { withCredentials: true })
          .then(({ data }) => {
            if(data.status === 'success'){
              this.user_id = data.user_id
              this.login = data.login || data.user_id
              this.isLoggedIn = true
              localStorage.setItem('user_id', data.user_id)
              localStorage.setItem('login', this.login)
              this.message = ''
            } else {
              this.message = data.message
            }
            resolve(data)
          })
          .catch((error) => {
            this.message = 'Неверный логин или пароль'
            reject(error)
          })
      })
    },

    async logout() {
      await axios.post('/api/auth.php?action=logout', {}, { withCredentials: true })
      this.user_id = null
      this.login = null
      this.isLoggedIn = false
      localStorage.removeItem('user_id')
      localStorage.removeItem('login')
    },
    checkAuth() {
      const id = localStorage.getItem('user_id')
      const login = localStorage.getItem('login')

      if (id) {
        this.user_id = id
        this.login = login
        this.isLoggedIn = true
      } else {
        this.user_id = null
        this.login = null
        this.isLoggedIn = false
      }
    }
  }
})
