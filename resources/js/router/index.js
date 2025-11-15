import { createRouter, createWebHistory } from 'vue-router'

// Основные страницы
import Home from '../components/pages/Home.vue'
import Login from '../components/pages/Login.vue'
import Register from '../components/pages/Register.vue'
import ApartmentDetail from '../components/pages/ApartmentDetail.vue'
import Checkout from '../components/pages/Checkout.vue'

// Layouts
import UserLayout from '../components/layouts/UserLayout.vue'
import AdminLayout from '../components/layouts/AdminLayout.vue'

// Админские страницы
import AdminApartments from '../components/pages/admin/AdminApartments.vue'
import AdminCalendar from '../components/pages/admin/AdminCalendar.vue'
import AdminNotifications from '../components/pages/admin/AdminNotifications.vue'
import AdminRenters from '../components/pages/admin/AdminRenters.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home
  },
  {
    path: '/login',
    name: 'login',
    component: Login
  },
  {
    path: '/register',
    name: 'register',
    component: Register
  },
  {
    path: '/apartment/:id',
    name: 'apartment-detail',
    component: ApartmentDetail
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: Checkout
  },
  {
    path: '/user',
    name: 'user',
    component: UserLayout
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminLayout,
    redirect: '/admin/apartments',
    children: [
      {
        path: 'apartments',
        name: 'admin-apartments',
        component: AdminApartments
      },
      {
        path: 'apartment/create',
        name: 'admin-apartment-create',
        component: () => import('../components/pages/admin/AdminApartmentForm.vue'),
        meta: { requiresAdmin: true }
      },
      {
        path: 'apartment/edit/:id',
        name: 'admin-apartment-edit',
        component: () => import('../components/pages/admin/AdminApartmentForm.vue'),
        meta: { requiresAdmin: true }
      },
      {
        path: 'calendar',
        name: 'admin-calendar',
        component: AdminCalendar
      },
      {
        path: 'notifications',
        name: 'admin-notifications',
        component: AdminNotifications
      },
      {
        path: 'renters',
        name: 'admin-renters',
        component: AdminRenters
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard для проверки прав администратора
router.beforeEach(async (to, from, next) => {
  // Проверяем, требует ли маршрут прав администратора
  if (to.matched.some(record => record.meta.requiresAdmin)) {
    try {
      const response = await fetch('/api/check-auth', {
        credentials: 'include'
      })
      const data = await response.json()
      
      if (data.user && data.user.role === 'admin') {
        next()
      } else {
        alert('Доступ запрещен. Только для администраторов.')
        next('/')
      }
    } catch (error) {
      console.error('Auth check error:', error)
      next('/login')
    }
  } else {
    next()
  }
})

export default router