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
  (response) => {
    // При успешном ответе обновляем CSRF токен из заголовков если есть
    const csrfToken = response.headers['x-csrf-token']
    if (csrfToken) {
      const metaToken = document.querySelector('meta[name="csrf-token"]')
      if (metaToken) {
        metaToken.setAttribute('content', csrfToken)
      }
    }
    return response
  },
  async (error) => {
    const originalRequest = error.config
    
    // Пропускаем ошибки для проверки авторизации
    if (originalRequest?.url?.includes('/api/check-auth')) {
      return Promise.reject(error)
    }
    
    // 401 - не авторизован
    if (error.response?.status === 401) {
      console.log('🔐 Unauthorized (401) - Session expired')
      
      // НЕ очищаем состояние автоматически - это может быть временная проблема
      // Только для определенных запросов (не для /api/check-auth)
      const isAuthCheck = originalRequest?.url?.includes('/api/check-auth')
      const isLogin = originalRequest?.url?.includes('/login')
      
      // Если это не проверка авторизации и не логин, пробуем повторить
      if (!isAuthCheck && !isLogin && originalRequest && !originalRequest._isRetry) {
        // Даем шанс обновить токен
        try {
          await fetch('/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'include'
          })
          // Повторяем запрос один раз
          if (!originalRequest._retry) {
            originalRequest._retry = true
            originalRequest._isRetry = true
            return instance.request(originalRequest)
          }
        } catch (e) {
          // Если не получилось, НЕ очищаем состояние - пусть пользователь сам решит
          console.warn('Failed to refresh token, but not clearing auth state')
        }
      }
      
      // НЕ отправляем событие authStateChanged автоматически
      // Это должно делаться только явно при выходе или проверке авторизации
    }
    // 403 - доступ запрещен
    if (error.response?.status === 403) {
      console.log('🚫 Forbidden (403)')
      // Не перенаправляем автоматически, просто возвращаем ошибку
    }
    // 419 - CSRF токен истек
    if (error.response?.status === 419) {
      console.log('🔄 CSRF token expired, refreshing...')
      try {
        // Получаем новый CSRF токен
        await fetch('/sanctum/csrf-cookie', {
          method: 'GET',
          credentials: 'include',
          headers: {
            'Accept': 'application/json'
          }
        })
        
        // Обновляем токен в meta теге
        const metaToken = document.querySelector('meta[name="csrf-token"]')
        if (metaToken) {
          const cookies = document.cookie.split(';')
          for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=')
            if (name === 'XSRF-TOKEN') {
              const token = decodeURIComponent(value)
              metaToken.setAttribute('content', token)
              break
            }
          }
        }
        
        // Повторяем запрос с новым токеном (только один раз)
        if (originalRequest && !originalRequest._retry) {
          originalRequest._retry = true
          const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          if (token) {
            originalRequest.headers['X-CSRF-TOKEN'] = token
            return instance.request(originalRequest)
          }
        }
      } catch (e) {
        console.error('Failed to refresh CSRF token:', e)
      }
    }
    return Promise.reject(error)
  }
)

export default instance