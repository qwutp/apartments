<template>
  <div class="checkout">
    <div class="checkout-container">
      <div class="left">
        <div class="apartment-info">
          <img :src="apartment.images && apartment.images[0] ? apartment.images[0].url : ''" :alt="apartment.name">
          <div>
            <h3>{{ apartment.name }}</h3>
            <p>{{ apartment.address }}</p>
            <p class="price">{{ formatPrice(apartment.price_per_night || apartment.price) }} за ночь</p>
          </div>
        </div>

        <div class="booking-dates">
          <h3>Даты бронирования</h3>
          <div class="date-fields">
            <div class="date-field">
              <label>Дата заезда</label>
              <input type="date" v-model="checkIn">
            </div>
            <div class="date-field">
              <label>Дата выезда</label>
              <input type="date" v-model="checkOut">
            </div>
          </div>
          <div class="guests-field">
            <label>Количество гостей</label>
            <input type="number" v-model="guests" min="1">
          </div>
        </div>

        <div class="personal-info">
          <h3>Личная информация</h3>
          <div class="form-row">
            <input v-model="form.first_name" placeholder="Имя">
            <input v-model="form.last_name" placeholder="Фамилия">
            <input v-model="form.patronymic" placeholder="Отчество">
          </div>
          <div class="form-row">
            <input v-model="form.passport_series" placeholder="Серия паспорта">
            <input v-model="form.passport_number" placeholder="Номер паспорта">
          </div>
          <div class="form-row">
            <input v-model="form.phone" placeholder="Номер телефона">
            <input v-model="form.email" placeholder="Электронная почта">
          </div>
        </div>
      </div>

      <div class="right">
        <div class="booking-panel">
          <div class="total">
            <span>{{ formatPrice(totalAmount) }} х {{ nightsCount }} ночей</span>
          </div>
          <button @click="confirmBooking" class="btn btn-primary" :disabled="loading">
            {{ loading ? 'Создаем бронирование...' : 'Перейти к оплате' }}
          </button>
          <p class="disclaimer">Подтверждая бронирование Вы соглашайтесь с условиями бронирования</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../axios.js'

export default {
  data() {
    return {
      apartment: { images: [] },
      checkIn: '',
      checkOut: '',
      guests: 1,
      form: {
        first_name: '',
        last_name: '',
        patronymic: '',
        passport_series: '',
        passport_number: '',
        phone: '',
        email: ''
      },
      loading: false
    }
  },
  computed: {
    nightsCount() {
      if (!this.checkIn || !this.checkOut) return 0
      const start = new Date(this.checkIn)
      const end = new Date(this.checkOut)
      const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24))
      return Math.max(diff, 1)
    },
    totalAmount() {
      if (!this.checkIn || !this.checkOut) return 0
      return this.nightsCount * (this.apartment.price_per_night || this.apartment.price || 0)
    }
  },
  methods: {
    formatPrice(price) {
      if (!price) return '0 ₽'
      return new Intl.NumberFormat('ru-RU').format(price) + ' ₽'
    },
    async confirmBooking() {
      if (!this.checkIn || !this.checkOut) {
        alert('Пожалуйста, выберите даты заезда и выезда')
        return
      }

      if (this.guests < 1) {
        alert('Количество гостей должно быть не менее 1')
        return
      }

      this.loading = true

      try {
        try {
          await axios.get('/sanctum/csrf-cookie')
        } catch (csrfError) {
          console.warn('CSRF cookie error before booking:', csrfError)
        }
        const response = await axios.post('/api/bookings', {
          apartment_id: this.apartment.id,
          check_in: this.checkIn,
          check_out: this.checkOut,
          guests: Number(this.guests),
          first_name: this.form.first_name,
          last_name: this.form.last_name,
          patronymic: this.form.patronymic,
          passport_series: this.form.passport_series,
          passport_number: this.form.passport_number,
          phone: this.form.phone,
          email: this.form.email
        })
        
        if (response.data?.booking?.id) {
          this.$router.push({ name: 'payment-confirm', params: { id: response.data.booking.id } })
        } else {
          alert('Не удалось создать бронирование. Попробуйте позже.')
        }
      } catch (error) {
        console.error('Booking error:', error)
        if (error.response?.status === 401) {
          alert('Пожалуйста, войдите в систему, чтобы продолжить бронирование.')
          this.$router.push('/login')
          return
        }
        const message = error.response?.data?.message || 'Ошибка при создании бронирования'
        alert(message)
      }
      finally {
        this.loading = false
      }
    }
  },
  async mounted() {
    const apartmentId = new URLSearchParams(window.location.search).get('apartment')
    const checkIn = new URLSearchParams(window.location.search).get('check_in')
    const checkOut = new URLSearchParams(window.location.search).get('check_out')
    const guests = new URLSearchParams(window.location.search).get('guests')
    if (!apartmentId) {
      alert('Не удалось определить апартаменты для бронирования')
      this.$router.push('/')
      return
    }
    
    if (checkIn) this.checkIn = checkIn
    if (checkOut) this.checkOut = checkOut
    if (guests) this.guests = parseInt(guests) || 1
    
    try {
      const response = await axios.get(`/api/apartments/${apartmentId}`)
      this.apartment = response.data
    } catch (error) {
      console.error('Error fetching apartment:', error)
    }
    
    // Load user profile data
    try {
      const profileResponse = await axios.get('/api/profile')
      if (profileResponse.data.user) {
        const user = profileResponse.data.user
        this.form = {
          first_name: user.first_name || '',
          last_name: user.last_name || '',
          patronymic: user.patronymic || '',
          passport_series: user.passport_series || '',
          passport_number: user.passport_number || '',
          phone: user.phone || '',
          email: user.email || ''
        }
      }
    } catch (error) {
      console.error('Error loading profile:', error)
    }
  }
}
</script>

<style scoped>
.checkout {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px 60px;
}

.checkout-container {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 30px;
}

.apartment-info {
  display: flex;
  gap: 15px;
  padding: 20px;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  margin-bottom: 20px;
}

.apartment-info img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 4px;
}

.apartment-info h3 {
  font-size: 16px;
  margin-bottom: 5px;
}

.apartment-info p {
  font-size: 12px;
  color: #666;
}

.price {
  color: var(--accent);
  font-weight: bold;
}

.booking-dates,
.personal-info {
  padding: 20px;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  margin-bottom: 20px;
}

.booking-dates h3,
.personal-info h3 {
  margin-bottom: 15px;
}

.date-fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 15px;
}

.date-field,
.guests-field {
  display: flex;
  flex-direction: column;
}

.date-field label,
.guests-field label {
  font-size: 12px;
  margin-bottom: 5px;
  color: #666;
}

input {
  padding: 10px;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 10px;
  margin-bottom: 10px;
}

.booking-panel {
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  padding: 20px;
  position: sticky;
  top: 20px;
}

.total {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
  font-weight: bold;
}

.amount {
  color: var(--accent);
  font-size: 18px;
}

.disclaimer {
  font-size: 11px;
  color: #999;
  margin-top: 15px;
  text-align: center;
  line-height: 1.4;
}

.btn {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Unbounded', sans-serif;
  font-size: 14px;
}

.btn-primary {
  background: var(--accent);
  color: #000;
}

@media (max-width: 1024px) {
  .checkout-container {
    grid-template-columns: 1fr;
  }
}
</style>
