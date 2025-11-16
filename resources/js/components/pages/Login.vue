<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <h1>Вход</h1>
        
        <div class="form-group">
          <label>Электронная почта</label>
          <input v-model="form.email" type="email" required>
        </div>
        <div class="form-group">
          <label>Пароль</label>
          <input v-model="form.password" type="password" required>
        </div>
        
        <button type="button" @click="login" class="btn btn-primary" :disabled="loading">
          {{ loading ? 'Вход...' : 'Войти' }}
        </button>
        
        <p class="auth-link">
          Нет аккаунта? <a href="#" @click.prevent="goToRegister">Зарегистрируйтесь</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Login',
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      loading: false
    }
  },
  async mounted() {
    // Предварительно получаем CSRF токен
    try {
      await axios.get('/sanctum/csrf-cookie')
    } catch (error) {
      console.log('CSRF token pre-loaded')
    }
  },
  methods: {
    async login() {
      this.loading = true
      console.log('Login attempt:', this.form)
      
      try {
        const response = await axios.post('/login', this.form)
        console.log('Login response:', response.data)
        
        if (response.data.success) {
          // Сохраняем пользователя в localStorage
          localStorage.setItem('authUser', JSON.stringify(response.data.user))
          
          // Уведомляем о успешной авторизации
          window.dispatchEvent(new CustomEvent('authStateChanged', { 
            detail: { user: response.data.user } 
          }))
          
          // Небольшая задержка перед переходом, чтобы состояние успело обновиться
          await new Promise(resolve => setTimeout(resolve, 100))
          
          this.$router.push('/')
        } else {
          alert('Ошибка входа: ' + (response.data.message || 'Неверные учетные данные'))
        }
      } catch (err) {
        console.error('Login error:', err)
        alert('Ошибка входа: ' + (err.response?.data?.message || 'Проверьте введенные данные'))
      } finally {
        this.loading = false
      }
    },

    
    
    goToRegister() {
      this.$router.push('/register')
    }
  },
}
</script>

<style scoped>
.auth-page {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}

.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}

.auth-card {
  width: 100%;
  max-width: 400px;
  padding: 40px;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  background: white;
  box-shadow: 0 2px 20px rgba(0,0,0,0.1);
}

.auth-card h1 {
  text-align: center;
  margin-bottom: 30px;
  font-size: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 12px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
}

.btn {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
  font-weight: bold;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--accent);
  color: #000;
}

.auth-link {
  text-align: center;
  margin-top: 20px;
  font-size: 13px;
}

.auth-link a {
  color: var(--accent);
  text-decoration: none;
  font-weight: bold;
  cursor: pointer;
}
</style>