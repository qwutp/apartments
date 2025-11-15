<template>
  <div class="profile">
    <div class="profile-section">
      <h2>Личная информация</h2>
      <form @submit.prevent="updateProfile">
        <div class="form-group">
          <label>Фамилия</label>
          <input v-model="form.last_name" placeholder="Введите фамилию" required>
        </div>
        <div class="form-group">
          <label>Имя</label>
          <input v-model="form.first_name" placeholder="Введите имя" required>
        </div>
        <div class="form-group">
          <label>Отчество</label>
          <input v-model="form.patronymic" placeholder="Введите отчество" required>
        </div>
        <div class="form-group">
          <label>Серия и номер паспорта</label>
          <input 
            v-model="passportFull" 
            placeholder="Введите серию и номер паспорта" 
            required
            maxlength="10"
            @input="handlePassportInput"
          >
        </div>
        <div class="form-group">
          <label>Номер телефона</label>
          <input 
            v-model="form.phone" 
            placeholder="Введите номер телефона" 
            required
            maxlength="11"
            pattern="[0-9]{11}"
          >
        </div>
        <div class="form-group">
          <label>Электронная почта</label>
          <input 
            v-model="form.email" 
            placeholder="Введите электронную почту" 
            type="email"
            required
          >
        </div>
        <button type="submit" class="btn btn-primary" :disabled="loading">
          {{ loading ? 'Сохранение...' : 'Сохранить личные данные' }}
        </button>
      </form>
    </div>

    <div class="profile-section">
      <h2>Изменение пароля</h2>
      <form @submit.prevent="changePassword">
        <div class="form-group">
          <label>Старый пароль</label>
          <input 
            type="password" 
            v-model="passwordForm.current_password" 
            placeholder="Введите старый пароль"
            required
            autocomplete="current-password"
          >
        </div>
        <div class="form-group">
          <label>Новый пароль</label>
          <input 
            type="password" 
            v-model="passwordForm.password" 
            placeholder="Введите новый пароль"
            required
            minlength="8"
            autocomplete="new-password"
          >
        </div>
        <div class="form-group">
          <label>Повторите новый пароль</label>
          <input 
            type="password" 
            v-model="passwordForm.password_confirmation" 
            placeholder="Повторите новый пароль"
            required
            autocomplete="new-password"
          >
        </div>
        <button type="submit" class="btn btn-primary" :disabled="passwordLoading">
          {{ passwordLoading ? 'Изменение...' : 'Изменить пароль' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        patronymic: '',
        passport_series: '',
        passport_number: '',
        phone: '',
        email: ''
      },
      passportFull: '',
      passwordForm: {
        current_password: '',
        password: '',
        password_confirmation: ''
      },
      errors: {},
      loading: false,
      passwordLoading: false
    }
  },
  mounted() {
    this.loadUserData()
  },
  methods: {
    async loadUserData() {
      try {
        const response = await axios.get('/profile')
        if (response.data.user) {
          this.form = { ...response.data.user }
          this.passportFull = (this.form.passport_series || '') + (this.form.passport_number || '')
        }
      } catch (error) {
        console.error('Ошибка загрузки данных:', error)
      }
    },
    handlePassportInput(event) {
      const value = event.target.value.replace(/\D/g, '')
      if (value.length <= 10) {
        this.passportFull = value
        this.form.passport_series = value.substring(0, 4)
        this.form.passport_number = value.substring(4, 10)
      }
    },

    validateForm() {
      this.errors = {}

      if (this.form.passport_series && !/^\d{4}$/.test(this.form.passport_series)) {
        this.errors.passport_series = 'Серия паспорта должна содержать 4 цифры'
      }

      if (this.form.passport_number && !/^\d{6}$/.test(this.form.passport_number)) {
        this.errors.passport_number = 'Номер паспорта должен содержать 6 цифр'
      }

      if (this.form.phone && !/^\d{11}$/.test(this.form.phone)) {
        this.errors.phone = 'Телефон должен содержать 11 цифр'
      }

      if (this.form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
        this.errors.email = 'Введите корректный email'
      }

      return Object.keys(this.errors).length === 0
    },

    async updateProfile() {
      if (!this.validateForm()) {
        alert('Пожалуйста, исправьте ошибки в форме')
        return
      }

      this.loading = true
      this.errors = {}

      try {
        const response = await axios.post('/profile', this.form)
        
        if (response.data.success) {
          alert('Данные успешно сохранены!')
          if (window.authUser) {
            window.authUser = { ...window.authUser, ...this.form }
          }
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors
          alert('Пожалуйста, исправьте ошибки в форме')
        } else {
          alert('Ошибка при сохранении данных: ' + (error.response?.data?.message || 'Неизвестная ошибка'))
        }
      } finally {
        this.loading = false
      }
    },

    async changePassword() {
      this.passwordLoading = true
      this.errors = {}

      try {
        const response = await axios.post('/profile/password', this.passwordForm)
        
        if (response.data.success) {
          alert('Пароль успешно изменен!')
          // Очищаем форму пароля
          this.passwordForm = {
            current_password: '',
            password: '',
            password_confirmation: ''
          }
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors
          alert('Пожалуйста, исправьте ошибки в форме')
        } else {
          alert('Ошибка при изменении пароля: ' + (error.response?.data?.message || 'Неизвестная ошибка'))
        }
      } finally {
        this.passwordLoading = false
      }
    }
  }
}
</script>

<style scoped>
.profile {
  padding: 20px;
}

.profile-section {
  margin-bottom: 40px;
  padding: 20px;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
}

.profile-section h2 {
  margin-bottom: 20px;
  font-size: 16px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

input {
  padding: 10px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
  background: white;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--accent);
  color: #000;
}
</style>