import axios from 'axios'

// Создаем экземпляр с базовыми настройками
const instance = axios.create({
  baseURL: '/',
  withCredentials: true, // ОБЯЗАТЕЛЬНО для cookies и сессий
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json'
  }
})

// Флаг чтобы избежать множественных одновременных запросов CSRF
let isFetchingCSRF = false
let csrfRequests = []

// Функция для гарантированного получения CSRF токена
const ensureCSRFToken = () => {
  return new Promise(async (resolve, reject) => {
    // Если уже получаем токен, ждем
    if (isFetchingCSRF) {
      csrfRequests.push({ resolve, reject })
      return
    }

    isFetchingCSRF = true

    try {
      // Проверяем есть ли уже токен
      let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      
      if (token) {
        console.log('🛡️ CSRF token already exists')
        isFetchingCSRF = false
        resolve(token)
        return
      }

      console.log('🛡️ Fetching CSRF token...')
      
      // Делаем запрос за CSRF cookie
      await instance.get('/sanctum/csrf-cookie')
      
      // Ждем немного чтобы cookie установились
      await new Promise(resolve => setTimeout(resolve, 100))
      
      // Получаем токен из meta тега
      token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      
      if (token) {
        console.log('🛡️ CSRF token received successfully')
        resolve(token)
      } else {
        console.error('❌ CSRF token not found after request')
        reject(new Error('CSRF token not available'))
      }
      
    } catch (error) {
      console.error('❌ Failed to get CSRF token:', error)
      reject(error)
    } finally {
      isFetchingCSRF = false
      // Разрешаем все ожидающие запросы
      csrfRequests.forEach(({ resolve, reject }) => {
        reject(new Error('CSRF fetch failed'))
      })
      csrfRequests = []
    }
  })
}

// Интерцептор для автоматического добавления CSRF токена
instance.interceptors.request.use(async (config) => {
  console.log(`🚀 ${config.method?.toUpperCase()} ${config.url}`)
  
  // Для модифицирующих запросов добавляем CSRF токен
  if (['post', 'put', 'delete', 'patch'].includes(config.method?.toLowerCase())) {
    try {
      const token = await ensureCSRFToken()
      if (token) {
        config.headers['X-CSRF-TOKEN'] = token
        console.log('🛡️ CSRF token added to request')
      }
    } catch (error) {
      console.error('❌ Cannot proceed without CSRF token')
      throw error
    }
  }
  
  return config
})

// Интерцептор для обработки ответов
instance.interceptors.response.use(
  (response) => {
    console.log('✅ Response success:', response.status)
    return response
  },
  async (error) => {
    const originalRequest = error.config
    
    console.error('❌ Response error:', error.response?.status, error.response?.data)
    
    // Если CSRF токен устарел (419) - пробуем обновить и повторить запрос
    if (error.response?.status === 419 && !originalRequest._retry) {
      console.log('🔄 CSRF token expired, refreshing...')
      originalRequest._retry = true
      
      try {
        // Сбрасываем токен и получаем новый
        const token = await ensureCSRFToken()
        if (token) {
          originalRequest.headers['X-CSRF-TOKEN'] = token
          return instance(originalRequest)
        }
      } catch (csrfError) {
        console.error('❌ Failed to refresh CSRF token')
      }
    }
    
    // Если не авторизован (401)
    if (error.response?.status === 401) {
      console.log('🔐 Unauthorized - redirecting to login')
      
      // Очищаем данные авторизации
      localStorage.removeItem('authUser')
      sessionStorage.clear()
      
      // Показываем сообщение
      if (!window.location.pathname.includes('/login')) {
        alert('Сессия истекла. Пожалуйста, войдите заново.')
        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname)
      }
    }
    
    return Promise.reject(error)
  }
)

export default instance