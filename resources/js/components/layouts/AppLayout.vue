<template>
  <div class="app-layout">
    <!-- Шапка показывается везде кроме логина и регистрации -->
    <header v-if="showHeader" class="header">
      <div class="header-inner">
        <div class="logo" @click="$router.push('/')">
          <div class="logo-icon">
            <img src="/images/logo-icon.png" alt="SweetHome" class="logo-image">
          </div>
          <span class="logo-text">SweetHome</span>
        </div>
        
        <div class="header-buttons">
          <template v-if="!authUser">
            <button type="button" @click="goToLogin" class="btn btn-login">Вход</button>
            <button type="button" @click="goToRegister" class="btn btn-register">Регистрация</button>
          </template>
          <template v-else>
            <button 
              v-if="authUser.role === 'admin'"
              @click="goToAdmin"
              class="btn btn-admin"
            >
              Панель администратора
            </button>
            <button 
              v-else
              @click="goToCabinet"
              class="btn btn-cabinet"
            >
              Личный кабинет
            </button>
            <button type="button" @click="logout" class="btn btn-logout" :disabled="loggingOut">
              {{ loggingOut ? 'Выход...' : 'Выход' }}
            </button>
          </template>
        </div>
      </div>
    </header>

    <!-- Основной контент -->
    <main class="main-content">
      <router-view></router-view>
    </main>

    <!-- Подвал показывается везде кроме логина и регистрации -->
    <footer v-if="showFooter" class="footer">
      <div class="footer-content">
        <div class="footer-section">
          <h3>О компании</h3>
          <p>SweetHome - сервис аренды апартаментов</p>
          <p>Лучшие предложения в вашем городе</p>
        </div>
        <div class="footer-section">
          <h3>Контакты</h3>
          <p>Email: info@sweethome.ru</p>
          <p>Телефон: +7 (999) 123-45-67</p>
          <p>График работы: 24/7</p>
        </div>
        <div class="footer-section">
          <h3>Помощь</h3>
          <p>Поддержка клиентов</p>
          <p>Частые вопросы</p>
          <p>Условия аренды</p>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2024 SweetHome. Все права защищены.</p>
      </div>
    </footer>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AppLayout',
  data() {
    return {
      authUser: null,
      showHeader: true,
      showFooter: true,
      loggingOut: false
    }
  },
  mounted() {
    this.loadAuthUser()
    this.updateVisibility()
    
    this.checkLogoutParam()
    // Слушаем события изменения авторизации
    window.addEventListener('authStateChanged', this.handleAuthChange)
  },
  beforeUnmount() {
    window.removeEventListener('authStateChanged', this.handleAuthChange)
  },
  watch: {
    '$route'(to, from) {
      this.updateVisibility()
      this.checkRouteAccess()
    }
  },
  methods: {
    handleAuthChange(event) {
      console.log('Auth state changed from event:', event.detail)
      this.authUser = event.detail.user
      this.checkRouteAccess()
    },

    checkLogoutParam() {
    const urlParams = new URLSearchParams(window.location.search)
    if (urlParams.has('logout')) {
      console.log('🔍 Обнаружен параметр выхода, принудительная очистка...')
      this.authUser = null
      localStorage.removeItem('authUser')
      sessionStorage.clear()
      
      // Убираем параметр из URL
      const newUrl = window.location.pathname
      window.history.replaceState({}, document.title, newUrl)
      
      // Дополнительная проверка с сервером
      this.forceAuthCheck()
    }
  },
    
    updateVisibility() {
      const noLayoutPaths = ['/login', '/register']
      const currentPath = this.$route.path
      
      this.showHeader = !noLayoutPaths.includes(currentPath)
      this.showFooter = !noLayoutPaths.includes(currentPath)
    },
    
    checkRouteAccess() {
  // Только если пользователь явно пытается зайти на защищенную страницу
  const protectedRoutes = ['/user', '/admin']
  const currentRoute = this.$route.path
  
  const isProtectedRoute = protectedRoutes.some(route => 
    currentRoute.startsWith(route)
  )
  
  // Если пользователь не авторизован и находится на защищенных маршрутах
  if (!this.authUser && isProtectedRoute) {
    console.log('🚫 Доступ запрещен, перенаправление на вход')
    this.$router.push('/login')
  }
  
  // Если пользователь авторизован и находится на логине/регистрации
  if (this.authUser && (currentRoute === '/login' || currentRoute === '/register')) {
    console.log('🔙 Пользователь авторизован, перенаправление на главную')
    this.$router.push('/')
  }
},

async forceAuthCheck() {
    try {
      const response = await axios.get('/api/check-auth')
      if (!response.data.user && this.authUser) {
        console.log('⚠️  Расхождение: сервер говорит что пользователя нет, но клиент думает иначе')
        this.authUser = null
        localStorage.removeItem('authUser')
        window.dispatchEvent(new CustomEvent('authStateChanged', { 
          detail: { user: null } 
        }))
      }
    } catch (error) {
      console.error('Ошибка принудительной проверки:', error)
    }
  },
    
    async loadAuthUser() {
  try {
    console.log('🔄 Загрузка пользователя...')
    const response = await axios.get('/api/check-auth')
    console.log('📡 Ответ от API:', response.data)
    this.authUser = response.data.user
    
    // НЕ делаем автоматический редирект здесь
    // Проверка доступа будет в отдельных методах
    
  } catch (error) {
    console.error('❌ Ошибка загрузки пользователя:', error)
    this.authUser = null
  }
},
    
  async logout() {
  if (this.loggingOut) return
  
  this.loggingOut = true
  console.log('🚪 Выход из аккаунта...')
  
  try {
    // Очищаем данные
    this.authUser = null
    localStorage.removeItem('authUser')
    
    // Отправляем запрос на сервер
    await axios.post('/logout')
    
    // Перезагружаем страницу
    window.location.href = '/'
    
  } catch (error) {
    console.error('Logout error:', error)
    // Все равно перезагружаем
    window.location.href = '/'
  }
},
    
    goToLogin() {
      this.$router.push('/login')
    },
    
    goToRegister() {
      this.$router.push('/register')
    },
    
    goToCabinet() {
      if (this.authUser) {
        this.$router.push('/user')
      } else {
        this.$router.push('/login')
      }
    },
    
    async goToAdmin() {
  console.log('🔄 Попытка перехода в админку...')
  
  // Сначала обновляем данные пользователя
  await this.loadAuthUser()
  console.log('👤 Текущий пользователь:', this.authUser)
  
  if (this.authUser?.role === 'admin') {
    console.log('✅ Доступ разрешен, переход в админку')
    this.$router.push('/admin')
  } else {
    console.log('❌ Доступ запрещен. Роль:', this.authUser?.role)
    alert('Доступ запрещен. Только для администраторов.')
    this.$router.push('/')
  }
}
  }
}
</script>

