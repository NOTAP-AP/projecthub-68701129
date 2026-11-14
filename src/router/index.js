import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/about',
    name: 'about',
    meta: { requiresAuth: true },
    component: () => import(/* webpackChunkName: "about" */ '../views/AboutView.vue')
  },
  {
    path: '/logout',
    name: 'logout',
    meta: { requiresAuth: true },
    component: () => import('../views/LogoutView.vue')
  },
  {
    path: "/login",
    name: "login",
    component: () => import("../views/login.vue"),
    props: { adminOnly: false }
  },
  {
    path: "/admin/login",
    name: "admin-login",
    component: () => import("../views/login.vue"),
    props: { adminOnly: true }
  }
  
]
const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.beforeEach(async (to) => {
  if (!to.meta.requiresAuth) {
    return true
  }

  try {
    const response = await fetch('/projecthub/php_api/session.php', {
      credentials: 'include'
    })

    if (response.ok) {
      return true
    }

    return {
      name: 'login',
      query: response.status === 401 ? {} : { auth_error: 'unavailable' }
    }
  } catch (error) {
    return {
      name: 'login',
      query: { auth_error: 'unavailable' }
    }
  }
})

export default router
