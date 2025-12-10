<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <h1>Регистрация</h1>
        
        <div class="form-group">
          <label>Имя</label>
          <input v-model="form.name" type="text" required>
        </div>
        <div class="form-group">
          <label>Электронная почта</label>
          <input v-model="form.email" type="email" required>
        </div>
        <div class="form-group">
          <label>Пароль</label>
          <input v-model="form.password" type="password" required>
        </div>
        <div class="form-group">
          <label>Подтвердить пароль</label>
          <input v-model="form.password_confirmation" type="password" required>
        </div>
        
        <button type="button" @click="register" class="btn btn-primary" :disabled="loading">
          {{ loading ? 'Регистрация...' : 'Зарегистрироваться' }}
        </button>
        <button type="button" @click="registerWithYandex" class="btn btn-warning text-dark">
          Зарегистрироваться через Яндекс
        </button>
        
        <p class="auth-link">
          Уже есть аккаунт? <a href="#" @click.prevent="goToLogin">Войдите</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Register',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      },
      loading: false
    }
  },
  methods: {
    async register() {
      this.loading = true
      console.log('Register attempt:', this.form)
      
      try {
        const response = await axios.post('/register', this.form)
        console.log('Register response:', response.data)
        
        if (response.data.success) {
          localStorage.setItem('authUser', JSON.stringify(response.data.user))
          
          window.dispatchEvent(new CustomEvent('authStateChanged', { 
            detail: { user: response.data.user } 
          }))
          
          this.$router.push('/')
        } else {
          alert('Ошибка регистрации: ' + (response.data.message || 'Проверьте введенные данные'))
        }
      } catch (err) {
        console.error('Register error:', err)
        alert('Ошибка регистрации: ' + (err.response?.data?.message || 'Проверьте введенные данные'))
      } finally {
        this.loading = false
      }
    },
    
    goToLogin() {
      this.$router.push('/login')
    },
    registerWithYandex() {
      window.location.href = '/auth/yandex'
    }
  }
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