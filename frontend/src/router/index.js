import { createRouter, createWebHistory } from 'vue-router'
import Register from '../components/Register.vue'
import Login  from '../components/Login.vue'
import Posts  from '../components/pages/Posts.vue'
import Post  from '../components/pages/Post.vue'
import Analytics  from '../components/pages/Analytics.vue'





const routes = [
  { path: '/signup', component: Register },
  { path: '/signin', component: Login },
  { path: '/posts', component: Posts },
  { path: '/post/:id', component: Post },
  { path: '/analytics', component: Analytics },
  { path: '/', redirect: '/posts' },



]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
