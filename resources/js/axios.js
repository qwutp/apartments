// axios.js - УЛУЧШЕННАЯ ВЕРСИЯ
import axios from 'axios'

const instance = axios.create({
  baseURL: '/',
  withCredentials: true,
  timeout: 30000, // 30 секунд таймаут
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json'
  }
})

// Функция для гарантированного получения CSRF токена
async function ensureCSRFToken() {
  // Сначала проверяем meta тег
  let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (token) {
    return token
  }
  
  // Если нет в meta, получаем через cookie
  try {
    // Используем обычный fetch чтобы избежать рекурсии
    const response = await fetch('/sanctum/csrf-cookie', {
      method: 'GET',
      credentials: 'include',
      headers: {
        'Accept': 'application/json'
      }
    })
    
    // Пробуем получить из cookie
    const cookies = document.cookie.split(';')
    for (let cookie of cookies) {
      const [name, value] = cookie.trim().split('=')
      if (name === 'XSRF-TOKEN') {
        token = decodeURIComponent(value)
        return token
      }
    }
    
    // Пробуем получить из meta тега после запроса
    token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    return token
  } catch (error) {
    console.error('Failed to get CSRF token:', error)
    return null
  }
}

instance.interceptors.request.use(async (config) => {
  // Для всех модифицирующих запросов добавляем CSRF токен
  if (['post', 'put', 'delete', 'patch'].includes(config.method?.toLowerCase())) {
    // Если токен уже установлен в заголовках, не перезаписываем
    if (!config.headers['X-CSRF-TOKEN']) {
      const token = await ensureCSRFToken()
      if (token) {
        config.headers['X-CSRF-TOKEN'] = token
      }
    }
  }
  return config
}, (error) => {
  return Promise.reject(error)
})

instance.interceptors.response.use(
  (response) => response,
  async (error) => {
    // 401 - не авторизован, перенаправляем на логин
    if (error.response?.status === 401) {
      console.log('🔐 Unauthorized (401), redirecting to login')
      localStorage.removeItem('authUser')
      window.dispatchEvent(new CustomEvent('authStateChanged', { detail: { user: null } }))
      // НЕ перенаправляем автоматически, пусть компонент сам решает
      // window.location.href = '/login'
    }
    // 403 - доступ запрещен (может быть админ middleware)
    if (error.response?.status === 403) {
      console.log('🚫 Forbidden (403)')
      // Не перенаправляем автоматически, просто возвращаем ошибку
    }
    // 419 - CSRF токен истек
    if (error.response?.status === 419) {
      console.log('🔄 CSRF token expired, getting new token...')
      try {
        await axios.get('/sanctum/csrf-cookie', { baseURL: '/' })
        // Повторяем запрос с новым токеном
        const config = error.config
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        if (token && config) {
          config.headers['X-CSRF-TOKEN'] = token
          return instance.request(config)
        }
      } catch (e) {
        console.error('Failed to refresh CSRF token:', e)
      }
    }
    return Promise.reject(error)
  }
)

export default instance