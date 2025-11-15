import axios from 'axios'

const instance = axios.create({
  baseURL: '/',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

// Глобальная переменная для CSRF токена
let csrfToken = null

// Функция для получения CSRF токена
async function getCSRFToken() {
  if (csrfToken) {
    return csrfToken
  }
  
  try {
    console.log('🛡️ Requesting CSRF token...')
    const response = await fetch('/sanctum/csrf-cookie', {
      method: 'GET',
      credentials: 'include'
    })
    
    if (response.ok) {
      csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      console.log('🛡️ CSRF token received:', csrfToken ? 'YES' : 'NO')
      return csrfToken
    }
  } catch (error) {
    console.error('❌ Failed to get CSRF token:', error)
  }
  
  return null
}

instance.interceptors.request.use(async (config) => {
  console.log(`🚀 ${config.method?.toUpperCase()} request to: ${config.url}`)
  
  // Для ВСЕХ запросов добавляем CSRF токен
  const token = await getCSRFToken()
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token
  }
  
  return config
})

instance.interceptors.response.use(
  (response) => {
    console.log('✅ Response received:', response.status)
    return response
  },
  (error) => {
    console.error('❌ Response error:', error.response?.status)
    
    if (error.response?.status === 401) {
      console.log('🔐 Unauthorized - clearing auth data')
      localStorage.removeItem('authUser')
      sessionStorage.clear()
      
      // Удаляем все cookies
      document.cookie.split(";").forEach(cookie => {
        const name = cookie.split("=")[0].trim()
        document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`
      })
      
      alert('Сессия истекла. Пожалуйста, войдите заново.')
      window.location.href = '/login'
    }
    
    if (error.response?.status === 419) {
      console.log('🔄 CSRF token expired')
      csrfToken = null // Сбрасываем токен
      alert('Сессия истекла. Пожалуйста, попробуйте еще раз.')
      window.location.reload()
    }
    
    return Promise.reject(error)
  }
)

export default instance