<style scoped>
/* Стили остаются без изменений */
.app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.header {
  height: 100px;
  background: #AFAFAF;
  border-bottom: 1px solid #E0E0E0;
  display: flex;
  align-items: center;
  width: 100%;
}

.header-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 0 30px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 18px;
  font-weight: bold;
  cursor: pointer;
  color: #000;
}

.logo:hover {
  opacity: 0.8;
}

.logo-icon {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.header-buttons {
  display: flex;
  gap: 10px;
  align-items: center;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s;
  min-width: 100px;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-login {
  background: transparent;
  color: #000;
  border: 2px solid #E0E0E0;
}

.btn-login:hover {
  background: rgba(0,0,0,0.05);
  border-color: var(--accent);
}

.btn-register {
  background: var(--accent);
  color: #000;
}

.btn-register:hover {
  background: #B8BBE0;
  transform: translateY(-2px);
}

.btn-admin,
.btn-cabinet {
  background: var(--accent);
  color: #000;
}

.btn-admin:hover,
.btn-cabinet:hover {
  background: #B8BBE0;
  transform: translateY(-2px);
}

.btn-logout {
  background: transparent;
  color: #000;
  border: 2px solid #E0E0E0;
}

.btn-logout:hover {
  background: rgba(0,0,0,0.05);
  border-color: #FF6B6B;
}

.main-content {
  flex: 1;
}

.footer {
  background: #AFAFAF;
  padding: 30px 20px 15px;
  margin-top: auto;
  border-top: 1px solid #E0E0E0;
  width: 100%;
}

.footer-content {
  max-width: 1400px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 15px;
}

.footer-section h3 {
  font-size: 14px;
  margin-bottom: 12px;
  color: #000;
  font-weight: 600;
}

.footer-section p {
  font-size: 12px;
  color: #666;
  margin-bottom: 6px;
  line-height: 1.4;
}

.footer-bottom {
  max-width: 1400px;
  margin: 0 auto;
  padding-top: 15px;
  border-top: 1px solid #E0E0E0;
  text-align: center;
}

.footer-bottom p {
  font-size: 11px;
  color: #999;
}
</style>